<?php
/*
	disks_raid_gstripe_info.php

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

function disk_raid_gstripe_info_ajax() {
	$cmd = '/sbin/gstripe list';
	mwexec2($cmd,$rawdata);
	return (0 === count($rawdata)) ? gettext('No RAID0 information found.') : implode(PHP_EOL,$rawdata);
}
if(is_ajax()):
	$status['area_refresh'] = disk_raid_gstripe_info_ajax();
	render_ajax($status);
endif;
$pgtitle = [gettext('Disks'),gettext('Software RAID'),gettext('RAID0'),gettext('Information')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_raid_geom.php',gettext('GEOM'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_raid_gvinum.php',gettext('RAID 0/1/5'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_raid_geom.php',gettext('Management'))->
			ins_tabnav_record('disks_raid_gstripe_tools.php',gettext('Maintenance'))->
			ins_tabnav_record('disks_raid_gstripe_info.php',gettext('Information'),gettext('Reload page'),true);
$pagecontent->
	add_area_data()->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_titleline(gettext('RAID0 Information & Status'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					addTDwC('celldata')->
						addElement('pre',['class' => 'cmdoutput'])->
							addElement('span',['id' => 'area_refresh'],disk_raid_gstripe_info_ajax());
//	add additional javascript code
$js_document_ready = <<<'EOJ'
	var gui = new GUI;
	gui.recall(5000,5000,'disks_raid_gstripe_info.php',null,function(data) {
		if($('#area_refresh').length > 0) {
			$('#area_refresh').text(data.area_refresh);
		}
	});
EOJ;
$body->add_js_document_ready($js_document_ready);
$document->render();
