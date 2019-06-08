<?php
/*
	disks_zfs_snapshot_add.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Snapshot'),gtext('Add')];

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
	$link = sprintf('<a href="%1$s">%2$s</a>', 'disks_zfs_zpool.php',gtext('pools'));
	$helpinghand = gtext('No configured pools.') . ' ' . gtext('Please add new %s first.');
	$helpinghand = sprintf($helpinghand,$link);
	$errormsg = $helpinghand;
endif;
$cnid = false;
if(isset($uuid) && (false !== $cnid)):
	// not supported
	$pconfig = [];
else:
	$pconfig['uuid'] = uuid();
	$pconfig['snapshot'] = '';
	$pconfig['path'] = '';
	$pconfig['name'] = '';
	//$pconfig['pool'] = '";
	$pconfig['recursive'] = false;
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: disks_zfs_snapshot.php');
		exit;
	endif;
	if(!isset($_POST['path'])):
		$pconfig['path'] = '';
	endif;
	// Input validation
	$reqdfields = ['path','name'];
	$reqdfieldsn = [gtext('Path'),gtext('Name')];
	$reqdfieldst = ['string','string'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	if(preg_match("/(\\s|\\@|\\'|\\\")+/", $_POST['name'])):
		$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."),gtext('Name'));
	endif;
	if(empty($input_errors)):
		$snapshot = [];
		$snapshot['uuid'] = $_POST['uuid'];
		$snapshot['path'] = $_POST['path'];
		$snapshot['name'] = $_POST['name'];
		$snapshot['snapshot'] =	$snapshot['path'].'@'.$snapshot['name'];
		$snapshot['recursive'] = isset($_POST['recursive']) ? true : false;
		//$mode = UPDATENOTIFY_MODE_NEW;
		//updatenotify_set("zfssnapshot", $mode, serialize($snapshot));
		//header("Location: disks_zfs_snapshot.php");
		//exit;
		$ret = zfs_snapshot_configure($snapshot);
		if($ret['retval'] == 0):
			header('Location: disks_zfs_snapshot.php');
			exit;
		endif;
		$errormsg = implode(PHP_EOL,$ret['output']);
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
<form action="disks_zfs_snapshot_add.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Snapshot'));
?>
		</thead>
		<tbody>
<?php
			$a_pathlist = [];
			foreach($a_path as $pathv):
				$a_pathlist[$pathv['path']] = $pathv['path'];
			endforeach;
			html_combobox2('path',gettext('Path'),$pconfig['path'],$a_pathlist,'',true);
			html_inputbox2('name',gettext('Name'),$pconfig['name'],'',true,20);
			html_checkbox2('recursive',gettext('Recursive'),!empty($pconfig['recursive']) ? true : false,gettext('Create recursive snapshot.'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Add');?>" onclick="enable_change(true)" />
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<script type="text/javascript">
<!--
<?php
if(isset($uuid) && (false !== $cnid)):
?>
<!-- Disable controls that should not be modified anymore in edit mode. -->
enable_change(false);
<?php
endif;
?>
//-->
</script>
<?php
include 'fend.inc';
?>
