<?php
/*
	services_dynamicdns.php

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

array_make_branch($config,'dynamicdns');
$pconfig['enable'] = isset($config['dynamicdns']['enable']);
$pconfig['provider'] = !empty($config['dynamicdns']['provider']) ? $config['dynamicdns']['provider'] : "";
$pconfig['domainname'] = !empty($config['dynamicdns']['domainname']) ? $config['dynamicdns']['domainname'] : "";
$pconfig['username'] = !empty($config['dynamicdns']['username']) ? $config['dynamicdns']['username'] : "";
$pconfig['password'] = !empty($config['dynamicdns']['password']) ? $config['dynamicdns']['password'] : "";
$pconfig['updateperiod'] = !empty($config['dynamicdns']['updateperiod']) ? $config['dynamicdns']['updateperiod'] : "";
$pconfig['forcedupdateperiod'] = !empty($config['dynamicdns']['forcedupdateperiod']) ? $config['dynamicdns']['forcedupdateperiod'] : "";
$pconfig['wildcard'] = isset($config['dynamicdns']['wildcard']);
if(isset($config['dynamicdns']['auxparam']) && is_array($config['dynamicdns']['auxparam'])): 
	$pconfig['auxparam'] = implode("\n", $config['dynamicdns']['auxparam']);
endif;

if ($_POST) {
	unset($input_errors);

	$pconfig = $_POST;

	/* input validation */
	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = ['provider','domainname','username','password'];
		$reqdfieldsn = [gtext('Provider'),gtext('Domain Name'),gtext('Username'),gtext('Password')];
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		$reqdfields = array_merge($reqdfields, ['updateperiod','forcedupdateperiod']);
		$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Update period'),gtext('Forced Update Period')]);
		$reqdfieldst = ['string','string','string','string','numeric','numeric'];
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	if (empty($input_errors)) {
		$config['dynamicdns']['enable'] = isset($_POST['enable']) ? true : false;
		$config['dynamicdns']['provider'] = $_POST['provider'];
		$config['dynamicdns']['domainname'] = $_POST['domainname'];
		$config['dynamicdns']['username'] = $_POST['username'];
		$config['dynamicdns']['password'] = $_POST['password'];
		$config['dynamicdns']['updateperiod'] = $_POST['updateperiod'];
		$config['dynamicdns']['forcedupdateperiod'] = $_POST['forcedupdateperiod'];
		$config['dynamicdns']['wildcard'] = isset($_POST['wildcard']) ? true : false;

		# Write additional parameters.
		unset($config['dynamicdns']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['dynamicdns']['auxparam'][] = $auxparam;
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("inadyn");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}
// Get list of available interfaces.
$a_interface = get_interface_list();
$pgtitle = [gtext('Services'),gtext('Dynamic DNS')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.provider.disabled = endis;
	document.iform.domainname.disabled = endis;
	document.iform.username.disabled = endis;
	document.iform.password.disabled = endis;
	document.iform.updateperiod.disabled = endis;
	document.iform.forcedupdateperiod.disabled = endis;
	document.iform.wildcard.disabled = endis;
	document.iform.auxparam.disabled = endis;
}

function provider_change() {
	switch(document.iform.provider.value) {
		case "dyndns.org":
		case "3322.org":
		case "easydns.com":
		case "custom":
			showElementById('wildcard_tr','show');
			break;

		default:
			showElementById('wildcard_tr','hide');
			break;
	}
}
//-->
</script>
<form action="services_dynamicdns.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td class="tabcont">
	    	<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Dynamic DNS"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
					<?php html_combobox("provider", gtext("Provider"), $pconfig['provider'],['dyndns.org' => 'dyndns.org', 'freedns.afraid.org' => 'freedns.afraid.org', 'zoneedit.com' => 'zoneedit.com', 'no-ip.com' => 'no-ip.com', '3322.org' => '3322.org', 'easydns.com' => 'easydns.com', 'dnsdynamic.org' => 'dnsdynamic.org', 'dhis.org' => 'dhis.org', 'dnsexit.com' => 'dnsexit.com', 'ipv6tb.he.net' => 'ipv6tb.he.net', 'tzo.com' => 'tzo.com', 'dynsip.org' => 'dynsip.org', 'dtdns.com' => 'dtdns.com', 'changeip.com' => 'changeip.com', 'dy.fi' => 'dy.fi', 'two-dns.de' => 'two-dns.de', 'custom' => gtext('Custom')], "", true, false, "provider_change()");?>
					<?php html_inputbox("domainname", gtext("Domain Name"), $pconfig['domainname'], gtext("A host name alias. This option can appear multiple times, for each domain that has the same IP. Use a space to separate multiple alias names."), true, 40);?>
					<?php html_inputbox("username", gtext("Username"), $pconfig['username'], "", true, 20);?>
					<?php html_passwordbox("password", gtext("Password"), $pconfig['password'], "", true, 20);?>
					<?php html_inputbox("updateperiod", gtext("Update Period"), $pconfig['updateperiod'], gtext("How often the IP is checked. The period is in seconds (max. is 10 days)."), false, 20);?>
					<?php html_inputbox("forcedupdateperiod", gtext("Forced Update Period"), $pconfig['forcedupdateperiod'], gtext("How often the IP is updated even if it is not changed. The period is in seconds (max. is 10 days)."), false, 20);?>
					<?php html_checkbox("wildcard", gtext("Wildcard"), !empty($pconfig['wildcard']) ? true : false, gtext("Enable domain wildcarding."), "", false);?>
					<?php html_textarea("auxparam", gtext("Additional Parameters"), !empty($pconfig['auxparam']) ? $pconfig['auxparam'] : "", sprintf(gtext("These parameters will be added to global settings in %s."), "inadyn.conf"), false, 65, 3, false, false);?>
			  </table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
				</div>
			</td>
		</tr>
	</table>
	<?php include 'formend.inc';?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
provider_change();
//-->
</script>
<?php include 'fend.inc';?>
