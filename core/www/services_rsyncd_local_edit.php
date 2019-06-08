<?php
/*
	services_rsyncd_local_edit.php

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

$sphere_scriptname = basename(__FILE__);

if(isset($_GET['uuid'])):
	$uuid = $_GET['uuid'];
endif;
if(isset($_POST['uuid'])):
	$uuid = $_POST['uuid'];
endif;
$a_rsynclocal = &array_make_branch($config,'rsync','rsynclocal');
if(isset($uuid) && (false !== ($cnid = array_search_ex($uuid, $a_rsynclocal,'uuid')))):
	$pconfig['enable'] = isset($a_rsynclocal[$cnid]['enable']);
	$pconfig['uuid'] = $a_rsynclocal[$cnid]['uuid'];
	$pconfig['source'] = $a_rsynclocal[$cnid]['source'];
	$pconfig['destination'] = $a_rsynclocal[$cnid]['destination'];
	$pconfig['minute'] = $a_rsynclocal[$cnid]['minute'];
	$pconfig['hour'] = $a_rsynclocal[$cnid]['hour'];
	$pconfig['day'] = $a_rsynclocal[$cnid]['day'];
	$pconfig['month'] = $a_rsynclocal[$cnid]['month'];
	$pconfig['weekday'] = $a_rsynclocal[$cnid]['weekday'];
	//$pconfig['sharetosync'] = $a_rsynclocal[$cnid]['sharetosync'];
	$pconfig['all_mins'] = $a_rsynclocal[$cnid]['all_mins'];
	$pconfig['all_hours'] = $a_rsynclocal[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_rsynclocal[$cnid]['all_days'];
	$pconfig['all_months'] = $a_rsynclocal[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_rsynclocal[$cnid]['all_weekdays'];
	$pconfig['description'] = $a_rsynclocal[$cnid]['description'];
	$pconfig['who'] = $a_rsynclocal[$cnid]['who'];
	$pconfig['recursive'] = isset($a_rsynclocal[$cnid]['options']['recursive']);
	$pconfig['times'] = isset($a_rsynclocal[$cnid]['options']['times']);
	$pconfig['compress'] = isset($a_rsynclocal[$cnid]['options']['compress']);
	$pconfig['archive'] = isset($a_rsynclocal[$cnid]['options']['archive']);
	$pconfig['delete'] = isset($a_rsynclocal[$cnid]['options']['delete']);
	$pconfig['delete_algorithm'] = $a_rsynclocal[$cnid]['options']['delete_algorithm'];
	$pconfig['quiet'] = isset($a_rsynclocal[$cnid]['options']['quiet']);
	$pconfig['perms'] = isset($a_rsynclocal[$cnid]['options']['perms']);
	$pconfig['xattrs'] = isset($a_rsynclocal[$cnid]['options']['xattrs']);
	$pconfig['extraoptions'] = $a_rsynclocal[$cnid]['options']['extraoptions'];
else:
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['source'] = '';
	$pconfig['destination'] = '';
	$pconfig['minute'] = [];
	$pconfig['hour'] = [];
	$pconfig['day'] = [];
	$pconfig['month'] = [];
	$pconfig['weekday'] = [];
	//$pconfig['sharetosync'] = '';
	$pconfig['all_mins'] = 0;
	$pconfig['all_hours'] = 0;
	$pconfig['all_days'] = 0;
	$pconfig['all_months'] = 0;
	$pconfig['all_weekdays'] = 0;
	$pconfig['description'] = '';
	$pconfig['who'] = 'root';
	$pconfig['recursive'] = false;
	$pconfig['times'] = false;
	$pconfig['compress'] = false;
	$pconfig['archive'] = true;
	$pconfig['delete'] = false;
	$pconfig['delete_algorithm'] = 'default';
	$pconfig['quiet'] = false;
	$pconfig['perms'] = false;
	$pconfig['xattrs'] = false;
	$pconfig['extraoptions'] = '';
endif;
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: services_rsyncd_local.php');
		exit;
	endif;
	// Input validation
	$reqdfields = ['source','destination','who'];
	$reqdfieldsn = [gtext('Source Share'),gtext('Destination Share'),gtext('Who')];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	if(!empty($_POST['Submit']) && gtext('Execute now') !== $_POST['Submit']):
		// Validate synchronization time
		do_input_validate_synctime($_POST,$input_errors);
	endif;
	if(empty($input_errors)):
		$rsynclocal = [];
		$rsynclocal['enable'] = isset($_POST['enable']) ? true : false;
		$rsynclocal['uuid'] = $_POST['uuid'];
		$rsynclocal['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$rsynclocal['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$rsynclocal['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$rsynclocal['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$rsynclocal['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$rsynclocal['source'] = $_POST['source'];
		$rsynclocal['destination'] = $_POST['destination'];
		$rsynclocal['all_mins'] = $_POST['all_mins'];
		$rsynclocal['all_hours'] = $_POST['all_hours'];
		$rsynclocal['all_days'] = $_POST['all_days'];
		$rsynclocal['all_months'] = $_POST['all_months'];
		$rsynclocal['all_weekdays'] = $_POST['all_weekdays'];
		$rsynclocal['description'] = $_POST['description'];
		$rsynclocal['who'] = $_POST['who'];
		$rsynclocal['options']['recursive'] = isset($_POST['recursive']) ? true : false;
		$rsynclocal['options']['times'] = isset($_POST['times']) ? true : false;
		$rsynclocal['options']['compress'] = isset($_POST['compress']) ? true : false;
		$rsynclocal['options']['archive'] = isset($_POST['archive']) ? true : false;
		$rsynclocal['options']['delete'] = isset($_POST['delete']) ? true : false;
		$rsynclocal['options']['delete_algorithm'] = $_POST['delete_algorithm'];
		$rsynclocal['options']['quiet'] = isset($_POST['quiet']) ? true : false;
		$rsynclocal['options']['perms'] = isset($_POST['perms']) ? true : false;
		$rsynclocal['options']['xattrs'] = isset($_POST['xattrs']) ? true : false;
		$rsynclocal['options']['extraoptions'] = $_POST['extraoptions'];
		if(isset($uuid) && (FALSE !== $cnid)):
			$a_rsynclocal[$cnid] = $rsynclocal;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		else:
			$a_rsynclocal[] = $rsynclocal;
			$mode = UPDATENOTIFY_MODE_NEW;
		endif;
		updatenotify_set('rsynclocal',$mode,$rsynclocal['uuid']);
		write_config();
		if(!empty($_POST['Submit']) && stristr($_POST['Submit'],gtext('Execute now'))):
			$retval = 0;
			// Update scripts and execute it.
			config_lock();
			$retval |= rc_exec_service('rsync_local');
			$retval |= rc_update_service('cron');
			config_unlock();
			if($retval == 0):
				updatenotify_clear('rsynclocal',$rsynclocal['uuid']);
			endif;
			$retval |= rc_exec_script_async("su -m {$rsynclocal['who']} -c '/bin/sh /var/run/rsync_local_{$rsynclocal['uuid']}.sh'");
			$savemsg = get_std_save_message($retval);
		else:
			header('Location: services_rsyncd_local.php');
			exit;
		endif;
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('Rsync'),gtext('Local'),isset($uuid) ? gtext('Edit') : gtext('Add')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
<?php // Control delete_algorithm_tr.?>
	$("#delete_algorithm_tr").toggle($("#delete").checked);
	$("#delete").change(function() {
		$("#delete_algorithm_tr").toggle(this.checked);
	});
});
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}
//]]>
</script>
<table id="area_navigator">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="services_rsyncd.php"><span><?=gtext('Server');?></span></a></li>
		<li class="tabinact"><a href="services_rsyncd_client.php"><span><?=gtext('Client');?></span></a></li>
		<li class="tabact"><a href="services_rsyncd_local.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Local');?></span></a></li>
	</ul></td></tr>
</table>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline_checkbox2('enable',gettext('Rsync Job'),!empty($pconfig['enable']) ? true : false,gettext('Enable'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltagreq"><?=gtext('Source Share');?></td>
				<td class="celldatareq">
					<input name="source" type="text" class="formfld" id="source" size="60" value="<?=htmlspecialchars($pconfig['source']);?>"/>
					<input name="browse" type="button" class="formbtn" id="Browse" onclick='ifield = form.source; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield; window.slash_source = 1;' value="..." /><br />
					<span class="vexpl"><?=gtext('Source directory to be synchronized.');?></span>
			  </td>
			</tr>
			<tr>
				<td class="celltagreq"><?=gtext('Destination Share');?></td>
				<td class="celldatareq">
					<input name="destination" type="text" class="formfld" id="destination" size="60" value="<?=htmlspecialchars($pconfig['destination']);?>" />
					<input name="browse2" type="button" class="formbtn" id="Browse2" onclick='ifield2 = form.destination; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield2.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield2; window.ifield = ifield2; window.slash_destination = 1;' value="..." /><br />
					<span class="vexpl"><?=gtext('Target directory.');?></span>
				</td>
			</tr>
<?php
			$a_user = [];
			foreach(system_get_user_list() as $userk => $userv):
				 $a_user[$userk] = $userk;
			endforeach;
			html_combobox2('who',gettext('Who'),$pconfig['who'],$a_user,'',true);
?>
			<tr>
				<td class="celltagreq"><?=gtext('Synchronization Time');?></td>
				<td class="celldatareq">
<?php
					include 'cs_scheduletime.php';
?>
				</td>
			</tr>
<?php
			html_inputbox2('description',gettext('Description'),htmlspecialchars($pconfig['description']),'',false,40);
?>
		</tbody>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_separator(2);
			html_titleline2(gettext('Advanced Options'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('recursive',gettext('Recursive'),!empty($pconfig['recursive']) ? true : false,gettext('Recurse into directories.'),'',false);
			html_checkbox2('times',gettext('Times'),!empty($pconfig['times']) ? true : false,gettext('Preserve modification times.'),'',false);
			html_checkbox2('compress',gettext('Compress'),!empty($pconfig['compress']) ? true : false,gettext('Compress file data during the transfer.'),'',false);
			html_checkbox2('archive',gettext('Archive'),!empty($pconfig['archive']) ? true : false,gettext('Archive mode.'),'',false);
			html_checkbox2('delete',gettext('Delete'),!empty($pconfig['delete']) ? true : false,gettext("Delete files on the receiving side that don't exist on sender."),'',false);
			$l_delalgo = [
				'default' => gettext("Default - Rsync will choose the 'during' algorithm when talking to rsync 3.0.0 or newer and the 'before' algorithm when talking to an older rsync."),
				'before' => gettext('Before - File-deletions will be performed before the transfer starts.'),
				'during' => gettext('During - File-deletions will be done incrementally as the transfer happens.'),
				'delay' => gettext('Delay - File-deletions will be computed during the transfer and will be done after the transfer.'),
				'after' => gettext('After - File-deletions will be done after the transfer has completed.')
			];
			html_radiobox2('delete_algorithm',gettext('Delete Algorithm'),$pconfig['delete_algorithm'],$l_delalgo,'',false);
			html_checkbox2('quiet',gettext('Quiet'),!empty($pconfig['quiet']),gettext('Suppress non-error messages.'),'',false);
			html_checkbox2('perms',gettext('Preserve Permissions'),!empty($pconfig['perms']) ? true : false,gettext('This option causes the receiving rsync to set the destination permissions to be the same as the source permissions.'),'',false);
			html_checkbox2('xattrs',gettext('Preserve Extended attributes'),!empty($pconfig['xattrs']) ? true : false,gettext('This option causes rsync to update the remote extended attributes to be the same as the local ones.'),'',false);
			$helpinghand = '<a href="http://rsync.samba.org/ftp/rsync/rsync.html" target="_blank">' . gettext('Please check the documentation') . '.</a>';
			html_inputbox2('extraoptions',gettext('Extra Options'),!empty($pconfig['extraoptions']) ? $pconfig['extraoptions'] : '',gettext('Extra options to rsync (usually empty).') . ' ' . $helpinghand,false,40);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext('Save') : gtext('Add')?>"/>
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
<?php
		if(isset($uuid) && (false !== $cnid)):
?>
		<input name="Submit" id="execnow" type="submit" class="formbtn" value="<?=gtext('Execute now');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
<?php
		endif;
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
