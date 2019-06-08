<?php
/*
	diag_infos_part.php

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

$a_disk = get_physical_disks_list();
$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('Partitions')];
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
			ins_tabnav_record('diag_infos_part.php',gettext('Partitions'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_infos_smart.php',gettext('S.M.A.R.T.'))->
			ins_tabnav_record('diag_infos_space.php',gettext('Space Used'))->
			ins_tabnav_record('diag_infos_swap.php',gettext('Swap'))->
			ins_tabnav_record('diag_infos_mount.php',gettext('Mounts'))->
			ins_tabnav_record('diag_infos_raid.php',gettext('Software RAID'))->
			ins_tabnav_record('diag_infos_iscsi.php',gettext('iSCSI Initiator'))->
			ins_tabnav_record('diag_infos_ad.php',gettext('MS Domain'))->
			ins_tabnav_record('diag_infos_samba.php',gettext('CIFS/SMB'))->
			ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
			ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'))->
			ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
			ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(empty($a_disk)):
	$content->
		add_table_data_settings()->
			ins_colgroup_data_settings()->
			push()->
			addTHEAD()->
				c2_titleline(gettext('Disk Partition Information'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					insTDwC('celldata',gettext('No disks found.'));
else:
	$do_separator = false;
	foreach($a_disk as $diskk => $diskv):
		$diskcontent = $content->
			add_table_data_settings()->
				ins_colgroup_data_settings();
		$thead = $diskcontent->addTHEAD();
		$tbody = $diskcontent->addTBODY();
		if($do_separator):
			$thead->c2_separator();
		else:
			$do_separator = true;
		endif;
		$thead->c2_titleline(sprintf(gettext('Device /dev/%s - %s'),$diskv['name'],$diskv['desc']));
		$cmd = sprintf('/sbin/gpart show %s',escapeshellarg($diskk));
		unset($geom_rawdata);
		exec($cmd,$geom_rawdata);
		$geom_output = implode(PHP_EOL,$geom_rawdata);
		unset($geom_rawdata);
		if(preg_match('/\S/',$geom_output)):
		else:
			$geom_output = gettext('No GEOM partition information found.');
		endif;
		$tbody->
			addTR()->
				insTDwC('celltag',gettext('GEOM Partition Information'))->
				addTDwC('celldata')->
					insElement('pre',['class' => 'cmdoutput'],$geom_output);
		$cmd = sprintf('/sbin/fdisk %s',escapeshellarg($diskk));
		unset($fdisk_rawdata);
		exec($cmd,$fdisk_rawdata);
		$fdisk_output = implode(PHP_EOL,$fdisk_rawdata);
		unset($fdisk_rawdata);
		if(preg_match('/\S/',$fdisk_output)):
		else:
			$fdisk_output = gettext('No fdisk information found.');
		endif;
		$tbody->
			addTR()->
				insTDwC('celltag',gettext('Fdisk Partition Information'))->
				addTDwC('celldata')->
					insElement('pre',['class' => 'cmdoutput'],$fdisk_output);
	endforeach;
endif;
$document->render();
