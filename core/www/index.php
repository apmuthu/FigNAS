<?php
/*
	index.php

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
// Configure page permission
$pgperm['allowuser'] = true;

require_once 'auth.inc';
require_once 'guiconfig.inc';
require_once 'zfs.inc';

$use_meter_tag = (is_bool($test = $config['system']['showcolorfulmeter'] ?? false) ? $test : true);
$pgtitle = [gtext('System Information')];
$pgtitle_omit = true;
array_make_branch($config,'vinterfaces','carp');
$sysinfo = system_get_sysinfo();
if(is_ajax()):
	render_ajax($sysinfo);
endif;
/**
 *	render function for cpu usage
 *	@global bool $use_meter_tag
 *	@global array $sysinfo
 */
function render_cpuusage() {
	global $use_meter_tag;
	global $sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['cpuusage'];
		$o = new co_DOMDocument();
		$td = $o->addTR()->insTDwC('celltag',gettext('CPU Usage'))->addTDwC('celldata');
		if($use_meter_tag):
			$inner = $td->addElement('meter',['id' => 'cpuusagev','class' => 'cpuusage','min' => 0,'optimum' => 45,'low' => 90,'high' => 95,'max' => 100,'value' => $sphere['pu'],'title' => $sphere['tu']]);
		else:
			$inner = $td;
		endif;
		$inner->
			insIMG(['src' => 'images/bar_left.gif','class' => 'progbarl','alt' => ''])->
			insIMG(['src' => 'images/bar_blue.gif','id' => 'cpuusageu','width' => $sphere['pu'],'class' => 'progbarcf','alt' => '','title' => $sphere['tu']])->
			insIMG(['src' => 'images/bar_gray.gif','id' => 'cpuusagef','width' => $sphere['pf'],'class' => 'progbarc','alt' => '','title' => $sphere['tu']])->
			insIMG(['src' => 'images/bar_right.gif','class' => 'progbarr meter','alt' => '']);
		$td->insSPAN(['id' => 'cpuusagep'],$sphere['tt']);
		$o->render();
	endif;
}
/**
 *	Render function for extended CPU usage
 *	@global bool $use_meter_tag
 *	@global array $sysinfo
 */
function render_cpuusage2() {
	global $use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
//		limit the number of CPU's shown to 16 cpus
		$sphere = $sysinfo['cpuusage2'];
		$cpus = min($sysinfo['cpus'],16);
		if($cpus > 1):
			echo '<tr>';
				echo '<td class="celltag">',gtext('CPU Core Usage'),'</td>';
				echo '<td class="celldata">';
					echo '<table class="area_data_settings" style="table-layout:auto;white-space:nowrap;"><tbody>';
						if($cpus > 4):
							$col_max = 2; // set max number of columns for high number of CPUs
						else:
							$col_max = 1; // set max number of columns for low number of CPUs
						endif;
						$tr_count = intdiv($cpus + $col_max - 1,$col_max);
						$cpu_index = 0;
						for($tr_counter = 0;$tr_counter < $tr_count;$tr_counter++):
							echo '<tr>';
							for($td_counter = 0;$td_counter < $col_max;$td_counter++):
								if($cpu_index < $cpus):
//									action
									$row = $sphere[$cpu_index];
									echo '<td class="nopad">';
									if($use_meter_tag):
										echo '<meter id="cpuusagev',$cpu_index,'" class="cpuusage" min="0" max="100" optimum="45" low="90" high="95" value="',$row['pu'],'" title="',$row['tu'],'">';
									endif;
									echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
										'<img src="images/bar_blue.gif" id="cpuusageu',$cpu_index,'" width="',$row['pu'],'" class="progbarcf" alt="" title="',$row['tu'],'"/>',
										'<img src="images/bar_gray.gif" id="cpuusagef',$cpu_index,'" width="',$row['pf'],'" class="progbarc" alt="" title="',$row['tf'],'"/>',
										'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
									if($use_meter_tag):
										echo '</meter>';
									endif;
									echo '</td>';
									echo '<td class="padr03">',htmlspecialchars(sprintf('%s %u:',gettext('Core'),$cpu_index)),'</td>';
									echo '<td class="padr1" style="text-align:right;" id="',sprintf('cpuusagep%s',$cpu_index),'">',$row['tt'],'</td>';
									if(!empty($sysinfo['cputemp2'][$cpu_index])):
										echo '<td class="padr03">',htmlspecialchars(sprintf('%s:',gettext('Temp'))),'</td>';
										echo '<td class="padr1" style="text-align:right;" id="',sprintf('cputemp%s',$cpu_index),'">',$sysinfo['cputemp2'][$cpu_index]['vuh'],'</td>';
									else:
										echo '<td class="padr03"></td>';
										echo '<td class="padr1"></td>';
									endif;
									$cpu_index++;
								else:
//									fill empty space
									echo '<td class="nopad"></td>';
									echo '<td class="padr03"></td>';
									echo '<td class="padr1"></td>';
									echo '<td class="padr03"></td>';
									echo '<td class="padr1"></td>';
								endif;
							endfor;
							echo '<td class="nopad100"></td>';
							echo '</tr>';
						endfor;
					echo '</tbody></table>';
				echo '</td>';
			echo '</tr>';
		endif;
	endif;
}
/**
 *	Render function for memory usage
 *	@global bool $use_meter_tag
 *	@global array $sysinfo
 */
function render_memusage() {
	global $use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['memusage'];
		$o = new co_DOMDocument();
		$td = $o->addTR()->insTDwC('celltag',gettext('Memory Usage'))->addTDwC('celldata');
		if($use_meter_tag):
			$inner = $td->addElement('meter',['id' => 'memusagev','class' => 'memusage','min' => 0,'high' => 98,'max' => 100,'value' => $sphere['pu'],'title' => $sphere['tu']]);
		else:
			$inner = $td;
		endif;
		$inner->
			insIMG(['src' => 'images/bar_left.gif','class' => 'progbarl','alt' => ''])->
			insIMG(['src' => 'images/bar_blue.gif','id' => 'memusageu','width' => $sphere['pu'],'class' => 'progbarcf','alt' => ''])->
			insIMG(['src' => 'images/bar_gray.gif','id' => 'memusagef','width' => $sphere['pf'],'class' => 'progbarc','alt' => ''])->
			insIMG(['src' => 'images/bar_right.gif','class' => 'progbarr meter','alt' => '']);
		$td->insSPAN(['id' => 'memusagep'],$sphere['tt']);
		$o->render();
	endif;
}
/**
 *	Render function for swap usage
 *	@global bool $use_meter_tag
 *	@global array $sysinfo
 */
function render_swapusage() {
	global $use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['swapusage'];
		$sphere_elements = count($sphere);
		if($sphere_elements > 0):
			echo '<tr>','<td class="celltag">',gtext('Swap Usage'),'</td>','<td class="celldata">','<table class="area_data_settings">','<tbody>';
			$index = 0;
			foreach($sphere as $row):
				$ctrlid = sprintf('swapusage_%s',$row['id']);
				echo '<tr><td class="nopad"><div id="',$ctrlid,'">';
				echo '<span id="',$ctrlid,'_name" class="name">',$row['name'],'</span>';
				echo '<br />';
				if($use_meter_tag):
					echo '<meter id="',$ctrlid,'_v" class="swapusage" min="0" max="100" high="95" value="',$row['pu'],'" title="',$row['tu'],'">';
				endif;
				echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
					'<img src="images/bar_blue.gif" id="',$ctrlid,'_bar_used" width="',$row['pu'],'" class="progbarcf" alt="" title="',$row['tu'],'"/>',
					'<img src="images/bar_gray.gif" id="',$ctrlid,'_bar_free" width="',$row['pf'],'" class="progbarc" alt="" title="',$row['tf'],'"/>',
					'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
				if($use_meter_tag):
					echo '</meter>';
				endif;
				echo '<span id="',$ctrlid,'_capofsize" class="capofsize">',$row['tt'],'</span>';
				echo '</div></td></tr>';
				$index++;
				if($index < $sphere_elements):
					echo '<tr><td class="nopad"><hr /></td></tr>';
				endif;
			endforeach;
			echo '</tbody>','</table>','</td>','</tr>';
		endif;
	endif;
}
/**
 *	Render function for load averages
 *	@param array $sysinfo
 */
function render_load_averages() {
	global $sysinfo;

	if(Session::isAdmin()):
		echo '<tr>';
			echo '<td class="celltag">',gtext('Load Averages'),'</td>';
			echo '<td class="celldata">';
				echo '<table class="area_data_settings" style="table-layout:auto;white-space:nowrap;"><tbody><tr>';
					echo '<td class="padr1"><span id="loadaverage">',$sysinfo['loadaverage'],'</span></td>';
					echo '<td class="nopad"><a href="status_process.php">',gtext('Show Process Information'),'</a></td>';
					echo '<td class="nopad100"></td>';
				echo '</tr></tbody></table>';
			echo '</td>';
		echo '</tr>';
	endif;
}
/**
 *	Render function for disk usage
 *	@global bool $use_meter_tag
 *	@global array $sysinfo
 */
function render_diskusage() {
	global $use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['diskusage'];
		$sphere_elements = count($sphere);
		if($sphere_elements > 0):
			echo '<tr>','<td class="celltag">',gtext('Disk Space Usage'),'</td>','<td class="celldata">','<table class="area_data_settings">','<tbody>';
			$index = 0;
			foreach($sphere as $row):
				$ctrlid = sprintf('diskusage_%s',$row['id']);
				echo '<tr><td class="nopad"><div id="',$ctrlid,'">';
				echo '<span id="',$ctrlid,'_name" class="name">',$row['name'],'</span>';
				echo '<br />';
				if($use_meter_tag):
					echo '<meter id="',$ctrlid,'_v" class="diskusage" min="0" max="100" value="',$row['pu'],'" title="',$row['tu'],'">';
				endif;
				echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
					'<img src="images/bar_blue.gif" id="',$ctrlid,'_bar_used" width="',$row['pu'],'" class="progbarcf" title="',$row['tu'],'" alt="" />',
					'<img src="images/bar_gray.gif" id="',$ctrlid,'_bar_free" width="',$row['pf'],'" class="progbarc" title="',$row['tf'],'" alt=""/>',
					'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
				if($use_meter_tag):
					echo '</meter>';
				endif;
				echo '<span id="',$ctrlid,'_capofsize" class="capofsize">',$row['tt'],'</span>';
				echo '</div></td></tr>';
				$index++;
				if($index < $sphere_elements):
					echo '<tr><td class="nopad"><hr /></td></tr>';
				endif;
			endforeach;
			echo '</tbody>','</table>','</td>','</tr>';
		endif;
	endif;
}
/**
 *	Render function for pool usage
 *	@global array $config
 *	@global bool $use_meter_tag
 *	@param array $sphere
 */
function render_poolusage() {
	global $config,$use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['poolusage'];
		$sphere_elements = count($sphere);
		if($sphere_elements > 0):
			echo '<tr>','<td class="celltag">',gtext('Pool Space Usage'),'</td>','<td class="celldata">','<table class="area_data_settings">','<tbody>';
			$index = 0;
			$zfs_settings = &array_make_branch($config,'zfs','settings');
			if(array_key_exists('capacity_warning',$zfs_settings)):
				$zfs_warning = filter_var($zfs_settings['capacity_warning'],FILTER_VALIDATE_INT,['options' => ['default' => 80,'min' => 80,'max' => 89]]);
			else:
				$zfs_warning = 80;
			endif;
			if(array_key_exists('capacity_critical',$zfs_settings)):
				$zfs_critical = filter_var($zfs_settings['capacity_critical'],FILTER_VALIDATE_INT,['options' => ['default' => 90,'min' => 90,'max' => 95]]);
			else:
				$zfs_critical = 90;
			endif;
			foreach($sphere as $row):
				$ctrlid = sprintf('poolusage_%s',$row['id']);
				switch($row['health']):
					case 'ONLINE':
						echo '<tr id="',$ctrlid,'_tr">';
						break;
					default:
						echo '<tr id="',$ctrlid,'_tr" class="error">';
						break;
				endswitch;
				echo '<td class="nopad"><div id="',$ctrlid,'">';
				echo '<span id="',$ctrlid,'_name" class="name">',$row['name'],'</span>';
				echo '<br />';
				if($use_meter_tag):
					echo '<meter id="',$ctrlid,'_v" class="poolusage" min="0" max="100" optimum="40" low="',$zfs_warning,'" high="',$zfs_critical,'" value="',$row['pu'],'" title="',$row['tu'],'">';
				endif;
				echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
					'<img src="images/bar_blue.gif" id="',$ctrlid,'_bar_used" width="',$row['pu'],'" class="progbarcf" title="',$row['tu'],'" alt=""/>',
					'<img src="images/bar_gray.gif" id="',$ctrlid,'_bar_free" width="',$row['pf'],'" class="progbarc" title="',$row['tf'],'" alt=""/>',
					'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
				if($use_meter_tag):
					echo '</meter>';
				endif;
				echo '<span id="',$ctrlid,'_capofsize" class="capofsize">',$row['tt'],'</span>';
				echo '<br />';
				echo gtext('State: '),sprintf('<span id="%s_state" class="state"><a href="disks_zfs_zpool_info.php?%s">%s</a></span>',$ctrlid,http_build_query(['pool' => $row['name']],NULL,ini_get('arg_separator.output'),PHP_QUERY_RFC3986),$row['health']);
				echo '</div></td></tr>';
				$index++;
				if($index < $sphere_elements):
					echo '<tr><td class="nopad"><hr /></td></tr>';
				endif;
			endforeach;
			echo '</tbody>','</table>','</td>','</tr>';
		endif;
	endif;
}
/**
 *	Render function for UPS info
 *	@global bool $use_meter_tag
 */
function render_upsinfo() {
	global $use_meter_tag,$sysinfo;

	if(Session::isAdmin()):
		$sphere = $sysinfo['upsinfo'];
		$sphere_elements = count($sphere);
		if($sphere_elements > 0):
			echo '<tr>',
				'<td class="celltag">',gtext('UPS Status'),'</td>',
				'<td class="celldata">','<table class="area_data_settings" style="table-layout:auto;white-space:nowrap;">','<tbody>';
			$index = 0;
			foreach($sphere as $ui):
				$id = sprintf('ups_status_%s_',$ui['id']);
				echo '<tr>',
					'<td class="padr03">',gtext('Identifier:'),'</td>',
					'<td class="padr1" id="',$id,'name">',htmlspecialchars($ui['name']),'</td>',
					'<td class="nopad"><a href="diag_infos_ups.php">',gtext('Show UPS Information'),'</a></td>',
					'<td class="nopad100"></td>',
					'</tr>';
				echo '<tr>';
				echo '<td class="padr03">',gtext('Status:'),'</td>',
					'<td class="nopad" colspan="2" id="',$id,'disp_status">',$ui['disp_status'],'</td>',
					'<td class="nopad100"></td>';
					'</tr>';
//				load
				$idl = $id . 'load_';
				$uil = $ui['load'];
				echo '<tr>';
				echo '<td class="padr03">',gtext('Load:'),'</td>';
				echo '<td class="nopad">';
				if($use_meter_tag):
					echo '<meter id="',$idl,'v" class="upsusage" min="0" optimum="30" low="60" high="80" max="100" value="',$uil['pu'],'" title="',$uil['tu'],'">';
				endif;
				echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
					'<img src="images/bar_blue.gif" id="',$idl,'bar_used" width="',$uil['pu'],'" class="progbarcf" title="',$uil['tu'],'" alt=""/>',
					'<img src="images/bar_gray.gif" id="',$idl,'bar_free" width="',$uil['pf'],'" class="progbarc" title="',$uil['tf'],'" alt=""/>',
					'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
				if($use_meter_tag):
					echo '</meter>';
				endif;
				echo '</td>';
				echo '<td class="nopad">',
					'<span id="',$idl,'used" class="capacity">',$uil['pu'],'%</span>',
					'</td>';
				echo '<td class="nopad100"></td>';
				echo '</tr>';
//				battery charge level
				$idb = $id . 'battery_';
				$uib = $ui['battery'];
				echo '<tr>';
				echo '<td class="padr03">',gtext('Battery Level:'),'</td>';
				echo '<td class="nopad">';
				if($use_meter_tag):
					echo '<meter id="',$idb,'v" class="upsusage" min="0" low="30" high="80" optimum="90" max="100" value="',$uib['pu'],'" title="',$uib['tu'],'">';
				endif;
				echo '<img src="images/bar_left.gif" class="progbarl" alt=""/>',
					'<img src="images/bar_blue.gif" id="',$idb,'bar_used" width="',$uib['pu'],'" class="progbarcf" title="',$uib['tu'],'" alt=""/>',
					'<img src="images/bar_gray.gif" id="',$idb,'bar_free" width="',$uib['pf'],'" class="progbarc" title="',$uib['tf'],'" alt=""/>',
					'<img src="images/bar_right.gif" class="progbarr meter" alt=""/>';
				if($use_meter_tag):
					echo '</meter>';
				endif;
				echo '</td>';
				echo '<td class="nopad">',
					'<span id="',$idb,'used" class="capacity">',$uib['pu'],'%</span>',
					'</td>';
				echo '<td class="nopad100"></td>';
				echo '</tr>';
				$index++;
				if($index < $sphere_elements):
					echo '<tr><td class="nopad" colspan="4"><hr /></td></tr>';
				endif;
			endforeach;
			echo '</tbody>','</table>','</td>','</tr>';
		endif;
	endif;
}
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000,5000,'index.php',null,function(data) {
		if($('#vipstatus').length) {
			$('#vipstatus').text(data.vipstatus);
		}
		if($('#system_uptime').length) {
			$('#system_uptime').text(data.uptime);
		}
		if($('#system_datetime').length) {
			$('#system_datetime').text(data.date);
		}
		if($('#memusagev').length) {
			$('#memusagev').attr({value:data.memusage.pu,title:data.memusage.tu});
		}
		if($('#memusageu').length) {
			$('#memusageu').attr({width:data.memusage.pu,title:data.memusage.tu});
		}
		if($('#memusagef').length) {
			$('#memusagef').attr({width:data.memusage.pf,title:data.memusage.tf});
		}
		if($('#memusagep').length) {
			$('#memusagep').text(data.memusage.tt);
		}
		if($('#loadaverage').length) {
			$('#loadaverage').text(data.loadaverage);
		}
		if(typeof(data.cputemp) !== 'undefined') {
			if($('#cputemp').length) {
				$('#cputemp').text(data.cputemp.vuh);
			}
		}
		if(typeof(data.cputemp2) !== 'undefined') {
			for(var idx = 0;idx < data.cputemp2.length;idx++) {
				var tu = data.cputemp2[idx];
				if($('#cputemp'+idx).length) {
					$('#cputemp'+idx).text(tu.vuh);
				}
			}
		}
		if(typeof(data.cpufreq) !== 'undefined') {
			if($('#cpufreq').length) {
				$('#cpufreq').text(data.cpufreq + 'MHz');
			}
		}
		if(typeof(data.cpuusage) !== 'undefined') {
			var cu = data.cpuusage;
			if($('#cpuusagev').length) {
				$('#cpuusagev').attr({value:cu.pu,title:cu.tu});
			}
			if($('#cpuusageu').length) {
				$('#cpuusageu').attr({width:cu.pu,title:cu.tu});
			}
			if($('#cpuusagef').length) {
				$('#cpuusagef').attr({width:cu.pf,title:cu.tu});
			}
			if($('#cpuusagep').length) {
				$('#cpuusagep').text(cu.tt);
			}
		}
		if(typeof(data.cpuusage2) !== 'undefined') {
			for(var idx = 0;idx < data.cpuusage2.length;idx++) {
				var cu = data.cpuusage2[idx];
				if($('#cpuusagev'+idx).length) {
					$('#cpuusagev'+idx).attr({value:cu.pu,title:cu.tu});
				}
				if($('#cpuusagep'+idx).length) {
					$('#cpuusagep'+idx).text(cu.tt);
				}
				if($('#cpuusageu'+idx).length) {
					$('#cpuusageu'+idx).attr({width:cu.pu,title:cu.tu});
				}
				if($('#cpuusagef'+idx).length) {
					$('#cpuusagef'+idx).attr({width:cu.pf,title:cu.tf});
				}
			}
		}
		if(typeof(data.diskusage) !== 'undefined') {
			for(var idx = 0;idx < data.diskusage.length;idx++) {
				var du = data.diskusage[idx];
				var id_prefix = '#diskusage_'+du.id+'_';
				if($(id_prefix+'name').length) {
					$(id_prefix+'name').text(du.name);
				}
				if($(id_prefix+'v').length) {
					$(id_prefix+'v').attr({value:du.pu,title:du.tu});
				}
				if($(id_prefix+'bar_used').length) {
					$(id_prefix+'bar_used').attr({width:du.pu,title:du.tu});
				}
				if($(id_prefix+'bar_free').length) {
					$(id_prefix+'bar_free').attr({width:du.pf,title:du.tf});
				}
				if($(id_prefix+'capofsize').length) {
					$(id_prefix+'capofsize').text(du.tt);
				}
			}
		}
		if(typeof(data.poolusage) !== 'undefined') {
			for(var idx = 0;idx < data.poolusage.length;idx++) {
				var pu = data.poolusage[idx];
				var id_prefix = '#poolusage_'+pu.id+'_';
				if($(id_prefix+'name').length) {
					$(id_prefix+'name').text(pu.name);
				}
				if($(id_prefix+'v').length) {
					$(id_prefix+'v').attr({value:pu.pu,title:pu.tu});
				}
				if($(id_prefix+'bar_used').length) {
					$(id_prefix+'bar_used').attr({width:pu.pu,title:pu.tu});
				}
				if($(id_prefix+'bar_free').length) {
					$(id_prefix+'bar_free').attr({width:pu.pf,title:pu.tf});
				}
				if($(id_prefix+'capofsize').length) {
					$(id_prefix+'capofsize').text(pu.tt);
				}
				if($(id_prefix+'state').length) {
					$(id_prefix+'state').children().text(pu.health);
				}
				if($(id_prefix+'tr').length) {
					switch(pu.health) {
						case('ONLINE'):
							$(id_prefix+'tr').removeClass('error');
							break;
						default:
							$(id_prefix+'tr').addClass('error');
							break;
					}
				}
			}
		}
		if(typeof(data.swapusage) !== 'undefined') {
			for(var idx = 0;idx < data.swapusage.length;idx++) {
				var su = data.swapusage[idx];
				var id_prefix = '#swapusage_'+su.id+'_';
				if($(id_prefix+'name').length) {
					$(id_prefix+'name').text(su.name);
				}
				if($(id_prefix+'v').length) {
					$(id_prefix+'v').attr({value:su.pu,title:su.tu});
				}
				if($(id_prefix+'bar_used').length) {
					$(id_prefix+'bar_used').attr({width:su.pu,title:su.tu});
				}
				if($(id_prefix+'bar_free').length) {
					$(id_prefix+'bar_free').attr({width:su.pf,title:su.tf});
				}
				if($(id_prefix+'capofsize').length) {
					$(id_prefix+'capofsize').text(su.tt);
				}
			}
		}
		if(typeof(data.upsinfo) !== 'undefined') {
			for(var idx = 0;idx < data.upsinfo.length;idx++) {
				var ui = data.upsinfo[idx];
				var id_prefix = '#ups_status_'+ui.id+'_';
				if($(id_prefix+'name').length) {
					$(id_prefix+'name').text(ui.name);
				}
				if($(id_prefix+'disp_status').length) {
					$(id_prefix+'disp_status').text(ui.disp_status);
				}
				var uil = ui['load'];
				var id_prefix = '#ups_status_'+ui.id+'_load_';
				if($(id_prefix+'v').length) {
					$(id_prefix+'v').attr({value:uil.pu,title:uil.tu});
				}
				if($(id_prefix+'bar_used').length) {
					$(id_prefix+'bar_used').attr({width:uil.pu,title:uil.tu});
				}
				if($(id_prefix+'bar_free').length) {
					$(id_prefix+'bar_free').attr({width:uil.pf,title:uil.tf});
				}
				if($(id_prefix+'used').length) {
					$(id_prefix+'used').text(uil.vuh);
				}
				var uib = ui['battery'];
				var id_prefix = '#ups_status_'+ui.id+'_battery_';
				if($(id_prefix+'v').length) {
					$(id_prefix+'v').attr({value:uib.pu,title:uib.tu});
				}
				if($(id_prefix+'bar_used').length) {
					$(id_prefix+'bar_used').attr({width:uib.pu,title:uib.tu});
				}
				if($(id_prefix+'bar_free').length) {
					$(id_prefix+'bar_free').attr({width:uib.pf,title:uib.tf});
				}
				if($(id_prefix+'used').length) {
					$(id_prefix+'used').text(uib.vuh);
				}
			}
		}
	});
});
//]]>
</script>
<?php
//	make sure normal user such as www can write to temporary
	$perms = fileperms('/tmp');
	if(($perms & 01777) != 01777):
		$errormsg .= sprintf(gtext('Wrong permission on %s.'),'/tmp');
		$errormsg .= "<br />\n";
	endif;
	$perms = fileperms('/var/tmp');
	if(($perms & 01777) != 01777):
		$errormsg .= sprintf(gtext('Wrong permission on %s.'),'/var/tmp');
		$errormsg .= "<br />\n";
	endif;
//	check DNS
	list($v4dns1,$v4dns2) = get_ipv4dnsserver();
	list($v6dns1,$v6dns2) = get_ipv6dnsserver();
	if(empty($v4dns1) && empty($v4dns2) && empty($v6dns1) && empty($v6dns2)):
		// need by service/firmware check?
		if(!isset($config['system']['disablefirmwarecheck']) || isset($config['ftpd']['enable'])):
			$errormsg .= gtext('No DNS setting found.');
			$errormsg .= "<br />\n";
		endif;
	endif;
	if(Session::isAdmin()):
		$lastconfigbackupstate = 0;
		if(isset($config['lastconfigbackup'])):
			$lastconfigbackup = intval($config['lastconfigbackup']);
			$now = time();
			if(($lastconfigbackup > 0) && ($lastconfigbackup < $now)):
				$test = $config['system']['backup']['settings']['reminderintervalshow'] ?? 28;
				$reminderintervalshow = filter_var($test,FILTER_VALIDATE_INT,['options' => ['default' => 28,'min_range' => 0,'max_range' => 9999]]);
				if($reminderintervalshow > 0):
					if(($now - $lastconfigbackup) > $reminderintervalshow * 24 * 60 * 60):
						$lastconfigbackupstate = 1;
					endif;
				endif;
			else:
				$lastconfigbackupstate = 2;
			endif;
		else:
			$lastconfigbackupstate = 3;
		endif;
		switch($lastconfigbackupstate):
			case 1:
				$errormsg .= gtext('Backup configuration reminder. The last configuration backup is older than the configured interval.');
				$errormsg .= '<br />';
				break;
			case 2:
				$errormsg .= gtext('Backup configuration. The date of the last configuration backup is invalid.');
				$errormsg .= '<br />';
				break;
			case 3:
				$errormsg .= gtext('Backup configuration. The date of the last configuration backup cannot be found.');
				$errormsg .= '<br />';
				break;
		endswitch;
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('System Information'));
?>
		</thead>
		<tbody>
<?php
			if(!empty($config['vinterfaces']['carp'])):
				html_textinfo2('vipstatus',gettext('Virtual IP address'),$sysinfo['vipstatus']);
			endif;
			html_textinfo2('hostname',gettext('Hostname'),$sysinfo['hostname']);
			html_textinfo2('version',gettext('Version'),sprintf('<strong>%s %s</strong> (%s %s)',get_product_version(),get_product_versionname(),gettext('revision'),get_product_revision()));
			html_textinfo2('builddate',gettext('Compiled'),get_datetime_locale(get_product_buildtimestamp()));
			exec('/sbin/sysctl -n kern.version',$osversion);
			html_textinfo2('platform_os',gettext('Platform OS'),sprintf('%s',$osversion[0]));
			html_textinfo2('platform',gettext('Platform'),sprintf(gettext('%s on %s'),$g['fullplatform'],$sysinfo['cpumodel']));
			if(isset($smbios['planar']) && is_array($smbios['planar'])):
				html_textinfo2('system',gettext('System'),sprintf('%s %s',$smbios['planar']['maker'] ?? '',$smbios['planar']['product'] ?? ''));
			elseif(isset($smbios['system']) && is_array($smbios['system'])):
				html_textinfo2('system',gettext('System'),sprintf('%s %s',$smbios['system']['maker'] ?? '',$smbios['system']['product'] ?? ''));
			endif;
			if(isset($smbios['bios']) && is_array($smbios['bios'])):
				html_textinfo2('system_bios',gettext('System BIOS'),sprintf('%s %s %s %s',$smbios['bios']['vendor'] ?? '',gettext('Version:'),$smbios['bios']['version'] ?? '',$smbios['bios']['reldate'] ?? ''));
			endif;
			html_textinfo2('system_datetime',gettext('System Time'),$sysinfo['date']);
			html_textinfo2('system_uptime',gettext('System Uptime'),$sysinfo['uptime']);
			if(Session::isAdmin()):
				if($config['lastchange']):
					html_textinfo2('last_config_change',gettext('System Config Change'),get_datetime_locale($config['lastchange']));
				endif;
				if(empty($sysinfo['cputemp2'])):
					if(!empty($sysinfo['cputemp'])):
						html_textinfo2('cputemp',gettext('CPU Temperature'),$sysinfo['cputemp']['vuh']);
					endif;
				endif;
				if(!empty($sysinfo['cpufreq'])):
					html_textinfo2('cpufreq',gettext('CPU Frequency'),sprintf('%sMHz',$sysinfo['cpufreq']));
				endif;
				render_cpuusage();
				render_cpuusage2();
				render_memusage();
				render_swapusage();
				render_load_averages();
				render_diskusage();
				render_poolusage();
				render_upsinfo();
				unset($vmlist);
				mwexec2('/usr/bin/find /dev/vmm -type c',$vmlist);
				unset($vmlist2);
				$vbox_user = 'vboxusers';
				$vbox_if = get_ifname($config['interfaces']['lan']['if']);
				$vbox_ipaddr = get_ipaddr($vbox_if);
				if(isset($config['vbox']['enable'])):
					mwexec2("/usr/local/bin/sudo -u {$vbox_user} /usr/local/bin/VBoxManage list runningvms",$vmlist2);
				else:
					$vmlist2 = [];
				endif;
				unset($vmlist3);
				if($g['arch'] == 'dom0'):
					$xen_if = get_ifname($config['interfaces']['lan']['if']);
					$xen_ipaddr = get_ipaddr($xen_if);
					$vmlist_json = shell_exec('/usr/local/sbin/xl list -l');
					$vmlist3 = json_decode($vmlist_json,true);
				else:
					$vmlist3 = [];
				endif;
				if(!empty($vmlist) || !empty($vmlist2) || !empty($vmlist3)):
?>
					<tr>
						<td class="celltag"><?=gtext('Virtual Machine');?></td>
						<td class="celldata">
							<table class="area_data_settings">
<?php
								$vmtype = 'BHyVe';
								$index = 0;
								foreach($vmlist as $vmpath):
									$vm = basename($vmpath);
									unset($temp);
									exec("/usr/sbin/bhyvectl ".escapeshellarg("--vm=$vm")." --get-lowmem | sed -e 's/.*\\///'",$temp);
									$vram = $temp[0] / 1024 / 1024;
									echo "<tr><td><div id='vminfo_$index'>";
									echo htmlspecialchars("$vmtype: $vm ($vram MiB)");
									echo '</div></td></tr>';
									if(++$index < count($vmlist)):
										echo '<tr><td class="nopad"><hr /></td></tr>';
									endif;
								endforeach;
								$vmtype = 'VBox';
								$index = 0;
								foreach($vmlist2 as $vmline):
									$vm = '';
									if(preg_match("/^\"(.+)\"\s*\{(\S+)\}$/",$vmline,$match)):
										$vm = $match[1];
										$uuid = $match[2];
									endif;
									if($vm == ''):
										continue;
									endif;
									$vminfo = get_vbox_vminfo($vbox_user,$uuid);
									$vram = $vminfo['memory']['value'];
									echo '<tr><td>';
									echo htmlspecialchars("$vmtype: $vm ($vram MiB)");
									if(isset($vminfo['vrde']) && $vminfo['vrde']['value'] == 'on'):
										echo ' VNC: ';
										$vncport = $vminfo['vrdeport']['value'];
										echo htmlspecialchars(sprintf('%s:%s',$vbox_ipaddr,$vncport));
									endif;
									echo '</td></tr>',PHP_EOL;
									if(++$index < count($vmlist2)):
										echo '<tr><td class="nopad"><hr /></td></tr>';
									endif;
								endforeach;
								$vmtype = 'Xen';
								$index = 0;
								$vncport_unused = 5900;
								foreach($vmlist3 as $k => $v):
									$domid = $v['domid'];
									$type = $v['config']['c_info']['type'];
									$vm = $v['config']['c_info']['name'];
									$vram = (int)(($v['config']['b_info']['target_memkb'] + 1023 ) / 1024);
									$vcpus = 1;
									if($domid == 0):
										$vcpus = @exec('/sbin/sysctl -q -n hw.ncpu');
										$info = get_xen_info();
										$cpus = $info['nr_cpus']['value'];
										$th = $info['threads_per_core']['value'];
										if(empty($th)) {
											$th = 1;
										}
										$core =(int)($cpus / $th);
										$mem = $info['total_memory']['value'];
										$ver = $info['xen_version']['value'];
									elseif(!empty($v['config']['b_info']['max_vcpus'])):
										$vcpus = $v['config']['b_info']['max_vcpus'];
									endif;
									echo "<tr><td><div id='vminfo3_$index'>";
									echo htmlspecialchars("$vmtype $type: $vm ($vram MiB / $vcpus VCPUs)");
									if($domid == 0):
										echo ' ';
										echo htmlspecialchars("Xen version {$ver} / {$mem} MiB / {$core} core".($th > 1 ? "/HT" : ""));
									elseif($type == 'pv' && isset($v['config']['vfbs']) && isset($v['config']['vfbs'][0]['vnc'])):
										$vnc = $v['config']['vfbs'][0]['vnc'];
										$vncport = 'unknown';
/*
										if(isset($vnc['display'])):
											$vncdisplay = $vnc['display'];
											$vncport = 5900 + $vncdisplay;
										elseif(isset($vnc['findunused'])):
											$vncport = $vncport_unused;
											$vncport_unused++;
										endif;
*/
										$console = get_xen_console($domid);
										if(!empty($console) && isset($console['vnc-port'])):
											$vncport = $console['vnc-port']['value'];
										endif;
										echo ' ';
										echo htmlspecialchars("vnc://{$xen_ipaddr}:{$vncport}/");
									elseif($type == 'hvm' && isset($v['config']['b_info']['type.hvm']['vnc']['enable'])):
										$vnc = $v['config']['b_info']['type.hvm']['vnc'];
										$vncport = 'unknown';
/*
										if(isset($vnc['display'])) {
											$vncdisplay = $vnc['display'];
											$vncport = 5900 + $vncdisplay;
										} else if(isset($vnc['findunused'])) {
											$vncport = $vncport_unused;
											$vncport_unused++;
										}
*/
										$console = get_xen_console($domid);
										if(!empty($console) && isset($console['vnc-port'])):
											$vncport = $console['vnc-port']['value'];
										endif;
										echo ' ';
										echo htmlspecialchars("VNC:/{$xen_ipaddr}:{$vncport}/");
									endif;
									echo '</div></td></tr>';
									if(++$index < count($vmlist3)):
										echo '<tr><td class="nopad"><hr /></td></tr>';
									endif;
								endforeach;
?>
							</table>
						</td>
					</tr>
<?php
				endif;
			endif;
?>
		</tbody>
	</table>
</td></tr></tbody></table>
<?php
include 'fend.inc';
