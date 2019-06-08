<?php
/*
	interfaces_lagg_edit.php

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

function interfaces_lagg_edit_get_sphere() {
	global $config;
	$sphere = new co_sphere_row('interfaces_lagg_edit','php');
	$sphere->set_row_identifier('uuid');
	$sphere->get_parent()->set_basename('interfaces_lagg','php');
	$sphere->row_default = [
		'enable' => true,
		'protected' => false,
		'laggproto' => 'failover',
		'laggport' => [],
		'desc' => ''
	];
	$sphere->grid = &array_make_branch($config,'vinterfaces','lagg');
	if(!empty($sphere->grid)):
		array_sort_key($sphere->grid,'if');
	endif;
	return $sphere;
}
$sphere = &interfaces_lagg_edit_get_sphere();
$l_lagg_protocol = [
	'failover' => gettext('Failover'),
	'lacp' => gettext('LACP (Link Aggregation Control Protocol)'),
	'loadbalance' => gettext('Loadbalance'),
	'roundrobin' => gettext('Roundrobin'),
	'none' => gettext('None')
];
$input_errors = [];
$hide_button_save = false;
if(false !== ($action = filter_input(INPUT_POST,'submit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]))):
	switch($action):
		case 'cancel':
			header($sphere->get_parent()->get_location());
			exit;
			break;
		case 'clone':
			$sphere->row[$sphere->get_row_identifier()] = uuid();
			$mode_page = PAGE_MODE_CLONE;
			break;
		case 'save':
			$id = filter_input(INPUT_POST,$sphere->get_row_identifier(),FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
			if(false === $id):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			if(!is_uuid_v4($id)):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			$sphere->row[$sphere->get_row_identifier()] = $id;
			$mode_page = PAGE_MODE_POST;
			break;
		default:
			header($sphere->get_parent()->get_location());
			exit;
			break;
	endswitch;
elseif(false !== ($action = filter_input(INPUT_GET,'submit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]))):
	switch($action):
		case 'add':
			$sphere->row[$sphere->get_row_identifier()] = uuid();
			$mode_page = PAGE_MODE_ADD;
			break;
		case 'edit':
			$id = filter_input(INPUT_GET,$sphere->get_row_identifier(),FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
			if(false === $id):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			if(!is_uuid_v4($id)):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			$sphere->row[$sphere->get_row_identifier()] = $id;
			$mode_page = PAGE_MODE_EDIT;
			break;
		default:
			header($sphere->get_parent()->get_location());
			exit;
			break;
	endswitch;
else:
	header($sphere->get_parent()->get_location());
	exit;
endif;
$sphere->row_id = array_search_ex($sphere->row[$sphere->get_row_identifier()],$sphere->grid,$sphere->get_row_identifier());
$isrecordnew = (false === $sphere->row_id);
switch($mode_page):
	case PAGE_MODE_ADD:
		if(!$isrecordnew): // add cannot have an uuid in config
			header($sphere->get_parent()->get_location());
			exit;
		endif;	
		$sphere->row['enable'] = $sphere->row_default['enable'];
		$sphere->row['protected'] = $sphere->row_default['protected'];
		$interface_id = 0;
		$interface_format = 'lagg%d';
		do {
			$interface_name = sprintf($interface_format,$interface_id);
			$interface_id++;
		} while(false !== array_search_ex($interface_name,$sphere->grid,'if'));
		$sphere->row['if'] = $interface_name;
		$sphere->row['laggproto'] = $sphere->row_default['laggproto'];
		$sphere->row['laggport'] = $sphere->row_default['laggport'];
		$sphere->row['desc'] = $sphere->row_default['desc'];
		break;
	case PAGE_MODE_CLONE: // clone cannot have an uuid in config
		if(!$isrecordnew):
			header($sphere->get_parent()->get_location());
			exit;
		endif;
		$sphere->row['enable'] = filter_input(INPUT_POST,'enable',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['protected'] = filter_input(INPUT_POST,'protected',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$interface_id = 0;
		$interface_format = 'lagg%d';
		do {
			$interface_name = sprintf($interface_format,$interface_id);
			$interface_id++;
		} while(false !== array_search_ex($interface_name,$sphere->grid,'if'));
		$sphere->row['if'] = $interface_name;
		$sphere->row['laggproto'] = filter_input(INPUT_POST,'laggproto',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['laggproto']]]);
		$sphere->row['laggport'] = filter_input(INPUT_POST,'laggport',FILTER_UNSAFE_RAW,['flags' => FILTER_FORCE_ARRAY,'options' => ['default' => $sphere->row_default['laggport']]]);
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['desc']]]);
		// adjust page mode information
		$mode_page = PAGE_MODE_ADD;
		break;
	case PAGE_MODE_EDIT:
		if($isrecordnew): // edit relies on an existing record
			header($sphere->get_parent()->get_location());
			exit;
		endif;
		$sphere->row['enable'] = isset($sphere->grid[$sphere->row_id]['enable']) && (is_bool($sphere->grid[$sphere->row_id]['enable']) ? $sphere->grid[$sphere->row_id]['enable'] : true);
		$sphere->row['protected'] = isset($sphere->grid[$sphere->row_id]['protected']) && (is_bool($sphere->grid[$sphere->row_id]['protected']) ? $sphere->grid[$sphere->row_id]['protected'] : true);
		$sphere->row['if'] = filter_var($sphere->grid[$sphere->row_id]['if'],FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		
		$sphere->row['laggproto'] = filter_var($sphere->grid[$sphere->row_id]['laggproto'],FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['laggport'] = filter_var($sphere->grid[$sphere->row_id]['laggport'],FILTER_UNSAFE_RAW,['flags' => FILTER_FORCE_ARRAY,'options' => ['default' => []]]);
		$sphere->row['desc'] = filter_var($sphere->grid[$sphere->row_id]['desc'],FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		break;
	case PAGE_MODE_POST:
		$sphere->row['enable'] = filter_input(INPUT_POST,'enable',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['protected'] = filter_input(INPUT_POST,'protected',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['if'] = filter_input(INPUT_POST,'if',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['laggproto'] = filter_input(INPUT_POST,'laggproto',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['laggport'] = filter_input(INPUT_POST,'laggport',FILTER_UNSAFE_RAW,['flags' => FILTER_FORCE_ARRAY,'options' => ['default' => []]]);
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$reqdfields = ['laggproto'];
		$reqdfieldsn = [gtext('Aggregation Protocol')];
		$reqdfieldst = ['string'];
		do_input_validation($sphere->row,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($sphere->row,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if(empty($input_errors)):
			if(!preg_match('/^lagg[\d]+$/i',$sphere->row['if'])):
				$input_errors[] = gtext('The name of the interface is wrong.');
				$hide_button_save = true;
			endif;
		endif;
		if(empty($input_errors)):
			if(!array_key_exists($sphere->row['laggproto'],$l_lagg_protocol)):
				$input_errors[] = gtext('The LAGG protocol is invalid.');
			endif;
		endif;
		if(empty($input_errors)):
			if(count($sphere->row['laggport']) < 1):
				$input_errors[] = gtext('At least one interface must be selected.');
			endif;
		endif;
		if(empty($input_errors)):
			if($isrecordnew):
				if(false !== array_search_ex($sphere->row['if'],$sphere->grid,'if')):
					$input_errors[] = gtext('The LAGG interface cannot be added because the interface name already exists.');
					$hide_button_save = true;
				endif;
			endif;
		endif;
		if(empty($input_errors)):
			$sphere->upsert();
			write_config();
			touch($d_sysrebootreqd_path);
			header($sphere->get_parent()->get_location());
			exit;
		endif;
		break;
endswitch;
$l_selectable_ports = [];
$l_available_ports = [];
$a_remaining_interfaces = get_interface_list(); // get all known interfaces from system
$a_available_interfaces = $a_remaining_interfaces;
foreach($sphere->grid as $row): // test all lagg
	if(empty($a_remaining_interfaces)): // don't continue if list of remaining interfaces is empty
		break; // break foreach
	endif;
	if(!empty($row['laggport'])):
		if($row['if'] !== $sphere->row['if']): // remove interfaces used by foreign lagg interfaces
			$a_remaining_interfaces = array_diff_key($a_remaining_interfaces,array_flip($row['laggport']));
		endif;
		if(!empty($a_available_interfaces)): // remove from available interfaces
			$a_available_interfaces = array_diff_key($a_available_interfaces,array_flip($row['laggport']));
		endif;
	endif;
endforeach;
foreach($a_remaining_interfaces as $interface_name => $interface_detail):
	if(preg_match('/^lagg[\d]+$/i',$interface_name)): // skip lagg interfaces
		continue;
	endif;
	$l_selectable_ports[$interface_name] = htmlspecialchars(sprintf('%s (%s)',$interface_name,$interface_detail['mac']));
endforeach;
foreach($a_available_interfaces as $interface_name => $interface_detail):
	if(preg_match('/^lagg[\d]+$/i',$interface_name)): // skip lagg interfaces
		continue;
	endif;
	$l_available_ports[$interface_name] = htmlspecialchars(sprintf('%s (%s)',$interface_name,$interface_detail['mac']));
endforeach;
$pgtitle = [gtext('Network'),gtext('Interface Management'),gtext('LAGG'),$isrecordnew ? gtext('Add') : gtext('Modify')];
include 'fbegin.inc';
$sphere->doj();
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
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('LAGG Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('if',gettext('Interface'),$sphere->row['if'],'',true,5,true);
			html_radiobox2('laggproto',gettext('Aggregation Protocol'),$sphere->row['laggproto'],$l_lagg_protocol,'',true);
			html_checkboxbox2('laggport',gettext('Ports'),$sphere->row['laggport'],$l_selectable_ports,'',true);
			html_inputbox2('desc',gettext('Description'),$sphere->row['desc'],gettext('You may enter a description here for your reference.'),false,40);
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		if(!$hide_button_save):
			echo $sphere->html_button('save',$isrecordnew ? gettext('Add') : gettext('Save'));
		endif;
		
		if(empty($input_errors) && !$isrecordnew && !empty($l_available_ports)):
			echo $sphere->html_button('clone',gettext('Clone'));
		endif;
		echo $sphere->html_button('cancel',gettext('Cancel'));
?>
		<input name="enable" type="hidden" value="<?=$sphere->row['enable'];?>"/>
		<input name="if" type="hidden" value="<?=$sphere->row['if'];?>"/>
		<input name="<?=$sphere->get_row_identifier();?>" type="hidden" value="<?=$sphere->row[$sphere->get_row_identifier()];?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
