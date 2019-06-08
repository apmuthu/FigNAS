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
require_once 'co_sphere.php';
require_once 'properties_sysctl.php';
require_once 'co_request_method.php';

function sysctl_edit_sphere() {
	global $config;

//	sphere structure
	$sphere = new co_sphere_row('system_sysctl_edit','php');
	$sphere->get_parent()->set_basename('system_sysctl');
	$sphere->set_notifier('sysctl');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->grid = &array_make_branch($config,'system','sysctl','param');
	return $sphere;
}
//	init properties and sphere
$cop = new sysctl_edit_properties();
$sphere = &sysctl_edit_sphere();

//	Collect all writeable sysctl's
unset($writable_sysctls);
exec('/sbin/sysctl -ANW',$writable_sysctls);
usort($writable_sysctls,'strcasecmp');
$options = array_combine($writable_sysctls,$writable_sysctls);
$cop->get_name()->set_options($options);
$rmo = new co_request_method();
$rmo->add('GET','add',PAGE_MODE_ADD);
$rmo->add('GET','edit',PAGE_MODE_EDIT);
$rmo->add('POST','add',PAGE_MODE_ADD);
$rmo->add('POST','cancel',PAGE_MODE_POST);
$rmo->add('POST','clone',PAGE_MODE_CLONE);
$rmo->add('POST','edit',PAGE_MODE_EDIT);
$rmo->add('POST','save',PAGE_MODE_POST);
$rmo->set_default('POST','cancel',PAGE_MODE_POST);
list($page_method,$page_action,$page_mode) = $rmo->validate();
//	init indicators
$input_errors = [];
$prerequisites_ok = true;
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
$isrecordnew = (RECORD_NEW === $record_mode);
$isrecordnewmodify = (RECORD_NEW_MODIFY === $record_mode);
$isrecordmodify = (RECORD_MODIFY === $record_mode);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
/*
 *	end determine record update mode
 */
$a_referer = [
	$cop->get_enable(),
	$cop->get_name(),
	$cop->get_comment(),
	$cop->get_value()
];
switch($page_mode):
	case PAGE_MODE_ADD:
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_CLONE:
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_input() ?? $referer->get_defaultvalue();
		endforeach;
		//	adjust page mode
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
		// apply post values that are applicable for all record modes
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_input();
			if(!isset($sphere->row[$name])):
				$sphere->row[$name] = $_POST[$name] ?? '';
				$input_errors[] = $referer->get_message_error();
			endif;
		endforeach;
		if($prerequisites_ok && empty($input_errors)):
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
$pgtitle = [gettext('System'),gettext('Advanced'),gettext('sysctl.conf'),$isrecordnew ? gettext('Add') : gettext('Edit')];
$document = new_page($pgtitle,$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
			ins_tabnav_record('system_email.php',gettext('Email'))->
			ins_tabnav_record('system_email_reports.php',gettext('Email Reports'))->
			ins_tabnav_record('system_monitoring.php',gettext('Monitoring'))->
			ins_tabnav_record('system_swap.php',gettext('Swap'))->
			ins_tabnav_record('system_rc.php',gettext('Command Scripts'))->
			ins_tabnav_record('system_cron.php',gettext('Cron'))->
			ins_tabnav_record('system_loaderconf.php',gettext('loader.conf'))->
			ins_tabnav_record('system_rcconf.php',gettext('rc.conf'))->
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'),gettext('Reload page'),true)->
			ins_tabnav_record('system_syslogconf.php',gettext('syslog.conf'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'),gettext('Reload page'),true)->
			ins_tabnav_record('system_sysctl_info.php',gettext('Information'));
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
$content->add_table_data_settings()->
	ins_colgroup_data_settings()->
	push()->
	addTHEAD()->
		c2_titleline_with_checkbox($cop->get_enable(),$sphere->row[$cop->get_enable()->get_name()],false,false,gettext('Configuration'))->
	last()->
	addTBODY()->
		c2_select($cop->get_name(),$sphere->row[$cop->get_name()->get_name()],true,false)->
		c2_input_text($cop->get_value(),$sphere->row[$cop->get_value()->get_name()],true,false)->
		c2_input_text($cop->get_comment(),$sphere->row[$cop->get_comment()->get_name()],false,false)->
	pop()->
	addTFoot()->
		c2_separator();
$content->add_table_data_settings()->
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
