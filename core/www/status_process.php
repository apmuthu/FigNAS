<?php
/*
	status_process.php

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

function get_sphere_status_process() {
	global $config;
	
	$sphere = new co_sphere_row('status_process','php');
	return $sphere;
}
function status_process_ajax() {
	$cmd = 'top -b -d1 27';
	mwexec2($cmd,$rawdata);
	return implode(PHP_EOL,$rawdata);
}
if(is_ajax()):
	$status['area_refresh'] = status_process_ajax();
	render_ajax($status);
endif;
$sphere = get_sphere_status_process();
$jcode = <<<EOJ
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'status_process.php', null, function(data) {
		if ($('#area_refresh').length > 0) {
			$('#area_refresh').text(data.area_refresh);
		}
	});
});
EOJ;
$document = new_page([gettext('Status'),gettext('Processes')]);
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
$body->addJavaScript($jcode);
$content = $pagecontent->add_area_data();
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Process State'))->
		pop()->
		addTBODY()->
			addTR()->
				insTDwC('celltag',gettext('Information'))->
				addTDwC('celldata')->
					addElement('pre',['class' => 'cmdoutput'])->
						insSPAN(['id' => 'area_refresh'],status_process_ajax());
$document->render();
