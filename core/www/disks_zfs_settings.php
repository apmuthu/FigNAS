<?php
/*
	disks_zfs_settings.php

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
require_once 'properties_disks_zfs_settings.php';
require_once 'co_request_method.php';

function get_sphere_disks_zfs_settings() {
	global $config;
	
	$sphere = new co_sphere_settings('disks_zfs_settings','php');
	$sphere->grid = &array_make_branch($config,'zfs','settings');
	return $sphere;
}
//	init properties and sphere
$cop = new properties_disks_zfs_settings();
$sphere = &get_sphere_disks_zfs_settings();
$input_errors = [];
$savemsg = '';
//	determine request method
$rmo = new co_request_method();
$rmo->add('GET','edit',PAGE_MODE_EDIT);
$rmo->add('GET','view',PAGE_MODE_VIEW);
$rmo->add('POST','edit',PAGE_MODE_EDIT);
$rmo->add('POST','save',PAGE_MODE_POST);
$rmo->add('POST','view',PAGE_MODE_VIEW);
$rmo->add('SESSION',$sphere->get_basename(),PAGE_MODE_VIEW);
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
$a_referer = [
	$cop->get_showusedavail(),
	$cop->get_capacity_warning(),
	$cop->get_capacity_critical(),
	$cop->get_scanondisk()
];
switch($page_action):
	case $sphere->get_basename():
		$retval = filter_var($_SESSION[$sphere->get_basename()],FILTER_VALIDATE_INT,['options' => ['default' => 0]]);
		unset($_SESSION['submit']);
		unset($_SESSION[$sphere->get_basename()]);
		$savemsg = get_std_save_message($retval);
		break;
endswitch;
switch($page_mode):
	case PAGE_MODE_VIEW:
	case PAGE_MODE_EDIT:
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_config($sphere->grid);
			if(is_null($sphere->row[$name])):
				if(array_key_exists($name,$sphere->grid)): 
					$sphere->row[$name] = $sphere->grid[$name];
				else:
					$sphere->row[$name] = $referer->get_defaultvalue();
				endif;
			endif;
		endforeach;
		break;
	case PAGE_MODE_POST:
		foreach($a_referer as $referer):
			$name = $referer->get_name();
			$sphere->row[$name] = $referer->validate_input();
			if(is_null($sphere->row[$name])):
				$input_errors[] = $referer->get_message_error();
				if(array_key_exists($name,$_POST)): 
					$sphere->row[$name] = $_POST[$name];
				else:
					$sphere->row[$name] = $referer->get_defaultvalue();
				endif;
			endif;
		endforeach;
		if(empty($input_errors)):
			$sphere->copyrowtogrid();
			write_config();
			$retval = 0;
			$_SESSION['submit'] = $sphere->get_basename();
			$_SESSION[$sphere->get_basename()] = $retval;
			header($sphere->get_location());
			exit;
		else:
			$page_mode = PAGE_MODE_EDIT;
		endif;
		break;
endswitch;
//	determine final page mode and calculate readonly flag
list($page_mode,$is_readonly) = calc_skipviewmode($page_mode);
//	create document
$document = new_page([gettext('Disks'),gettext('ZFS'),gettext('Settings')],$sphere->get_scriptname());
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'),gettext('Reload page'),true);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg);
//	add content
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('ZFS Settings'))->
		pop()->
		addTBODY()->
			c2_checkbox($cop->get_showusedavail(),$sphere->row[$cop->get_showusedavail()->get_name()],false,$is_readonly);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Maintenance Tool Settings'))->
		pop()->
		addTBODY()->
			c2_checkbox($cop->get_scanondisk(),$sphere->row[$cop->get_scanondisk()->get_name()],false,$is_readonly);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_separator()->
			c2_titleline(gettext('Capacity Alert Thresholds'))->
		pop()->
		addTBODY()->
			c2_input_text($cop->get_capacity_warning(),$sphere->row[$cop->get_capacity_warning()->get_name()],false,$is_readonly)->
			c2_input_text($cop->get_capacity_critical(),$sphere->row[$cop->get_capacity_critical()->get_name()],false,$is_readonly);
//	add buttons
switch($page_mode):
	case PAGE_MODE_VIEW:
		$document->add_area_buttons()->ins_button_edit();
		break;
	case PAGE_MODE_EDIT:
		$document->add_area_buttons()->ins_button_save()->ins_button_cancel();
		break;
endswitch;
//	add additional javascript code
$body->add_js_on_load($sphere->get_js_on_load(),'grid');
$body->add_js_document_ready($sphere->get_js_document_ready(),'grid');
$body->addJavaScript($sphere->get_js(),'grid');
//	showtime
$document->render();
