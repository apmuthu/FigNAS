<?php
/*
	services_samba_ad_init.php

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

$errormsg = '';
$do_init = false;

list($pconfig['dns_forwarder']) = get_ipv4dnsserver();
if($pconfig['dns_forwarder'] == '127.0.0.1'):
	$pconfig['dns_forwarder'] = '';
endif;
$pconfig['dns_domain'] = strtolower($config['system']['domain'] ?? '');
if(preg_match('/^([^\.]+)\./', $pconfig['dns_domain'], $m)):
	$pconfig['netbios_domain'] = strtoupper($m[1]);
else:
	$pconfig['netbios_domain'] = strtoupper($pconfig['dns_domain']);
	$errormsg .= gtext('Domain is missing 2nd level name.');
	$errormsg .= '<br/>';
endif;
$pconfig['path'] = '';
$pconfig['user_shares'] = false;
$realm = strtoupper($pconfig['dns_domain']);
$hostname = $config['system']['hostname'] ?? '';
$netbiosname = strtoupper($config['system']['hostname'] ?? '');
if(isset($config['interfaces']['lan']['ipaddr'])):
	if($config['interfaces']['lan']['ipaddr'] == 'dhcp'):
		$errormsg .= gtext('Cannot use DHCP for LAN interface.');
		$errormsg .= '<br/>';
	endif;
else:
	$errormsg .= gtext('LAN interface is not configured.');
	$errormsg .= '<br/>';
endif;
$dns_configured = false;
foreach($config['system']['dnsserver'] as $dnsserver):
	if(is_string($dnsserver) && preg_match('/\S/',$dnsserver)):
		$dns_configured = true;
		break; // break loop
	endif;
endforeach;
foreach($config['system']['ipv6dnsserver'] as $dnsserver):
	if(is_string($dnsserver) && preg_match('/\S/',$dnsserver)):
		$dns_configured = true;
		break; // break loop
	endif;
endforeach;
if(!$dns_configured):
	$errormsg .= gtext('No DNS server have been configured.');
	$errormsg .= '<br/>';
endif;
/*
if((!empty($config['system']['dnsserver']) && $config['system']['dnsserver'][0] == "") && (!empty($config['system']['ipv6dnsserver']) && $config['system']['ipv6dnsserver'][0] == "")) {
	$errormsg .= gtext('DNS server is empty.');
	$errormsg .= '<br/>';
}
*/
if(isset($config['system']['ntp']['enable'])):
else:
	$errormsg .= gtext('NTP is not enabled.');
	$errormsg .= '<br/>';
endif;
if(isset($config['samba']['enable'])):
	$errormsg .= gtext('CIFS/SMB is enabled.');
	$errormsg .= '<br/>';
endif;
if($_POST):
	unset($input_errors);
	unset($errormsg);
	unset($do_init);

	$pconfig = $_POST;
	if(!file_exists($_POST['path'])):
		$input_errors[] = gtext('Path not found.');
	elseif(file_exists($_POST['path'] . '/sysvol')):
		$input_errors[] = gtext('A sysvol folder was found.');
	endif;
	if($_POST['password'] != $_POST['password_confirm']):
		$input_errors[] = gtext('The confirmed password does not match. Please ensure the passwords match exactly.');
	elseif($_POST['password'] == ''):
		//$input_errors[] = gtext("The admin password is empty.");
		endif;
	if($_POST['dns_forwarder'] == ''):
		$input_errors[] = gtext('DNS server is empty.');
	endif;
	if(empty($input_errors)):
		$do_init = true;
		$config['sambaad']['enable'] = false;
		$config['sambaad']['path'] = $_POST['path'];
		$config['sambaad']['dns_forwarder'] = $_POST['dns_forwarder'];
		$config['sambaad']['dns_domain'] = $_POST['dns_domain'];
		$config['sambaad']['netbios_domain'] = $_POST['netbios_domain'];
		$config['sambaad']['user_shares'] = isset($_POST['user_shares']) ? true : false;
		$realm = strtoupper($config['sambaad']['dns_domain']);
		$domain = strtoupper($config['sambaad']['netbios_domain']);
		$password = $_POST['password'];
		$path = $config['sambaad']['path'];
		$cmsargs = [];
		$cmdargs[] = escapeshellarg('--use-rfc2307');
		$cmdargs[] = escapeshellarg('--function-level=2008_R2');
		$cmdargs[] = escapeshellarg(sprintf('--realm=%s',$realm));
		$cmdargs[] = escapeshellarg(sprintf('--domain=%s',$domain));
		$cmdargs[] = escapeshellarg('--server-role=dc');
		$cmdargs[] = escapeshellarg('--dns-backend=SAMBA_INTERNAL');
		if(!empty($password)):
			$cmdargs[] = escapeshellarg(sprintf('--adminpass=%s',$password));
		endif;
		$cmdargs[] = escapeshellarg(sprintf('--option=cache directory = %s',$path));
		$cmdargs[] = escapeshellarg(sprintf('--option=lock directory = %s',$path));
		$cmdargs[] = escapeshellarg(sprintf('--option=state directory = %s',$path));
		$cmdargs[] = escapeshellarg(sprintf('--option=private dir = %s/private',$path));
		$cmdargs[] = escapeshellarg(sprintf('--option=smb passwd file = %s/private/smbpasswd',$path));
		$cmdargs[] = escapeshellarg(sprintf('--option=usershare path = %s/usershares',$path));
		$cmdargs[] = escapeshellarg('--option=nsupdate command = /usr/local/bin/samba-nsupdate -g');
		// adjust DNS server
		unset($config['system']['dnsserver']);
		$config['system']['dnsserver'][] = '127.0.0.1';
		write_config();
		$retval = 0;
		if(isset($config['samba']['enable'])):
			$config['samba']['enable'] = false;
			write_config();
			config_lock();
			$retval |= rc_update_service('samba');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
		endif;
		if(file_exists('/var/etc/smb4.conf')):
			if (unlink('/var/etc/smb4.conf') == false):
				$input_errors[] = sprintf(gtext('Failed to remove: %s'),'/var/etc/smb4.conf');
			endif;
		endif;
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('Samba AD'),gtext('Initialize')];
include 'fbegin.inc';
?>
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
		<li class="tabinact"><a href="services_samba_ad.php"><span><?=gtext('Settings');?></span></a></li>
		<li class="tabact"><a href="services_samba_ad_init.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Initialize');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<form action="services_samba_ad_init.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Samba Active Directory Domain Controller'));
?>
		</thead>
		<tbody>
<?php
			html_text2('hostname',gettext('Hostname'),htmlspecialchars($hostname));
			html_text2('netbiosname',gettext('NetBIOS Name'),htmlspecialchars($netbiosname));
			html_inputbox2('dns_forwarder',gettext('DNS Forwarder'),$pconfig['dns_forwarder'],'',true,40);
			html_inputbox2('dns_domain',gettext('DNS Domain'),$pconfig['dns_domain'],'',true,40);
			html_inputbox2('netbios_domain',gettext('NetBIOS Domain'),$pconfig['netbios_domain'],'',true,40);
			//html_text2('realm',gettext('Kerberos realm'),htmlspecialchars($realm));
			html_passwordconfbox2('password','password_confirm',gettext('Admin Password'),'','',gettext('Generate password if left empty.'),true);
			html_filechooser2('path',gettext('Path'),$pconfig['path'],sprintf(gettext('Permanent samba data path (e.g. %s).'),'/mnt/data/samba4'),$g['media_path'],true);
			html_checkbox2('user_shares',gettext('User Shares'),!empty($pconfig['user_shares']) ? true : false,gettext('Append user defined shares'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Initialize');?>" />
	</div>
<?php
	if($do_init):
		echo sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:"));
		echo '<pre class="cmdoutput">';
		ob_end_flush();
		$cmd = sprintf('/usr/local/bin/samba-tool domain provision %s',implode(' ',$cmdargs));
		echo gtext('Initializing...'),PHP_EOL;
/*
		mwexec2("$cmd 2>&1", $rawdata, $result);
		foreach ($rawdata as $line) {
			echo htmlspecialchars($line)."\n";
		}
*/
		$handle = popen("$cmd 2>&1",'r');
		while(!feof($handle)):
			$line = fgets($handle);
			echo htmlspecialchars($line);
			ob_flush();
			flush();
		endwhile;
		$result = pclose($handle);
		echo('</pre>');
		if($result == 0):
			rename('/var/etc/smb4.conf',"${path}/smb4.conf.created");
			rc_exec_service('resolv');
		endif;
	endif;
?>
	<div id="remarks">
<?php
		$helpinghand = '<a href="system.php">' . gtext('Check System|General Setup before initializing') . '</a>.';
		html_remark('note', gtext('Note'), sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li></ul></div>", gtext("All data in the path is overwritten. To avoid invalid data/permission, using an empty UFS directory is recommended."), $helpinghand));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
