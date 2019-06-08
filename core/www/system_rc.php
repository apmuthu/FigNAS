<?php
/*
	system_rc.php

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
$sphere_scriptname_child = 'system_rc_edit.php';
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = $sphere_header;
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
$gt_selection_toggle = gtext('Toggle Selected Commands');
$gt_selection_toggle_confirm = gtext('Do you want to toggle selected commands?');
$gt_selection_enable = gtext('Enable Selected Commands');
$gt_selection_enable_confirm = gtext('Do you want to enable selected commands?');
$gt_selection_disable = gtext('Disable Selected Commands');
$gt_selection_disable_confirm = gtext('Do you want to disable selected commands?');
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
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		$retval |= updatenotify_process($sphere_notifier, $sphere_notifier_processor);
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete($sphere_notifier);
		endif;
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['enable_selected_rows']) && $_POST['enable_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfigfile = false;
		foreach($checkbox_member_array as $checkbox_member_record):
			if(false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))):
				if(!(isset($sphere_array[$index]['enable']))):
					$sphere_array[$index]['enable'] = true;
					$updateconfigfile = true;
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
					if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
					endif;
				endif;
			endif;
		endforeach;
		if($updateconfigfile):
			write_config();
			$updateconfigfile = false;
		endif;
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['disable_selected_rows']) && $_POST['disable_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfigfile = false;
		foreach($checkbox_member_array as $checkbox_member_record):
			if(false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))):
				if(isset($sphere_array[$index]['enable'])):
					unset($sphere_array[$index]['enable']);
					$updateconfigfile = true;
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
					if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
					endif;
				endif;
			endif;
		endforeach;
		if($updateconfigfile):
			write_config();
			$updateconfigfile = false;
		endif;
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['toggle_selected_rows']) && $_POST['toggle_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfigfile = false;
		foreach($checkbox_member_array as $checkbox_member_record):
			if(false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))):
				if(isset($sphere_array[$index]['enable'])):
					unset($sphere_array[$index]['enable']);
				else:
					$sphere_array[$index]['enable'] = true;
				endif;
				$updateconfigfile = true;
				$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
				if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
				endif;
			endif;
		endforeach;
		if($updateconfigfile):
			write_config();
			$updateconfigfile = false;
		endif;
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['delete_selected_rows']) && $_POST['delete_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		foreach($checkbox_member_array as $checkbox_member_record):
			if(false !== ($index = array_search_ex($checkbox_member_record,$sphere_array,'uuid'))):
				$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
				switch($mode_updatenotify):
					case UPDATENOTIFY_MODE_NEW:
						updatenotify_clear($sphere_notifier, $sphere_array[$index]['uuid']);
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY_CONFIG, $sphere_array[$index]['uuid']);
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						updatenotify_clear($sphere_notifier, $sphere_array[$index]['uuid']);
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $sphere_array[$index]['uuid']);
						break;
					case UPDATENOTIFY_MODE_UNKNOWN:
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $sphere_array[$index]['uuid']);
						break;
				endswitch;
			endif;
		endforeach;
		header($sphere_header);
		exit;
	endif;
endif;
function rc_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			write_config();
			break;
		case UPDATENOTIFY_MODE_DIRTY:
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			array_make_branch($config,'rc','param');
			if(false !== ($index = array_search_ex($data,$config['rc']['param'],'uuid'))):
				unset($config['rc']['param'][$index]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
$l_type = [
	'1' => gtext('PreInit'),
	'2' => gtext('PostInit'),
	'3' => gtext('Shutdown'),
	'971' => gtext('Post Upgrade')
];
$enabletogglemode = isset($config['system']['enabletogglemode']);
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Command Scripts')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init action buttons.?>
<?php if ($enabletogglemode):?>
	$("#toggle_selected_rows").click(function () {
		return confirm('<?=$gt_selection_toggle_confirm;?>');
	});
<?php else:?>
	$("#enable_selected_rows").click(function () {
		return confirm('<?=$gt_selection_enable_confirm;?>');
	});
	$("#disable_selected_rows").click(function () {
		return confirm('<?=$gt_selection_disable_confirm;?>');
	});
<?php endif;?>
	$("#delete_selected_rows").click(function () {
		return confirm('<?=$gt_selection_delete_confirm;?>');
	});
<?php // Disable action buttons.?>
	disableactionbuttons(true);
	// Init toggle checkbox
	$("#togglemembers").click(function() {
		togglecheckboxesbyname(this, "<?=$checkbox_member_name;?>[]");
	});
<?php // Init member checkboxes.?>
	$("input[name='<?=$checkbox_member_name;?>[]']").click(function() {
		controlactionbuttons(this, '<?=$checkbox_member_name;?>[]');
	});
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function disableactionbuttons(ab_disable) {
	var ab_element;
<?php if ($enabletogglemode):?>
	ab_element = document.getElementById('toggle_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
<?php else:?>
	ab_element = document.getElementById('enable_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
	ab_element = document.getElementById('disable_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
<?php endif;?>
	ab_element = document.getElementById('delete_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
}
function togglecheckboxesbyname(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
				if (a_trigger[i].checked) {
					ab_disable = false;
				}
			}
		}
	}
	if (ego.type == 'checkbox') { ego.checked = false; }
	disableactionbuttons(ab_disable);
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (a_trigger[i].checked) {
				ab_disable = false;
				break;
			}
		}
	}
	disableactionbuttons(ab_disable);
}
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
			html_titleline2(gettext('Overview'), 7);
?>
			<tr>
				<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Command');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhell"><?=gtext('Comment');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
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
<?php
						if($notdirty && $notprotected):
?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
<?php
						else:
?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" disabled="disabled"/>
<?php
						endif;
?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['name']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['value']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>">
<?php
						if($enabled):
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
<?php
								if($notdirty && $notprotected):
?>
									<a href="<?=$sphere_scriptname_child;?>?uuid=<?=$sphere_record['uuid'];?>"><img src="<?=$img_path['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>" /></a>
<?php
								else:
									if($notprotected):
?>
										<img src="<?=$img_path['del'];?>" title="<?=$gt_record_del;?>" alt="<?=$gt_record_del;?>"/>
<?php
									else:
?>
										<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>"/>
<?php
									endif;
								endif;
?>
							</td>
<?php
							if(updatenotify_exists($sphere_notifier)):
							else:
?>
								<td><a href="system_rc_sort.php"><img src="<?=$img_path['mai'];?>" title="<?=$gt_record_mai;?>" alt="<?=$gt_record_mai;?>" /></a></td>
<?php
							endif;
?>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="6"></th>
				<th class="lceadd"><a href="<?=$sphere_scriptname_child;?>"><img src="<?=$img_path['add'];?>" title="<?=$gt_record_add;?>" alt="<?=$gt_record_add;?>"/></a></th>
			</tr>
		</tfoot>
	</table>
	<div id="submit">
<?php
		if ($enabletogglemode):
?>
			<input type="submit" class="formbtn" name="toggle_selected_rows" id="toggle_selected_rows" value="<?=$gt_selection_toggle;?>"/>
<?php
		else:
?>
			<input type="submit" class="formbtn" name="enable_selected_rows" id="enable_selected_rows" value="<?=$gt_selection_enable;?>"/>
			<input type="submit" class="formbtn" name="disable_selected_rows" id="disable_selected_rows" value="<?=$gt_selection_disable;?>"/>
<?php
		endif;
?>
		<input name="delete_selected_rows" id="delete_selected_rows" type="submit" class="formbtn" value="<?=$gt_selection_delete;?>"/>
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
