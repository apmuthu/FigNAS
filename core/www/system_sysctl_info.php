<?php
/*
	system_sysctl_info.php

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

function sysctl_info_sphere() {
	$sphere = new co_sphere_grid('system_sysctl_info','php');
	$sphere->set_row_identifier('name');
	$sphere->set_enadis(true);
	$sphere->set_lock(true);
	$sphere->grid = [];
	return $sphere;
}
$cop = new systcl_info_properties();
$sphere = &sysctl_info_sphere();
unset($output);
mwexec2('/sbin/sysctl -adeot',$output);
foreach($output as $row):
	list($sysctl_name,$sysctl_type,$sysctl_info) = explode('=',$row,3);
	if(preg_match('/\S/',$sysctl_type)):
		$sphere->grid[$sysctl_name] = ['sysctltype' => $sysctl_type,'sysctlinfo' => $sysctl_info];
	endif;
endforeach;
unset($output);
mwexec2('/sbin/sysctl -ae',$output);
foreach($output as $row):
	list($sysctl_name,$sysctl_value) = explode('=',$row,2);
	if(array_key_exists($sysctl_name,$sphere->grid)):
		$sphere->grid[$sysctl_name]['value'] = $sysctl_value;
	endif;
endforeach;
unset($output);
$pgtitle = [gettext('System'),gettext('Advanced'),gettext('sysctl.conf'),gettext('Info')];
$record_exists = count($sphere->grid) > 0;
$a_col_width = ['30%','15%','30%','25%'];
$n_col_width = count($a_col_width);
//	prepare additional javascript code
$jcode = $sphere->doj(false);
if($record_exists):
	$document = new_page($pgtitle,null,'tablesort');
else:
	$document = new_page($pgtitle);
endif;
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
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'))->
			ins_tabnav_record('system_sysctl_info.php',gettext('Information'),gettext('Reload page'),true);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(gettext('Overview'),$n_col_width)->
	addTR()->
		insTHwC('lhell',$cop->get_name()->get_title())->
		insTHwC('lhell',$cop->get_sysctltype()->get_title())->
		insTHwC('lhell',$cop->get_sysctlinfo()->get_title())->
		insTHwC('lhebl',$cop->get_value()->get_title());
if($record_exists):
	foreach($sphere->grid as $sphere->row_id => $sphere->row):
		$tbody->
			addTR()->
				insTDwC('lcell',$sphere->row_id ?? '')->
				insTDwC('lcell',$sphere->row[$cop->get_sysctltype()->get_name()] ?? '')->
				insTDwC('lcell',$sphere->row[$cop->get_sysctlinfo()->get_name()] ?? '')->
				insTDwC('lcebl',$sphere->row[$cop->get_value()->get_name()] ?? '');
	endforeach;
else:
	$tbody->ins_no_records_found($n_col_width);
endif;
$document->render();
