<?php
/*
	diag_updatenotifier.php

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

function get_sphere_diag_updatenotifier() {
//	sphere structure
	$sphere = new co_sphere_grid('diag_updatenotifier','php');
	$sphere->set_row_identifier('id');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->
		setmsg_cbm_delete(gettext('Delete Selected Notifier'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected notifier?'));
	$sphere->grid = updatenotify_get_all();
	return $sphere;
}
$sphere = get_sphere_diag_updatenotifier();
//	determine request method
$rmo = new co_request_method();
$rmo->add('POST',$sphere->get_cbm_button_val_delete(),PAGE_MODE_POST);
$rmo->set_default('GET','view',PAGE_MODE_VIEW);
list($page_method,$page_action,$page_mode) = $rmo->validate();
switch($page_action):
	case $sphere->get_cbm_button_val_delete(): // rows.delete
		$sphere->cbm_grid = filter_input(INPUT_POST,$sphere->get_cbm_name(),FILTER_DEFAULT,['flags' => FILTER_REQUIRE_ARRAY,'options' => ['default' => []]]);
		foreach($sphere->cbm_grid as $sphere->cbm_row):
			if(false !== array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->get_row_identifier())):
				updatenotify_delete_id($sphere->cbm_row);
			endif;
		endforeach;
		header($sphere->get_location());
		exit;
		break;
endswitch;
$pgtitle = [gettext('Diagnostics'),gettext('Updatenotifier')];
$record_exists = count($sphere->grid) > 0;
$a_col_width = ['5%','15%','15%','10%','30%','25%'];
$n_col_width = count($a_col_width);
//	prepare additional javascript code
$jcode = $sphere->doj(false);
if($record_exists):
	$document = new_page($pgtitle,$sphere->get_scriptname(),'notabnav','tablesort');
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
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg)->
	ins_error_box($errormsg);
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$thead->ins_titleline(gettext('Overview'),$n_col_width);
if($record_exists):
	$thead->
		addTR()->
			push()->
			addTHwC('lhelc sorter-false parser-false')->
				ins_cbm_checkbox_toggle($sphere)->
			pop()->
			insTHwC('lhell',gettext('Key'))->
			insTHwC('lhell',gettext('Time Stamp'))->
			insTHwC('lhell',gettext('Mode'))->
			insTHwC('lhell',gettext('Data'))->
			insTHwC('lhebl',gettext('Processor'));
else:
	$thead->
		addTR()->
			insTHwC('lhelc')->
			insTHwC('lhell',gettext('Key'))->
			insTHwC('lhell',gettext('Time Stamp'))->
			insTHwC('lhell',gettext('Mode'))->
			insTHwC('lhell',gettext('Data'))->
			insTHwC('lhebl',gettext('Processor'));
endif;
if($record_exists):
	foreach($sphere->grid as $sphere->row_id => $sphere->row):
		switch($sphere->row['mode']):
			case UPDATENOTIFY_MODE_NEW:
				$mode = gettext('New');
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
				$mode = gettext('Modify');
				break;
			case UPDATENOTIFY_MODE_DIRTY:
				$mode = gettext('Delete');
				break;
			case UPDATENOTIFY_MODE_DIRTY_CONFIG:
				$mode = gettext('Remove');
				break;
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode = gettext('Unknown');
				break;
			default:
				$mode = $sphere->row['mode'];
				break;
		endswitch;
		$ts = get_datetime_locale((int)$sphere->row['ts']);
		$tbody->
			addTR()->
				push()->
				addTDwC('lcelc')->
					ins_cbm_checkbox($sphere,false)->
				pop()->
				insTDwC('lcell',$sphere->row['key'])->
				insTDwC('lcell',$ts)->
				insTDwC('lcell',$mode)->
				insTDwC('lcell',$sphere->row['data'])->
				insTDwC('lcebl',$sphere->row['processor']);
	endforeach;
else:
	$tbody->ins_no_records_found($n_col_width);
endif;
$document->
	add_area_buttons()->
		ins_cbm_button_delete($sphere);
$document->render();
