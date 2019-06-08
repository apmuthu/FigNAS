<?php
/*
	status_graph_tabs.inc

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
$gt_reload_page = gettext('Reload Page');
$tabnav = $document->
	add_area_tabnav()->
		add_tabnav_upper();
$tabnav->
	ins_tabnav_record('status_graph.php',gettext('System Load'),isset($status_graph) ? $gt_reload_page : '',isset($status_graph))->
	ins_tabnav_record('status_graph_cpu.php',gettext('CPU Load'),isset($status_cpu) ? $gt_reload_page : '',isset($status_cpu));
if(isset($config['rrdgraphs']['enable'])):
	if(isset($config['rrdgraphs']['cpu_frequency'])):
		$tabnav->ins_tabnav_record('status_graph_cpu_freq.php',gettext('CPU Frequency'),isset($rrd_cpu_freq) ? $gt_reload_page : '',isset($rrd_cpu_freq));
	endif;
	if(isset($config['rrdgraphs']['cpu_temperature'])):
		$tabnav->ins_tabnav_record('status_graph_cpu_temp.php',gettext('CPU Temperature'),isset($rrd_cpu_temp) ? $gt_reload_page : '',isset($rrd_cpu_temp));
	endif;
	if(isset($config['rrdgraphs']['cpu'])):
		$tabnav->ins_tabnav_record('status_graph_cpu_usage.php',gettext('CPU Usage'),isset($rrd_cpu) ? $gt_reload_page : '',isset($rrd_cpu));
	endif;
	if(isset($config['rrdgraphs']['load_averages'])):
		$tabnav->ins_tabnav_record('status_graph_load_averages.php',gettext('Load Averages'),isset($rrd_load_averages) ? $gt_reload_page : '',isset($rrd_load_averages));
	endif;
	if(isset($config['rrdgraphs']['disk_usage'])):
		$tabnav->ins_tabnav_record('status_graph_disk_usage.php',gettext('Disk Usage'),isset($rrd_disk_usage) ? $gt_reload_page : '',isset($rrd_disk_usage));
	endif;
	if(isset($config['rrdgraphs']['memory_usage'])):
		$tabnav->ins_tabnav_record('status_graph_memory.php',gettext('Memory Usage'),isset($rrd_memory) ? $gt_reload_page : '',isset($rrd_memory));
	endif;
	if(isset($config['rrdgraphs']['latency'])):
		$tabnav->ins_tabnav_record('status_graph_latency.php',gettext('Network Latency'),isset($rrd_latency) ? $gt_reload_page : '',isset($rrd_latency));
	endif;
	if(isset($config['rrdgraphs']['lan_load'])):
		$tabnav->ins_tabnav_record('status_graph_network.php',gettext('Network Traffic'),isset($rrd_lan) ? $gt_reload_page : '',isset($rrd_lan));
	endif;
	if(isset($config['rrdgraphs']['no_processes'])):
		$tabnav->ins_tabnav_record('status_graph_processes.php',gettext('Processes'),isset($rrd_processes) ? $gt_reload_page : '',isset($rrd_processes));
	endif;
	if(isset($config['rrdgraphs']['ups'])):
		$tabnav->ins_tabnav_record('status_graph_ups.php',gettext('UPS'),isset($rrd_ups) ? $gt_reload_page : '',isset($rrd_ups));
	endif;
	if(isset($config['rrdgraphs']['uptime'])):
		$tabnav->ins_tabnav_record('status_graph_uptime.php',gettext('Uptime'),isset($rrd_uptime) ? $gt_reload_page : '',isset($rrd_uptime));
	endif;
	if(isset($config['rrdgraphs']['arc_usage'])):
		$tabnav->ins_tabnav_record('status_graph_zfs_arc.php',gettext('ZFS ARC'),isset($rrd_zfs_arc) ? $gt_reload_page : '',isset($rrd_zfs_arc));
	endif;
	if(isset($config['rrdgraphs']['l2arc_usage'])):
		$tabnav->ins_tabnav_record('status_graph_zfs_l2arc.php',gettext('ZFS L2ARC'),isset($rrd_zfs_l2arc) ? $gt_reload_page : '',isset($rrd_zfs_l2arc));
	endif;
endif;
