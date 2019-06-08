<?php
/*
	services_minidlna.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

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

use services\minidlnad\setting_toolbox as toolbox;
use services\minidlnad\shared_toolbox;

//	init indicators
$input_errors = [];
//	preset $savemsg when a reboot is pending
if(file_exists($d_sysrebootreqd_path)):
	$savemsg = get_std_save_message(0);
endif;
//	init properties, sphere and rmo
$cop = toolbox::init_properties();
$sphere = toolbox::init_sphere();
$rmo = toolbox::init_rmo($cop,$sphere);
//	list of configured interfaces
$a_interface = get_interface_list();
$l_interfaces = [];
foreach($a_interface as $k_interface => $ifinfo):
	$ifinfo = get_interface_info($k_interface);
	switch($ifinfo['status']):
		case 'up':
		case 'associated':
			$l_interfaces[$k_interface] = $k_interface;
			break;
	endswitch;
endforeach;
$cop->get_interface()->set_options($l_interfaces);
$a_referer = [
	$cop->get_auxparam(),
//	$cop->get_disablesubtitles(),
	$cop->get_enable(),
	$cop->get_enabletivo(),
	$cop->get_forcesortcriteria(),
	$cop->get_friendlyname(),
	$cop->get_home(),
	$cop->get_inotify(),
	$cop->get_interface(),
	$cop->get_loglevel(),
	$cop->get_notifyinterval(),
	$cop->get_port(),
	$cop->get_rootcontainer(),
	$cop->get_strict(),
	$cop->get_widelinks()
];
$pending_changes = updatenotify_exists($sphere->get_notifier());
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'SESSION':
		switch($page_action):
			case $sphere->get_script()->get_basename():
				$retval = filter_var($_SESSION[$sphere->get_script()->get_basename()],FILTER_VALIDATE_INT,['options' => ['default' => 0]]);
				unset($_SESSION['submit'],$_SESSION[$sphere->get_script()->get_basename()]);
				$savemsg = get_std_save_message($retval);
				if($retval !== 0):
					$page_action = 'edit';
					$page_mode = PAGE_MODE_EDIT;
				else:
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				endif;
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'apply':
				$retval = 0;
				$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
				chown($sphere->grid[$cop->get_home()->get_name()],'dlna');
				chmod($sphere->grid[$cop->get_home()->get_name()],0755);
				config_lock();
				$retval != rc_stop_service('minidlna');
				$retval |= rc_update_service('minidlna');
				$retval |= rc_update_service('mdnsresponder');
				config_unlock();
				$_SESSION['submit'] = $sphere->get_script()->get_basename();
				$_SESSION[$sphere->get_script()->get_basename()] = $retval;
				header($sphere->get_script()->get_location());
				exit;
				break;
			case 'disable':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				if($sphere->grid[$name]):
					$sphere->grid[$name] = false;
					write_config();
					config_lock();
					$retval |= rc_update_service('minidlna');
					$retval |= rc_update_service('mdnsresponder');
					config_unlock();
					$_SESSION['submit'] = $sphere->get_script()->get_basename();
					$_SESSION[$sphere->get_script()->get_basename()] = $retval;
					header($sphere->get_script()->get_location());
					exit;
				else:
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				endif;
			case 'enable':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				if($sphere->grid[$name] || $pending_changes):
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				else:
					$sphere->grid[$name] = true;
					write_config();
					config_lock();
					$retval |= rc_update_service('minidlna');
					$retval |= rc_update_service('mdnsresponder');
					config_unlock();
					$_SESSION['submit'] = $sphere->get_script()->get_basename();
					$_SESSION[$sphere->get_script()->get_basename()] = $retval;
					header($sphere->get_script()->get_location());
					exit;
				endif;
				break;
			case 'reload':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				if($sphere->grid[$name]):
					mwexec_bg('service minidlna rescan');
					$savemsg = gettext('A rescan has been issued.');
				endif;
				$page_action = 'view';
				$page_mode = PAGE_MODE_VIEW;
				break;
		endswitch;
		break;
endswitch;
//	validate
switch($page_action):
	case 'edit':
	case 'view':
		$source = $sphere->grid;
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			switch($name):
				case 'auxparam':
					if(array_key_exists($name,$source)):
						if(is_array($source[$name])):
							$source[$name] = implode(PHP_EOL,$source[$name]);
						endif;
					endif;
					break;
			endswitch;
			$sphere->row[$name] = $referer->validate_array_element($source);
			if(is_null($sphere->row[$name])):
				if(array_key_exists($name,$source) && is_scalar($source[$name])):
					$sphere->row[$name] = $source[$name];
				else:
					$sphere->row[$name] = $referer->get_defaultvalue();
				endif;
			endif;
		endforeach;
		break;
	case 'save':
		$source = $_POST;
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_input();
			if(is_null($sphere->row[$name])):
				$input_errors[] = $referer->get_message_error();
				if(array_key_exists($name,$source) && is_scalar($source[$name])):
					$sphere->row[$name] = $source[$name];
				else:
					$sphere->row[$name] = $referer->get_defaultvalue();
				endif;
			endif;
		endforeach;
		if(empty($input_errors)):
			foreach($a_referer as $referer):
				$name = $referer->get_name();
				switch($name):
					case 'auxparam':
						$auxparam_grid = [];
						foreach(explode(PHP_EOL,$sphere->row[$name]) as $auxparam_row):
							$auxparam_grid[] = trim($auxparam_row,"\t\n\r");
						endforeach;
						$sphere->row[$name] = $auxparam_grid;
						break;
				endswitch;
				$sphere->grid[$name] = $sphere->row[$name];
			endforeach;
			write_config();
			updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,'SERVICE',$sphere->get_notifier_processor());
			header($sphere->get_script()->get_location());
			exit;
		else:
			$page_mode = PAGE_MODE_EDIT;
		endif;
		break;
endswitch;
//	determine final page mode and calculate readonly flag
list($page_mode,$is_readonly) = calc_skipviewmode($page_mode);
$is_enabled = $sphere->row[$cop->get_enable()->get_name()];
$is_running = (0 === rc_is_service_running('minidlna'));
$is_running_message = $is_running ? gettext('Yes') : gettext('No');
//	create document
$pgtitle = [gettext('Services'),gettext('MiniDLNA'),gettext('Settings')];
$document = new_page($pgtitle,$sphere->get_script()->get_scriptname());
//	add tab navigation
shared_toolbox::add_tabnav($document);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg)->
	ins_error_box($errormsg);
if($pending_changes):
	$content->ins_config_has_changed_box();
endif;
//	add content
$n_auxparam_rows = min(64,max(5,1 + substr_count($sphere->row[$cop->get_auxparam()->get_name()],PHP_EOL)));
$tds = $content->add_table_data_settings();
$tds->ins_colgroup_data_settings();
$thead = $tds->addTHEAD();
$tbody = $tds->addTBODY();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$thead->c2_titleline(gettext('MiniDLNA'));
		break;
	case PAGE_MODE_EDIT:
		$thead->c2_titleline_with_checkbox($cop->get_enable(),$sphere,false,$is_readonly,gettext('MiniDLNA'));
		break;
endswitch;
$tbody->
	c2_textinfo('running',gettext('Service Active'),$is_running_message)->
	c2_input_text($cop->get_friendlyname(),$sphere,true,$is_readonly)->
	c2_select($cop->get_interface(),$sphere,true,$is_readonly)->
	c2_input_text($cop->get_port(),$sphere,false,$is_readonly)->
	c2_input_text($cop->get_notifyinterval(),$sphere,false,$is_readonly)->
	c2_filechooser($cop->get_home(),$sphere,true,$is_readonly)->
	c2_checkbox($cop->get_inotify(),$sphere,false,$is_readonly)->
	c2_radio_grid($cop->get_rootcontainer(),$sphere,false,$is_readonly)->
	c2_checkbox($cop->get_strict(),$sphere,false,$is_readonly)->
	c2_checkbox($cop->get_enabletivo(),$sphere,false,$is_readonly)->
	c2_checkbox($cop->get_widelinks(),$sphere,false,$is_readonly)->
	c2_select($cop->get_loglevel(),$sphere,false,$is_readonly)->
	c2_input_text($cop->get_forcesortcriteria(),$sphere,false,$is_readonly)->
	c2_textarea($cop->get_auxparam(),$sphere,false,$is_readonly,60,$n_auxparam_rows);
if($is_running):
	$tbody->addTFOOT()->c2_separator();
	$if = get_ifname($sphere->row['if']);
	$ipaddr = get_ipaddr($if);
	if(preg_match('/\S/',$sphere->row[$cop->get_port()->get_name()])):
		$url = sprintf('http://%s:%s/status',$ipaddr,$sphere->row[$cop->get_port()->get_name()]);
	else:
		$url = sprintf('http://%s:8200/status',$ipaddr);
	endif;
	$text = sprintf('<a href="%s" target="_blank">%s</a>',$url,$url);
	$content->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_titleline(gettext('MiniDLNA Media Server WebGUI'))->
			pop()->
			addTBODY()->
				c2_textinfo('url',gettext('URL'),$text);
endif;
//	add buttons
$buttons = $document->add_area_buttons();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$buttons->ins_button_edit();
		if($pending_changes && $is_enabled):
			$buttons->ins_button_enadis(!$is_enabled);
		elseif(!$pending_changes):
			$buttons->ins_button_enadis(!$is_enabled);
			$buttons->ins_button_reload($is_enabled,gettext('Rescan'));
		endif;
		break;
	case PAGE_MODE_EDIT:
		$buttons->ins_button_save();
		$buttons->ins_button_cancel();
		break;
endswitch;
//	showtime
$document->render();
