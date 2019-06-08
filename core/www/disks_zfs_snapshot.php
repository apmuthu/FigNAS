<?php
/*
	disks_zfs_snapshot.php

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

$sphere_scriptname = basename(__FILE__);
$sphere_scriptname_child = 'disks_zfs_snapshot_edit.php';
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = $sphere_header;
$sphere_notifier = 'zfssnapshot';
$sphere_notifier_processor = 'zfssnapshot_process_updatenotification';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_add = gtext('Add Snapshot');
$gt_record_mod = gtext('Edit Snapshot');
$gt_record_del = gtext('Snapshot is marked for deletion');
$gt_record_loc = gtext('Snapshot is locked');
$gt_record_unl = gtext('Snapshot is unlocked');
$gt_record_mai = gtext('Maintenance');
$gt_record_inf = gtext('Information');
$gt_selection_delete = gtext('Delete Selected Snapshots');
$gt_selection_delete_confirm = gtext('Do you want to delete selected snapshots?');
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png',
	'mup' => 'images/up.png',
	'mdn' => 'images/down.png'
];

function get_zfs_snapshots() {
	$result = [];
	$cmd = 'zfs list -pH -o name,used,creation -t snapshot 2>&1';
	mwexec2($cmd,$rawdata);
	foreach($rawdata as $line):
		$a = preg_split('/\t/',$line);
		$r = [];
		$name = $a[0];
		$r['snapshot'] = $name;
		// the following regex splits the snapshot name into
		// 1: [pool name]
		// 2: /[dataset name | volume name]
		// 3: [dataset name | volume name]
		// 4: [snapshot name]
		if(preg_match('/^([^\/\@]+)(\/([^\@]+))?\@(.*)$/', $name, $m)):
			$r['pool'] = $m[1];
			$r['name'] = $m[4];
			$r['path'] = $m[1].$m[2];
		else:
			$r['pool'] = 'unknown'; // XXX
			$r['name'] = 'unknown'; // XXX
			$r['path'] = $name;
		endif;
		$r['used'] = $a[1];
		$r['creation'] = $a[2];
		$result[] = $r;
	endforeach;
	return $result;
}
$a_snapshot = get_zfs_snapshots();

if(isset($_SESSION['filter_time'])):
	$filter_time = $_SESSION['filter_time'];
else:
	$filter_time = '1week';
endif;
$l_filter_time = [
    '1week' => sprintf(gettext('%d week'),1),
    '2weeks' => sprintf(gettext('%d weeks'),2),
    '30days' => sprintf(gettext('%d days'),30),
    '60days' => sprintf(gettext('%d days'),60),
    '90days' => sprintf(gettext('%d days'),90),
    '180days' => sprintf(gettext('%d days'),180),
    '0' => gettext('All')
];

function get_zfs_snapshots_filter($snapshots,$filter) {
	if($filter['time'] == 0):
		return $snapshots;
	else:
		$now = time() / 86400;
		$now *= 86400;
		$f_time = strtotime(sprintf('-%s',$filter['time']),$now);
		$result = [];
		foreach($snapshots as $snapshot):
			if($snapshot['creation'] >= $f_time):
				$result[] = $snapshot;
			endif;
		endforeach;
	endif;
	return $result;
}
$sphere_array = get_zfs_snapshots_filter($a_snapshot,['time' => $filter_time]);

if($_POST):
	if(isset($_POST['filter']) && $_POST['filter']):
		$_SESSION['filter_time'] = $_POST['filter_time'];
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['apply']) && $_POST['apply']):
		$ret = array('output' => [], 'retval' => 0);
		if(!file_exists($d_sysrebootreqd_path)):
			// Process notifications
			$ret = zfs_updatenotify_process($sphere_notifier, $sphere_notifier_processor);
		endif;
		$savemsg = get_std_save_message($ret['retval']);
		if($ret['retval'] == 0):
			updatenotify_delete($sphere_notifier);
			header($sphere_header);
			exit;
		endif;
		updatenotify_delete($sphere_notifier);
		$errormsg = implode("\n", $ret['output']);
	endif;
	if(isset($_POST['delete_selected_rows']) && $_POST['delete_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		foreach($checkbox_member_array as $checkbox_member_record):
			if(false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'snapshot'))):
				$identifier = serialize(['snapshot' => $checkbox_member_record, 'recursive' => false]);
				if(!isset($sphere_array[$index]['protected'])):
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $identifier);
					switch($mode_updatenotify):
						case UPDATENOTIFY_MODE_NEW:  
							updatenotify_clear($sphere_notifier, $identifier);
							updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY_CONFIG, $identifier);
							break;
						case UPDATENOTIFY_MODE_MODIFIED:
							updatenotify_clear($sphere_notifier, $identifier);
							updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $identifier);
							break;
						case UPDATENOTIFY_MODE_UNKNOWN:
							updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $identifier);
							break;
					endswitch;
				endif;
			endif;
		endforeach;
		header($sphere_header);
		exit;
	endif;
endif;

function zfssnapshot_process_updatenotification($mode, $data) {
	global $config;
	$ret = [
		'output' => [],
		'retval' => 0
	];
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
			$data = unserialize($data);
			$ret = zfs_snapshot_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$data = unserialize($data);
			$ret = zfs_snapshot_properties($data);
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$data = unserialize($data);
			$ret = zfs_snapshot_destroy($data);
			break;
	endswitch;
	return $ret;
}
$pgtitle = [gtext('Disks'), gtext('ZFS'), gtext('Snapshots'), gtext('Snapshot')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init action buttons
	$("#delete_selected_rows").click(function () {
		return confirm('<?=$gt_selection_delete_confirm;?>');
	});
	// Disable action buttons.
	disableactionbuttons(true);
	// Init toggle checkbox
	$("#togglemembers").click(function() {
		togglecheckboxesbyname(this, "<?=$checkbox_member_name;?>[]");
	});
	// Init member checkboxes
	$("input[name='<?=$checkbox_member_name;?>[]']").click(function() {
		controlactionbuttons(this, '<?=$checkbox_member_name;?>[]');
	});
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function disableactionbuttons(ab_disable) {
	$("#delete_selected_rows").prop("disabled", ab_disable);
}
function togglecheckboxesbyname(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
				if (a_trigger[i].checked) {
					ab_disable = false;
				}
			}
		}
	}
	if (ego.type == 'checkbox') { ego.checked = false; }
	disableactionbuttons(ab_disable);
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (a_trigger[i].checked) {
				ab_disable = false;
				break;
			}
		}
	}
	disableactionbuttons(ab_disable);
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshot'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_snapshot_clone.php',gettext('Clone'))->
			ins_tabnav_record('disks_zfs_snapshot_auto.php',gettext('Auto Snapshot'))->
			ins_tabnav_record('disks_zfs_snapshot_info.php',gettext('Information'));
$document->render();
?>
<form action="disks_zfs_snapshot.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists($sphere_notifier)):
		print_config_change_box();
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Filter'));
?>
		</thead>
		<tbody>
<?php
			html_combobox2('filter_time', gettext('Age'), $filter_time, $l_filter_time, '');
?>
		</tbody>
	</table>
	<div id="submit">
		<input type="submit" class="formbtn" id="filter" name="filter" value="<?=gtext('Apply Filter');?>"/>
	</div>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:35%">
			<col style="width:20%">
			<col style="width:10%">
			<col style="width:20%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('Overview'), 6);
?>
			<tr>
				<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></th>
				<th class="lhell"><?=sprintf('%1$s (%2$d/%3$d)', gtext('Path'), count($sphere_array), count($a_snapshot));?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Used');?></th>
				<th class="lhell"><?=gtext('Create Date');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($sphere_array as $sphere_record):
				$identifier = serialize(['snapshot' => $sphere_record['snapshot'], 'recursive'=> false]);
				$notificationmode = updatenotify_get_mode($sphere_notifier, $identifier);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$notprotected = !isset($sphere_record['protected']);
?>
				<tr>
					<td class="lcelc">
<?php
						if ($notdirty && $notprotected):
?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['snapshot'];?>" id="<?=$sphere_record['snapshot'];?>"/>
<?php
						else:
?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['snapshot'];?>" id="<?=$sphere_record['snapshot'];?>" disabled="disabled"/>
<?php
						endif;
?>
					</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['path']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['name']);?>&nbsp;</td>
					<td class="lcell"><?=format_bytes($sphere_record['used'],2,false,is_sidisksizevalues());?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars(get_datetime_locale($sphere_record['creation']));?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
<?php
								if($notdirty && $notprotected):
?>
									<a href="<?=$sphere_scriptname_child;?>?snapshot=<?=urlencode($sphere_record['snapshot']);?>"><img src="<?=$img_path['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>"  class="spin oneemhigh"/></a>
<?php
								else:
									if ($notprotected):
?>
										<img src="<?=$img_path['del'];?>" title="<?=$gt_record_del;?>" alt="<?=$gt_record_del;?>"/>
<?php
									else:
?>
										<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>"/>
<?php
									endif;
								endif;
?>
							</td>
							<td></td>
							<td>
								<a href="disks_zfs_snapshot_info.php?uuid=<?=urlencode($sphere_record['snapshot']);?>"><img src="<?=$g_img['inf'];?>" title="<?=$gt_record_inf?>" alt="<?=$gt_record_inf?>"/></a>
							</td>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="5"></td>
				<td class="lceadd">
					<a href="disks_zfs_snapshot_add.php"><img src="<?=$img_path['add'];?>" title="<?=$gt_record_add;?>" border="0" alt="<?=$gt_record_add;?>" class="spin oneemhigh"/></a>
				</td>
			</tr>
		</tfoot>
	</table>
	<div id="submit">
		<input name="delete_selected_rows" id="delete_selected_rows" type="submit" class="formbtn" value="<?=$gt_selection_delete;?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
