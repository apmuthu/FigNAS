<?php
/*
	system_sysctl.php

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

function sysctl_sphere() {
	global $config;

	$sphere = new co_sphere_grid('system_sysctl','php');
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	$sphere->set_notifier('sysctl');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(true);
	$sphere->set_lock(true);
	$sphere->
		setmsg_sym_add(gettext('Add sysctl variable'))->
		setmsg_sym_mod(gettext('Edit sysctl variable'))->
		setmsg_sym_del(gettext('Sysctl variable is marked for deletion'))->
		setmsg_sym_loc(gettext('Sysctl variable is locked'))->
		setmsg_sym_unl(gettext('Sysctl variable is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected Options'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected options?'))->
		setmsg_cbm_disable(gettext('Disable Selected Options'))->
		setmsg_cbm_disable_confirm(gettext('Do you want to disable selected options?'))->
		setmsg_cbm_enable(gettext('Enable Selected Options'))->
		setmsg_cbm_enable_confirm(gettext('Do you want to enable selected options?'))->
		setmsg_cbm_toggle(gettext('Toggle Selected Options'))->
		setmsg_cbm_toggle_confirm(gettext('Do you want to toggle selected options?'));
	$sphere->grid = &array_make_branch($config,'system','sysctl','param');
	return $sphere;
}
function sysctl_process_updatenotification($mode,$data) {
	global $config;

	$retval = 0;
	$sphere = &sysctl_sphere();
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if(false !== ($sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier()))):
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
//	init properties and sphere
$cop = new sysctl_properties();
$sphere = &sysctl_sphere();
$rmo = new co_request_method();
$rmo->add('POST','apply',PAGE_MODE_POST);
$rmo->add('POST',$sphere->get_cbm_button_val_delete(),PAGE_MODE_POST);
if($sphere->is_enadis_enabled() && method_exists($cop,'get_enable')):
	if($sphere->toggle()):
		$rmo->add('POST',$sphere->get_cbm_button_val_toggle(),PAGE_MODE_POST);
	else:
		$rmo->add('POST',$sphere->get_cbm_button_val_enable(),PAGE_MODE_POST);
		$rmo->add('POST',$sphere->get_cbm_button_val_disable(),PAGE_MODE_POST);
	endif;
endif;
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_method):
	case 'POST':
		switch($page_action):
			case 'apply':
				$retval = 0;
				if(!file_exists($d_sysrebootreqd_path)):
					$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
					config_lock();
					$retval |= rc_update_service($sphere->get_notifier());
					config_unlock();
				endif;
				$savemsg = get_std_save_message($retval);
				if($retval == 0):
					updatenotify_delete($sphere->get_notifier());
				endif;
				header($sphere->get_location());
				exit;
				break;
			case $sphere->get_cbm_button_val_delete():
				updatenotify_cbm_delete($sphere,$cop);
				header($sphere->get_location());
				exit;
				break;
			case $sphere->get_cbm_button_val_toggle():
				if(updatenotify_cbm_toggle($sphere,$cop)):
					write_config();
				endif;
				header($sphere->get_location());
				exit;
				break;
			case $sphere->get_cbm_button_val_enable():
				if(updatenotify_cbm_enable($sphere,$cop)):
					write_config();
				endif;
				header($sphere->get_location());
				exit;
				break;
			case $sphere->get_cbm_button_val_disable():
				if(updatenotify_cbm_disable($sphere,$cop)):
					write_config();
				endif;
				header($sphere->get_location());
				exit;
				break;
		endswitch;
		break;
endswitch;
//	sunrise: verify if setting exists, otherwise run init tasks
if(!empty($sphere->grid)):
	$key1 = array_column($sphere->grid,'name');
	$key2 = array_column($sphere->grid,'uuid');
	array_multisort($key1,SORT_ASC,SORT_NATURAL | SORT_FLAG_CASE,$key2,SORT_ASC,SORT_STRING | SORT_FLAG_CASE,$sphere->grid);
endif;
$pgtitle = [gettext('System'),gettext('Advanced'),gettext('sysctl.conf')];
$record_exists = count($sphere->grid) > 0;
$a_col_width = ['5%','30%','20%','10%','25%','10%'];
$n_col_width = count($a_col_width);
//	prepare additional javascript code
$jcode = $sphere->doj(false);
if($record_exists):
	$document = new_page($pgtitle,$sphere->get_scriptname(),'tablesort');
else:
	$document = new_page($pgtitle,$sphere->get_scriptname());
endif;
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
if(updatenotify_exists($sphere->get_notifier())):
	$content->ins_config_has_changed_box();
endif;
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->ins_titleline(gettext('Overview'),$n_col_width);
$tr = $thead->addTR();
if($record_exists):
	$tr->
		push()->
		addTHwC('lhelc sorter-false parser-false')->
			ins_cbm_checkbox_toggle($sphere)->
		pop()->
		insTHwC('lhell',$cop->get_name()->get_title())->
		insTHwC('lhell',$cop->get_value()->get_title())->
		insTHwC('lhelc sorter-image',gettext('Status'))->
		insTHwC('lhell',$cop->get_comment()->get_title())->
		insTHwC('lhebl sorter-false parser-false',$cop->get_toolbox()->get_title());
else:
	$tr->
		insTHwC('lhelc')->
		insTHwC('lhell',$cop->get_name()->get_title())->
		insTHwC('lhell',$cop->get_value()->get_title())->
		insTHwC('lhelc',gettext('Status'))->
		insTHwC('lhell',$cop->get_comment()->get_title())->
		insTHwC('lhebl',$cop->get_toolbox()->get_title());
endif;
if($record_exists):
	foreach($sphere->grid as $sphere->row):
		$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->get_row_identifier_value());
		$is_notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
		$is_enabled = $sphere->is_enadis_enabled() ? (is_bool($test = $sphere->row[$cop->get_enable()->get_name()] ?? false) ? $test : true) : true;
		$is_notprotected = $sphere->is_lock_enabled() ? !(is_bool($test = $sphere->row[$cop->get_protected()->get_name()] ?? false) ? $test : true) : true;
		if($is_enabled):
			$src = $g_img['ena'];
			$title = gettext('Enabled');
			$dc = '';
		else:
			$src = $g_img['dis'];
			$title = gettext('Disabled');
			$dc = 'd';
		endif;
		$tbody->
			addTR()->
				push()->
				addTDwC('lcelc' . $dc)->
					ins_cbm_checkbox($sphere,!($is_notdirty && $is_notprotected))->
				last()->
				insTDwC('lcell' . $dc,$sphere->row[$cop->get_name()->get_name()] ?? '')->
				insTDwC('lcell' . $dc,$sphere->row[$cop->get_value()->get_name()] ?? '')->
				addTDwC('lcelc' . $dc)->
					addA(['title' => $title])->insIMG(['src' => $src,'alt' => $title,'class' => 'oneemhigh'])->
				pop()->
				insTDwC('lcell' . $dc,$sphere->row[$cop->get_comment()->get_name()] ?? '')->
				add_toolbox_area()->
					ins_toolbox($sphere,$is_notprotected,$is_notdirty)->
					ins_maintainbox($sphere,false)->
					ins_informbox($sphere,false);
	endforeach;
else:
	$tbody->ins_no_records_found($n_col_width);
endif;
$tfoot->ins_record_add($sphere,$n_col_width);
$document->add_area_buttons()->ins_cbm_button_enadis($sphere)->ins_cbm_button_delete($sphere);
$content->add_area_remarks()->ins_remark('note',gettext('Note'),gettext('These sysctl variables will be added to /etc/sysctl.conf. This allows you to make changes to a running system.'));
$document->render();
