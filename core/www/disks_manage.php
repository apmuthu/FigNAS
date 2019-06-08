<?php
/*
	disks_manage.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies
	of XigmaNAS, either expressed or implied.
*/
require_once 'auth.inc';
require_once 'guiconfig.inc';
require_once 'co_sphere.php';

function device_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;
	$sphere = &disks_manage_get_sphere();
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
		case UPDATENOTIFY_MODE_DIRTY:
			if(false !== ($sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier()))):
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
function disks_manage_get_sphere() {
	global $config;
	$sphere = new co_sphere_grid('disks_manage','php');
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	$sphere->set_notifier('device');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->
		setmsg_sym_add(gettext('Add Disk'))->
		setmsg_sym_mod(gettext('Edit Disk'))->
		setmsg_sym_del(gettext('Disk is marked for deletion'))->
		setmsg_sym_loc(gettext('Disk is locked'))->
		setmsg_sym_unl(gettext('Disk is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected Disks'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected disks?') . '\n' . gettext('Any service using these disks might become invalid or inaccessible!'));
	$sphere->grid = &array_make_branch($config,'disks','disk');
	return $sphere;
}
$sphere = &disks_manage_get_sphere();
array_sort_key($sphere->grid,'name');
$gt_import_confirm = gettext('Do you want to import disks?') . '\n' . gettext('The existing configuration may be overwritten.');
$gt_importswraid_confirm = gettext('Do you want to import software RAID disks?') . '\n' . gettext('The existing configuration may be overwritten.');
if($_POST) {
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
			config_lock();
			$retval |= rc_update_service('ataidle');
			$retval |= rc_update_service('smartd');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete($sphere->get_notifier());
		endif;
		header($sphere->get_location());
		exit;
	endif;
	
	$clean_import = false;
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'import':
				$clean_import = isset($_POST['clearimport']);
				$retval = disks_import_all_disks($clean_import);
				if($retval == 0):
					$savemsg = gtext('No new disk found.');
				elseif($retval > 0):
					$savemsg = gtext('All disks are imported.');
				else:
					$input_errors[] = gtext('Detected an error while importing.');
				endif;
				if($retval >= 0):
					disks_update_mounts();
				endif;
//				header($sphere->get_location()));
//				exit;
				break;
			case 'importswraid':
				$clean_import = isset($_POST['clearimportswraid']);
				$retval = disks_import_all_swraid_disks($clean_import);
				if($retval == 0):
					$savemsg = gtext('No new software RAID disk found.');
				elseif($retval > 0):
					$savemsg = gtext('All software RAID disks are imported.');
				else:
					$input_errors[] = gtext('Detected an error while importing.');
				endif;
				if($retval >= 0):
					disks_update_mounts();
				endif;
//					header($sphere->get_location());
//					exit;
				break;
			case 'rescanbusses':
				$cmd = 'camcontrol rescan all';
				mwexec2($cmd,$rawdata);
				header($sphere->get_location());
				exit;
				break;
			case $sphere->get_cbm_button_val_delete():
				$sphere->cbm_grid = $_POST[$sphere->get_cbm_name()] ?? [];
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->get_row_identifier()))):
						$mode_updatenotify = updatenotify_get_mode($sphere->get_notifier(),$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
						switch ($mode_updatenotify):
							case UPDATENOTIFY_MODE_NEW:
								updatenotify_clear($sphere->get_notifier(),$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY_CONFIG,$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_MODIFIED:
								updatenotify_clear($sphere->get_notifier(),$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_UNKNOWN:
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
								break;
						endswitch;
					endif;
				endforeach;
				header($sphere->get_location());
				exit;
				break;
		endswitch;
	endif;
}
//	get all physical disks including CDROM.
$a_phy_disk = array_merge((array)get_physical_disks_list(),(array)get_cdrom_list());
//	make sure detected disks have the same ID in configuration.
$verify_errors = disks_verify_all_disks($a_phy_disk);
if(!empty($verify_errors)):
	$errormsg .= gtext('Configuration information about devices is different from physical devices. Please remove those devices and re-add them or run import disks with clear configuration option enabled.');
	$errormsg .= "<br />\n";
endif;
$pgtitle = [gtext('Disks'),gtext('Management'),gtext('HDD Management')];
include 'fbegin.inc';
echo $sphere->doj();
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("#button_import").click(function () {
		return confirm("<?=$gt_import_confirm?>");
	});
	$("#button_importswraid").click(function () {
		return confirm("<?=$gt_importswraid_confirm?>");
	});
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabact"><a href="disks_manage.php" title="<?=gtext('Reload page');?>"><span><?=gtext('HDD Management');?></span></a></li>
		<li class="tabinact"><a href="disks_init.php"><span><?=gtext('HDD Format');?></span></a></li>
		<li class="tabinact"><a href="disks_manage_smart.php"><span><?=gtext('S.M.A.R.T.');?></span></a></li>
		<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext('iSCSI Initiator');?></span></a></li>
  	</ul></td></tr>
</tbody></table>
<form action="<?=$sphere->get_scriptname();?>" method="post" id="iform" name="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(updatenotify_exists('device')):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:5%">
			<col style="width:13%">
			<col style="width:7%">
			<col style="width:13%">
			<col style="width:5%">
			<col style="width:16%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:8%">
			<col style="width:10%">
			
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('HDD Management'),11);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Device'); ?></th>
				<th class="lhell"><?=gtext('Device Model'); ?></th>
				<th class="lhell"><?=gtext('Size'); ?></th>
				<th class="lhell"><?=gtext('Serial Number'); ?></th>
				<th class="lhell"><?=gtext('Controller'); ?></th>
				<th class="lhell"><?=gtext('Controller Model'); ?></th>
				<th class="lhell"><?=gtext('Standby'); ?></th>
				<th class="lhell"><?=gtext('Filesystem'); ?></th>
				<th class="lhell"><?=gtext('Status'); ?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($sphere->grid as $sphere->row):
				$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->row[$sphere->get_row_identifier()]);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$enabled = $sphere->is_enadis_enabled() ? isset($sphere->row['enable']) : true;
				$notprotected = $sphere->is_lock_enabled() ? !isset($sphere->row['protected']) : true;
				switch ($notificationmode):
					case UPDATENOTIFY_MODE_NEW:
						$status = gtext('Initializing');
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						$status = gtext('Modifying');
						break;
					case UPDATENOTIFY_MODE_DIRTY_CONFIG:
					case UPDATENOTIFY_MODE_DIRTY:
						$status = gtext('Deleting');
						break;
					default:
						if($sphere->row['type'] == 'HAST'):
							$role = $a_phy_disk[$sphere->row['name']]['role'];
							$status = sprintf("%s (%s)", (0 == disks_exists($sphere->row['devicespecialfile'])) ? gtext('ONLINE') : gtext('MISSING'),$role);
							$sphere->row['size'] = $a_phy_disk[$sphere->row['name']]['size'];
						else:
							if(isset($verify_errors[$sphere->row['name']]) && is_array($verify_errors[$sphere->row['name']])):
								switch($verify_errors[$sphere->row['name']]['error']):
									case 1:
										$status = sprintf("%s : %s",gtext('MOVED TO') ,$verify_errors[$sphere->row['name']]['new_devicespecialfile']);
										break;
									case 2:
										if(empty($verify_errors[$sphere->row['name']]['old_serial']) === false):
											$old_serial = htmlspecialchars($verify_errors[$sphere->row['name']]['old_serial']);
										else:
											$old_serial = gtext('n/a');
										endif;
										if(empty($verify_errors[$sphere->row['name']]['new_serial']) === false):
											$new_serial = htmlspecialchars($verify_errors[$sphere->row['name']]['new_serial']);
										else:
											$new_serial = gtext('n/a');
										endif;
										$status = sprintf("%s (%s : '%s' %s '%s')",gtext('CHANGED'),gtext('Device Serial'),$old_serial,gtext('to'),$new_serial);
										break;
									case 4:
										$status = sprintf("%s (%s : '%s' %s '%s')",gtext('CHANGED'),gtext('Controller'),htmlspecialchars($verify_errors[$sphere->row['name']]['config_controller']),gtext('to'),htmlspecialchars($verify_errors[$sphere->row['name']]['new_controller']));
										break;
									case 8:
										$status = sprintf("%s (%s)",gtext('MISSING'),$sphere->row['devicespecialfile']);
										break;
									default:
										$status = gtext('ONLINE');
								endswitch;
							else:
								$status = gtext('ONLINE');
							endif;
						endif;
						break;
				endswitch;
?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
<?php
						if($notdirty && $notprotected):
							echo $sphere->html_checkbox_cbm(false);
						else:
							echo $sphere->html_checkbox_cbm(true);
						endif;
?>
					</td>
<?php
					$serial = empty($sphere->row['serial']) ?  gtext('n/a') : htmlspecialchars($sphere->row['serial']);
					$harddiskstandby = $sphere->row['harddiskstandby'] ? htmlspecialchars($sphere->row['harddiskstandby']) : gtext('Always On');
					$fstype =  empty($sphere->row['fstype']) ? gtext('Unknown or unformatted') : htmlspecialchars(get_fstype_shortdesc($sphere->row['fstype']));
?>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['name']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['model']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['size']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$serial;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['controller'] . $sphere->row['controller_id']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['controller_desc']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$harddiskstandby;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$fstype;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($status);?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><colgroup><col style="width:33%"><col style="width:34%"><col style="width:33%"></colgroup><tbody><tr>
<?php
							echo $sphere->html_toolbox($notprotected,$notdirty);
?>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
<?php
			echo $sphere->html_footer_add(11);
?>
		</tfoot>
	</table>
	<div id="submit">
		<?=$sphere->html_button_delete_rows();?>
		<?=html_button('rescanbusses',gettext('Rescan Busses'));?>
	</div>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('Import Disks'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('clearimport',gettext('Clear Configuration'),false,gettext('Clear configuration information before importing disks.'));
?>
		</tbody>
		<tfoot><tr><td colspan="2" class="lcenl">&nbsp;</td></tr></tfoot>
	</table>
	<div id="submit">
		<?=html_button('import',gettext('Import'));?>
	</div>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('Import Software RAID Disks'));
?>
		<tbody>
<?php
			html_checkbox2('clearimportswraid',gettext('Clear Configuration'),false,gettext('Clear configuration information before importing software RAID disks.'));
?>
		</tbody>
		<tfoot><tr><td colspan="2" class="lcenl">&nbsp;</td></tr></tfoot>
	</table>
	<div id="submit">
		<?=html_button('importswraid',gettext('Import'));?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
