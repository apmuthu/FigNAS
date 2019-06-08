<?php
/*
	system_email.php

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
require_once 'email.inc';

$sphere_scriptname = basename(__FILE__);
array_make_branch($config,'system','email');
$pconfig['from'] = $config['system']['email']['from'];
$pconfig['server'] = $config['system']['email']['server'];
$pconfig['port'] = $config['system']['email']['port'];
$pconfig['auth'] = isset($config['system']['email']['auth']);
$pconfig['authmethod'] = $config['system']['email']['authmethod'];
$pconfig['security'] = isset($config['system']['email']['security']);
$pconfig['starttls'] = isset($config['system']['email']['starttls']);
$pconfig['tls_certcheck'] = isset($config['system']['email']['tls_certcheck']);
$pconfig['tls_use_default_trust_file'] = isset($config['system']['email']['tls_use_default_trust_file']);
$pconfig['tls_trust_file'] = $config['system']['email']['tls_trust_file'] ?? '';
$pconfig['tls_fingerprint'] = $config['system']['email']['tls_fingerprint'] ?? '';
$pconfig['tls_crl_file'] = $config['system']['email']['tls_crl_file'] ?? '';
$pconfig['tls_cert_file'] = $config['system']['email']['tls_cert_file'] ?? '';
$pconfig['tls_key_file'] = $config['system']['email']['tls_key_file'] ?? '';
$pconfig['username'] = $config['system']['email']['username'];
$pconfig['password'] = $config['system']['email']['password'];
$pconfig['passwordconf'] = $pconfig['password'];
$pconfig['sendto'] = isset($config['system']['email']['sendto']) ? $config['system']['email']['sendto'] : (isset($config['system']['email']['from']) ? $config['system']['email']['from'] : '');

if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	// Input validation.
	$reqdfields = ['from','sendto','server','port'];
	$reqdfieldsn = [gtext('From Email Address'),gtext('To Email Address'),gtext('SMTP Server'),gtext('Port')];
	$reqdfieldst = ['string','string','string','string'];
	if(isset($_POST['auth'])):
		if(isset($_POST['authmethod']) && is_string($_POST['authmethod']) && ($_POST['authmethod'] !== 'external')):
			$reqdfields = array_merge($reqdfields,['username','password']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Username'),gtext('Password')]);
			$reqdfieldst = array_merge($reqdfieldst,['string','string']);
		endif;
	endif;
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
	// Check for a password mismatch.
	if(isset($_POST['auth']) && ($_POST['password'] !== $_POST['passwordconf'])) :
		$input_errors[] = gtext('The passwords do not match.');
	endif;
	if(empty($input_errors)):
		$config['system']['email']['from'] = $_POST['from'];
		$config['system']['email']['sendto'] = $_POST['sendto'];
		$config['system']['email']['server'] = $_POST['server'];
		$config['system']['email']['port'] = $_POST['port'];
		$config['system']['email']['auth'] = isset($_POST['auth']);
		$config['system']['email']['authmethod'] = $_POST['authmethod'];
		$config['system']['email']['security'] = isset($_POST['security']);
		$config['system']['email']['starttls'] = isset($_POST['starttls']);
		$config['system']['email']['tls_certcheck'] = isset($_POST['tls_certcheck']);
		$config['system']['email']['tls_use_default_trust_file'] = isset($_POST['tls_use_default_trust_file']) ? true : false;
		$config['system']['email']['tls_trust_file'] = $_POST['tls_trust_file'] ?? '';
		$config['system']['email']['tls_fingerprint'] = $_POST['tls_fingerprint'] ?? '';
		$config['system']['email']['tls_crl_file'] = $_POST['tls_crl_file'] ?? '';
		$config['system']['email']['tls_cert_file'] = $_POST['tls_cert_file'] ?? '';
		$config['system']['email']['tls_key_file'] = $_POST['tls_key_file'] ?? '';
		$config['system']['email']['username'] = $_POST['username'];
		$config['system']['email']['password'] = $_POST['password'];
		write_config();
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_exec_service('msmtp');
			config_unlock();
		endif;
		// Send test email.
		if(isset($_POST['submit'])):
			if($_POST['submit'] == 'sendtestemail'):
				$subject = sprintf(gettext('Test email from host: %s'),system_get_hostname());
				$message = gettext('This email has been sent to validate your email configuration.');
				$retval = @email_send($config['system']['email']['sendto'],$subject,$message,$error);
				if(0 == $retval):
					$savemsg = gtext('Test email successfully sent.');
					write_log(sprintf('Test email successfully sent to: %s.',$config['system']['email']['sendto']));
				else:
					$failmsg = gtext('Failed to send test email.')
						. ' '
						. '<a href="' . 'diag_log.php' . '">'
						. gtext('Please check the log files')
						. '</a>.';
					write_log(sprintf('Failed to send test email to: %s.',$config['system']['email']['sendto']));
				endif;
			elseif($_POST['submit'] == 'save'):
				$savemsg = get_std_save_message($retval);
			endif;
		endif;
	endif;
endif;
$l_authmethod = [
	'plain' => gtext('Plain-text'),
	'scram-sha-1' => 'SCRAM-SHA-1',
	'cram-md5' => 'CRAM-MD5',
	'digest-md5' => 'Digest-MD5',
//	'gssapi' => 'GSSAPI',
	'external' => 'External',
	'login' => gtext('Login'),
	'ntlm' => 'NTLM',
	'on' => gtext('Best available')
];
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Email Setup')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
	$('#auth').click(function() { auth_change(); });
	$('#security').click(function() { security_change(); });
	$('#tls_certcheck').click(function() { tls_certcheck_change(); });
	$('#tls_use_default_trust_file').click(function() { tls_use_default_trust_file_change() });
	auth_change();
	security_change();
});
function auth_change() {
	switch (document.iform.auth.checked) {
		case true:
			showElementById('username_tr','show');
			showElementById('password_tr','show');
			showElementById('authmethod_tr','show');
			break;
		case false:
			showElementById('username_tr','hide');
			showElementById('password_tr','hide');
			showElementById('authmethod_tr','hide');
			break;
	}
}
function security_change() {
	switch (document.iform.security.checked) {
		case true:
			showElementById('starttls_tr','show');
			showElementById('tls_certcheck_tr','show');
			tls_certcheck_change();
			showElementById('tls_cert_file_tr','show');
			showElementById('tls_key_file_tr','show');
			break;
		case false:
			showElementById('starttls_tr','hide');
			showElementById('tls_certcheck_tr','hide');
			showElementById('tls_use_default_trust_file_tr','hide');
			showElementById('tls_trust_file_tr','hide');
			showElementById('tls_fingerprint_tr','hide');
			showElementById('tls_crl_file_tr','hide');
			showElementById('tls_cert_file_tr','hide');
			showElementById('tls_key_file_tr','hide');
			break;
	}
}
function tls_certcheck_change() {
	switch (document.iform.tls_certcheck.checked) {
		case true:
			showElementById('tls_use_default_trust_file_tr','show');
			tls_use_default_trust_file_change();
			showElementById('tls_fingerprint_tr','show');
			showElementById('tls_crl_file_tr','show');
			break;
		case false:
			showElementById('tls_use_default_trust_file_tr','hide');
			showElementById('tls_trust_file_tr','hide');
			showElementById('tls_fingerprint_tr','hide');
			showElementById('tls_crl_file_tr','hide');
			break;
	}
}
function tls_use_default_trust_file_change() {
	switch (document.iform.tls_use_default_trust_file.checked) {
		case true:
			showElementById('tls_trust_file_tr','hide');
			break;
		case false:
			showElementById('tls_trust_file_tr','show');
			break;
	}
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
			ins_tabnav_record('system_email.php',gettext('Email'),gettext('Reload page'),true)->
			ins_tabnav_record('system_email_reports.php',gettext('Email Reports'))->
			ins_tabnav_record('system_monitoring.php',gettext('Monitoring'))->
			ins_tabnav_record('system_swap.php',gettext('Swap'))->
			ins_tabnav_record('system_rc.php',gettext('Command Scripts'))->
			ins_tabnav_record('system_cron.php',gettext('Cron'))->
			ins_tabnav_record('system_loaderconf.php',gettext('loader.conf'))->
			ins_tabnav_record('system_rcconf.php',gettext('rc.conf'))->
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'))->
			ins_tabnav_record('system_syslogconf.php',gettext('syslog.conf'));
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(!empty($failmsg)):
		print_error_box($failmsg);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('System Email Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('from',gettext('From Email Address'),$pconfig['from'],gettext('From email address for sending system messages.'),true,62);
			html_inputbox2('sendto',gettext('To Email Address'),$pconfig['sendto'],gettext('Destination email address. Separate email addresses by semi-colon.'),true,62);
			html_inputbox2('server',gettext('SMTP Server'),$pconfig['server'],gettext('Outgoing SMTP mail server address.'),true,62);
			html_inputbox2('port',gettext('Port'),$pconfig['port'],gettext('The default SMTP mail server port, usually port 25 or port 587.'),true,5);
?>
		</tbody>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('SMTP Authentication'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('auth',gettext('Authentication'),!empty($pconfig['auth']) ? true : false,gettext('Enable SMTP authentication.'));
			html_inputbox2('username',gettext('Username'),$pconfig['username'],'',true,40);
			html_passwordconfbox2('password','passwordconf',gettext('Password'),$pconfig['password'],$pconfig['passwordconf'],'',true);
			html_combobox2('authmethod',gettext('Authentication Method'),$pconfig['authmethod'],$l_authmethod,'',true);
?>
		</tbody>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_separator2();
			html_titleline2(gettext('Transport Layer Security (TLS)'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('security',gettext('Use TLS'),!empty($pconfig['security']),gettext('Enable SSL/TLS for secured connections. You also need to configure the TLS trust file. For some servers you may need to disable STARTTLS.'),'',false);
			html_checkbox2('starttls',gettext('Enable STARTTLS'),!empty($pconfig['starttls']),gettext('Start TLS from within the session.'),'',false);
			html_checkbox2('tls_certcheck',gettext('TLS Server Certificate Check'),!empty($pconfig['tls_certcheck']),gettext('Enable checks of the server certificate.'),'',false);
			html_checkbox2('tls_use_default_trust_file',gettext('Use Default TLS Trust File'),!empty($pconfig['tls_use_default_trust_file']),gettext('Use default TLS trust file /usr/local/etc/ssl/cert.pem.'),'',false);
			html_filechooser2('tls_trust_file',gettext('TLS Trust File'),$pconfig['tls_trust_file'],gettext('The name of the TLS trust file. The file must be in PEM format containing one or more certificates of trusted Certification Authorities (CAs).'),$g['media_path'],false,60);
			html_inputbox2('tls_fingerprint',gettext('TLS Fingerprint'),$pconfig['tls_fingerprint'],gettext('Set the fingerprint of a single certificate to accept for TLS.'),false,60);
			html_filechooser2('tls_crl_file',gettext('TLS CRL File'),$pconfig['tls_crl_file'],gettext('Certificate revocation list (CRL) file for TLS, to check for revoked certificates.'),$g['media_path'],false,60);
			html_filechooser2('tls_cert_file',gettext('TLS Cert File'),$pconfig['tls_cert_file'],gettext('Send a client certificate to the server (use this together with option TLS Key File). The file must contain a certificate in PEM format.'),$g['media_path'],false,60);
			html_filechooser2('tls_key_file',gettext('TLS Key File'),$pconfig['tls_key_file'],gettext('Send a client certificate to the server (use this together with option TLS Cert File). The file must contain the private key of a certificate in PEM format.'),$g['media_path'],false,60);
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		echo html_button('save',gettext('Save'));
		echo html_button('sendtestemail',gettext('Send Test Email'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
