<?php
/*
	system_defaults.php

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
require_once 'co_request_method.php';

function system_defaults_sphere() {
//	sphere structure
	$sphere = new co_sphere_row('system_defaults','php');
	$sphere->get_parent()->set_basename('index');
	return $sphere;
}
$cmd_perform_action = false;
//	init sphere
$sphere = &system_defaults_sphere();
$rmo = new co_request_method();
$rmo->add('POST','save',PAGE_MODE_POST);
$rmo->add('POST','cancel',PAGE_MODE_POST);
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'POST':
		switch($page_action):
			case 'cancel': // cancel - nothing to do
				header($sphere->get_parent()->get_location());
				exit;
			case 'save': // reboot
				$cmd_perform_action = true;
				if(file_exists($d_sysrebootreqd_path)):
					unlink($d_sysrebootreqd_path);
				endif;
				break;
		endswitch;
		break;
endswitch;
$pgtitle = [gettext('System'),gettext('Factory Defaults')];
$document = new_page($pgtitle,$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if($cmd_perform_action):
	$content->ins_info_box(gettext('The system configuration is reset to factory defaults and the server is restarted.'));
else:
	$content->ins_warning_box(gettext('Are you sure you want to reset the system configuration to factory defaults?'));
endif;
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Factory Defaults'))->
		pop()->
		addTBODY(['class' => 'donothighlight'])->
			c2_textinfo('note1',gettext('Note'),gettext('The system configuration is reset to factory defaults and the server is restarted.'))->
			c2_textinfo('note2',gettext('Warning'),gettext('The entire system configuration will be overwritten.'))->
			c2_textinfo('note3',gettext('IP Address'),$g['default_ip'])->
			c2_textinfo('note4',gettext('Admin Password'),$g['default_passwd']);
if($cmd_perform_action):
else:
	$buttons = $document->add_area_buttons();
	$buttons->
		ins_button_save(gettext('Yes'))->
		ins_button_cancel(gettext('No'));
	if('0401' == date('md')):
		$buttons->ins_button_cancel(gettext('Maybe'));
	endif;
endif;
//	showtime
if($cmd_perform_action):
	ob_start();
	$document->render();
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	while(ob_get_level() > 0):
		ob_end_flush();
	endwhile;
	flush();
	sleep(5);
	reset_factory_defaults();
	flush();
	sleep(5);
	system_reboot();
else:
	$document->render();
endif;
