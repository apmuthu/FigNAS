<?php
/*
	disks_zfs_volume_edit.php

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
require_once 'properties_zfs_dataset.php';

function get_volblocksize($pool,$name) {
	$cmd = sprintf('zfs get -H -o value volblocksize %s 2>&1',escapeshellarg(sprintf('%s/%s',$pool,$name)));
	mwexec2($cmd,$rawdata,$retval);
	if(0 == $retval):
		return $rawdata[0];
	else:
		return '-';
	endif;
}
function get_sphere_disks_zfs_volume_edit() {
	global $config;
	$sphere = new co_sphere_row('disks_zfs_volume_edit','php');
	$sphere->set_row_identifier('uuid');
	$sphere->get_parent()->set_basename('disks_zfs_volume','php');
	$sphere->set_notifier('zfsvolume');
	$sphere->grid = &array_make_branch($config,'zfs','volumes','volume');
	if(!empty($sphere->grid)):
		array_sort_key($sphere->grid,'name');
	endif;
	return $sphere;
}
//	init properties and sphere
$property = new properties_zfs_volume();
$sphere = &get_sphere_disks_zfs_volume_edit();
//	init indicators
$input_errors = [];
$prerequisites_ok = true;
//	request method
$server_request_method_regexp = '/^(GET|POST)$/';
if(array_key_exists('REQUEST_METHOD',$_SERVER)):
	$server_request_method = filter_var($_SERVER['REQUEST_METHOD'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $server_request_method_regexp]]);
else:
	$server_request_method = NULL;
endif;
//	resource id
$resource_id_filter_options = [
	'flags' => FILTER_REQUIRE_SCALAR,
	'options' => [
		'default' => NULL,
		'regexp' => '/^[\da-f]{4}([\da-f]{4}-){2}4[\da-f]{3}-[89ab][\da-f]{3}-[\da-f]{12}$/'
]];
//	determine page mode and validate resource id
switch($server_request_method):
	default: // unsupported request method
		$sphere->row[$sphere->get_row_identifier()] = NULL;
		break;
	case 'GET':
		$action = filter_input(
			INPUT_GET,
			'submit',
			FILTER_VALIDATE_REGEXP,
			[
				'flags' => FILTER_REQUIRE_SCALAR,
				'options' => [
					'default' => '',
					'regexp' => '/^(add|edit)$/'
		]]);
		switch($action):
			default: // unsupported action
				$sphere->row[$sphere->get_row_identifier()] = NULL;
				break;
			case 'add': // bring up a form with default values and let the user modify it
				$page_mode = PAGE_MODE_ADD;
				$sphere->row[$sphere->get_row_identifier()] = uuid();
				break;
			case 'edit': // modify the data of the provided resource id and let the user modify it
				$page_mode = PAGE_MODE_EDIT;
				$sphere->row[$sphere->get_row_identifier()] = filter_input(INPUT_GET,$sphere->get_row_identifier(),FILTER_VALIDATE_REGEXP,$resource_id_filter_options);
				break;
		endswitch;
		break;
	case 'POST':
		$action = filter_input(
			INPUT_POST,
			'submit',
			FILTER_VALIDATE_REGEXP,
			[
				'flags' => FILTER_REQUIRE_SCALAR,
				'options' => [
					'default' => '',
					'regexp' => '/^(add|cancel|clone|edit|save)$/'
		]]);
		switch($action):
			default:  // unsupported action
				$sphere->row[$sphere->get_row_identifier()] = NULL;
				break;
			case 'add': // bring up a form with default values and let the user modify it
				$page_mode = PAGE_MODE_ADD;
				$sphere->row[$sphere->get_row_identifier()] = uuid();
				break;
			case 'cancel': // cancel - nothing to do
				$sphere->row[$sphere->get_row_identifier()] = NULL;
				break;
			case 'clone': // clone requires a new resource id, get it from uuid() and validate
				$page_mode = PAGE_MODE_CLONE;
				$sphere->row[$sphere->get_row_identifier()] = uuid();
				break;
			case 'edit': // edit requires a resource id, get it from input and validate
				$page_mode = PAGE_MODE_EDIT;
				$sphere->row[$sphere->get_row_identifier()] = filter_input(INPUT_POST,$sphere->get_row_identifier(),FILTER_VALIDATE_REGEXP,$resource_id_filter_options);
				break;
			case 'save': // modify requires a resource id, get it from input and validate
				$page_mode = PAGE_MODE_POST;
				$sphere->row[$sphere->get_row_identifier()] = filter_input(INPUT_POST,$sphere->get_row_identifier(),FILTER_VALIDATE_REGEXP,$resource_id_filter_options);
				break;
		endswitch;
		break;
endswitch;
/*
 *	exit if $sphere->row[$sphere->row_identifier()] is NULL
 */
if(!isset($sphere->row[$sphere->get_row_identifier()])):
	header($sphere->get_parent()->get_location());
	exit;
endif;
/*
 *	search resource id in sphere
 */
$sphere->row_id = array_search_ex($sphere->row[$sphere->get_row_identifier()],$sphere->grid,$sphere->get_row_identifier());
/*
 *	start determine record update mode
 */
$updatenotify_mode = updatenotify_get_mode($sphere->get_notifier(),$sphere->row[$sphere->get_row_identifier()]); // get updatenotify mode
$record_mode = RECORD_ERROR;
if(false === $sphere->row_id): // record does not exist in config
	if(in_array($page_mode,[PAGE_MODE_ADD,PAGE_MODE_CLONE,PAGE_MODE_POST],true)): // ADD or CLONE or POST
		switch($updatenotify_mode):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$record_mode = RECORD_NEW;
				break;
		endswitch;
	endif;
else: // record found in configuration
	if(in_array($page_mode,[PAGE_MODE_EDIT,PAGE_MODE_POST,PAGE_MODE_VIEW],true)): // EDIT or POST or VIEW
		switch($updatenotify_mode):
			case UPDATENOTIFY_MODE_NEW:
				$record_mode = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
				$record_mode = RECORD_MODIFY;
				break;
			case UPDATENOTIFY_MODE_UNKNOWN:
				$record_mode = RECORD_MODIFY;
				break;
		endswitch;
	endif;
endif;
if(RECORD_ERROR === $record_mode): // oops, something went wrong
	header($sphere->get_parent()->get_location());
	exit;
endif;
$isrecordnew = (RECORD_NEW === $record_mode);
$isrecordnewmodify = (RECORD_NEW_MODIFY === $record_mode);
$isrecordmodify = (RECORD_MODIFY === $record_mode);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
/*
 *	end determine record update mode
 */
$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)): // Throw error message if no pool exists
	$errormsg = gtext('No configured pools.') . ' ' . '<a href="' . 'disks_zfs_zpool.php' . '">' . gtext('Please add new pools first.') . '</a>';
	$prerequisites_ok = false;
else:
	array_sort_key($a_pool,'name');
endif;
$a_dataset = &array_make_branch($config,'zfs','datasets','dataset');
if(empty($a_dataset)):
else:	
	array_sort_key($a_dataset,'name');
endif;
$a_poollist = zfs_get_pool_list();
$l_poollist = [];
$use_si = is_sidisksizevalues();
foreach($a_pool as $r_pool):
	$r_poollist = $a_poollist[$r_pool['name']];
	$helpinghand = sprintf('%s: %s',$r_pool['name'],format_bytes($r_poollist['size'],2,false,$use_si));
	if(!empty($r_pool['desc'])):
		$helpinghand .= ' ' . $r_pool['desc'];
	endif;
	$l_poollist[$r_pool['name']] = htmlspecialchars($helpinghand);
endforeach;
$a_referrer = ['checksum','compression','dedup','logbias','primarycache','secondarycache','sync','volblocksize','volmode','volsize'];
switch($page_mode):
	case PAGE_MODE_ADD:
		$sphere->row['name'] = '';
		$sphere->row['pool'] = '';
		$sphere->row['desc'] = '';
		$sphere->row['sparse'] = false;
		foreach($a_referrer as $referrer):
			$sphere->row[$referrer] = $property->$referrer->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_CLONE:
		$sphere->row['name'] = filter_input(INPUT_POST,'name',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['pool'] = filter_input(INPUT_POST,'pool',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['sparse'] = filter_input(INPUT_POST,'sparse',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		foreach($a_referrer as $referrer):
			$sphere->row[$referrer] = $property->$referrer->validate_input() ?? $property->$referrer->get_defaultvalue();
		endforeach;
		//	adjust page mode
		$page_mode = PAGE_MODE_ADD;
		break;
	case PAGE_MODE_EDIT:
		$sphere->row['name'] = $sphere->grid[$sphere->row_id]['name'];
		$sphere->row['pool'] = $sphere->grid[$sphere->row_id]['pool'][0];
		$sphere->row['desc'] = $sphere->grid[$sphere->row_id]['desc'];
		$sphere->row['sparse'] = isset($sphere->grid[$sphere->row_id]['sparse']);
		foreach($a_referrer as $referrer):
			$sphere->row[$referrer] = $property->$referrer->validate_value($sphere->grid[$sphere->row_id][$referrer]) ?? $property->$referrer->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_POST:
		// apply post values that are applicable for all record modes
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['sparse'] = filter_input(INPUT_POST,'sparse',FILTER_VALIDATE_BOOLEAN,['options' => ['default' => false]]);
		foreach(['checksum','compression','dedup','logbias','primarycache','secondarycache','sync','volmode','volsize'] as $referrer):
			$sphere->row[$referrer] = $property->$referrer->validate_input();
		endforeach;
		switch($record_mode):
			case RECORD_NEW:
			case RECORD_NEW_MODIFY:
				$sphere->row['name'] = filter_input(INPUT_POST,'name',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
				$sphere->row['pool'] = filter_input(INPUT_POST,'pool',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
				$sphere->row['volblocksize'] = $property->volblocksize->validate_input();
				break;
			case RECORD_MODIFY:
				$sphere->row['name'] = $sphere->grid[$sphere->row_id]['name'];
				$sphere->row['pool'] = $sphere->grid[$sphere->row_id]['pool'][0];
				$sphere->row['volblocksize'] = $property->volblocksize->validate_value($sphere->grid[$sphere->row_id]['volblocksize']);
				break;
		endswitch;
		// Input validation
		$reqdfields = ['pool','name'];
		$reqdfieldsn = [gtext('Pool'),gtext('Name')];
		$reqdfieldst = ['string','string'];
		do_input_validation($sphere->row,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($sphere->row,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		// validate text input elements
		foreach(['volsize'] as $referrer):
			if(!isset($sphere->row[$referrer])):
				$sphere->row[$referrer] = $_POST[$referrer] ?? $property->$referrer->get_defaultvalue(); // restore $_POST or populate default
				$input_errors[] = $property->$referrer->get_message_error();
			endif;
		endforeach;
		// validate list elements
		foreach(['checksum','compression','dedup','logbias','primarycache','secondarycache','sync','volblocksize','volmode'] as $referrer):
			if(!isset($sphere->row[$referrer])):
				$sphere->row[$referrer] = '';
				$input_errors[] = $property->$referrer->get_message_error();
			endif;
		endforeach;
		if(empty($input_errors)):
			// check for a valid name with the format name[/name], blanks are not supported.
			$helpinghand = preg_quote('.:-_','/');
			if(!(preg_match('/^[a-z\d][a-z\d'.$helpinghand.']*(?:\/[a-z\d][a-z\d'.$helpinghand.']*)*$/i',$sphere->row['name']))):
				$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."),gtext('Name'));
			endif;
		endif;
/*	
		1. RECORD_MODIFY: throw error if posted pool is different from configured pool.
		2. RECORD_NEW: posted pool/name must not exist in configuration or live.
		3. RECORD_NEW_MODIFY: if posted pool/name is different from configured pool/name: posted pool/name must not exist in configuration or live.
		4. RECORD_MODIFY: if posted name is different from configured name: pool/posted name must not exist in configuration or live.
*/
		// 1.
		if(empty($input_errors)):
			if($isrecordmodify && (0 !== strcmp($sphere->grid[$sphere->row_id]['pool'][0],$sphere->row['pool']))):
				$input_errors[] = gtext('Pool cannot be changed.');
			endif;
		endif;
		// 2., 3., 4.
		if(empty($input_errors)):
			$poolslashname = escapeshellarg($sphere->row['pool'] . '/' . $sphere->row['name']); // create quoted full dataset name
			if($isrecordnew || (!$isrecordnew && (0 !== strcmp(escapeshellarg($sphere->grid[$sphere->row_id]['pool'][0] . '/' . $sphere->grid[$sphere->row_id]['name']),$poolslashname)))):
				// throw error when pool/name already exists in live
				if(empty($input_errors)):
					$cmd = sprintf('zfs get -H -o value type %s 2>&1',$poolslashname);
					mwexec2($cmd,$retdat,$retval);
					switch($retval):
						case 1: // An error occured. => zfs dataset doesn't exist
							break;
						case 0: // Successful completion. => zfs dataset found
							$input_errors[] = sprintf(gtext('%s already exists as a %s.'),$poolslashname,$retdat[0]);
							break;
						case 2: // Invalid command line options were specified.
							$input_errors[] = gtext('Failed to execute command zfs.');
							break;
					endswitch;
				endif;
				// throw error when pool/name exists in configuration file, zfs->volumes->volume[]
				if(empty($input_errors)):
					foreach($sphere->grid as $r_volume):
						if(0 === strcmp(escapeshellarg($r_volume['pool'][0].'/'.$r_volume['name']),$poolslashname)):
							$input_errors[] = sprintf(gtext('%s is already configured as a volume.'),$poolslashname);
							break;
						endif;
					endforeach;
				endif;
				// throw error when pool/name exists in configuration file, zfs->datasets->dataset[] 
				if(empty($input_errors)):
					foreach($a_dataset as $r_dataset):
						if(0 === strcmp(escapeshellarg($r_dataset['pool'][0].'/'.$r_dataset['name']),$poolslashname)):
							$input_errors[] = sprintf(gtext('%s is already configured as a filesystem.'),$poolslashname);
							break;
						endif;
					endforeach;
				endif;
			endif;
		endif;
		if($prerequisites_ok && empty($input_errors)):
			// convert listtags to arrays
			$helpinghand = $sphere->row['pool'];
			$sphere->row['pool'] = [$helpinghand];
			if($isrecordnew):
				$sphere->grid[] = $sphere->row;
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_NEW,$sphere->row[$sphere->get_row_identifier()]);
			else:
				$sphere->grid[$sphere->row_id] = $sphere->row;
				if(UPDATENOTIFY_MODE_UNKNOWN == $updatenotify_mode):
					updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->row[$sphere->get_row_identifier()]);
				endif;
			endif;
			write_config();
			header($sphere->get_parent()->get_location()); // cleanup
			exit;
		endif;
		break;
endswitch;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Volumes'),gtext('Volume'),($isrecordnew) ? gtext('Add') : gtext('Edit')];
include 'fbegin.inc';
$sphere->doj();
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volume'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_volume_info.php',gettext('Information'));
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_inputbox2('name',gettext('Name'),$sphere->row['name'],'',true,60,$isrecordmodify,false,128,gettext('Enter a name for this volume'));
			html_combobox2('pool',gettext('Pool'),$sphere->row['pool'],$l_poollist,'',true,$isrecordmodify);
			html_inputbox2('desc',gettext('Description'),$sphere->row['desc'],gettext('You may enter a description here for your reference.'),false,40,false,false,40,gettext('Enter a description'));
			$node = new co_DOMDocument();
			$node->c2_input_text($property->volsize,$sphere->row['volsize'],true,false);
			$node->render();
			html_checkbox2('sparse',gettext('Sparse Volume'),!empty($sphere->row['sparse']) ? true : false,gettext('Use as sparse volume (thin provisioning).'),'',false);
			$node = new co_DOMDocument();
			$node->c2_radio_grid($property->volmode,$sphere->row['volmode'],true);
			$node->c2_select($property->compression,$sphere->row['compression'],true);
			$node->c2_select($property->dedup,$sphere->row['dedup'],true);
			$node->c2_radio_grid($property->sync,$sphere->row['sync'],true); 
			$node->c2_select($property->volblocksize,$sphere->row['volblocksize'],false,$isrecordmodify);
			$node->c2_radio_grid($property->primarycache,$sphere->row['primarycache']);
			$node->c2_radio_grid($property->secondarycache,$sphere->row['secondarycache']);
			$node->c2_select($property->checksum,$sphere->row['checksum']);
			$node->c2_radio_grid($property->logbias,$sphere->row['logbias']);
			$node->render();
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		if($isrecordnew):
			echo $sphere->html_button('save',gettext('Add'));
		else:
			echo $sphere->html_button('save',gettext('Apply'));
			if($prerequisites_ok && empty($input_errors)):
				echo $sphere->html_button('clone',gettext('Clone Configuration'));
			endif;
		endif;
			echo $sphere->html_button('cancel',gettext('Cancel'));
?>
		<input name="<?=$sphere->get_row_identifier();?>" type="hidden" value="<?=$sphere->row[$sphere->get_row_identifier()];?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
