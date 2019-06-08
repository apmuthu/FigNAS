<?php
/*
	services_samba_ad.php

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

array_make_branch($config,'samba');
array_make_branch($config,'sambaad','auxparam');
array_make_branch($config,'interfaces','lan');
array_make_branch($config,'system','dnsserver');
array_make_branch($config,'system','ipv6dnsserver');
array_make_branch($config,'system','ntp');

$errormsg='';

if($config['interfaces']['lan']['ipaddr'] == 'dhcp'):
	$errormsg .= gtext("Cannot use DHCP for LAN interface.");
	$errormsg .= "<br/>";
endif;
if((!empty($config['system']['dnsserver']) && $config['system']['dnsserver'][0] == "")
   && (!empty($config['system']['ipv6dnsserver']) && $config['system']['ipv6dnsserver'][0] == "")):
	$errormsg .= gtext("DNS server is empty.");
	$errormsg .= "<br/>";
endif;
if(!isset($config['system']['ntp']['enable'])):
	$errormsg .= gtext("NTP is not enabled.");
	$errormsg .= "<br/>";
endif;
/*
if (isset($config['samba']['enable'])) {
	$errormsg .= gtext("CIFS/SMB is enabled.");
	$errormsg .= "<br/>";
}
*/
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['enable'])):
		if(empty($config['sambaad']) || empty($config['sambaad']['path']) || !file_exists($config['sambaad']['path'] . '/sysvol')):
			$input_errors[] = gtext('You must initialize data before enabling.');
		endif;
	endif;
	if($_POST['dns_forwarder'] == ''):
		$input_errors[] = gtext('DNS server is empty.');
	endif;
	if(empty($input_errors)):
		$config['sambaad']['enable'] = isset($_POST['enable']) ? true : false;
		$config['samba']['enable'] = isset($_POST['enable']) ? true : false;
		$config['sambaad']['dns_forwarder'] = $_POST['dns_forwarder'];
		$config['sambaad']['user_shares'] = isset($_POST['user_shares']) ? true : false;
		unset($config['sambaad']['auxparam']);
		foreach(explode("\n", $_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam, "\t\n\r");
			if(!empty($auxparam)):
				$config['sambaad']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service('samba');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
if(!empty($config['sambaad']['path'])):
	$pconfig['enable'] = isset($config['sambaad']['enable']);
	$pconfig['dns_domain'] = $config['sambaad']['dns_domain'];
	$pconfig['netbios_domain'] = $config['sambaad']['netbios_domain'];
	$pconfig['dns_forwarder'] = $config['sambaad']['dns_forwarder'];
	$pconfig['path'] = $config['sambaad']['path'];
	$pconfig['user_shares'] = isset($config['sambaad']['user_shares']);
else:
	$pconfig['enable'] = false;
	$pconfig['dns_domain'] = '';
	$pconfig['netbios_domain'] = '';
	$pconfig['dns_forwarder'] = '';
	$pconfig['path'] = '';
	$pconfig['user_shares'] = false;
endif;
$realm = strtoupper($pconfig['dns_domain']);
$hostname = $config['system']['hostname'];
$netbiosname = strtoupper($config['system']['hostname']);
$pconfig['auxparam'] = '';
if(is_array($config['sambaad']['auxparam'])):
	$pconfig['auxparam'] = implode("\n", $config['sambaad']['auxparam']);
endif;
$pgtitle = [gtext('Services'),gtext('Samba AD')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
$(document).ready(function(){
	function enable_change(enable_change) {
		var val = !($('#enable').prop('checked') || enable_change);
	}
	$('#enable').click(function(){
		enable_change(false);
	});
	$('input:submit').click(function(){
		enable_change(true);
	});
	enable_change(false);
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabact"><a href="services_samba_ad.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Settings');?></span></a></li>
		<li class="tabinact"><a href="services_samba_ad_init.php"><span><?=gtext('Initialize');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<form action="services_samba_ad.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
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
			html_titleline_checkbox2('enable',gettext('Samba Active Directory Domain Controller'),!empty($pconfig['enable']) ? true : false,gettext('Enable'),'');
?>
		</thead>
		<tbody>
<?php
			html_text2('hostname',gettext('Hostname'),htmlspecialchars($hostname));
			html_text2('netbiosname',gettext('NetBIOS Name'),htmlspecialchars($netbiosname));
			html_inputbox2('dns_forwarder',gettext('DNS Forwarder'),$pconfig['dns_forwarder'],'',false,40);
			html_text2('dns_domain',gettext('DNS Domain'),htmlspecialchars($pconfig['dns_domain']));
			html_text2('netbios_domain',gettext('NetBIOS Domain'),htmlspecialchars($pconfig['netbios_domain']));
			html_text2('path',gettext('Path'),htmlspecialchars($pconfig['path']));
			html_checkbox2('user_shares',gettext('User Shares'),!empty($pconfig['user_shares']) ? true : false, gettext('Append user defined shares'),'',false);
			$helpinghand = '<a href="http://us1.samba.org/samba/docs/man/manpages-3/smb.conf.5.html" target="_blank">'
				. gettext('Please check the documentation')
				. '</a>.';
			html_textarea2('auxparam',gettext('Additional Parameters'),$pconfig['auxparam'], sprintf(gettext('These parameters are added to [Global] section of %s.'),'smb4.conf') . ' ' . $helpinghand,false,65,5,false,false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>"/>
	</div>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li><li>%s</li></ul></div>",gettext('When Samba AD is enabled, stand-alone CIFS/SMB file sharing cannot be used.'),gettext('NTP must be enabled.'),gettext('DHCP cannot be used for LAN interface.')));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
