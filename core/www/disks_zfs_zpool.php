<?php
/*
	disks_zfs_zpool.php

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
require_once 'zfs.inc';
require_once 'co_sphere.php';

function disks_zfs_zpool_get_sphere() {
	global $config;
	
	$sphere = new co_sphere_grid('disks_zfs_zpool','php');
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	$sphere->get_inform()->set_basename($sphere->get_basename() . '_info');
	$sphere->set_notifier('zfspool');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->
		setmsg_sym_add(gettext('Add Pool'))->
		setmsg_sym_mod(gettext('Edit Pool'))->
		setmsg_sym_inf(gettext('Pool Information'))->
		setmsg_sym_del(gettext('Pool is marked for deletion'))->
		setmsg_sym_loc(gettext('Pool is protected'))->
		setmsg_sym_unl(gettext('Pool is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected Pools'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected pools?'));
	$sphere->grid = &array_make_branch($config,'zfs','pools','pool');
	return $sphere;
}
function zfspool_process_updatenotification($mode,$data) {
	global $g;
	
	$retval = 0;
	$sphere = &disks_zfs_zpool_get_sphere();
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			$retval |= zfs_zpool_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= zfs_zpool_properties($data);
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			$sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier());
			if(false !== $sphere->row_id):
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier());
			if(false !== $sphere->row_id):
				$sphere->row = $sphere->grid[$sphere->row_id];
				//	check if pool exists
				$a_pools = [];
				if(array_key_exists('name',$sphere->row)):
					$a_pools = cli_zpool_info($sphere->row['name'],'name');
				endif;
				if(0 < count($a_pools)):
					//	delete pool
					$retval |= zfs_zpool_destroy($data);
					if($retval === 0):
						unset($sphere->grid[$sphere->row_id]);
						write_config();
						//	remove existing pool cache
						conf_mount_rw();
						unlink_if_exists(sprintf('%s/boot/zfs/zpool.cache',$g['cf_path']));
						conf_mount_ro();
					endif;
				else:
					//	delete orphaned config record
					unset($sphere->grid[$sphere->row_id]);
					write_config();
				endif;
			endif;
			break;
	endswitch;
	return $retval;
}
$sphere = &disks_zfs_zpool_get_sphere();
if(empty($sphere->grid)):
else:
	array_sort_key($sphere->grid,'name');
endif;
if($_POST):
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
//		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
			$savemsg = get_std_save_message($retval);
			if($retval == 0):
				updatenotify_delete($sphere->get_notifier());
			endif;
			header($sphere->get_location());
			exit;
//		endif;
	endif;
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case $sphere->get_cbm_button_val_delete():
				$sphere->cbm_grid = $_POST[$sphere->get_cbm_name()] ?? [];
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->get_row_identifier()))):
						$mode_updatenotify = updatenotify_get_mode($sphere->get_notifier(),$sphere->grid[$sphere->row_id][$sphere->get_row_identifier()]);
						switch($mode_updatenotify):
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
endif;
$sphere_addon_grid = zfs_get_pool_list();
$showusedavail = isset($config['zfs']['settings']['showusedavail']);
$use_si = is_sidisksizevalues();
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Pools'),gtext('Management')];
include 'fbegin.inc';
echo $sphere->doj();
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_zpool_vdevice.php',gettext('Virtual Device'))->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Management'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_zpool_tools.php',gettext('Tools'))->
			ins_tabnav_record('disks_zfs_zpool_info.php',gettext('Information'))->
			ins_tabnav_record('disks_zfs_zpool_io.php',gettext('I/O Statistics'));
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists($sphere->get_notifier())):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%"> 
			<col style="width:15%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:6%">
			<col style="width:6%">
			<col style="width:6%">
			<col style="width:7%">
			<col style="width:15%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'),11);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
<?php
				if($showusedavail):
?>
					<th class="lhell"><?=gtext('Used');?></th>
					<th class="lhell"><?=gtext('Avail');?></th>
<?php
				else:
?>
					<th class="lhell"><?=gtext('Alloc');?></th>
					<th class="lhell"><?=gtext('Free');?></th>
<?php
				endif;
?>
				<th class="lhell"><?=gtext('Frag');?></th>
				<th class="lhell"><?=gtext('Capacity');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhell"><?=gtext('Health');?></th>
				<th class="lhell"><?=gtext('AltRoot');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($sphere->grid as $sphere->row):
				$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->row[$sphere->get_row_identifier()]);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$enabled = $sphere->is_enadis_enabled() ? isset($sphere->row['enable']) : true;
				$notprotected = $sphere->is_lock_enabled() ? !isset($sphere->row['protected']) : true;
				switch($notificationmode):
					case UPDATENOTIFY_MODE_NEW:
						$size = $used = $avail = $frag = $cap = $dedup = $health = $altroot = gtext('Initializing');
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						$size = $used = $avail = $frag = $cap = $dedup = $health = $altroot = gtext('Modifying');
						break;
					default:
						$size = $used = $avail = $frag = $cap = $dedup = $health = $altroot = gtext('Unknown');
						break;
				endswitch;
				if(is_array($sphere_addon_grid) && array_key_exists($sphere->row['name'],$sphere_addon_grid)):
					$sphere_addon_row = $sphere_addon_grid[$sphere->row['name']];
					if($showusedavail):
						$size = format_bytes($sphere_addon_row['used'] + $sphere_addon_row['avail'],2,false,$use_si);
						$used = format_bytes($sphere_addon_row['used'],2,false,$use_si);
						$avail = format_bytes($sphere_addon_row['avail'],2,false,$use_si);
					else:	
						$size = format_bytes($sphere_addon_row['size'],2,false,$use_si);
						$used = format_bytes($sphere_addon_row['alloc'],2,false,$use_si);
						$avail = format_bytes($sphere_addon_row['free'],2,false,$use_si);
					endif;
					$frag = $sphere_addon_row['frag'];
					$cap = sprintf('%d%%',$sphere_addon_row['cap']);
					$dedup = $sphere_addon_row['dedup'];
					$health = $sphere_addon_row['health'];
					$altroot = $sphere_addon_row['altroot'];
				endif;
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
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=isset($sphere->row['name']) ? htmlspecialchars($sphere->row['name']) : '';?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$size;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$used;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$avail;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$frag;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$cap;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$dedup;?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><a href="disks_zfs_zpool_info.php?pool=<?=$sphere->row['name']?>"><?=$health;?></a></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$altroot;?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><colgroup><col style="width:33%"><col style="width:34%"><col style="width:33%"></colgroup><tbody><tr>
<?php
							echo $sphere->html_toolbox($notprotected,$notdirty);
?>
							<td></td>
<?php
							echo $sphere->html_informbox();
?>
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
<?php
		if($sphere->is_enadis_enabled()):
			if($sphere->toggle()):
				echo $sphere->html_button_toggle_rows();
			else:
				echo $sphere->html_button_enable_rows();
				echo $sphere->html_button_disable_rows();
			endif;
		endif;
		echo $sphere->html_button_delete_rows();
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';