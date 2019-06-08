<?php
/*
	system_rc_sort.php

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
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: system_rc.php';
$sphere_notifier = 'rc';
$sphere_notifier_processor = 'rc_process_updatenotification';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];

$gt_record_add = gtext('Add command');
$gt_record_del = gtext('Command is marked for removal');
$gt_record_dow = gtext('Move down');
$gt_record_inf = gtext('Information');
$gt_record_loc = gtext('Command is protected');
$gt_record_mai = gtext('Maintenance');
$gt_record_mod = gtext('Edit command');
$gt_record_unl = gtext('Command is unlocked');
$gt_record_up = gtext('Move up');
$gt_selection_delete = gtext('Delete Selected Commands');
$gt_selection_delete_confirm = gtext('Do you want to delete selected commands?');
$img_path = [
	'add' => 'images/add.png',
	'del' => 'images/delete.png',
	'dow' => 'images/down.png',
	'inf' => 'images/info.png',
	'loc' => 'images/locked.png',
	'mai' => 'images/maintain.png',
	'mod' => 'images/edit.png',
	'unl' => 'images/unlocked.png',
	'up' => 'images/up.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png'
];
// sunrise: verify if setting exists, otherwise run init tasks
$sphere_array = &array_make_branch($config,'rc','param');
if($_POST):
	if(isset($_POST['Submit'])):
		if($_POST[$checkbox_member_name] && is_array($_POST[$checkbox_member_name])):
			$a_param =[];
			foreach($_POST[$checkbox_member_name] as $r_member):
				if(is_string($r_member)):
					if(false !== ($index = array_search_ex($r_member, $sphere_array, 'uuid'))):
						$a_param[] = $sphere_array[$index];
					endif;
				endif;
			endforeach;
			$sphere_array = $a_param;
			write_config();
		endif;
		header($sphere_header_parent);
		exit;
	endif;
endif;
$l_type = [
	'1' => gtext('PreInit'),
	'2' => gtext('PostInit'),
	'3' => gtext('Shutdown'),
	'971' => gtext('Post Upgrade')
];
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Command Scripts'),gtext('Sort')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	// Init move row capability
	$('#system_rc_list img.move').click(function() {
		var row = $(this).closest('table').closest('tr');
		if ($(this).hasClass('up')) row.prev().before(row);
		if ($(this).hasClass('down')) row.next().after(row);
	});
});
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
			ins_tabnav_record('system_email.php',gettext('Email'))->
			ins_tabnav_record('system_email_reports.php',gettext('Email Reports'))->
			ins_tabnav_record('system_monitoring.php',gettext('Monitoring'))->
			ins_tabnav_record('system_swap.php',gettext('Swap'))->
			ins_tabnav_record('system_rc.php',gettext('Command Scripts'),gettext('Reload page'),true)->
			ins_tabnav_record('system_cron.php',gettext('Cron'))->
			ins_tabnav_record('system_loaderconf.php',gettext('loader.conf'))->
			ins_tabnav_record('system_rcconf.php',gettext('rc.conf'))->
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'))->
			ins_tabnav_record('system_syslogconf.php',gettext('syslog.conf'));
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(updatenotify_exists($sphere_notifier)):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:7%">
			<col style="width:18%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Reorder Commands'), 7);
?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Command');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhell"><?=gtext('Comment');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody id="system_rc_list">
<?php
			foreach($sphere_array as $sphere_record):
				$notificationmode = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$enabled = isset($sphere_record['enable']);
				$notprotected = !isset($sphere_record['protected']);
				$gt_type = array_key_exists($sphere_record['typeid'],$l_type) ? $l_type[$sphere_record['typeid']] : gtext('Unknown');
?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
						<input type="hidden" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['name']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['value']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>">
<?php
						if ($enabled):
?>
							<a title="<?=gtext('Enabled');?>"><center><img src="<?=$img_path['ena'];?>"/></center></a>
<?php
						else:
?>
							<a title="<?=gtext('Disabled');?>"><center><img src="<?=$img_path['dis'];?>"/></center></a>
<?php
						endif;
?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['comment']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$gt_type;?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
								<img src="<?=$img_path['up'];?>" title="<?=$gt_record_up;?>" alt="<?=$gt_record_up;?>" class="move up"/>
								<img src="<?=$img_path['dow'];?>" title="<?=$gt_record_dow;?>" alt="<?=$gt_record_dow;?>" class="move down"/>
							</td>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" id="reorder_rows" type="submit" class="formbtn" value="<?=gtext('Reorder Commands');?>"/>
	</div>
	<div id="remarks">
<?php
		html_remark('note', gtext('Note'), gtext('These commands will be executed pre or post system initialization (booting) or before system shutdown.'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
