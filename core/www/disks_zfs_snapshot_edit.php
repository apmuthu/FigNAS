<?php
/*
	disks_zfs_snapshot_edit.php

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

if(isset($_GET['uuid'])):
	$uuid = $_GET['uuid'];
endif;
if(isset($_POST['uuid'])):
	$uuid = $_POST['uuid'];
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Snapshot'),gtext('Edit')];
$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)):
else:
	array_sort_key($a_pool,'name');
endif;
function get_zfs_paths() {
	$result = [];
	mwexec2("zfs list -H -o name -t filesystem,volume 2>&1", $rawdata);
	foreach($rawdata as $line):
		$a = preg_split("/\t/", $line);
		$r = [];
		$name = $a[0];
		$r['path'] = $name;
		if(preg_match('/^([^\/\@]+)(\/([^\@]+))?$/', $name, $m)):
			$r['pool'] = $m[1];
		else:
			$r['pool'] = 'unknown'; // XXX
		endif;
		$result[] = $r;
	endforeach;
	return $result;
}
$a_path = get_zfs_paths();
if(!isset($uuid) && (!sizeof($a_pool))):
	$link = sprintf('<a href="%1$s">%2$s</a>', 'disks_zfs_zpool.php', gtext('pools'));
	$helpinghand = gtext('No configured pools.') . ' ' . gtext('Please add new %s first.');
	$helpinghand = sprintf($helpinghand, $link);
	$errormsg = $helpinghand;
endif;
if(isset($_GET['snapshot'])):
	$snapshot = $_GET['snapshot'];
endif;
if(isset($_POST['snapshot'])):
	$snapshot = $_POST['snapshot'];
endif;
$cnid = FALSE;
if(isset($snapshot) && !empty($snapshot)):
	$pconfig['uuid'] = uuid();
	$pconfig['snapshot'] = $snapshot;
	if(preg_match('/^([^\/\@]+)(\/([^\@]+))?\@(.*)$/', $pconfig['snapshot'], $m)):
		$pconfig['pool'] = $m[1];
		$pconfig['path'] = $m[1].$m[2];
		$pconfig['name'] = $m[4];
	else:
		$pconfig['pool'] = '';
		$pconfig['path'] = '';
		$pconfig['name'] = '';
	endif;
	$pconfig['newpath'] = '';
	$pconfig['newname'] = '';
	$pconfig['recursive'] = false;
	$pconfig['action'] = 'clone';
else:
	// not supported
	$pconfig = [];
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: disks_zfs_snapshot.php');
		exit;
	endif;
	if(isset($_POST['action'])):
		$action = $_POST['action'];
	endif;
	if(empty($action)):
		$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Action"));
	else:
		switch($action):
			case 'clone':
				// Input validation
				$reqdfields = ['newpath'];
				$reqdfieldsn = [gtext('Path')];
				$reqdfieldst = ['string'];
				do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
				do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
				if(preg_match("/(\\s|\\@|\\'|\\\")+/", $_POST['newpath'])):
					$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Path"));
				endif;
				if(empty($input_errors)):
					$snapshot = [];
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['pool'] = $_POST['pool'];
					$snapshot['path'] = $_POST['newpath'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$ret = zfs_snapshot_clone($snapshot);
					if($ret['retval'] == 0):
						header('Location: disks_zfs_snapshot.php');
						exit;
					endif;
					$errormsg = implode(PHP_EOL,$ret['output']);
				endif;
				break;
			case 'delete':
				// Input validation not required
				if(empty($input_errors)):
					$snapshot = [];
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$snapshot['recursive'] = isset($_POST['recursive']) ? true : false;
					$ret = zfs_snapshot_destroy($snapshot);
					if($ret['retval'] == 0):
						header('Location: disks_zfs_snapshot.php');
						exit;
					endif;
					$errormsg = implode(PHP_EOL,$ret['output']);
				endif;
				break;
			case 'rollback':
				// Input validation not required
				if(empty($input_errors)):
					$snapshot = [];
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$snapshot['force_delete'] = isset($_POST['force_delete']) ? true : false;
					$ret = zfs_snapshot_rollback($snapshot);
					if($ret['retval'] == 0):
						header('Location: disks_zfs_snapshot.php');
						exit;
					endif;
					$errormsg = implode(PHP_EOL,$ret['output']);
				endif;
				break;
			default:
				$input_errors[] = sprintf(gtext("The attribute '%s' is invalid."), 'action');
				break;
		endswitch;
	endif;
endif;
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function enable_change(enable_change) {
	document.iform.name.disabled = !enable_change;
}
function action_change() {
	showElementById('newpath_tr','hide');
	showElementById('recursive_tr','hide');
	showElementById('force_delete_tr','hide');
	var action = document.iform.action.value;
	switch (action) {
		case "clone":
			showElementById('newpath_tr','show');
			showElementById('recursive_tr','hide');
			showElementById('force_delete_tr','hide');
			break;
		case "delete":
			showElementById('newpath_tr','hide');
			showElementById('recursive_tr','show');
			showElementById('force_delete_tr','hide');
			break;
		case "rollback":
			showElementById('newpath_tr','hide');
			showElementById('recursive_tr','hide');
			showElementById('force_delete_tr','show');
			break;
		default:
			break;
	}
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
<form action="disks_zfs_snapshot_edit.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Edit Snapshot'));
?>
		</thead>
		<tbody>
<?php
			html_text2('snapshot',gettext('Snapshot'),htmlspecialchars($pconfig['snapshot']));
			$a_action = [
				'clone' => gettext('Clone'),
				'delete' => gettext('Delete'),
				'rollback' => gettext('Rollback')
			];
			html_combobox2('action',gettext('Action'),$pconfig['action'],$a_action,'',true,false,'action_change()');
			html_inputbox2('newpath',gettext('Path'),$pconfig['newpath'],'',true,30);
			html_checkbox2('recursive',gettext('Recursive'),!empty($pconfig['recursive']) ? true : false,gettext('Deletes the recursive snapshot.'),'',false);
			html_checkbox2('force_delete',gettext('Force Delete'),!empty($pconfig['force_delete']) ? true : false,gettext('Destroy any snapshots and bookmarks more recent than the one specified.'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Execute");?>" onclick="enable_change(true)" />
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
		<input name="snapshot" type="hidden" value="<?=$pconfig['snapshot'];?>" />
		<input name="pool" type="hidden" value="<?=$pconfig['pool'];?>" />
		<input name="path" type="hidden" value="<?=$pconfig['path'];?>" />
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<script type="text/javascript">
<!--
enable_change(true);
action_change();
//-->
</script>
<?php
include 'fend.inc';
?>
