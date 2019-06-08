<?php
/*
	interfaces_lagg.php

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

function lagg_inuse($ifn) {
	global $config, $g;
	if(isset($config['interfaces']['lan']['if']) && ($config['interfaces']['lan']['if'] === $ifn)):
		return true;
	endif;
	if(isset($config['interfaces']['wan']['if']) && ($config['interfaces']['wan']['if'] === $ifn)):
		return true;
	endif;
	for($i = 1;isset($config['interfaces']['opt' . $i]);$i++):
		if(isset($config['interfaces']['opt' . $i]['if']) && ($config['interfaces']['opt' . $i]['if'] === $ifn)):
			return true;
		endif;
	endfor;
	return false;
}
function interfaces_lagg_get_sphere() {
	global $config;
	$sphere = new co_sphere_grid('interfaces_lagg','php');
	$sphere->get_modify()->set_basename($sphere->get_basename() . '_edit');
	$sphere->set_row_identifier('uuid');
	$sphere->set_enadis(false);
	$sphere->set_lock(false);
	$sphere->
		setmsg_sym_add(gettext('Add LAGG'))->
		setmsg_sym_mod(gettext('Edit LAGG'))->
		setmsg_sym_del(gettext('LAGG is marked for deletion'))->
		setmsg_sym_loc(gettext('LAGG is protected'))->
		setmsg_sym_unl(gettext('LAGG is unlocked'))->
		setmsg_cbm_delete(gettext('Delete Selected LAGGs'))->
		setmsg_cbm_delete_confirm(gettext('Do you want to delete selected LAGGs?'));
	$sphere->grid = &array_make_branch($config,'vinterfaces','lagg');
	return $sphere;
}
$sphere = &interfaces_lagg_get_sphere();
array_sort_key($sphere->grid,'if');
if($_POST):
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case $sphere->get_cbm_button_val_delete():
				$sphere->cbm_grid = $_POST[$sphere->get_cbm_name()] ?? [];
				$updateconfig = false;
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->get_row_identifier()))):
						$sphere->row = $sphere->grid[$sphere->record_id];
						//	Check if interface is still in use.
						if(lagg_inuse($sphere->row['if'])):
							$input_errors[] = htmlspecialchars($sphere->row['if']) . ': ' . gtext('LAGG cannot be deleted because it is still being used as an interface.');
						else:
							$cmd = sprintf('/usr/local/sbin/rconf attribute remove %s',escapeshellarg('ifconfig_' . $sphere->row['if']));
							mwexec($cmd);
							unset($sphere->grid[$sphere->row_id]);
							$updateconfig = true;
						endif;
					endif;
				endforeach;
				if($updateconfig):
					write_config();
					touch($d_sysrebootreqd_path);
					header($sphere->get_location());
					exit;
				endif;
				break;
		endswitch;
	endif;
endif;
$l_lagg_protocol = [
	'failover' => gtext('Failover'),
	'lacp' => gtext('LACP (Link Aggregation Control Protocol)'),
	'loadbalance' => gtext('Loadbalance'),
	'roundrobin' => gtext('Roundrobin'),
	'none' => gtext('None')
];
$ports_available = false;
$a_available_interfaces = get_interface_list(); // get all known interfaces from system
foreach($sphere->grid as $row): // test all lagg
	if(empty($a_available_interfaces)): // don't continue if list of remaining interfaces is empty
		break; // break foreach
	endif;
	if(!empty($row['laggport'])):
		$a_available_interfaces = array_diff_key($a_available_interfaces,array_flip($row['laggport']));
	endif;
endforeach;
foreach($a_available_interfaces as $interface_name => $interface_detail):
	if(preg_match('/^lagg[\d]+$/i',$interface_name)): // skip lagg interfaces
		continue;
	endif;
	$ports_available = true;
	break;
endforeach;
$pgtitle = [gtext('Network'),gtext('Interface Management'),gtext('LAGG')];
include 'fbegin.inc';
echo $sphere->doj();
//	add tab navigation
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('interfaces_assign.php',gettext('Management'))->
			ins_tabnav_record('interfaces_wlan.php',gettext('WLAN'))->
			ins_tabnav_record('interfaces_vlan.php',gettext('VLAN'))->
			ins_tabnav_record('interfaces_lagg.php',gettext('LAGG'),gettext('Reload page'),true)->
			ins_tabnav_record('interfaces_bridge.php',gettext('Bridge'))->
			ins_tabnav_record('interfaces_carp.php',gettext('CARP'));
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:25%">
			<col style="width:25%">
			<col style="width:20%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'),6);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Virtual Interface');?></th>
				<th class="lhell"><?=gtext('Protocol');?></th>
				<th class="lhell"><?=gtext('Ports');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			$notificationmode = false;
			$notdirty = true;
			foreach($sphere->grid as $sphere->row):
				$enabled = $sphere->is_enadis_enabled() ? isset($sphere->row['enable']) : true;
				$notprotected = $sphere->is_lock_enabled() ? !isset($sphere->row['protected']) : true;
				if(isset($sphere->row['laggproto']) && is_string($sphere->row['laggproto']) && array_key_exists($sphere->row['laggproto'],$l_lagg_protocol)):
					$lagg_protocol = $l_lagg_protocol[$sphere->row['laggproto']];
				else:
					$lagg_protocol = gtext('Unknown Aggregation Protocol');
				endif;
				$class_lcelc = $enabled ? 'lcelc' : 'lcelcd';
				$class_lcell = $enabled ? 'lcell' : 'lcelld';
?>
				<tr>
					<td class="<?=$class_lcelc;?>">
<?php
						if($notdirty && $notprotected && !lagg_inuse($sphere->row['if'])):
							echo $sphere->html_checkbox_cbm(false);
						else:
							echo $sphere->html_checkbox_cbm(true);
						endif;
?>
					</td>
					<td class="<?=$class_lcell;?>"><?=htmlspecialchars($sphere->row['if']);?></td>
					<td class="<?=$class_lcell;?>"><?=htmlspecialchars($lagg_protocol);?></td>
					<td class="<?=$class_lcell;?>"><?=htmlspecialchars(implode(' ', $sphere->row['laggport']));?></td>
					<td class="<?=$class_lcell;?>"><?=htmlspecialchars($sphere->row['desc']);?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><colgroup><col style="width:33%"><col style="width:34%"><col style="width:33%"></colgroup><tbody><tr>
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
			if($ports_available):
				echo $sphere->html_footer_add(6);
			endif;
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
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
