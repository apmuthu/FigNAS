<?php
/*
	status_graph_latency.php

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

array_make_branch($config,'rrdgraphs');
$rrd_latency = true;
$refresh = 300;
if(isset($config['rrdgraphs']['refresh_time'])):
	if(!empty($config['rrdgraphs']['refresh_time'])):
		$refresh = $config['rrdgraphs']['refresh_time'];
	endif;
endif;
mwexec('/usr/local/share/rrdgraphs/rrd-graph.sh latency',true);
$document = new_page([gettext('Status'),gettext('Monitoring'),gettext('Network Latency')]);
//	get areas
$head = $document->getElementById('head');
$pagecontent = $document->getElementById('pagecontent');
$head->insElement('meta',['http-equiv' => 'refresh','content' => $refresh]);
//	add tab navigation
include 'status_graph_tabs.inc';
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$table = $content->add_table_data_settings();
$table->addTHEAD()->ins_titleline(gettext('Network Latency'));
$now = time();
$content->
	ins_remark('remark','',sprintf(gettext('Graph updates every %d seconds.'),$refresh));
$content->
	addDIV(['class' => 'rrdgraphs'])->
		insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-latency_daily.png?rand=%s',$now),'alt' => gettext('RRDGraphs Daily Network Latency Graph')])->
		insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-latency_weekly.png?rand=%s',$now),'alt' => gettext('RRDGraphs Weekly Network Latency Graph')])->
		insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-latency_monthly.png?rand=%s',$now),'alt' => gettext('RRDGraphs Monthly Network Latency Graph')])->
		insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-latency_yearly.png?rand=%s',$now),'alt' => gettext('RRDGraphs Yearly Network Latency Graph')]);
$document->render();
