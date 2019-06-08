<?php
/*
	diag_infos_samba.php

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

function diag_infos_samba_1_ajax() {
	global $config;
	
	if(is_bool($test = $config['samba']['enable'] ?? false) ? $test : true):
		$cmd = "/usr/bin/env LC_ALL=en_US.UTF-8 smbstatus -b | grep -v 'Samba version'";
		mwexec2($cmd,$rawdata);
		return trim(implode(PHP_EOL,$rawdata));
	else:
		return gettext('CIFS/SMB is disabled.');
	endif;
}
function diag_infos_samba_2_ajax() {
	global $config;
	
	if(is_bool($test = $config['samba']['enable'] ?? false) ? $test : true):
		$cmd = "/usr/bin/env LC_ALL=en_US.UTF-8 smbstatus -S";
		mwexec2($cmd,$rawdata);
		return trim(implode(PHP_EOL,$rawdata));
	else:
		return gettext('No information available.');
	endif;
}
function diag_infos_samba_3_ajax() {
	global $config;
	
	if(is_bool($test = $config['samba']['enable'] ?? false) ? $test : true):
		$cmd = "/usr/bin/env LC_ALL=en_US.UTF-8 smbstatus -L | grep -v 'Locked files'";
		mwexec2($cmd,$rawdata);
		return trim(implode(PHP_EOL,$rawdata));
	else:
		return gettext('No information available.');
	endif;
}
if(is_ajax()):
	$status['area_refresh_1'] = diag_infos_samba_1_ajax();
	$status['area_refresh_2'] = diag_infos_samba_2_ajax();
	$status['area_refresh_3'] = diag_infos_samba_3_ajax();
	render_ajax($status);
endif;
$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('CIFS/SMB')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_infos_disks.php',gettext('Disks'))->
			ins_tabnav_record('diag_infos_disks_info.php',gettext('Disks (Info)'))->
			ins_tabnav_record('diag_infos_part.php',gettext('Partitions'))->
			ins_tabnav_record('diag_infos_smart.php',gettext('S.M.A.R.T.'))->
			ins_tabnav_record('diag_infos_space.php',gettext('Space Used'))->
			ins_tabnav_record('diag_infos_swap.php',gettext('Swap'))->
			ins_tabnav_record('diag_infos_mount.php',gettext('Mounts'))->
			ins_tabnav_record('diag_infos_raid.php',gettext('Software RAID'))->
			ins_tabnav_record('diag_infos_iscsi.php',gettext('iSCSI Initiator'))->
			ins_tabnav_record('diag_infos_ad.php',gettext('MS Domain'))->
			ins_tabnav_record('diag_infos_samba.php',gettext('CIFS/SMB'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
			ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'))->
			ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
			ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
$pagecontent->
	add_area_data()->
		push()->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_titleline(gettext('Active Users'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					addTDwC('celldata')->
						addElement('pre',['class' => 'cmdoutput'])->
							addElement('span',['id' => 'area_refresh_1'],diag_infos_samba_1_ajax())->
		last()->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_separator()->
				c2_titleline(gettext('Active Shares'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					addTDwC('celldata')->
						addElement('pre',['class' => 'cmdoutput'])->
							addElement('span',['id' => 'area_refresh_2'],diag_infos_samba_2_ajax())->
		pop()->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_separator()->
				c2_titleline(gettext('Locked Files'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					addTDwC('celldata')->
						addElement('pre',['class' => 'cmdoutput'])->
							addElement('span',['id' => 'area_refresh_3'],diag_infos_samba_3_ajax());
//	add additional javascript code
$js_document_ready = <<<'EOJ'
	var gui = new GUI;
	gui.recall(5000,5000,'diag_infos_samba.php',null,function(data) {
		if($('#area_refresh_1').length) {
			$('#area_refresh_1').text(data.area_refresh_1);
		}
		if($('#area_refresh_2').length) {
			$('#area_refresh_2').text(data.area_refresh_2);
		}
		if($('#area_refresh_3').length) {
			$('#area_refresh_3').text(data.area_refresh_3);
		}
	});
EOJ;
$body->add_js_document_ready($js_document_ready);
$document->render();
