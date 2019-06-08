<?php
/*
	services_afp.php

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

function services_afp_get_sphere() {
	global $config;
	$sphere = new co_sphere_settings('services_afp','php');
	$sphere->row_default = [
		'enable' => false,
		'afpname' => '',
		'uams_guest' => false,
//		'uams_randum' => false,
//		'uam_gss' => false,
		'uams_pam' => false,
		'uams_passwd' => false,
		'uams_dhx_pam' => false,
		'uams_dhx_passwd' => true,
		'uams_dhx2_pam' => false,
		'uams_dhx2_passwd' => true,
		'auxparam' => ''
	];
	$sphere->grid = &array_make_branch($config,'afp');
	array_make_branch($config,'afp','auxparam');
	if(empty($sphere->grid)):
		$sphere->grid = $sphere->row_default;
		write_config();
		header($sphere->get_location());
		exit;
	endif;
	return $sphere;
}
$sphere = &services_afp_get_sphere();
$gt_button_apply_confirm = gtext('Do you want to apply these settings?');
$input_errors = [];
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
		if(isset($source['clrtxt'])):
			switch($source['clrtxt']):
				case 'uams_pam':
					$source['uams_pam'] = true;
					break;
				case 'uams_passwd':
					$source['uams_passwd'] = true;
					break;
			endswitch;
			unset($source['clrtxt']);
		endif;
		if(isset($source['dhx'])):
			switch($source['dhx']):
				case 'uams_dhx_pam':
					$source['uams_dhx_pam'] = true;
					break;
				case 'uams_dhx_passwd':
					$source['uams_dhx_passwd'] = true;
					break;
			endswitch;
			unset($switch['dhx']);
		endif;
		if(isset($source['dhx2'])):
			switch($source['dhx2']):
				case 'uams_dhx2_pam':
					$source['uams_dhx2_pam'] = true;
					break;
				case 'uams_dhx2_passwd':
					$source['uams_dhx2_passwd'] = true;
					break;
			endswitch;
			unset($source['dhx2']);
		endif;
		break;
	default:
		$source = $sphere->grid;
		break;
endswitch;
$sphere->row['enable'] = isset($source['enable']);
$sphere->row['afpname'] = $source['afpname'] ?? $sphere->row_default['afpname'];
$sphere->row['uams_guest'] = isset($source['uams_guest']);
//	$sphere->row['uams_randum'] = isset($source['uams_randum']);
//	$sphere->row['uam_gss'] = isset($source['uam_gss']);
$sphere->row['uams_pam'] = isset($source['uams_pam']);
$sphere->row['uams_passwd'] = isset($source['uams_passwd']);
$sphere->row['uams_dhx_pam'] = isset($source['uams_dhx_pam']);
$sphere->row['uams_dhx_passwd'] = isset($source['uams_dhx_passwd']);
$sphere->row['uams_dhx2_pam'] = isset($source['uams_dhx2_pam']);
$sphere->row['uams_dhx2_passwd'] = isset($source['uams_dhx2_passwd']);
$sphere->row['auxparam'] = $source['auxparam'] ?? $sphere->row_default['auxparam'];
if(is_array($sphere->row['auxparam'])):
	$sphere->row['auxparam'] = implode("\n",$sphere->row['auxparam']);
endif;
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
		$authmethods = 0;
		$authmethods += $sphere->row['uams_guest'] ? 1 : 0;
//		$authmethods += $sphere->row['uams_randum'] ? 1 : 0;
//		$authmethods += $sphere->row['uam_gss'] ? 1 : 0;
		$authmethods += $sphere->row['uams_pam'] ? 1 : 0;
		$authmethods += $sphere->row['uams_passwd'] ? 1 : 0;
		$authmethods += $sphere->row['uams_dhx_pam'] ? 1 : 0;
		$authmethods += $sphere->row['uams_dhx_passwd'] ? 1 : 0;
		$authmethods += $sphere->row['uams_dhx2_pam'] ? 1 : 0;
		$authmethods += $sphere->row['uams_dhx2_passwd'] ? 1 : 0;
		if($authmethods == 0):
			$input_errors[] = gtext('You must select at least one authentication method.');
		endif;
		if(empty($input_errors)):
			//	convert parameters
			$auxparam_grid = [];
			foreach(explode("\n",$sphere->row['auxparam']) as $auxparam_row):
				$auxparam_row = trim($auxparam_row,"\t\n\r");
				if(!empty($auxparam_row)):
					$auxparam_grid[] = $auxparam_row;
				endif;
			endforeach;
			$sphere->row['auxparam'] = $auxparam_grid;
			$sphere->copyrowtogrid();
			write_config();
			$retval = 0;
			config_lock();
			$retval |= rc_update_service('netatalk');
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
			$retval |= rc_update_service('netatalk');
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
$pgtitle = [gtext('Services'),gtext('AFP')];
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
		<li class="tabact"><a href="services_afp.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Settings');?></span></a></li>
		<li class="tabinact"><a href="services_afp_share.php"><span><?=gtext('Shares');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="services_afp.php" method="post" name="iform" id="iform">
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
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_titleline2(gettext('Apple Filing Protocol'));
					break;
				case PAGE_MODE_EDIT:
					html_titleline_checkbox2('enable',gettext('Apple Filing Protocol'),$sphere->row['enable'],gettext('Enable'));
					break;
			endswitch;
?>
		</thead>
		<tbody>
<?php
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_textinfo2('enable',gettext('Service Enabled'),$sphere->row['enable'] ? gettext('Yes') : gettext('No'));
					html_textinfo2('afpname',gettext('Server Name'),$sphere->row['afpname']);
					html_checkbox2('uams_guest',gettext('Guest Authentication'),$sphere->row['uams_guest'],'','',false,true);
//					html_checkbox2('uams_randum',gettext('Random Authentication'),$sphere->row['uams_randum'],'','',false,true);
//					html_checkbox2('uam_gss',gettext('Kerberos Authentication'),$sphere->row['uam_gss'],'','',false,true);
					if($sphere->row['uams_pam']):
						html_textinfo2('clrtxt',gettext('Clear Text Authentication'),gettext('PAM authentication is selected.'));
					elseif($sphere->row['uams_passwd']):
						html_textinfo2('clrtxt',gettext('Clear Text Authentication'),gettext('Local user authentication is selected.'));
					endif;
					if($sphere->row['uams_dhx_pam']):
						html_textinfo2('dhx',gettext('DHX Authentication'),gettext('PAM authentication is selected.'));
					elseif($sphere->row['uams_dhx_passwd']):
						html_textinfo2('dhx',gettext('DHX Authentication'),gettext('Local user authentication is selected.'));
					endif;
					if($sphere->row['uams_dhx2_pam']):
						html_textinfo2('dhx2',gettext('DHX2 Authentication'),gettext('PAM authentication is selected.'));
					elseif($sphere->row['uams_dhx2_passwd']):
						html_textinfo2('dhx2',gettext('DHX2 Authentication'),gettext('Local user authentication is selected.'));
					endif;
					html_textarea2('auxparam',gettext('Additional Parameters'),$sphere->row['auxparam'],'',false,65,5,true,false);
					break;
				case PAGE_MODE_EDIT:
					html_inputbox2('afpname',gettext('Server Name'),$sphere->row['afpname'],gettext('Name of the server. If this field is left empty the default server is specified.'),false,30,false,false,30);
					html_checkbox2('uams_guest',gettext('Guest Authentication'),$sphere->row['uams_guest'],gettext('Enable guest access.'));
//					html_checkbox2('uams_randum',gettext('Random Authentication'),$sphere->row['uams_randum'],gettext('Allow random number and two-way random number exchange for authentication.'));
//					html_checkbox2('uam_gss',gettext('Kerberos Authentication'),$sphere->row['uam_gss'],gettext('Allow Kerberos V for authentication.'));
?>
					<tr id="clrtxt_tr">
						<td class="celltag"><?=gtext('Clear Text Authentication');?></td>
						<td class="celldata">
							<table class="area_data_selection">
								<colgroup>
									<col style="width:5%">
									<col style="width:95%">
								</colgroup>
								<thead>
									<tr>
										<th class="lhelc"></th>
										<th class="lhebl"><?=gtext('Selection');?></th>
									</tr>
								</thead>
								<tbody>
									<tr id="uams_none_tr">
										<td class="lcelc"><input name="clrtxt" id="uams_none" type="radio" value="off"<?=($sphere->row['uams_passwd'] || $sphere->row['uams_pam']) ? '' : ' checked="checked"';?>/></td>
										<td class="lcebl"><label for="uams_none"><?=gtext('Authentification method is disabled.');?></label></td>
									</tr>
									<tr id="uams_passwd_tr">
										<td class="lcelc"><input name="clrtxt" id="uams_passwd" type="radio" value="uams_passwd"<?=($sphere->row['uams_passwd']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_passwd"><?=gtext('Use local user authentication.');?></label></td>
									</tr>
									<tr id="uams_pam_tr">
										<td class="lcelc"><input name="clrtxt" id="uams_pam" type="radio" value="uams_pam"<?=($sphere->row['uams_pam']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_pam"><?=gtext('Use PAM for user authentication.');?></label></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr id="dhx_tr">
						<td class="celltag"><?=gtext('DHX Authentication');?></td>
						<td class="celldata">
							<table class="area_data_selection">
								<colgroup>
									<col style="width:5%">
									<col style="width:95%">
								</colgroup>
								<thead>
									<tr>
										<th class="lhelc"></th>
										<th class="lhebl"><?=gtext('Selection');?></th>
									</tr>
								</thead>
								<tbody>
									<tr id="uams_dhx_none_tr">
										<td class="lcelc"><input name="dhx" id="uams_dhx_none" type="radio" value="off"<?=($sphere->row['uams_dhx_passwd'] || $sphere->row['uams_dhx_pam']) ? '' : ' checked="checked"';?>/></td>
										<td class="lcebl"><label for="uams_dhx_none"><?=gtext('Authentification method is disabled.');?></label></td>
									</tr>
									<tr id="uams_dhx_passwd_tr">
										<td class="lcelc"><input name="dhx" id="uams_dhx_passwd" type="radio" value="uams_dhx_passwd"<?=($sphere->row['uams_dhx_passwd']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_dhx_passwd"><?=gtext('Use local user authentication.');?></label></td>
									</tr>
									<tr id="uams_dhx_pam_tr">
										<td class="lcelc"><input name="dhx" id="uams_dhx_pam" type="radio" value="uams_dhx_pam"<?=($sphere->row['uams_dhx_pam']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_dhx_pam"><?=gtext('Use PAM for user authentication.');?></label></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr id="dhx2_tr">
						<td class="celltag"><?=gtext('DHX2 Authentication');?></td>
						<td class="celldata">
							<table class="area_data_selection">
								<colgroup>
									<col style="width:5%">
									<col style="width:95%">
								</colgroup>
								<thead>
									<tr>
										<th class="lhelc"></th>
										<th class="lhebl"><?=gtext('Selection');?></th>
									</tr>
								</thead>
								<tbody>
									<tr id="uams_dhx2_none_tr">
										<td class="lcelc"><input name="dhx2" id="uams_dhx2_none" type="radio" value="off"<?=($sphere->row['uams_dhx2_passwd'] || $sphere->row['uams_dhx2_pam']) ? '' : ' checked="checked"';?>/></td>
										<td class="lcebl"><label for="uams_dhx2_none"><?=gtext('Authentification method is disabled.');?></label></td>
									</tr>
									<tr id="uams_dhx2_passwd_tr">
										<td class="lcelc"><input name="dhx2" id="uams_dhx2_passwd" type="radio" value="uams_dhx2_passwd"<?=($sphere->row['uams_dhx2_passwd']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_dhx2_passwd"><?=gtext('Use local user authentication.');?></label></td>
									</tr>
									<tr id="uams_dhx2_pam_tr">
										<td class="lcelc"><input name="dhx2" id="uams_dhx2_pam" type="radio" value="uams_dhx2_pam"<?=($sphere->row['uams_dhx2_pam']) ? ' checked="checked"' : '';?>/></td>
										<td class="lcebl"><label for="uams_dhx2_pam"><?=gtext('Use PAM for user authentication.');?></label></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
<?php
					$helpinghand = '<a href="http://netatalk.sourceforge.net/3.1/htmldocs/afp.conf.5.html" target="_blank">'
						. gettext('Please check the documentation')
						. '</a>.';
					html_textarea2('auxparam',gettext('Additional Parameters'),$sphere->row['auxparam'],gettext('Add any supplemental parameters.') . ' ' . $helpinghand,false,65,5,false,false);
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
	<div id="remarks">
<?php
		$link = '<a href="system_advanced.php">'
			. gettext('Zeroconf/Bonjour')
			. '</a>';
		html_remark2('note',gettext('Note'),sprintf(gettext('You have to activate %s to advertise this service to clients.'),$link));
?>
	</div>
<?php
	include 'formend.inc';
?>
</form></td></tr></tbody></table>
<?php
include 'fend.inc';
?>
