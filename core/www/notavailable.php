<?php
/*
	notavailable.php

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

function notavailable_sphere() {
	global $config;

//	sphere structure
	$sphere = new co_sphere_row('notavailable','php');
	$sphere->get_parent()->set_basename('index');
	return $sphere;
}
//	init sphere
$sphere = &notavailable_sphere();
$rmo = new co_request_method();
$rmo->add('POST','cancel',PAGE_MODE_POST);
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'POST':
		switch($page_action):
			case 'cancel': // cancel - nothing to do
				header($sphere->get_parent()->get_location());
				exit;
		endswitch;
		break;
endswitch;
$pgtitle = [gettext('NOT YET AVAILABLE')];
$document = new_page($pgtitle,$sphere->get_scriptname(),'notabnav');
$pagecontent = $document->getElementById('pagecontent');
$content = $pagecontent->add_area_data();
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('NOT YET AVAILABLE'));
$document->
	add_area_buttons()->
		ins_button_cancel();
//	showtime
$document->render();
