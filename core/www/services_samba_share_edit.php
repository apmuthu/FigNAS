<?php
/*
	services_samba_share_edit.php

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
require_once 'properties_services_samba_share_edit.php';
require_once 'co_request_method.php';

function get_sphere_services_samba_share_edit() {
	global $config;
	
//	sphere structure
	$sphere = new co_sphere_row('services_samba_share_edit','php');
	$sphere->get_parent()->set_basename('services_samba_share');
	$sphere->set_notifier('smbshare');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->grid = &array_make_branch($config,'samba','share');
	if(!empty($sphere->grid)):
		array_sort_key($sphere->grid,'name');
	endif;
	return $sphere;
}
// get mount info specified path
function get_mount_info($path){
	if(file_exists($path) === false):
		return false;
	endif;
	//	get all mount points
	$a_mounts = [];
	mwexec2('/sbin/mount -p',$rawdata);
	foreach($rawdata as $line):
		list($dev,$dir,$fs,$opt,$dump,$pass) = preg_split('/\s+/',$line);
		$a_mounts[] = [
			'dev' => $dev,
			'dir' => $dir,
			'fs' => $fs,
			'opt' => $opt,
			'dump' => $dump,
			'pass' => $pass,
		];
	endforeach;
	if(empty($a_mounts)):
		return false;
	endif;
	// check path with mount list
	do {
		foreach($a_mounts as $mountv):
			if(strcmp($mountv['dir'],$path) == 0):
				// found mount point
				return $mountv;
			endif;
		endforeach;
		// path don't have parent?
		if(strpos($path,'/') === false):
			break;
		endif;
		// retry with parent
		$pathinfo = pathinfo($path);
		$path = $pathinfo['dirname'];
	} while (1);
	return false;
}
$cop = new properties_services_samba_share_edit();
$sphere = get_sphere_services_samba_share_edit();
$rmo = new co_request_method();
$rmo->add('GET','add',PAGE_MODE_ADD);
$rmo->add('GET','edit',PAGE_MODE_EDIT);
$rmo->add('POST','add',PAGE_MODE_ADD);
$rmo->add('POST','cancel',PAGE_MODE_POST);
$rmo->add('POST','edit',PAGE_MODE_EDIT);
$rmo->add('POST','save',PAGE_MODE_POST);
$rmo->set_default('POST','cancel',PAGE_MODE_POST);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'GET':
		switch($page_action):
			case 'add':
				//	bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'edit':
				//	modify the data of the provided resource id and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input(INPUT_GET);
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'add':
				//	bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] =  $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'cancel':
				//	cancel - nothing to do
				$sphere->row[$sphere->get_row_identifier()] = NULL;
				break;
			case 'edit':
				//	edit requires a resource id, get it from input and validate
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input();
				break;
			case 'save':
				//	modify requires a resource id, get it from input and validate
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input();
				break;
		endswitch;
		break;
endswitch;
/*
 *	exit if $sphere->row[$sphere->row_identifier()] is NULL
 */
if(is_null($sphere->get_row_identifier_value())):
	header($sphere->get_parent()->get_location());
	exit;
endif;
/*
 *	search resource id in sphere
 */
$sphere->row_id = array_search_ex($sphere->get_row_identifier_value(),$sphere->grid,$sphere->get_row_identifier());
/*
 *	start determine record update mode
 */
$updatenotify_mode = updatenotify_get_mode($sphere->get_notifier(),$sphere->get_row_identifier_value()); // get updatenotify mode
$record_mode = RECORD_ERROR;
if(false === $sphere->row_id):
	//	record does not exist in config
	if(in_array($page_mode,[PAGE_MODE_ADD,PAGE_MODE_POST],true)): // ADD or POST
		switch($updatenotify_mode):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$record_mode = RECORD_NEW;
				break;
		endswitch;
	endif;
else:
	//	record found in configuration
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
if(RECORD_ERROR === $record_mode):
	//	oops, something went wrong
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
$input_errors = [];
$a_referer = [
	$cop->get_name(),
	$cop->get_path(),
	$cop->get_comment(),
	$cop->get_readonly(),
	$cop->get_browseable(),
	$cop->get_guest(),
	$cop->get_inheritpermissions(),
	$cop->get_inheritacls(),
	$cop->get_recyclebin(),
	$cop->get_hidedotfiles(),
	$cop->get_shadowcopy(),
	$cop->get_shadowformat(),
	$cop->get_zfsacl(),
	$cop->get_storealternatedatastreams(),
	$cop->get_storentfsacls(),
	$cop->get_afpcompat(),
	$cop->get_vfs_fruit_resource(),
	$cop->get_vfs_fruit_time_machine(),
	$cop->get_vfs_fruit_metadata(),
	$cop->get_vfs_fruit_locking(),
	$cop->get_vfs_fruit_encoding(),
	$cop->get_hostsallow(),
	$cop->get_hostsdeny(),
	$cop->get_auxparam()
];
switch($page_mode):
	case PAGE_MODE_ADD:
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_EDIT:
		$source = $sphere->grid[$sphere->row_id];
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->validate_config($source);
		endforeach;
		//	special treatment for auxparam
		$name = $cop->get_auxparam()->get_name();
		if(is_array($sphere->row[$name])):
			$sphere->row[$name] = implode(PHP_EOL,$sphere->row[$name]);
		endif;
		break;
	case PAGE_MODE_POST:
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->validate_input();
			if(is_null($sphere->row[$referer->get_name()])):
				$sphere->row[$referer->get_name()] = $_POST[$referer->get_name()] ?? '';
				$input_errors[] = $referer->get_message_error();
			endif;
		endforeach;
		//	check for duplicate name.
		$name = $cop->get_name()->get_name();
		$index = array_search_ex($sphere->row[$name],$sphere->grid,$name);
		if(false !== $index):
			if($isrecordnew):
				$input_errors[] = gettext('The share name is already used.');
			elseif($index !== $sphere->row_id):
				$input_errors[] = gettext('The share name is already used.');
			endif;
		endif;
		//	enable ZFS ACL on ZFS mount point
		$mntinfo = get_mount_info($sphere->row[$cop->get_path()->get_name()]);
		if($mntinfo !== false && $mntinfo['fs'] === 'zfs'):
			if($isrecordnew):
				// first time creation
				$sphere->row[$cop->get_zfsacl()->get_name()] = true;
			endif;
		endif;
		if(empty($input_errors)):
			//	special treatment for auxparam
			$name = $cop->get_auxparam()->get_name();
			$a_test = explode(PHP_EOL,$sphere->row[$name]);
			$sphere->row[$name] = [];
			foreach($a_test as $r_test):
				$row = trim($r_test,"\t\n\r");
				if(preg_match('/\S/',$row)):
					$sphere->row[$name][] = $row;
				endif;
			endforeach;
			if($isrecordnew):
				$sphere->grid[] = $sphere->row;
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_NEW,$sphere->get_row_identifier_value());
			else:
				foreach($sphere->row as $key => $value):
					$sphere->grid[$sphere->row_id][$key] = $value;
				endforeach;
				if(UPDATENOTIFY_MODE_UNKNOWN == $updatenotify_mode):
					updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->get_row_identifier_value());
				endif;
			endif;
			write_config();
			header($sphere->get_parent()->get_location()); // cleanup
			exit;
		endif;
		break;
endswitch;
$pgtitle = [gettext('Services'),gettext('CIFS/SMB'),gettext('Share'),$isrecordnew ? gettext('Add') : gettext('Edit')];
if(is_bool($test = $config['system']['showdisabledsections'] ?? false) ? $test : true):
	$jcode = NULL;
else:
	$jcode = <<<EOJ
$(document).ready(function() {
	$('#{$cop->get_afpcompat()->get_id()}').change(function() {
		switch(this.checked) {
			case true:
				$('#{$cop->get_vfs_fruit_resource()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_time_machine()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_metadata()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_locking()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_encoding()->get_id()}_tr').show();
				break;
			case false:
				$('#{$cop->get_vfs_fruit_encoding()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_locking()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_metadata()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_time_machine()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_resource()->get_id()}_tr').hide();
				break;
        }
    });
	$('#{$cop->get_afpcompat()->get_id()}').change();
	$('#{$cop->get_shadowcopy()->get_id()}').change(function() {
		switch(this.checked) {
			case true:
				$('#{$cop->get_shadowformat()->get_id()}').show();
				break;
			case false:
				$('#{$cop->get_shadowformat()->get_id()}').hide();
				break;
        }
	});
	$('#{$cop->get_shadowcopy()->get_id()}').change();
});
EOJ;
endif;
$document = new_page($pgtitle,$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add additional javascript code
if(isset($jcode)):
	$body->addJavaScript($jcode);
endif;
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('services_samba.php',gettext('Settings'))->
			ins_tabnav_record('services_samba_share.php',gettext('Shares'),gettext('Reload page'),true);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg)->
	ins_error_box($errormsg);
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Share Settings'))->
		pop()->
		addTBODY()->
			c2_input_text($cop->get_name(),$sphere->row[$cop->get_name()->get_name()],true)->
			c2_input_text($cop->get_comment(),$sphere->row[$cop->get_comment()->get_name()],true)->
			c2_filechooser($cop->get_path(),$sphere->row[$cop->get_path()->get_name()],true)->
			c2_checkbox($cop->get_readonly(),$sphere->row[$cop->get_readonly()->get_name()])->
			c2_checkbox($cop->get_browseable(),$sphere->row[$cop->get_browseable()->get_name()])->
			c2_checkbox($cop->get_guest(),$sphere->row[$cop->get_guest()->get_name()])->
			c2_checkbox($cop->get_inheritpermissions(),$sphere->row[$cop->get_inheritpermissions()->get_name()])->
			c2_checkbox($cop->get_recyclebin(),$sphere->row[$cop->get_recyclebin()->get_name()])->
			c2_checkbox($cop->get_hidedotfiles(),$sphere->row[$cop->get_hidedotfiles()->get_name()])->
			c2_checkbox($cop->get_shadowcopy(),$sphere->row[$cop->get_shadowcopy()->get_name()])->
			c2_input_text($cop->get_shadowformat(),$sphere->row[$cop->get_shadowformat()->get_name()])->
			c2_checkbox($cop->get_zfsacl(),$sphere->row[$cop->get_zfsacl()->get_name()])->
			c2_checkbox($cop->get_inheritacls(),$sphere->row[$cop->get_inheritacls()->get_name()])->
			c2_checkbox($cop->get_storealternatedatastreams(),$sphere->row[$cop->get_storealternatedatastreams()->get_name()])->
			c2_checkbox($cop->get_storentfsacls(),$sphere->row[$cop->get_storentfsacls()->get_name()])->
			c2_input_text($cop->get_hostsallow(),$sphere->row[$cop->get_hostsallow()->get_name()])->
			c2_input_text($cop->get_hostsdeny(),$sphere->row[$cop->get_hostsdeny()->get_name()]);
$n_auxparam_rows = min(64,max(5,1 + substr_count($sphere->row[$cop->get_auxparam()->get_name()],PHP_EOL)));
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_separator()->
			c2_titleline_with_checkbox($cop->get_afpcompat(),$sphere->row['afpcompat'],false,false,gettext('Apple Filing Protocol (AFP) Compatibility Settings'))->
		pop()->
		addTBODY()->
			c2_radio_grid($cop->get_vfs_fruit_resource(),$sphere->row['vfs_fruit_resource'])->
			c2_radio_grid($cop->get_vfs_fruit_time_machine(),$sphere->row['vfs_fruit_time_machine'])->
			c2_radio_grid($cop->get_vfs_fruit_metadata(),$sphere->row['vfs_fruit_metadata'])->
			c2_radio_grid($cop->get_vfs_fruit_locking(),$sphere->row['vfs_fruit_locking'])->
			c2_radio_grid($cop->get_vfs_fruit_encoding(),$sphere->row['vfs_fruit_encoding']);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_separator()->
			c2_titleline(gettext('Additional Parameters'))->
		pop()->
		addTBODY()->
			c2_textarea($cop->get_auxparam(),$sphere->row['auxparam'],false,false,65,$n_auxparam_rows);
$buttons = $document->add_area_buttons();
if($isrecordnew):
	$buttons->ins_button_add();
else:
	$buttons->ins_button_save();
endif;
$buttons->ins_button_cancel();
$buttons->insINPUT(['name' => $sphere->get_row_identifier(),'type' => 'hidden','value' => $sphere->get_row_identifier_value()]);
$document->render();
