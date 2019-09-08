<?php
/*
	system_webgui.php

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

use system\webgui\setting_toolbox as toolbox;
use system\webgui\shared_toolbox;

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
$a_referer = [
/*
	$cop->get_enabletogglemode(),
	$cop->get_skipviewmode(),
	$cop->get_adddivsubmittodataframe(),
 */
	$cop->get_cssfcfile(),
	$cop->get_cssfcfilemode(),
	$cop->get_cssguifile(),
	$cop->get_cssguifilemode(),
	$cop->get_cssloginfile(),
	$cop->get_cssloginfilemode(),
	$cop->get_cssnavbarfile(),
	$cop->get_cssnavbarfilemode(),
	$cop->get_csstabsfile(),
	$cop->get_csstabsfilemode(),
	$cop->get_cssstylefile(),
	$cop->get_cssstylefilemode()
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
				$_SESSION['submit'] = $sphere->get_script()->get_basename();
				$_SESSION[$sphere->get_script()->get_basename()] = $retval;
				header($sphere->get_script()->get_location());
				exit;
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
$input_errors_found = count($input_errors) > 0;
//	create document
$pgtitle = [gettext('System'),gettext('Advanced'),gettext('WebGUI')];
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
if($pending_changes && !$input_errors_found):
	$content->ins_config_has_changed_box();
endif;
//	add content
/*
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Button Settings'))->
		last()->
		addTBODY()->
			c2_checkbox($cop->get_enabletogglemode(),$sphere,false,$is_readonly)->
			c2_checkbox($cop->get_skipviewmode(),$sphere,false,$is_readonly)->
			c2_checkbox($cop->get_adddivsubmittodataframe(),$sphere,false,$is_readonly)->
		pop()->
		addTFOOT()->
			c2_separator();
 */
$tbody = $content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('CSS Settings'))->
		pop()->
		addTBODY();
$hooks_cssfcfile = $tbody->
	c2_filechooser($cop->get_cssfcfile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_cssfcfile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_cssfcfilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_cssfcfilemode());
$hooks_cssguifile = $tbody->
	c2_filechooser($cop->get_cssguifile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_cssguifile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_cssguifilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_cssguifilemode());
$hooks_cssloginfile = $tbody->
	c2_filechooser($cop->get_cssloginfile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_cssloginfile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_cssloginfilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_cssloginfilemode());
$hooks_cssnavbarfile = $tbody->
	c2_filechooser($cop->get_cssnavbarfile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_cssnavbarfile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_cssnavbarfilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_cssnavbarfilemode());
$hooks_csstabsfile = $tbody->
	c2_filechooser($cop->get_csstabsfile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_csstabsfile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_csstabsfilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_csstabsfilemode());
$hooks_cssstylefile = $tbody->
	c2_filechooser($cop->get_cssstylefile(),$sphere,false,$is_readonly)->
	get_hooks();
$hooks_cssstylefile['fc']->
	insDIV(['class' => 'gap'])->
	ins_radio_grid($cop->get_cssstylefilemode(),$sphere,false,$is_readonly)->
	ins_description($cop->get_cssstylefilemode());
//	add buttons
$buttons = $document->add_area_buttons();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$buttons->ins_button_edit();
		break;
	case PAGE_MODE_EDIT:
		$buttons->ins_button_save();
		$buttons->ins_button_cancel();
		break;
endswitch;
//	showtime
$document->render();
