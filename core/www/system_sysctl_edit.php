<?php
/*
	system_sysctl_edit.php

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
require_once 'autoload.php';

use system\sysctl\row_toolbox as toolbox;
use system\sysctl\shared_toolbox;
use system\sysctl\grid_toolbox as toolbox_user;

//	init indicators
$input_errors = [];
$prerequisites_ok = true;
//	preset $savemsg when a reboot is pending
if(file_exists($d_sysrebootreqd_path)):
	$savemsg = get_std_save_message(0);
endif;
//	init properties and sphere
$cop = toolbox::init_properties();
$sphere = toolbox::init_sphere();
$cop_user = toolbox_user::init_properties();
$sphere_user = toolbox_user::init_sphere();
$rmo = toolbox::init_rmo();
list($page_method,$page_action,$page_mode) = $rmo->validate();
//	determine page mode and validate resource id
switch($page_method):
	case 'GET':
		switch($page_action):
			case 'add': // bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'edit': // modify the data of the provided resource id and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input(INPUT_GET);
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'add': // bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'cancel': // cancel - nothing to do
				$sphere->row[$sphere->get_row_identifier()] = NULL;
				break;
			case 'clone':
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'edit': // edit requires a resource id, get it from input and validate
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input();
				break;
			case 'save': // modify requires a resource id, get it from input and validate
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
$isrecordnew = RECORD_NEW === $record_mode;
$isrecordnewmodify = RECORD_NEW_MODIFY === $record_mode;
$isrecordmodify = RECORD_MODIFY === $record_mode;
$isrecordnewornewmodify = $isrecordnew || $isrecordnewmodify;
/*
 *	end determine record update mode
 */
$a_referer = [
	$cop->get_enable(),
	$cop->get_name(),
	$cop->get_value(),
	$cop->get_description()
];
//	Collect all writeable sysctl's
unset($writable_sysctls);
exec('/sbin/sysctl -ANW',$writable_sysctls);
usort($writable_sysctls,'strcasecmp');
$options = array_combine($writable_sysctls,$writable_sysctls);
$cop->get_name()->set_options($options);
switch($page_mode):
	case PAGE_MODE_ADD:
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_CLONE:
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_input() ?? $referer->get_defaultvalue();
		endforeach;
//		adjust page mode
		$page_mode = PAGE_MODE_ADD;
		break;
	case PAGE_MODE_EDIT:
		$source = $sphere->grid[$sphere->row_id];
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_config($source);
		endforeach;
		break;
	case PAGE_MODE_POST:
		if($isrecordmodify):
			$source = $sphere->grid[$sphere->row_id];
		endif;
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			if($isrecordmodify && !$referer->get_editableonmodify()):
//				validate protected items from config
				$sphere->row[$name] = $referer->validate_config($source);
				if(is_null($sphere->row[$name])):
					$sphere->row[$name] = $source[$name] ?? '';
					$input_errors[] = $referer->get_message_error();
				endif;
			else:
				$sphere->row[$name] = $referer->validate_input();
				if(is_null($sphere->row[$name])):
					$sphere->row[$name] = filter_input(INPUT_POST,$name,FILTER_DEFAULT) ?? '';
					$input_errors[] = $referer->get_message_error();
				endif;
			endif;
		endforeach;
		if($prerequisites_ok && empty($input_errors)):
			$sphere->upsert();
			if($isrecordnew):
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_NEW,$sphere->get_row_identifier_value(),$sphere->get_notifier_processor());
			elseif(UPDATENOTIFY_MODE_UNKNOWN == $updatenotify_mode):
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->get_row_identifier_value(),$sphere->get_notifier_processor());
			endif;
			write_config();
			header($sphere->get_parent()->get_location()); // cleanup
			exit;
		endif;
		break;
endswitch;
//	collect system description for sysctl.
$sysctl_name = $sphere->row[$cop->get_name()->get_name()] ?? '';
if(preg_match('/\S/',$sysctl_name)):
	list(,$sysctl_type,$sysctl_info) = explode('=',exec(sprintf('/sbin/sysctl -deit %s',escapeshellarg($sysctl_name))),3);
	list(,$sysctl_value) = explode('=',exec(sprintf('/sbin/sysctl -eih %s',escapeshellarg($sysctl_name))),2);
else:
	$sysctl_value = '';
	$sysctl_type = '';
	$sysctl_info = '';
endif;
$pgtitle = [gettext('System'),gettext('Advanced'),gettext('sysctl.conf'),($isrecordnew) ? gettext('Add') : gettext('Edit')];
$document = new_page($pgtitle,$sphere->get_script()->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
shared_toolbox::add_tabnav($document);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg)->
	ins_error_box($errormsg);
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline_with_checkbox($cop->get_enable(),$sphere,false,false,gettext('Sysctl Setting'))->
		last()->
		addTBODY()->
			c2_select($cop->get_name(),$sphere,true,$cop->get_name()->is_readonly_rowmode($isrecordnewornewmodify))->
			c2_input_text($cop->get_value(),$sphere,true,$cop->get_value()->is_readonly_rowmode($isrecordnewornewmodify))->
			c2_input_text($cop->get_description(),$sphere,false,$cop->get_description()->is_readonly_rowmode($isrecordnewornewmodify))->
		pop()->
		addTFOOT()->
			c2_separator();
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Current Setting'))->
		pop()->
		addTBODY()->
			c2_textinfo('infoname',gettext('Name'),$sysctl_name)->
			c2_textinfo('infodesc',gettext('Information'),$sysctl_info)->
			c2_textinfo('infotype',gettext('Data Type'),$sysctl_type)->
			c2_textinfo('infovalue',gettext('Value'),$sysctl_value);
$buttons = $document->add_area_buttons();
if($isrecordnew):
	$buttons->ins_button_add();
else:
	$buttons->ins_button_save();
	if($prerequisites_ok && empty($input_errors)):
		$buttons->ins_button_clone();
	endif;
endif;
$buttons->ins_button_cancel();
$buttons->ins_input_hidden($sphere->get_row_identifier(),$sphere->get_row_identifier_value());
//	additional javascript code
$body->addJavaScript($sphere->get_js());
$body->add_js_on_load($sphere->get_js_on_load());
$body->add_js_document_ready($sphere->get_js_document_ready());
$document->render();
