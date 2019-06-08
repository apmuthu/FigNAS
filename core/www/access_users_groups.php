<?php
/*
	access_users_groups.php

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

function userdb_group_process_updatenotification($mode,$data) {
	global $config;
	$retval = 0;
	$sphere = &access_users_groups_get_sphere();
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
		case UPDATENOTIFY_MODE_DIRTY:
			if(false !== ($sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier()))):
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
function access_users_groups_get_sphere() {
	global $config;
	$sphere = new co_sphere_grid('access_users_groups','php');
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	$sphere->set_notifier('userdb_group');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false); // internally managed
	$sphere->set_lock(true); // internally managed
	$sphere->
		setmsg_sym_add(gettext('Add Group'))->
		setmsg_sym_mod(gettext('Edit Group'))->
		setmsg_sym_del(gettext('Group is marked for deletion'))->
		setmsg_sym_loc(gettext('Group is protected'))->
		setmsg_sym_unl(gettext('Group is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected Groups'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected groups?'));
	$sphere->grid = &array_make_branch($config,'access','group');
	return $sphere;
}
$sphere = &access_users_groups_get_sphere();
//	settings for config['access']
$access_settings = &array_make_branch($config,'access','settings');
$showsystemgroups = !isset($access_settings['hidesystemgroups']);
if($_POST):
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
			config_lock();
			$retval |= rc_exec_service('userdb');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete($sphere->get_notifier());
		endif;
	endif;
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case $sphere->get_cbm_button_val_delete():
				$sphere->cbm_grid = $_POST[$sphere->get_cbm_name()] ?? [];
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($index_uuid = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->get_row_identifier()))):
						$mode_updatenotify = updatenotify_get_mode($sphere->get_notifier(),$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
						switch ($mode_updatenotify):
							case UPDATENOTIFY_MODE_NEW:
								updatenotify_clear($sphere->get_notifier(),$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY_CONFIG,$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_MODIFIED:
								updatenotify_clear($sphere->get_notifier(),$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_UNKNOWN:
								updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$index_uuid][$sphere->get_row_identifier()]);
								break;
						endswitch;
					endif;
				endforeach;
				header($sphere->get_location());
				exit;
				break;
			case 'show':
				if(!$showsystemgroups):
					$access_settings['hidesystemgroups'] = false;
					write_config();
					header($sphere->get_location());
					exit;
				endif;
				break;
			case 'hide':
				if($showsystemgroups):
					$access_settings['hidesystemgroups'] = true;
					write_config();
					header($sphere->get_location());
					exit;
				endif;
				break;
		endswitch;
	endif;
endif;
$a_group = system_get_group_list();
/*
 *	a_group[groupname] => groupid
 *	sphere->grid[] => [name,id,desc,uuid]
 */
$l_group = [];
if($showsystemgroups):
	if(is_array($a_group)):
		$helpinghand = gtext('System');
		foreach($a_group as $key => $val):
			$l_group[$key] = ['name' => $key,'id' => $val,'desc' => $helpinghand,'uuid' => uuid(),'protected' => true,'enable' => false];
		endforeach;
	endif;
endif;
foreach($sphere->grid as $sphere->row):
	$key = $sphere->row['name'];
	if(preg_match('/\S/',$key)):
		if(!isset($l_group[$key])): // add or update group
			$l_group[$key] = [];
			$l_group[$key]['name'] = $key;
			$l_group[$key]['id'] = $sphere->row['id'];
		endif;
		$l_group[$key]['desc'] = $sphere->row['desc'] ?? '';
		$l_group[$key]['uuid'] = $sphere->row['uuid'];
		$l_group[$key]['protected'] = false;
		$l_group[$key]['enable'] = true;
	endif;
endforeach;
array_sort_key($l_group,'name');

$pgtitle = [gtext('Access'), gtext('Groups')];
include 'fbegin.inc';
echo $sphere->doj();
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('access_users.php',gettext('Users'))->
			ins_tabnav_record('access_publickey.php',gettext('Public Keys'))->
			ins_tabnav_record('access_users_groups.php',gettext('Groups'),gettext('Reload page'),true);
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" id="iform" name="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if($savemsg):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists($sphere->get_notifier())):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:25%">
			<col style="width:10%">
			<col style="width:50%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'),5);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Group');?></th>
				<th class="lhell"><?=gtext('GID');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($l_group as $sphere->row):
				$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->row[$sphere->get_row_identifier()]);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				// $enabled = $sphere->is_enadis_enabled() ? isset($sphere->row['enable']) : true;
				$enabled = $sphere->row['enable'];
				$notprotected = $sphere->is_lock_enabled() ? !$sphere->row['protected'] : true;
?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
<?php
						if($notdirty && $notprotected):
							echo $sphere->html_checkbox_cbm(false);
						else:
							echo $sphere->html_checkbox_cbm(true);
						endif;
?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['name']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['id']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['desc']);?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
<?php
							echo $sphere->html_toolbox($notprotected,$notdirty);
?>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
<?php
			echo $sphere->html_footer_add(5);
?>
		</tfoot>
	</table>
	<div id="submit">
<?php
		if($sphere->is_enadis_enabled()):
			if($sphere->toggle()):
				echo $sphere->html_button_toggle_rows();
			else:
				echo $sphere->html_button_enable_rows();
				echo $sphere->html_button_disable_rows();
			endif;
		endif;
		echo $sphere->html_button_delete_rows();
		if($showsystemgroups):
			echo html_button('hide',gettext('Hide System Groups'));
		else:
			echo html_button('show',gettext('Show System Groups'));
		endif;
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
