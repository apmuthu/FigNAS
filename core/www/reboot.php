<?php
/*
	reboot.php

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

function reboot_sphere() {
//	sphere structure
	$sphere = new co_sphere_row('reboot','php');
	$sphere->get_parent()->set_basename('index');
	return $sphere;
}
$cmd_perform_action = false;
//	init sphere
$sphere = &reboot_sphere();
$rmo = new co_request_method();
$rmo->add('POST','save',PAGE_MODE_POST);
$rmo->add('POST','cancel',PAGE_MODE_POST);
$rmo->add('SESSION',$sphere->get_scriptname(),PAGE_MODE_VIEW);
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'SESSION':
		switch($page_action):
			case $sphere->get_scriptname():
				$retval = filter_var($_SESSION[$sphere->get_scriptname()],FILTER_VALIDATE_INT,['options' => ['default' => 0]]);
				unset($_SESSION['submit'],$_SESSION[$sphere->get_scriptname()]);
				if($retval !== 0):
					$cmd_perform_action = true;
					if(file_exists($d_sysrebootreqd_path)):
						unlink($d_sysrebootreqd_path);
					endif;
				endif;
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'cancel': // cancel - nothing to do
				header($sphere->get_parent()->get_location());
				exit;
			case 'save': // reboot
				$_SESSION['submit'] = $sphere->get_scriptname();
				$_SESSION[$sphere->get_scriptname()] = '1';
				header($sphere->get_location());
				exit;
				break;
		endswitch;
		break;
endswitch;
$pgtitle = [gettext('System'),gettext('Reboot'),gettext('Now')];
$document = new_page($pgtitle,$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('reboot.php',gettext('Now'),gettext('Reload page'),true)->
			ins_tabnav_record('reboot_sched.php',gettext('Scheduled'));
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	add_table_data_settings()->
		push()->
		addCOLGROUP()->
			insCOL(['style' => 'width: 100%'])->
		pop()->
			addTHEAD()->
				ins_titleline(gettext('Reboot'));
if($cmd_perform_action):
	$content->ins_info_box(gettext('The server is rebooting now.'));
else:
	$content->ins_warning_box(gettext('Are you sure you want to reboot the server?'));
	$buttons = $content->add_area_buttons(false);
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
	system_reboot();
else:
	$document->render();
endif;
