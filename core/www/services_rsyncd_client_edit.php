<?php
/*
	services_rsyncd_client_edit.php

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
$a_rsyncclient = &array_make_branch($config,'rsync','rsyncclient');
if(isset($uuid) && (false !== ($cnid = array_search_ex($uuid, $a_rsyncclient,'uuid')))):
	$pconfig['enable'] = isset($a_rsyncclient[$cnid]['enable']);
	$pconfig['uuid'] = $a_rsyncclient[$cnid]['uuid'];
	$pconfig['rsyncserverip'] = $a_rsyncclient[$cnid]['rsyncserverip'];
	$pconfig['localshare'] = $a_rsyncclient[$cnid]['localshare'];
	$pconfig['remoteshare'] = $a_rsyncclient[$cnid]['remoteshare'];
	$pconfig['minute'] = $a_rsyncclient[$cnid]['minute'];
	$pconfig['hour'] = $a_rsyncclient[$cnid]['hour'];
	$pconfig['day'] = $a_rsyncclient[$cnid]['day'];
	$pconfig['month'] = $a_rsyncclient[$cnid]['month'];
	$pconfig['weekday'] = $a_rsyncclient[$cnid]['weekday'];
	//$pconfig['sharetosync'] = $a_rsyncclient[$cnid]['sharetosync'];
	$pconfig['all_mins'] = $a_rsyncclient[$cnid]['all_mins'];
	$pconfig['all_hours'] = $a_rsyncclient[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_rsyncclient[$cnid]['all_days'];
	$pconfig['all_months'] = $a_rsyncclient[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_rsyncclient[$cnid]['all_weekdays'];
	$pconfig['description'] = $a_rsyncclient[$cnid]['description'];
	$pconfig['who'] = $a_rsyncclient[$cnid]['who'];
	$pconfig['recursive'] = isset($a_rsyncclient[$cnid]['options']['recursive']);
	$pconfig['nodaemonreq'] = isset($a_rsyncclient[$cnid]['options']['nodaemonreq']);
	$pconfig['times'] = isset($a_rsyncclient[$cnid]['options']['times']);
	$pconfig['compress'] = isset($a_rsyncclient[$cnid]['options']['compress']);
	$pconfig['archive'] = isset($a_rsyncclient[$cnid]['options']['archive']);
	$pconfig['delete'] = isset($a_rsyncclient[$cnid]['options']['delete']);
	$pconfig['delete_algorithm'] = $a_rsyncclient[$cnid]['options']['delete_algorithm'];
	$pconfig['quiet'] = isset($a_rsyncclient[$cnid]['options']['quiet']);
	$pconfig['perms'] = isset($a_rsyncclient[$cnid]['options']['perms']);
	$pconfig['xattrs'] = isset($a_rsyncclient[$cnid]['options']['xattrs']);
	$pconfig['reversedirection'] = isset($a_rsyncclient[$cnid]['options']['reversedirection']);
	$pconfig['extraoptions'] = $a_rsyncclient[$cnid]['options']['extraoptions'];
else:
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['rsyncserverip'] = "";
	$pconfig['localshare'] = "";
	$pconfig['remoteshare'] = "";
	$pconfig['minute'] = [];
	$pconfig['hour'] = [];
	$pconfig['day'] = [];
	$pconfig['month'] = [];
	$pconfig['weekday'] = [];
	//$pconfig['sharetosync'] = "";
	$pconfig['all_mins'] = 0;
	$pconfig['all_hours'] = 0;
	$pconfig['all_days'] = 0;
	$pconfig['all_months'] = 0;
	$pconfig['all_weekdays'] = 0;
	$pconfig['description'] = "";
	$pconfig['who'] = "root";
	$pconfig['recursive'] = true;
	$pconfig['nodaemonreq'] = false;
	$pconfig['times'] = true;
	$pconfig['compress'] = true;
	$pconfig['archive'] = false;
	$pconfig['delete'] = false;
	$pconfig['delete_algorithm'] = "default";
	$pconfig['quiet'] = false;
	$pconfig['perms'] = false;
	$pconfig['xattrs'] = false;
	$pconfig['reversedirection'] = false;
	$pconfig['extraoptions'] = "";
endif;

if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: services_rsyncd_client.php');
		exit;
	endif;
	// Input validation
	$reqdfields = ['localshare','rsyncserverip','remoteshare','who'];
	$reqdfieldsn = [gtext('Local Share (Destination)'),gtext('Remote Rsync Server'),gtext('Remote Module (Source)'),gtext('Who')];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	if(!empty($_POST['Submit']) && gtext('Execute now') !== $_POST['Submit']):
		// Validate synchronization time
		do_input_validate_synctime($_POST,$input_errors);
	endif;
	if(empty($input_errors)):
		$rsyncclient = [];
		$rsyncclient['enable'] = isset($_POST['enable']) ? true : false;
		$rsyncclient['uuid'] = $_POST['uuid'];
		$rsyncclient['rsyncserverip'] = $_POST['rsyncserverip'];
		$rsyncclient['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$rsyncclient['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$rsyncclient['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$rsyncclient['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$rsyncclient['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$rsyncclient['localshare'] = $_POST['localshare'];
		$rsyncclient['remoteshare'] = $_POST['remoteshare'];
		$rsyncclient['all_mins'] = $_POST['all_mins'];
		$rsyncclient['all_hours'] = $_POST['all_hours'];
		$rsyncclient['all_days'] = $_POST['all_days'];
		$rsyncclient['all_months'] = $_POST['all_months'];
		$rsyncclient['all_weekdays'] = $_POST['all_weekdays'];
		$rsyncclient['description'] = $_POST['description'];
		$rsyncclient['who'] = $_POST['who'];
		$rsyncclient['options']['recursive'] = isset($_POST['recursive']) ? true : false;
		$rsyncclient['options']['nodaemonreq'] = isset($_POST['nodaemonreq']) ? true : false;
		$rsyncclient['options']['times'] = isset($_POST['times']) ? true : false;
		$rsyncclient['options']['compress'] = isset($_POST['compress']) ? true : false;
		$rsyncclient['options']['archive'] = isset($_POST['archive']) ? true : false;
		$rsyncclient['options']['delete'] = isset($_POST['delete']) ? true : false;
		$rsyncclient['options']['delete_algorithm'] = $_POST['delete_algorithm'];
		$rsyncclient['options']['quiet'] = isset($_POST['quiet']) ? true : false;
		$rsyncclient['options']['perms'] = isset($_POST['perms']) ? true : false;
		$rsyncclient['options']['xattrs'] = isset($_POST['xattrs']) ? true : false;
		$rsyncclient['options']['reversedirection'] = isset($_POST['reversedirection']) ? true : false;
		$rsyncclient['options']['extraoptions'] = $_POST['extraoptions'];
		if(isset($uuid) && (FALSE !== $cnid)):
			$a_rsyncclient[$cnid] = $rsyncclient;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		else:
			$a_rsyncclient[] = $rsyncclient;
			$mode = UPDATENOTIFY_MODE_NEW;
		endif;
		updatenotify_set("rsyncclient", $mode, $rsyncclient['uuid']);
		write_config();
		if(!empty($_POST['Submit']) && stristr($_POST['Submit'], gtext("Execute now"))):
			$retval = 0;
			// Update scripts and execute it.
			config_lock();
			$retval |= rc_exec_service("rsync_client");
			$retval |= rc_update_service("cron");
			config_unlock();
			if($retval == 0):
				updatenotify_clear('rsyncclient', $rsyncclient['uuid']);
			endif;
			$retval |= rc_exec_script_async("su -m {$rsyncclient['who']} -c '/bin/sh /var/run/rsync_client_{$rsyncclient['uuid']}.sh'");
			$savemsg = get_std_save_message($retval);
		else:
			header('Location: services_rsyncd_client.php');
			exit;
		endif;
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('Rsync'),gtext('Client'),isset($uuid) ? gtext('Edit') : gtext('Add')];
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
		<li class="tabinact"><a href="services_rsyncd.php"><span><?=gtext("Server");?></span></a></li>
		<li class="tabact"><a href="services_rsyncd_client.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Client");?></span></a></li>
		<li class="tabinact"><a href="services_rsyncd_local.php"><span><?=gtext("Local");?></span></a></li>
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
				<td class="celltagreq"><?=gtext("Local Share (Destination)");?></td>
				<td class="celldatareq">
					<input name="localshare" type="text" class="formfld" id="localshare" size="60" value="<?=htmlspecialchars($pconfig['localshare']);?>" />
					<input name="browse" type="button" class="formbtn" id="Browse" onclick='ifield = form.localshare; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield; window.slash_localshare = 1;' value="..." /><br />
					<span class="vexpl"><?=gtext("Path to be shared to be synchronized.");?></span>
			  </td>
			</tr>
<?php
			html_inputbox2('rsyncserverip',gettext('Remote Rsync Server'),htmlspecialchars($pconfig['rsyncserverip']),gettext("IP or FQDN address of remote Rsync server."),true,20);
			html_inputbox2('remoteshare',gettext('Remote Module (Source)'),htmlspecialchars($pconfig['remoteshare']),'',true,20);
			$a_user = [];
			foreach(system_get_user_list() as $userk => $userv):
				$a_user[$userk] = htmlspecialchars($userk);
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
			html_checkbox2('nodaemonreq',gettext('Remote Rsync Daemon'),!empty($pconfig['nodaemonreq']) ? true : false,gettext('Run without requiring remote rsync daemon. (Disabled by default)'),'',false);
			html_checkbox2('times',gettext('Times'),!empty($pconfig['times']) ? true : false,gettext('Preserve modification times.'),'',false);
			html_checkbox2('compress',gettext('Compress'),!empty($pconfig['compress']) ? true : false,gettext('Compress file data during the transfer.'),'',false);
			html_checkbox2('archive',gettext('Archive'),!empty($pconfig['archive']) ? true : false,gettext('Archive mode.'),'',false);
			html_checkbox2('delete',gettext('Delete'),!empty($pconfig['delete']) ? true : false,gettext("Delete files on the receiving side that don't exist on sender."),'',false);
			$l_delalgo = [
				'default' => gettext("Default - Rsync will choose the 'during' algorithm when talking to rsync 3.0.0 or newer, and the 'before' algorithm when talking to an older rsync."),
				'before' => gettext('Before - File-deletions will be done before the transfer starts.'),
				'during' => gettext('During - File-deletions will be done incrementally as the transfer happens.'),
				'delay' => gettext('Delay - File-deletions will be computed during the transfer, and then removed after the transfer completes.'),
				'after' => gettext('After - File-deletions will be done after the transfer has completed.')
			];
			html_radiobox2('delete_algorithm',gettext('Delete Algorithm'),$pconfig['delete_algorithm'],$l_delalgo,'',false);
			html_checkbox2('quiet',gettext('Quiet'),!empty($pconfig['quiet']),gettext('Suppress non-error messages.'),'',false);
			html_checkbox2('perms', gettext('Preserve Permissions'),!empty($pconfig['perms']) ? true : false, gettext('This option causes the receiving rsync to set the destination permissions to be the same as the source permissions.'),'',false);
			html_checkbox2('xattrs',gettext('Preserve Extended Attributes'),!empty($pconfig['xattrs']) ? true : false, gettext('This option causes rsync to update the remote extended attributes to be the same as the local ones.'),'',false);
			html_checkbox2('reversedirection',gettext('Reverse Direction'),!empty($pconfig['reversedirection']) ? true : false, gettext('This option causes rsync to copy the local data to the remote server.'),'',false);
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
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>"/>
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
