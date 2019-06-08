<?php
/*
	services_rsyncd.php

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

function services_rsyncd_get_sphere() {
	global $config;
	$sphere = new co_sphere_settings('services_rsyncd','php');
	$sphere->row_default = [
		'enable' => false,
		'port' => 873,
		'motd' => '',
		'rsyncd_user' => 'ftp',
		'auxparam' => []
	];
	$sphere->grid = &array_make_branch($config,'rsyncd');
	array_make_branch($sphere->grid,'auxparam');
	return $sphere;
}
$sphere = &services_rsyncd_get_sphere();
$gt_button_apply_confirm = gtext('Do you want to apply these settings?');
array_make_branch($config,'access','user');
array_sort_key($config['access']['user'],'login');
$a_user = &$config['access']['user'];
$input_errors = [];
$a_message = [];
//	identify page mode
$mode_page = ($_POST) ? PAGE_MODE_POST : PAGE_MODE_VIEW;
switch($mode_page):
	case PAGE_MODE_POST:
		if(isset($_POST['submit'])):
			$page_action = $_POST['submit'];
			switch($page_action):
				case 'edit':
					$mode_page = PAGE_MODE_EDIT;
					break;
				case 'save':
					break;
				case 'enable':
					break;
				case 'disable':
					break;
				default:
					$mode_page = PAGE_MODE_VIEW;
					$page_action = 'view';
					break;
			endswitch;
		else:
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		endif;
		break;
	case PAGE_MODE_VIEW:
		$page_action = 'view';
		break;
endswitch;
//	get configuration data, depending on the source
switch($page_action):
	case 'save':
		$source = $_POST;
		$sphere->row['motd'] = $source['motd'] ?? $sphere->row_default['motd'];
		$sphere->row['auxparam'] = $source['auxparam'] ?? $sphere->row_default['auxparam'];
		break;
	default:
		$source = $sphere->grid;
		$sphere->row['motd'] = isset($source['motd']) ? base64_decode($source['motd']) : $sphere->row_default['motd'];
		if(isset($source['auxparam']) && is_array($source['auxparam'])):
			$sphere->row['auxparam'] = implode("\n",$source['auxparam']);
		endif;
		break;
endswitch;
$sphere->row['enable'] = isset($source['enable']);
$sphere->row['port'] = $source['port'] ?? $sphere->row_default['port'];
$sphere->row['rsyncd_user'] = $source['rsyncd_user'] ?? $sphere->row_default['rsyncd_user'];
//	process enable
switch($page_action):
	case 'enable':
		if($sphere->row['enable']):
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		else: // enable and run a full validation
			$sphere->row['enable'] = true;
			$page_action = 'save'; // continue with save procedure
		endif;
		break;
endswitch;
//	process save and disable
switch($page_action):
	case 'save':
		//	Input validation.
		$reqdfields = ['rsyncd_user','port'];
		$reqdfieldsn = [gtext('Map to User'),gtext('TCP Port')];
		$reqdfieldst = ['string','port'];
		do_input_validation($sphere->row,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($sphere->row,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if(empty($input_errors)):
			//	conversion
			$sphere->row['motd'] = base64_encode($sphere->row['motd']);
			$helpinghand = [];
			foreach(explode("\n",$sphere->row['auxparam']) as $auxparam):
				$auxparam = trim($auxparam, "\t\n\r");
				if(preg_match('/\S/',$auxparam)):
					$helpinghand[] = $auxparam;
				endif;
			endforeach;
			$sphere->row['auxparam'] = $helpinghand;
			$sphere->copyrowtogrid();
			write_config();
			$retval = 0;
			config_lock();
			$retval |= rc_update_service('rsyncd');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
			header($sphere->get_location());
			exit;
		else:
			$mode_page = PAGE_MODE_EDIT;
			$page_action = 'edit';
		endif;
		break;
	case 'disable':
		if($sphere->row['enable']): // if enabled, disable it
			$sphere->row['enable'] = false;
			$sphere->grid['enable'] = $sphere->row['enable'];
			write_config();
			$retval = 0;
			config_lock();
			$retval |= rc_update_service('rsyncd');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
			header($sphere->get_location());
			exit;
		endif;
		$mode_page = PAGE_MODE_VIEW;
		$page_action = 'view';
		break;
endswitch;
//	determine final page mode
switch($mode_page):
	case PAGE_MODE_EDIT:
		break;
	default:
		if(isset($config['system']['skipviewmode'])):
			$mode_page = PAGE_MODE_EDIT;
			$page_action = 'edit';
		else:
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		endif;
		break;
endswitch;
//  prepare lookups
$l_user = ['ftp' => gettext('Guest')];
foreach ($a_user as $r_user):
	$l_user[$r_user['login']] = $r_user['login'];
endforeach;
$gt_auxparam = sprintf(gettext('These parameters will be added to [global] settings in %s.'),'rsyncd.conf')
	. ' '
	. '<a href="https://rsync.samba.org/ftp/rsync/rsyncd.conf.html" target="_blank">'
	. gettext('Please check the documentation')
	. '</a>.';
$pgtitle = [gtext('Services'),gtext('Rsync'),gtext('Server'),gtext('Settings')];
include 'fbegin.inc';
switch($mode_page):
	case PAGE_MODE_VIEW:
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<?php
		break;
	case PAGE_MODE_EDIT:
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("#iform").submit(function() {	spinner(); });
	$(".spin").click(function() { spinner(); });
	$("#button_save").click(function () {
		return confirm("<?=$gt_button_apply_confirm;?>");
	});
});
//]]>
</script>
<?php
		break;
endswitch;	
?>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabact"><a href="services_rsyncd.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Server');?></span></a></li>
		<li class="tabinact"><a href="services_rsyncd_client.php"><span><?=gtext('Client');?></span></a></li>
		<li class="tabinact"><a href="services_rsyncd_local.php"><span><?=gtext('Local');?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabact"><a href="services_rsyncd.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Settings');?></span></a></li>
		<li class="tabinact"><a href="services_rsyncd_module.php"><span><?=gtext('Modules');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	foreach($a_message as $r_message):
		print_info_box($r_message);
	endforeach;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_titleline2(gettext('Network File System'));
					break;
				case PAGE_MODE_EDIT:
					html_titleline_checkbox2('enable',gettext('Rsync'),$sphere->row['enable'],gettext('Enable'));
					break;
			endswitch;
?>
		</thead>
		<tbody>
<?php
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_textinfo2('enable',gettext('Service Enabled'),$sphere->row['enable'] ? gettext('Yes') : gettext('No'));
					if(isset($l_user[$sphere->row['rsyncd_user']])):
						$helpinghand = $l_user[$sphere->row['rsyncd_user']];
					else:
						$helpinghand = '';
					endif;
					html_textinfo2('rsyncd_user',gettext('Map to User'),$helpinghand);
					html_textinfo2('port',gettext('TCP Port'),$sphere->row['port']);
					html_textarea2('motd',gettext('MOTD'),$sphere->row['motd'],gettext('Message of the day.'),false,65,7,true,false);
					html_textarea2('auxparam',gettext('Additional Parameters'),$sphere->row['auxparam'],$gt_auxparam,false,65,5,true,false);
					break;
				case PAGE_MODE_EDIT:
					html_combobox2('rsyncd_user',gettext('Map to User'),$sphere->row['rsyncd_user'],$l_user,'',true);
					html_inputbox2('port',gettext('TCP Port'),$sphere->row['port'],gettext('Alternate TCP port. (Default is 873).'),true,20);
					html_textarea2('motd',gettext('MOTD'),$sphere->row['motd'],gettext('Message of the day.'),false,65,7,false,false);
					html_textarea2('auxparam',gettext('Additional Parameters'),$sphere->row['auxparam'],$gt_auxparam,false,65,5,false,false);
					break;
			endswitch;
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		switch($mode_page):
			case PAGE_MODE_VIEW:
				echo $sphere->html_button('edit',gettext('Edit'));
				if($sphere->row['enable']):
					echo $sphere->html_button('disable',gettext('Disable'));
				else:
					echo $sphere->html_button('enable',gettext('Enable'));
				endif;
				break;
			case PAGE_MODE_EDIT:
				echo $sphere->html_button('save',gettext('Apply'));
				echo $sphere->html_button('cancel',gettext('Cancel'));
				break;
		endswitch;
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
