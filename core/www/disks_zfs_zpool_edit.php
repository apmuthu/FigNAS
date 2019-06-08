<?php
/*
	disks_zfs_zpool_edit.php

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
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: disks_zfs_zpool.php';
$sphere_notifier = 'zfspool';
$sphere_array = [];
$sphere_record = [];
$gt_record_loc = gtext('Virtual device is already in use.');
$gt_record_opn = gtext('Virtual device can be removed.');
$prerequisites_ok = true; // flag to indicate lack of information / resources
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png'
];

$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD); // detect page mode
if(PAGE_MODE_POST == $mode_page): // POST is Cancel or not Submit => cleanup
	if((isset($_POST['Cancel']) && $_POST['Cancel']) || !(isset($_POST['Submit']) && $_POST['Submit'])):
		header($sphere_header_parent);
		exit;
	endif;
endif;

if((PAGE_MODE_POST == $mode_page) && isset($_POST['uuid']) && is_uuid_v4($_POST['uuid'])):
	$sphere_record['uuid'] = $_POST['uuid'];
elseif((PAGE_MODE_EDIT == $mode_page) && isset($_GET['uuid']) && is_uuid_v4($_GET['uuid'])):
	$sphere_record['uuid'] = $_GET['uuid'];
else:
	$mode_page = PAGE_MODE_ADD; // Force ADD
	$sphere_record['uuid'] = uuid();
endif;

$sphere_array = &array_make_branch($config,'zfs','pools','pool');
if(empty($sphere_array)):
else:
	array_sort_key($sphere_array,'name');
endif;

$index = array_search_ex($sphere_record['uuid'], $sphere_array, 'uuid'); // find index of uuid
$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']); // get updatenotify mode for uuid
$mode_record = RECORD_ERROR;
if(false !== $index): // uuid found
	if((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))): // POST or EDIT
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		endswitch;
	endif;
else: // uuid not found
	if((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page)): // POST or ADD
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		endswitch;
	endif;
endif;
if(RECORD_ERROR == $mode_record): // oops, someone tries to cheat, over and out
	header($sphere_header_parent);
	exit;
endif;
$isrecordnew = (RECORD_NEW === $mode_record);
$isrecordnewmodify = (RECORD_NEW_MODIFY == $mode_record);
$isrecordmodify = (RECORD_MODIFY === $mode_record);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);

$a_vdevice = &array_make_branch($config,'zfs','vdevices','vdevice');
if(empty($a_vdevice)):
	$errormsg = gtext('No configured virtual devices.')
		. ' '
		. '<a href="' . 'disks_zfs_zpool_vdevice.php' . '">'
		. gtext('Please add a virtual device first.')
		. '</a>';
	$prerequisites_ok = false;
else:
	array_sort_key($a_vdevice,'name');
endif;

if(PAGE_MODE_POST == $mode_page): // We know POST is "Submit", already checked
	unset($input_errors);
	switch($mode_record):
		case RECORD_NEW:
		case RECORD_NEW_MODIFY:
			$sphere_record['name'] = $_POST['name'];
			$sphere_record['vdevice'] = $_POST['vdevice'];
			$sphere_record['root'] = $_POST['root'];
			$sphere_record['mountpoint'] = $_POST['mountpoint'];
			$sphere_record['force'] = isset($_POST['force']) ? true : false;
			$sphere_record['desc'] = $_POST['desc'];
			break;
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['vdevice'] = $sphere_array[$index]['vdevice'];
			$sphere_record['root'] = $sphere_array['root'];
			$sphere_record['mountpoint'] = $_POST['mountpoint'];
			$sphere_record['force'] = isset($sphere_array['force']) ? true : false;
			$sphere_record['desc'] = $_POST['desc'];
			break;
	endswitch;

	// Input validation
	$reqdfields = ['name','vdevice'];
	$reqdfieldsn = [gtext('Name'),gtext('Virtual Devices')];

	do_input_validation($sphere_record, $reqdfields, $reqdfieldsn, $input_errors);

	if($prerequisites_ok && empty($input_errors)): // check for a valid pool name.
		if(!zfs_is_valid_poolname($sphere_record['name'])):
			$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext('Name'));
		endif;
	endif;

	if($prerequisites_ok && empty($input_errors)):
		switch($mode_record): // check if the new pool name or a renamed pool on new_modify already exists
			case RECORD_NEW:
			case RECORD_NEW_MODIFY:
				$helpinghand = escapeshellarg($sphere_record['name']); // create quoted pool name
				// throw error when pool name already exists in live.
				mwexec2(sprintf("zpool list -H -o name %s 2>&1", $helpinghand), $retdat, $retval);
				switch($retval):
					case 1: // An error occured. => pool doesn't exist
						break;
					case 0: // Successful completion. => pool found
						$input_errors[] = sprintf(gtext('%s already exists.'), $sphere_record['name']);
						break;
					case 2: // Invalid command line options were specified.
						$input_errors[] = gtext('Failed to execute command zpool.');
						break;
				endswitch;
		endswitch;
	endif;
			
	if($prerequisites_ok && empty($input_errors)):
		switch($mode_record): // verify config
			case RECORD_NEW: // pool name must not exist in config at all
				if(false !== array_search_ex($sphere_record['name'], $sphere_array, 'name')):
					$input_errors[] = gtext('This pool name already exists.');
				endif;
				break;
			case RECORD_NEW_MODIFY: // if the pool name has changed it shouldn't be found in config
				if($sphere_record['name'] !== $sphere_array[$index]['name']): // pool name has changed
					if(false !== array_search_ex($sphere_record['name'], $sphere_array, 'name')):
						$input_errors[] = gtext('This pool name already exists.');
					endif;
				endif;
				break;
			case RECORD_MODIFY: // should never happen because sphere_record['name'] should be set to $sphere_array[$index]['name']
				if($sphere_record['name'] !== $sphere_array[$index]['name']):
					$input_errors[] = gtext('The name of the pool cannot be changed.');
				endif;
				break;
		endswitch;
	endif;

	// Check vdevices
	$hastpool = false;
	if(isset($sphere_record['vdevice']) && is_array($sphere_record['vdevice'])):
		$n = 0;
		foreach($sphere_record['vdevice'] as $vdevice_name):
			$i = array_search_ex($vdevice_name, $a_vdevice, 'name');
			if($i !== false):
				$r_vdevice = $a_vdevice[$i];
				// flag if hast devices have been selected
				foreach($r_vdevice['device'] as $device):
					if(preg_match('/^\/dev\/hast\//', $device)):
						$hastpool = true;
					endif;
				endforeach;
				// don't count spare, cache and log devices
				if(preg_match('/^(spare|cache|log)$/', $r_vdevice['type'])):
					continue;
				endif;
			endif;
			// count disk, file, mirror and raidz. They are allowed
			$n++;
		endforeach;
		if($n == 0):
			$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext('Virtual devices'));
		endif;
	endif;
	$sphere_record['hastpool'] = $hastpool;

	if($prerequisites_ok && empty($input_errors)):
		if($isrecordnew):
			$sphere_array[] = $sphere_record;
			updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_NEW, $sphere_record['uuid']);
		else:
			$sphere_array[$index] = $sphere_record;
			if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
				updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
			endif;
		endif;
		write_config();
		header($sphere_header_parent);
		exit;
	endif;
else: // EDIT / ADD
	switch($mode_record):
		case RECORD_NEW:
			$sphere_record['name'] = '';
			$sphere_record['root'] = '';
			$sphere_record['mountpoint'] = '';
			$sphere_record['force'] = false;
			$sphere_record['desc'] = '';	
			break;
		case RECORD_NEW_MODIFY:
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['vdevice'] = $sphere_array[$index]['vdevice'];
			$sphere_record['root'] = $sphere_array[$index]['root'];
			$sphere_record['mountpoint'] = $sphere_array[$index]['mountpoint'];
			$sphere_record['force'] = isset($a_dataset[$index]['force']);
			$sphere_record['desc'] = $sphere_array[$index]['desc'];	
			break;
	endswitch;
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Pools'),gtext('Management'),(!$isrecordnew) ? gtext('Edit') : gtext('Add')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
}); 
//]]>
</script>
<?php
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
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('name', gettext('Name'), $sphere_record['name'], '', false, 20, $isrecordmodify);
			html_inputbox2('root', gettext('Root'), $sphere_record['root'], gettext('Creates the pool with an alternate root.'), false, 40, $isrecordmodify);
			html_inputbox2('mountpoint', gettext('Mount Point'), $sphere_record['mountpoint'], gettext('Sets an alternate mount point for the root dataset. Default is /mnt.'), false, 40);
			html_checkbox2('force', gettext('Force Use'), $sphere_record['force'], gettext('Forces use of vdevs, even if they appear in use or specify different size. (This is not recommended.)'), '', false, $isrecordmodify);
			html_inputbox2('desc', gettext('Description'), $sphere_record['desc'], gettext('You may enter a description here for your reference.'), false, 40);
?>
		</tbody>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:20%">
			<col style="width:20%">
			<col style="width:50%">
			<col style="width:5%">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('Virtual Device List'), 5);
?>
			<tr>
				<td class="lhelc"><input type="checkbox" class="oneemhigh" name="togglemembers" disabled="disabled"/></td>
				<td class="lhell"><?=gtext('Name');?></td>
				<td class="lhell"><?=gtext('Type');?></td>
				<td class="lhell"><?=gtext('Description');?></td>
				<td class="lhebl"><?=gtext('Toolbox');?></td>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($a_vdevice as $r_vdevice):
				$isnotmemberofapool = (false === array_search_ex($r_vdevice['name'], $sphere_array, 'vdevice'));
				$ismemberofthispool = (isset($sphere_record['vdevice']) && is_array($sphere_record['vdevice']) && in_array($r_vdevice['name'], $sphere_record['vdevice']));
				if($isrecordnewornewmodify):
					if($isnotmemberofapool || $ismemberofthispool):
?>
						<tr>
							<td class="lcelc">
<?php
								if($ismemberofthispool):
?>
									<input type="checkbox" class="oneemhigh" name="vdevice[]" value="<?=$r_vdevice['name'];?>" id="<?=$r_vdevice['uuid'];?>" checked="checked"/>
<?php
								else:
?>
									<input type="checkbox" class="oneemhigh" name="vdevice[]" value="<?=$r_vdevice['name'];?>" id="<?=$r_vdevice['uuid'];?>"/>
<?php
								endif;
?>
							</td>
							<td class="lcell"><?=htmlspecialchars($r_vdevice['name']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_vdevice['type']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_vdevice['desc']);?></td>
							<td class="lcebcd">
<?php
								if($ismemberofthispool):
?>
									<img src="<?=$img_path['unl'];?>" title="<?=$gt_record_opn;?>" alt="<?=$gt_record_opn;?>" />
<?php
								endif;
?>
							</td>
						</tr>
<?php
					endif;
				endif;
				if($isrecordmodify):
					if($ismemberofthispool):
?>
						<tr>
							<td class="lcelcd">
								<input type="checkbox" class="oneemhigh" name="vdevice[]" value="<?=$r_vdevice['name'];?>" id="<?=$r_vdevice['uuid'];?>" checked="checked" disabled="disabled"/>
							</td>
							<td class="lcelld"><?=htmlspecialchars($r_vdevice['name']);?></td>
							<td class="lcelld"><?=htmlspecialchars($r_vdevice['type']);?></td>
							<td class="lcelld"><?=htmlspecialchars($r_vdevice['desc']);?></td>
							<td class="lcebcd">
								<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>" />
							</td>
						</tr>
<?php
					endif;
				endif;
			endforeach;
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=($isrecordnew) ? gtext('Add') : gtext('Save');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>" />
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
