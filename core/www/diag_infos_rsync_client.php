<?php
/*
	diag_infos_rsync_client.php

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
//	require_once 'properties_services_rsyncd_client.php';
//	require_once 'co_request_method.php';

function diag_infos_rsync_client_get_sphere() {
	global $config;
	
	$sphere = new co_sphere_grid('diag_infos_rsync_client','php');
/*
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	sphere->set_notifier('rsyncclient');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->
		setmsg_sym_add(gettext('Add Rsync Job'))->
		setmsg_sym_mod(gettext('Edit Rsync Job'))->
		setmsg_sym_del(gettext('Rsync job is marked for deletion'))->
		setmsg_sym_loc(gettext('Rsync job is protected'))->
		setmsg_sym_unl(gettext('Rsync job is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected Rsync Jobs'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected rsync jobs?'))->
		setmsg_cbm_disable(gettext('Disable Selected Rsync Jobs'))->
		setmsg_cbm_disable_confirm(gettext('Do you want to disable selected rsync jobs?'))->
		setmsg_cbm_enable(gettext('Enable Selected Rsync Jobs'))->
		setmsg_cbm_enable_confirm(gettext('Do you want to enable selected rsync jobs?'))->
		setmsg_cbm_toggle(gettext('Toggle Selected Rsync Jobs'))->
		setmsg_cbm_toggle_confirm(gettext('Do you want to toggle selected rsync jobs?'));
 */
//	sphere data
	$sphere->grid = &array_make_branch($config,'rsync','rsyncclient');
	return $sphere;
}
function diag_infos_rsync_client_selection($cop,$sphere) {
	global $g_img;
/*
	global $d_sysrebootreqd_path;
	global $savemsg;

	$input_errors = [];
	$errormsg = '';
 */
	$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('RSYNC Client')];
	$record_exists = count($sphere->grid) > 0;
	$use_tablesort = count($sphere->grid) > 1;
	$a_col_width = ['20%','20%','10%','20%','30%'];
	$n_col_width = count($a_col_width);
	if($use_tablesort):
		$document = new_page($pgtitle,NULL,'tablesort');
	else:
		$document = new_page($pgtitle);
	endif;
	//	get areas
//	$body = $document->getElementById('main');
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
				ins_tabnav_record('diag_infos_samba.php',gettext('CIFS/SMB'))->
				ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
				ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'),gettext('Reload page'),true)->
				ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
				ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
				ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
				ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
	//	create data area
	$content = $pagecontent->add_area_data();
/*
	//	display information, warnings and errors
	$content->
		ins_input_errors($input_errors)->
		ins_info_box($savemsg)->
		ins_error_box($errormsg);
	if(file_exists($d_sysrebootreqd_path)):
		$content->ins_info_box(get_std_save_message(0));
	endif;
	if(updatenotify_exists($sphere->get_notifier())):
		$content->ins_config_has_changed_box();
	endif;
 */
	//	add content
	$table = $content->add_table_data_selection();
	$table->ins_colgroup_with_styles('width',$a_col_width);
	$thead = $table->addTHEAD();
	if($record_exists):
		$tbody = $table->addTBODY();
	else:
		$tbody = $table->addTBODY(['class' => 'donothighlight']);
	endif;
//	$tfoot = $table->addTFOOT();
	$thead->ins_titleline(gettext('RSYNC Client Information & Status'),$n_col_width);
	$tr = $thead->addTR();
//	if($record_exists):
		$tr->
/*
			push()->
			addTHwC('lhelc sorter-false parser-false')->
				ins_cbm_checkbox_toggle($sphere)->
			pop()->
 */
			insTHwC('lhell',gettext('Remote Server Address'))->
			insTHwC('lhell',gettext('Remote Share Name'))->
			insTHwC('lhelc',gettext('Direction'))->
			insTHwC('lhell',gettext('Local Share'))->
			insTHwC('lhebl',gettext('Detected Remote Shares'));
/*
			insTHwC('lhebl sorter-false parser-false',gettext('Toolbox'));
	else:
		$tr->
			insTHwC('lhelc')->
			insTHwC('lhell',gettext('Remote Share Name'))->
			insTHwC('lhell',gettext('Remote Server Address'))->
			insTHwC('lhell',gettext('Local Share (Destination)'))->
			insTHwC('lhebl',gettext('Detected Remote Shares'))->
			insTHwC('lhebl',gettext('Toolbox'));
	endif;
 */
	if($record_exists):
		foreach($sphere->grid as $sphere->row_id => $sphere->row):
/*
			$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->get_row_identifier_value());
			$is_notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
			$is_enabled = $sphere->is_enadis_enabled() ? (is_bool($test = $sphere->row[$cop->get_enable()->get_name()] ?? false) ? $test : true): true;
			$is_notprotected = $sphere->is_lock_enabled() ? !(is_bool($test = $sphere->row[$cop->get_protected()->get_name()] ?? false) ? $test : true) : true;
			$dc = $is_enabled ? '' : 'd';
 */
			$remoteserverips = [];
			$remoteshare = is_string($test = $sphere->row['remoteshare'] ?? '') ? $test : '';
			$rsyncserverip = is_string($test = $sphere->row['rsyncserverip'] ?? '') ? $test : '';
			$is_reversedirection = is_bool($test = $sphere->row['reverseddirection'] ?? false) ? $test : true;
			$localshare = is_string($test = $sphere->row['localshare'] ?? '') ? $test : '';
			if(array_key_exists($rsyncserverip,$remoteserverips)):
				$detected_shares = $remoteserverips[$rsyncserverip];
			else:
				unset($rawdata);
				$cmd = sprintf('/usr/local/bin/rsync %s::',$rsyncserverip);
				exec($cmd,$rawdata);
				$remoteserverips[$rsyncserverip] = [];
				foreach($rawdata as $row):
					$remoteserverips[$rsyncserverip][] = array_map('trim',explode("\t",$row,2));
				endforeach;
				$detected_shares = $remoteserverips[$rsyncserverip];
			endif;
			$tr = $tbody->addTR();
			$tr->
				insTDwC('lcell',$rsyncserverip)->
				insTDwC('lcell',$remoteshare);
			if($is_reversedirection):
				$gt = gettext('Push');
				$tr->
					addTDwC('lcelc')->
						addA(['title' => $gt])->
							insIMG(['src' => $g_img['mle'],'alt' => $gt]);
			else:
				$gt = gettext('Pull');
				$tr->
					addTDwC('lcelc')->
						addA(['title' => $gt])->
							insIMG(['src' => $g_img['mri'],'alt' => $gt]);
			endif;
			$table_detected = $tr->
				insTDwC('lcell',$localshare)->
				addTDwC('lcebl')->
					add_table_data_selection();
			//	subsection list detected shares
			$a_col_width_detected = ['50%','50%'];
//			$n_col_width_detected = count($a_col_width);
			$table_detected->ins_colgroup_with_styles('width',$a_col_width_detected);
			$tbody_detected = $table_detected->addTBODY(['class' => 'donothighlight']);
			foreach($detected_shares as $detected_share_info):
				$tr_detected = $tbody_detected->addTR();
				switch(count($detected_share_info)):
					case 0:
						break;
					case 1:
						$tr_detected->
							insTD(['colspan' => '2'],$detected_share_info[0] ?? '');
						break;
					default:
						$tr_detected->
							insTD([],$detected_share_info[0] ?? '')->
							insTD([],$detected_share_info[1] ?? '');
				endswitch;
			endforeach;
		endforeach;
	else:
		$tbody->ins_no_records_found($n_col_width,gettext('No RSYNC Client configured.'));
	endif;
/*
	$tfoot->ins_record_add($sphere,$n_col_width);
	$document->
		add_area_buttons()->
			ins_cbm_button_enadis($sphere)->
			ins_cbm_button_delete($sphere);
	//	additional javascript code
	$body->addJavaScript($sphere->get_js());
	$body->add_js_on_load($sphere->get_js_on_load());
	$body->add_js_document_ready($sphere->get_js_document_ready());
 */
	$document->render();
}
$sphere = diag_infos_rsync_client_get_sphere();
diag_infos_rsync_client_selection('',$sphere);
