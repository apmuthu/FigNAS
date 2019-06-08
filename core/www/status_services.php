<?php
/*
	services_status.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

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
require_once 'properties_status_services.php';

function status_services_sphere() {
	global $g,$config;

	$sphere = new co_sphere_grid('status_services','php');
//	sphere external content
	$sphere->grid = [];
	$ups_script = 'nut';
	if(isset($config['ups']['mode']) && ($config['ups']['mode'] == 'slave')):
		$ups_script = 'nut_upsmon';
	endif;
	if('dom0' !== $g['arch']):
		$sphere->grid[] = ['name' => gettext('HAST'),'link' => 'services_hast.php','config' => 'hast','scriptname' => 'hastd'];
		$sphere->grid[] = ['name' => gettext('CIFS/SMB'),'link' => 'services_samba.php','config' => 'samba','scriptname' => 'samba'];
		$sphere->grid[] = ['name' => gettext('FTP'),'link' => 'services_ftp.php','config' => 'ftpd','scriptname' => 'proftpd'];
		$sphere->grid[] = ['name' => gettext('TFTP'),'link' => 'services_tftp.php','config' => 'tftpd','scriptname' => 'tftpd'];
		$sphere->grid[] = ['name' => gettext('SSH'),'link' => 'services_sshd.php','config' => 'sshd','scriptname' => 'sshd'];
		$sphere->grid[] = ['name' => gettext('NFS'),'link' => 'services_nfs.php','config' => 'nfsd','scriptname' => 'nfsd'];
		$sphere->grid[] = ['name' => gettext('AFP'),'link' => 'services_afp.php','config' => 'afp','scriptname' => 'netatalk'];
		$sphere->grid[] = ['name' => gettext('RSYNC'),'link' => 'services_rsyncd.php','config' => 'rsyncd','scriptname' => 'rsyncd'];
		$sphere->grid[] = ['name' => gettext('Syncthing'),'link' => 'services_syncthing.php','config' => 'syncthing','scriptname' => 'syncthing'];
		$sphere->grid[] = ['name' => gettext('Unison'),'link' => 'services_unison.php','config' => 'unison','scriptname' => 'unison'];
		$sphere->grid[] = ['name' => gettext('iSCSI Target CTL'),'link' => 'services_ctl.php','config' => 'ctld','scriptname' => 'ctld'];
		$sphere->grid[] = ['name' => gettext('iSCSI Target ISTGT'),'link' => 'services_iscsitarget.php','config' => 'iscsitarget','scriptname' => 'iscsi_target'];
		$sphere->grid[] = ['name' => gettext('DLNA/UPnP Fuppes'),'link' => 'services_fuppes.php','config' => 'upnp','scriptname' => 'fuppes'];
		$sphere->grid[] = ['name' => gettext('DLNA/UPnP MiniDLNA'),'link' => 'services_minidlna.php','config' => 'minidlna','scriptname' => 'minidlna'];
		$sphere->grid[] = ['name' => gettext('iTunes/DAAP'),'link' => 'services_daap.php','config' => 'daap','scriptname' => 'mt-daapd'];
		$sphere->grid[] = ['name' => gettext('Dynamic DNS'),'link' => 'services_dynamicdns.php','config' => 'dynamicdns','scriptname' => 'inadyn'];
		$sphere->grid[] = ['name' => gettext('SNMP'),'link' => 'services_snmp.php','config' => 'snmpd','scriptname' => 'bsnmpd'];
		$sphere->grid[] = ['name' => gettext('UPS'),'link' => 'services_ups.php','config' => 'ups','scriptname' => $ups_script];
		$sphere->grid[] = ['name' => gettext('Webserver'),'link' => 'services_websrv.php','config' => 'websrv','scriptname' => 'websrv'];
		$sphere->grid[] = ['name' => gettext('MariaDB'),'link' => 'services_mariadb.php','config' => 'mariadb','scriptname' => 'mysqldb'];
		$sphere->grid[] = ['name' => gettext('BitTorrent'),'link' => 'services_bittorrent.php','config' => 'bittorrent','scriptname' => 'transmission'];
		$sphere->grid[] = ['name' => gettext('LCDproc'),'link' => 'services_lcdproc.php','config' => 'lcdproc','scriptname' => 'LCDd'];
	else:
		$sphere->grid[] = ['name' => gettext('SSH'),'link' => 'services_sshd.php','config' => 'sshd','scriptname' => 'sshd'];
		$sphere->grid[] = ['name' => gettext('NFS'),'link' => 'services_nfs.php','config' => 'nfsd','scriptname' => 'nfsd'];
		$sphere->grid[] = ['name' => gettext('iSCSI Target'),'link' => 'services_iscsitarget.php','config' => 'iscsitarget','scriptname' => 'iscsi_target'];
		$sphere->grid[] = ['name' => gettext('CAM Target Layer / iSCSI Target'),'link' => 'services_ctl.php','config' => 'ctld','scriptname' => 'ctld'];
		$sphere->grid[] = ['name' => gettext('UPS'),'link' => 'services_ups.php','config' => 'ups','scriptname' => $ups_script];
	endif;
	return $sphere;
}
$cop = new status_services_properties();
$sphere = status_services_sphere();
$pgtitle = [gettext('Status'),gettext('Services')];
$a_col_width = ['70%','10%','10%','10%'];
$n_col_width = count($a_col_width);
$document = new_page($pgtitle,$sphere->get_scriptname(),'tablesort');
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
//	add content
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->ins_titleline(gettext('Overview'),$n_col_width);
$thead->addTR()->
	insTHwC('lhell',$cop->get_name()->get_title())->
	insTHwC('lhelc sorter-false parser-false',$cop->get_enabled()->get_title())->
	insTHwC('lhelc sorter-false parser-false',$cop->get_status()->get_title())->
	insTHwC('lhebl sorter-false parser-false',$cop->get_toolbox()->get_title());
foreach($sphere->grid as $sphere->row_id => $sphere->row):
	$is_enabled = is_bool($test = $config[$sphere->row['config']]['enable'] ?? false) ? $test : true;
	$is_running = (0 == rc_is_service_running($sphere->row['scriptname']));
	$dc = $is_enabled ? '' : 'd';
	$tba = $tbody->
		addTR()->
			insTDwC('lcell' . $dc,$sphere->row[$cop->get_name()->get_name()] ?? '')->
			ins_enadis_icon($is_enabled)->
			ins_enadis_icon($is_running)->
			add_toolbox_area()->
				push()->
				addTD()->
					addA(['href' => $sphere->row['link']])->
						insIMG(['src' => $g_img['mod'],'title' => gettext('Modify Service'),'alt' => gettext('Modify Service'),'class' => 'spin oneemhigh'])->
				pop()->
				ins_maintainbox($sphere,false)->
				ins_informbox($sphere,false);
endforeach;
$document->render();
