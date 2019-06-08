<?php
/*
	diag_infos_ad.php

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

array_make_branch($config,'ad');
$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('MS Active Directory')];
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
			ins_tabnav_record('diag_infos_ad.php',gettext('MS Domain'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_infos_samba.php',gettext('CIFS/SMB'))->
			ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
			ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'))->
			ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
			ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
$area_data = $pagecontent->add_area_data();
$table = $area_data->add_table_data_settings();
$table->ins_colgroup_data_settings();
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->c2_titleline(gettext('MS Active Directory Information & Status'));
if(is_bool($test = $config['ad']['enable'] ?? false) ? $test : true):
	unset($rawdata);
	$cmd = sprintf('/usr/local/bin/net rpc testjoin -S %s 2>&1',escapeshellarg($config['ad']['domaincontrollername']));
	exec($cmd,$rawdata);
	$tbody->addTR()->
		insTDwC('celltag',gettext('Result of testjoin'))->
		addTDwC('celldata')->
			addElement('pre',['class' => 'cmdoutput'])->
				addElement('span',[],implode(PHP_EOL,$rawdata));
	unset($rawdata);
	$cmd = '/usr/local/bin/wbinfo -p 2>&1';
	exec($cmd,$rawdata);
	$tbody->addTR()->
		insTDwC('celltag',gettext('Winbindd Availability'))->
		addTDwC('celldata')->
			addElement('pre',['class' => 'cmdoutput'])->
				addElement('span',[],implode(PHP_EOL,$rawdata));
	unset($rawdata);
	$cmd = '/usr/local/bin/wbinfo -t 2>&1';
	exec($cmd,$rawdata);
	$tbody->addTR()->
		insTDwC('celltag',gettext('Trust Account Status'))->
		addTDwC('celldata')->
			addElement('pre',['class' => 'cmdoutput'])->
				addElement('span',[],implode(PHP_EOL,$rawdata));
	$tfoot->c2_separator();
	$table = $area_data->add_table_data_settings();
	$table->ins_colgroup_data_settings();
	$thead = $table->addTHEAD();
	$tbody = $table->addTBODY();
	$thead->c2_titleline(gettext('Imported Users'));
	unset($rawdata);
	$cmd = sprintf('/usr/local/bin/net rpc user -S %s -U %s -v 2>&1',
		escapeshellarg($config['ad']['domaincontrollername']),
		escapeshellarg(sprintf('%s%%%s',$config['ad']['username'],$config['ad']['password']))
	);
	exec($cmd,$rawdata);
	$tbody->addTR()->
		insTDwC('celltag',gettext('Information'))->
		addTDwC('celldata')->
			addElement('pre',['class' => 'cmdoutput'])->
				addElement('span',[],implode(PHP_EOL,$rawdata));
else:
	$tbody->addTR()->
		insTDwC('celltag',gettext('Information'))->
		addTDwC('celldata')->
			addElement('pre',['class' => 'cmdoutput'])->
				addElement('span',[],gettext('AD authentication is disabled.'));
endif;
$document->render();
