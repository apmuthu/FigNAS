<?php
/*
	diag_log_settings.php

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
require_once 'diag_log.inc';
require_once 'co_sphere.php';
require_once 'properties_diag_log_settings.php';

function get_sphere_diag_log_settings() {
	global $config;
	$sphere = new co_sphere_row('diag_log_settings','php');
	$sphere->grid = &array_make_branch($config,'syslogd');
	array_make_branch($config,'syslogd','remote');
	return $sphere;
}
//	init properties and sphere
$cop = new properties_diag_log_settings();
$sphere = &get_sphere_diag_log_settings();
$input_errors = [];
$savemsg = '';
//	request method
$methods = ['GET','POST'];
$methods_regexp = sprintf('/^(%s)$/',implode('|',array_map(function($element) { return preg_quote($element,'/'); },$methods)));
if(array_key_exists('REQUEST_METHOD',$_SERVER)):
	$method = filter_var($_SERVER['REQUEST_METHOD'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $methods_regexp]]);
else:
	$method = NULL;
endif;
//	determine page mode
$page_mode = PAGE_MODE_VIEW;
switch($method):
	case 'POST':
		$actions = ['edit','save','cancel'];
		$actions_regexp = sprintf('/^(%s)$/',implode('|',array_map(function($element) { return preg_quote($element,'/'); },$actions)));
		$action = filter_input(INPUT_POST,'submit',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '','regexp' => $actions_regexp]]);
		switch($action):
			case 'edit':
				$page_mode = PAGE_MODE_EDIT;
				break;
			case 'save':
				$page_mode = PAGE_MODE_POST;
				break;
		endswitch;
		break;
endswitch;
switch($page_mode):
	case PAGE_MODE_VIEW:
	case PAGE_MODE_EDIT:
		$a_referer = [
			$cop->get_disablecomp(),
			$cop->get_disablesecure(),
			$cop->get_resolve(),
			$cop->get_reverse()
		];
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->validate_config($sphere->grid);
		endforeach;
		$a_referer = [
			$cop->get_daemon(),
			$cop->get_enable(),
			$cop->get_ftp(),
			$cop->get_rsyncd(),
			$cop->get_smartd(),
			$cop->get_sshd(),
			$cop->get_system()
		];
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->validate_config($sphere->grid['remote']);
		endforeach;
		$referer = $cop->get_nentries();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_array_element($sphere->grid);
		if(is_null($sphere->row[$referer_name])):
			if(array_key_exists($referer_name,$sphere->grid) && is_scalar($sphere->grid[$referer_name])): 
				$input_errors[] = $referer->get_message_error();
				$sphere->row[$referer_name] = $sphere->grid[$referer_name];
			else:
				$sphere->row[$referer_name] = $referer->get_defaultvalue();
			endif;
		endif;
		$referer = $cop->get_ipaddr();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_array_element($sphere->grid['remote']);
		if(is_null($sphere->row[$referer_name])):
			$throw_error = $sphere->row[$cop->get_enable()->get_name()];
			if(array_key_exists($referer_name,$sphere->grid['remote']) && is_string($sphere->grid['remote'][$referer_name]) && preg_match('/\S/',$sphere->grid['remote'][$referer_name])):
				$throw_error = true;
				$sphere->row[$referer_name] = $sphere->grid['remote'][$referer_name];
			else:
				$sphere->row[$referer_name] = $referer->get_defaultvalue();
			endif;
			if($throw_error):
				$input_errors[] = $referer->get_message_error();
			endif;
		endif;
		$referer = $cop->get_port();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_array_element($sphere->grid['remote'],['ui','514','empty']);
		if(is_null($sphere->row[$referer_name])):
			if(array_key_exists($referer_name,$sphere->grid['remote']) && is_scalar($sphere->grid['remote'][$referer_name])):
				$input_errors[] = $referer->get_message_error();
				$sphere->row[$referer_name] = $sphere->grid['remote'][$referer_name];
			else:
				$sphere->row[$referer_name] = $referer->get_defaultvalue();
			endif;
		endif;
		break;
	case PAGE_MODE_POST:
		$a_referer = [
			$cop->get_disablecomp(),
			$cop->get_disablesecure(),
			$cop->get_resolve(),
			$cop->get_reverse(),
			$cop->get_daemon(),
			$cop->get_enable(),
			$cop->get_ftp(),
			$cop->get_rsyncd(),
			$cop->get_smartd(),
			$cop->get_sshd(),
			$cop->get_system()
		];
		foreach($a_referer as $referer):
			$sphere->row[$referer->get_name()] = $referer->validate_input();
		endforeach;
		$referer = $cop->get_nentries();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_input();
		if(is_null($sphere->row[$referer_name])):
			$input_errors[] = $referer->get_message_error();
			if(array_key_exists($referer_name,$_POST) && is_scalar($_POST[$referer_name])): 
				$sphere->row[$referer_name] = $_POST[$referer_name];
			else:
				$sphere->row[$referer_name] = $referer->get_defaultvalue();
			endif;
		endif;
		//	IP address must be valid when remote syslog is enabled
		//	IP address can be empty or must be valid when remote syslog is disabled
		$referer = $cop->get_ipaddr();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_input();
		if(is_null($sphere->row[$referer_name])):
			$sphere->row[$referer_name] = filter_input(INPUT_POST,$referer_name,FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
			if($sphere->row[$cop->get_enable()->get_name()] || preg_match('/\S/',$sphere->row[$referer_name])):
				$input_errors[] = $referer->get_message_error();
			endif;
		endif;
		//	Port must be empty or a valid port number
		$referer = $cop->get_port();
		$referer_name = $referer->get_name();
		$sphere->row[$referer_name] = $referer->validate_input(INPUT_POST,['ui','514','empty']);
		if(is_null($sphere->row[$referer_name])):
			$sphere->row[$referer_name] = $referer->validate_input(INPUT_POST,'scalar');
			$input_errors[] = $referer->get_message_error();
		endif;
		if(empty($input_errors)):
			$a_referer_name = [
				$cop->get_disablecomp()->get_name(),
				$cop->get_disablesecure()->get_name(),
				$cop->get_nentries()->get_name(),
				$cop->get_resolve()->get_name(),
				$cop->get_reverse()->get_name()
			];
			foreach($a_referer_name as $referer_name):
				$sphere->grid[$referer_name] = $sphere->row[$referer_name];
			endforeach;
			$a_referer_name = [
				$cop->get_daemon()->get_name(),
				$cop->get_enable()->get_name(),
				$cop->get_ftp()->get_name(),
				$cop->get_ipaddr()->get_name(),
				$cop->get_port()->get_name(),
				$cop->get_rsyncd()->get_name(),
				$cop->get_smartd()->get_name(),
				$cop->get_sshd()->get_name(),
				$cop->get_system()->get_name()
			];
			foreach($a_referer_name as $referer_name):
				$sphere->grid['remote'][$referer_name] = $sphere->row[$referer_name];
			endforeach;
			write_config();
			$retval = 0;
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval = rc_restart_service('syslogd');
				config_unlock();
			endif;
			$savemsg = get_std_save_message($retval);
			$page_mode = PAGE_MODE_VIEW;
		else:
			$page_mode = PAGE_MODE_EDIT;
		endif;
		break;
endswitch;
//	determine final page mode and calculate readonly flag
switch($page_mode):
	case PAGE_MODE_EDIT:
		$is_readonly = false;
		break;
	default:
		if(is_bool($test = $config['system']['skipviewmode'] ?? false) ? $test : true):
			$page_mode = PAGE_MODE_EDIT;
			$is_readonly = false;
		else:
			$page_mode = PAGE_MODE_VIEW;
			$is_readonly = true;
		endif;
		break;
endswitch;
//	prepare additional javascript code
$jcode = [];
if(is_bool($test = $config['system']['showdisabledsections'] ?? false) ? $test : true):
	$jcode[PAGE_MODE_EDIT] = NULL;
	$jcode[PAGE_MODE_VIEW] = NULL;
else:
	$jcode[PAGE_MODE_EDIT] = <<<EOJ
$(document).ready(function() {
	$('#{$cop->get_enable()->get_id()}').change(function() {
		switch(this.checked) {
			case true:
				$('#{$cop->get_ipaddr()->get_id()}_tr').show();
				$('#{$cop->get_port()->get_id()}_tr').show();
				$('#{$cop->get_system()->get_id()}_tr').show();
				$('#{$cop->get_ftp()->get_id()}_tr').show();
				$('#{$cop->get_rsyncd()->get_id()}_tr').show();
				$('#{$cop->get_sshd()->get_id()}_tr').show();
				$('#{$cop->get_smartd()->get_id()}_tr').show();
				$('#{$cop->get_daemon()->get_id()}_tr').show();
				break;
			case false:
				$('#{$cop->get_daemon()->get_id()}_tr').hide();
				$('#{$cop->get_smartd()->get_id()}_tr').hide();
				$('#{$cop->get_sshd()->get_id()}_tr').hide();
				$('#{$cop->get_rsyncd()->get_id()}_tr').hide();
				$('#{$cop->get_ftp()->get_id()}_tr').hide();
				$('#{$cop->get_system()->get_id()}_tr').hide();
				$('#{$cop->get_port()->get_id()}_tr').hide();
				$('#{$cop->get_ipaddr()->get_id()}_tr').hide();
				break;
        }
    });
	$('#{$cop->get_enable()->get_id()}').change();
});
EOJ;
	$jcode[PAGE_MODE_VIEW] = $jcode[PAGE_MODE_EDIT];
endif;
//	create document
$document = new_page([gettext('Diagnostics'),gettext('Log'),gettext('Settings')],$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add additional javascript code
if(isset($jcode[$page_mode])):
	$body->addJavaScript($jcode[$page_mode]);
endif;
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_log.php',gettext('Log'))->
			ins_tabnav_record('diag_log_settings.php',gettext('Settings'),gettext('Reload page'),true);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg);
//	add content
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Log Settings'))->
			parentNode->
		addTBODY()->
			c2_checkbox($cop->get_reverse(),$sphere->row[$cop->get_reverse()->get_name()],false,$is_readonly)->
			c2_input_text($cop->get_nentries(),$sphere->row[$cop->get_nentries()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_resolve(),$sphere->row[$cop->get_resolve()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_disablecomp(),$sphere->row[$cop->get_disablecomp()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_disablesecure(),$sphere->row[$cop->get_disablesecure()->get_name()],false,$is_readonly);
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_separator()->
			c2_titleline_with_checkbox($cop->get_enable(),$sphere->row[$cop->get_enable()->get_name()],false,$is_readonly,gettext('Remote Syslog Server'))->
			parentNode->
		addTBODY()->
			c2_input_text($cop->get_ipaddr(),$sphere->row[$cop->get_ipaddr()->get_name()],false,$is_readonly)->
			c2_input_text($cop->get_port(),$sphere->row[$cop->get_port()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_system(),$sphere->row[$cop->get_system()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_ftp(),$sphere->row[$cop->get_ftp()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_rsyncd(),$sphere->row[$cop->get_rsyncd()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_sshd(),$sphere->row[$cop->get_sshd()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_smartd(),$sphere->row[$cop->get_smartd()->get_name()],false,$is_readonly)->
			c2_checkbox($cop->get_daemon(),$sphere->row[$cop->get_daemon()->get_name()],false,$is_readonly);
//	add buttons
switch($page_mode):
	case PAGE_MODE_VIEW:
		$document->add_area_buttons()->ins_button_edit();
		break;
	case PAGE_MODE_EDIT:
		$document->add_area_buttons()->ins_button_save()->ins_button_cancel();
		break;
endswitch;
//	done
$document->render();
