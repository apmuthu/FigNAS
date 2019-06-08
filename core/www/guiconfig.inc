<?php
/*
	guiconfig.inc

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

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")) {
	@date_default_timezone_set(@date_default_timezone_get());
}

/* make sure nothing is cached */
if(isset($omit_nocacheheaders) && $omit_nocacheheaders):
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Pragma: no-cache");
endif;

/* parse the configuration and include all configuration functions */
require_once 'config.inc';
require_once 'functions.inc';
require_once 'rc.inc';
require_once 'wui.inc';
require_once 'wui2.php';
require_once 'updatenotify.inc';
require_once 'session.inc';

/* Set the current language */
system_language_load();

$d_fwupenabled_path = $g['varrun_path'] . "/fwup.enabled";
$d_firmwarelock_path = $g['varrun_path'] . "/firmware.lock";
$d_sysrebootreqd_path = $g['varrun_path'] . "/sysreboot.reqd";
$d_upnpconfdirty_path = $g['varrun_path'] . "/upnp.conf.dirty";
$d_packagesconfdirty_path = $g['varrun_path'] . "/packages.conf.dirty";

if(file_exists($d_firmwarelock_path)):
	if(!$d_isfwfile):
		header("Location: system_firmware.php");
		exit;
	else:
		return;
	endif;
endif;

/* Reserverd login names */
$reservedlogin = ['root','toor','daemon','operator','bin','tty','kmem','www','nobody','ftp','sshd'];

/* TCP flags */
$tcpflags = ["fin",'syn','rst','psh','ack','urg'];

/* default setting */
$input_errors = [];
$errormsg = "";
$savemsg = "";

/* AJAX misc */
function is_ajax() {
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])):
		if(strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'],'XMLHttpRequest') == 0):
			return true;
		endif;
	endif;
	return false;
}

function render_ajax($data,$result = 0) {
	$a = ['result' => $result];
	if(isset($data)):
		if(is_array($data)):
			$a = array_merge($a,$data);
		else:
			$a = array_merge($a,['data' => $data]);
		endif;
	endif;
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($a);
	exit;
}

function do_input_validation($postdata,$reqdfields,$reqdfieldsn,&$input_errors) {
	/* check for bad control characters */
	foreach($postdata as $pn => $pd):
		if(is_string($pd) && preg_match("/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f]/",$pd)):
			$input_errors[] = sprintf( gtext("The attribute '%s' contains invalid characters."),$pn);
		endif;
	endforeach;
	for($i = 0;$i < count($reqdfields);$i++):
		if(!isset($postdata[$reqdfields[$i]]) || ($postdata[$reqdfields[$i]] === "")):
			$input_errors[] = sprintf( gtext("The attribute '%s' is required."),$reqdfieldsn[$i]);
		endif;
	endfor;
}

/* Validate attribute type. */
function do_input_validation_type($postdata,$reqdfields,$reqdfieldsn,$reqdfieldst,&$input_errors) {
	/* Validate type. */
	for($i = 0;$i < count($reqdfields);$i++):
		if(isset($postdata[$reqdfields[$i]]) && ($postdata[$reqdfields[$i]] !== '')):
			$valid = false;
			$message = '';
			switch($reqdfieldst[$i]):
				case 'string':
					$valid = is_string($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' must be a string."),$reqdfieldsn[$i]);
					break;
				case 'numeric':
					$valid = is_numeric($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' must be a number."),$reqdfieldsn[$i]);
					break;
				case 'numericint':
					$valid = is_numericint($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' must be a number."),$reqdfieldsn[$i]);
					break;
				case 'ipaddr':
					$valid = is_ipaddr($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is not a valid IP address."),$reqdfieldsn[$i]);
					break;
				case 'macaddr':
					$valid = is_macaddr($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is not a valid MAC address."),$reqdfieldsn[$i]);
					break;
				case 'subnet':
					$valid = is_subnet($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is not a valid subnet mask."),$reqdfieldsn[$i]);
					break;
				case 'domain':
					$valid = is_domain($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid domain name."),$reqdfieldsn[$i]);
					break;
				case 'netbios':
					$valid = is_netbios($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid NetBIOS name."),$reqdfieldsn[$i]);
					break;
				case 'hostname':
					$valid = is_hostname($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid host name."),$reqdfieldsn[$i]);
					break;
				case 'workgroup':
					$valid = is_workgroup($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid workgroup name."),$reqdfieldsn[$i]);
					break;
				case 'filemode':
					$valid = is_filemode($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is not a valid file mode mask."),$reqdfieldsn[$i]);
					break;
				case 'mtu':
					$valid = is_mtu($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is invalid."),$reqdfieldsn[$i]);
					break;
				case 'port':
					$valid = is_port($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' is an invalid port number."),$reqdfieldsn[$i]);
					break;
				case 'password':
					$valid = is_validpassword($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' contains the illegal character ':' and is not a valid password."),$reqdfieldsn[$i]);
					break;
				case 'certificate':
					$valid = is_valid_certificate($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' does not appear to be a valid certificate."),$reqdfieldsn[$i]);
					break;
				case 'privatekey':
					$valid = is_valid_privatekey($postdata[$reqdfields[$i]],"RSA");
					$message = sprintf( gtext("The attribute '%s' does not appear to be a valid private key."),$reqdfieldsn[$i]);
					break;
				case 'privatedsakey':
					$valid = is_valid_privatekey($postdata[$reqdfields[$i]],"DSA");
					$message = sprintf( gtext("The attribute '%s' does not appear to be a valid private %s key."),$reqdfieldsn[$i],"DSA");
					break;
				case 'alias':
					$valid = is_validaliasname($postdata[$reqdfields[$i]]);
					$message = sprintf( gtext("The attribute '%s' may only consist of the characters a-z, A-Z, 0-9."),$reqdfieldsn[$i]);
					break;
				case 'time':
					$valid = (FALSE !== strptime($postdata[$reqdfields[$i]],"%H:%M:%S"));
					$message = sprintf( gtext("The attribute '%s' is not a valid time value."),$reqdfieldsn[$i]);
					break;
				case 'array':
					$valid = (is_array($postdata[$reqdfields[$i]]) && !empty($postdata[$reqdfields[$i]]));
					$message = sprintf( gtext("The attribute '%s' must contain at least one entry."),$reqdfieldsn[$i]);
					break;
			endswitch;
			if(!$valid):
				$input_errors[] = $message;
			endif;
		endif;
	endfor;
}

function do_input_validate_synctime($postdata,&$input_errors) {
	$data = [];
	$data[] = ['field' => 'minute','all' => 'all_mins','text' => gtext('Minutes')];
	$data[] = ['field' => 'hour','all' => 'all_hours','text' => gtext('Hours')];
	$data[] = ['field' => 'day','all' => 'all_days','text' => gtext('Days')];
	$data[] = ['field' => 'month','all' => 'all_months','text' => gtext('Months')];
	$data[] = ['field' => 'weekday','all' => 'all_weekdays','text' => gtext('Week Days')];

	foreach($data as $datak => $datav):
		if(!$postdata[$datav['all']]):
			if(!isset($postdata[$datav['field']]) || empty($postdata[$datav['field']])):
				$input_errors[] = sprintf(gtext("You have to select at least one item for '%s'."),$datav['text']);
			endif;
		endif;
	endforeach;
}

function print_input_errors($input_errors) {
	global $g_img;

	$id = 'errorbox';
	$src = $g_img['box.error'];
	$alt = '';
	echo '<div id="',$id,'">',PHP_EOL;
	echo '<div class="mbcl-1">',PHP_EOL;
	echo '<div class="mbcl-2 mbci-min">',PHP_EOL;
	echo '<div class="icon mbci-min">',PHP_EOL;
	echo '<img src="',$src,'" alt="',$alt,'">',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '<div class="message mbci-max">',PHP_EOL;
	echo '<div class="mbcl-3 mbci-min">',PHP_EOL;
	echo '<div>',gtext('The following input errors were detected'),':',PHP_EOL;
	echo '<ul>',PHP_EOL;
	foreach($input_errors as $msg):
		echo '<li>',$msg,'</li>',PHP_EOL;
	endforeach;
	echo '</ul>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
}

function verify_xz_file($fname) {
	$returnvar = mwexec("/usr/bin/xz -t " . escapeshellarg($fname),true);
	if($returnvar != 0):
		return 0;
	else:
		return 1;
	endif;
}

function verify_gzip_file($fname) {
	$returnvar = mwexec("/usr/bin/gzip -t " . escapeshellarg($fname),true);
	if($returnvar != 0):
		return 0;
	else:
		return 1;
	endif;
}

function print_core_box($type,$msg,$mbcl1i2 = NULL) {
	global $g_img;

	switch ($type):
		case 'error':
			$id = 'errorbox';
			$src = $g_img['box.error'];
			$alt = '';
			break;
		case 'info':
			$id = 'infobox';
			$src = $g_img['box.info'];
			$alt = '';
			break;
		case 'warning':
			$id = 'warningbox';
			$src = $g_img['box.warning'];
			$alt = '';
			break;
	endswitch;
	echo '<div id="',$id,'">',PHP_EOL;
	echo '<div class="mbcl-1">',PHP_EOL;
	echo '<div class="mbcl-2 mbci-min">',PHP_EOL;
	echo '<div class="icon mbci-min">',PHP_EOL;
	echo '<img src="',$src,'" alt="',$alt,'">',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '<div class="message mbci-max">',PHP_EOL;
	echo '<div class="mbcl-3 mbci-min">',PHP_EOL;
	echo '<div>',$msg,'</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
	if(is_string($mbcl1i2)):
		echo '<div class="mbci-min">',PHP_EOL;
		echo '<div>',$mbcl1i2,'</div>',PHP_EOL;
		echo '</div>',PHP_EOL;
	endif;
	echo '</div>',PHP_EOL;
	echo '</div>',PHP_EOL;
}

function print_info_box($msg,$mbcl1i2 = NULL) {
	print_core_box('info',$msg,$mbcl1i2);
}

function print_error_box($msg,$mbcl1i2 = NULL) {
	print_core_box('error',$msg,$mbcl1i2);
}

function print_warning_box($msg,$mbcl1i2 = NULL) {
	print_core_box('warning',$msg,$mbcl1i2);
}

function print_config_change_box() {
	$message = gtext('Apply changes');
	$gt_info = gtext('The configuration has been changed.')
		. '<br />'
		. gtext('You must apply the changes in order for them to take effect.')
		. '<br />'
		. '<b>'
		. '<a href="' . 'diag_log.php' . '">'
		. gtext('If this message persist take a look at the system log for more information.')
		. '</a>'
		. '</b>';
	$ab_info = sprintf('<input name="apply" type="submit" class="formbtn" id="apply" value="%s"/>',$message);
	echo '<div id="applybox">';
	print_info_box($gt_info,$ab_info);
	echo '</div>';
}
function get_std_save_message($ok) {
	global $d_sysrebootreqd_path;

	if($ok == 0):
		if(file_exists($d_sysrebootreqd_path)):
			$gt_ret = gtext('The changes have been saved.')
			. ' '
			. '<a href="' . 'reboot.php' . '">'
			. gtext('You have to reboot the system for the changes to take effect.')
			. '</a>';
		else:
			$gt_ret = gtext('The changes have been applied successfully.');
		endif;
	else:
		$gt_ret = gtext('Error')
			. ': '
			. gtext('The changes could not be applied')
			. ' ('
			. gtext('Error Code')
			. ' '
			. sprintf('%s',$ok)
			. ').';
	endif;
	return $gt_ret;
}
function calc_skipviewmode($page_mode) : array {
	global $config;

	switch($page_mode):
		case PAGE_MODE_EDIT:
			$new_page_mode = PAGE_MODE_EDIT;
			break;
		default:
			if(is_bool($test = $config['system']['skipviewmode'] ?? false) ? $test : true):
				$new_page_mode = PAGE_MODE_EDIT;
			else:
				$new_page_mode = PAGE_MODE_VIEW;
			endif;
			break;
	endswitch;
	return [$new_page_mode,$new_page_mode === PAGE_MODE_VIEW];
}
function html_inputbox($ctrlname,$title,$value,$desc,$required = false,$size = 40,$readonly = false) {
	$ctrl = new HTMLEditBox($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_passwordbox($ctrlname,$title,$value,$desc,$required = false,$size = 25,$readonly = false) {
	$ctrl = new HTMLPasswordBox($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_passwordconfbox($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$desc,$required = false,$size = 25,$readonly = false) {
	$ctrl = new HTMLPasswordConfBox($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_textarea($ctrlname,$title,$value,$desc,$required = false,$columns = 40,$rows = 5,$readonly = false,$wrap = true) {
	$ctrl = new HTMLTextArea($ctrlname,$title,$value,$desc,$columns,$rows);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetWrap($wrap);
	$ctrl->Render();
}

function html_filechooser($ctrlname,$title,$value,$desc,$path,$required = false,$size = 67,$readonly = false) {
	$ctrl = new HTMLFileChooser($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_combobox($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = "") {
	$ctrl = new HTMLComboBox($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_mountcombobox($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLMountComboBox($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_timezonecombobox($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLTimeZoneComboBox($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_checkbox($ctrlname,$title,$checked,$caption = "",$desc = "",$required = false,$onclick = "") {
	$ctrl = new HTMLCheckBox($ctrlname,$title,$checked,$caption,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_separator($colspan = 2,$idname = '') {
	$ctrl = new HTMLSeparator();
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline($title,$colspan = 2,$idname = '') {
	$ctrl = new HTMLTitleLine($title);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline_checkbox($ctrlname,$title,$value,$caption,$onclick = "",$colspan = 2) {
	$ctrl = new HTMLTitleLineCheckBox($ctrlname,$title,$value,$caption);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_text($ctrlname,$title,$text) {
	$ctrl = new HTMLText($ctrlname,$title,$text);
	$ctrl->Render();
}

function html_textinfo($ctrlname,$title,$text) {
	$ctrl = new HTMLTextInfo($ctrlname,$title,$text);
	$ctrl->Render();
}

function html_remark($ctrlname,$title,$text) {
	$ctrl = new HTMLRemark($ctrlname,$title,$text);
	$ctrl->Render();
}

function html_listbox($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = "") {
	$ctrl = new HTMLListBox($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_ipv4addrbox($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLIPv4AddressBox($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_ipv6addrbox($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLIPv6AddressBox($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_folderbox($ctrlname,$title,$value,$desc,$path,$required = false,$readonly = false) {
	$ctrl = new HTMLFolderBox($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_inputbox2($ctrlname,$title,$value,$desc,$required = false,$size = 40,$readonly = false,$altpadding = false,$maxlength = 0,string $placeholder = '') {
	$ctrl = new HTMLEditBox2($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetAltPadding($altpadding);
	$ctrl->SetMaxLength($maxlength);
	$ctrl->SetPlaceholder($placeholder);
	$ctrl->Compose()->render();
}

function html_passwordbox2($ctrlname,$title,$value,$desc,$required = false,$size = 25,$readonly = false,string $placeholder = '') {
	$ctrl = new HTMLPasswordBox2($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPlaceholder($placeholder);
	$ctrl->Compose()->render();
}

function html_passwordconfbox2($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$desc,$required = false,$size = 25,$readonly = false,string $placeholder = '',string $placeholderconf = '') {
	$ctrl = new HTMLPasswordConfBox2($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPlaceholder($placeholder);
	$ctrl->SetPlaceholderConfirm($placeholderconf);
	$ctrl->Compose()->render();
}

function html_textarea2($ctrlname,$title,$value,$desc,$required = false,$columns = 40,$rows = 5,$readonly = false,$wrap = true) {
	$ctrl = new HTMLTextArea2($ctrlname,$title,$value,$desc,$columns,$rows);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetWrap($wrap);
	$ctrl->Compose()->render();
}

function html_filechooser2($ctrlname,$title,$value,$desc,$path,$required = false,$size = 67,$readonly = false) {
	$ctrl = new HTMLFileChooser2($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Compose()->render();
}

function html_combobox2($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = "") {
	$ctrl = new HTMLComboBox2($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}

function html_radiobox2($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = '') {
	$ctrl = new HTMLRadioBox2($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}

function html_mountcombobox2($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLMountComboBox2($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_timezonecombobox2($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLTimeZoneComboBox2($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_languagecombobox2($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLLanguageComboBox2($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_interfacecombobox2($ctrlname,$title,$value,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLInterfaceComboBox2($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_checkbox2($ctrlname,$title,$checked,$caption = "",$desc = "",$required = false,$readonly = false,$onclick = "",$altpadding = false) {
	$ctrl = new HTMLCheckBox2($ctrlname,$title,$checked,$caption,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetAltPadding($altpadding);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}

function html_separator2($colspan = 2,$ctrlname = '') {
	$ctrl = new HTMLSeparator2();
	$ctrl->SetColSpan($colspan);
	$ctrl->SetCtrlName($ctrlname);
	$ctrl->Compose()->render();
}

function html_titleline2($title,$colspan = 2,$ctrlname = '') {
	$ctrl = new HTMLTitleLine2($ctrlname,$title,'');
	$ctrl->SetColSpan($colspan);
	$ctrl->Compose()->render();
}

function html_titleline_checkbox2($ctrlname,$title,$value,$caption,$onclick = "",$colspan = 2) {
	$ctrl = new HTMLTitleLineCheckBox2($ctrlname,$title,$value,$caption);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}

function html_text2($ctrlname,$title,$text) {
	$ctrl = new HTMLText2($ctrlname,$title,$text);
	$ctrl->Compose()->render();
}

function html_textinfo2($ctrlname,$title,$text) {
	$ctrl = new HTMLTextInfo2($ctrlname,$title,$text);
	$ctrl->Compose()->render();
}

function html_remark2($ctrlname,$title,$text) {
	$ctrl = new HTMLRemark2($ctrlname,$title,$text);
	$ctrl->Compose()->render();
}

function html_listbox2($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = "") {
	$ctrl = new HTMLListBox2($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}
function html_checkboxbox2($ctrlname,$title,$value,$options,$desc,$required = false,$readonly = false,$onclick = '') {
	$ctrl = new HTMLCheckboxBox2($ctrlname,$title,$value,$options,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Compose()->render();
}

function html_ipv4addrbox2($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLIPv4AddressBox2($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_ipv6addrbox2($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc,$required = false,$readonly = false) {
	$ctrl = new HTMLIPv6AddressBox2($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Compose()->render();
}

function html_folderbox2($ctrlname,$title,$value,$desc,$path,$required = false,$readonly = false) {
	$ctrl = new HTMLFolderBox2($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Compose()->render();
}

function html_minidlnabox2($ctrlname,$title,$value,$desc,$path,$required = false,$readonly = false) {
	$ctrl = new HTMLFolderBox12($ctrlname,$title,$value,$desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Compose()->render();
}
function html_button(string $value = NULL,string $content = NULL,string $id = NULL) {
	$element = 'button';
	$class = 'formbtn';
	if(is_null($value)):
		$value = 'cancel';
	endif;
	if(is_null($id)):
		$id = sprintf('%1$s_%2$s',$element,$value);
	endif;
	if(is_null($content)):
		$content = gettext('Cancel');
	endif;
	$button_attributes = [
		'name' => 'submit',
		'type' => 'submit',
		'class' => $class,
		'value' => $value,
		'id' => $id
	];
	if($value === 'cancel'):
		$button_attributes['formnovalidate'] = 'formnovalidate';
	endif;
	$root = new co_DOMDocument();
	$o_button = $root->addElement($element,$button_attributes,$content);
	return $root->get_html();
}
function html_row_toolbox(string $link = '',string $gt_record_mod = '',string $gt_record_del = '',string $gt_record_loc = '',bool $notprotected = true,bool $notdirty = true) {
	global $g_img;
	$root = new co_DOMDocument();
	$o = $root->addElement('td');
	if($notdirty && $notprotected): // editable
		$o_a = $o->addElement('a',['href' => $link]);
		$o_a->addElement('img',['src' => $g_img['mod'],'title' => $gt_record_mod,'alt' => $gt_record_mod,'class' => 'spin']);
	else:
		if($notprotected): // dirty
			$o->addElement('img',['src' => $g_img['del'],'title' => $gt_record_del,'alt' => $gt_record_del]);
		else: // protected
			$o->addElement('img',['src' => $g_img['loc'],'title' => $gt_record_loc,'alt' => $gt_record_loc]);
		endif;
	endif;
	return $root->get_html();
}
function get_weekday_names(bool $longnames = true,bool $localized = true) {
	global $g_weekdays,$g_weekdays_short,$g_weekdays_en,$g_weekdays_en_short;

	return $longnames ? ($localized ? $g_weekdays : $g_weekdays_en) : ($localized ? $g_weekdays_short : $g_weekdays_en_short);
}

function get_month_names(bool $longnames = true,bool $localized = true) {
	global $g_months,$g_month_short,$g_month_en,$g_month_en_short;

	return $longnames ? ($localized ? $g_month : $g_month_en) : ($localized ? $g_month_short : $g_month_en_short);
}
function gentitle(array $title = []) {
	$navlevelsep = htmlspecialchars(' > '); // Navigation level separator string.
	return implode($navlevelsep,$title);
}
function genhtmltitle(array $title = []) {
	return htmlspecialchars(system_get_hostname()) . (empty($title) ? '' : ' - ' . gentitle($title));
}
/**
 *	provides the header menu
 *	@return array $menu
 */
function get_headermenu(): array {
	global $g;
	global $config;
/*
 *	'type': 'external' = external, target = _blank
 *	'type': 'internal' = internal, target = _self
 *	'type': 'separator' = separator
 *	'description': description, gettext(string)
 *	'link': link, string
 *	'visible': visible, boolean
 */
	if(empty($_SESSION['g']['headermenu'])):
		//	create new menu
		$menu = [];
		$isAdminSession = Session::isAdmin();
		//	home
		$menu_name = 'home';
		$menu[$menu_name] = ['type' => 'internal','visible' => true,'img' => '/images/home.png','description' => gettext('Home'),'link' => '/index.php','menuitem' => []];
		//	system
		$menu_name = 'system';
		$menu[$menu_name] = ['type' => 'nolink','visible' => true,'img' => '','description' => gettext('System'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('General'),'link' => '/system.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Advanced'),'link' => '/system_advanced.php'];
		$menu_items[] = ['type' => 'internal','visible' => !$isAdminSession,'description' => gettext('Password'),'link' => '/userportal_system_password.php'];
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		if('full' == $g['platform']):
			if($g['zroot']):
				$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Firmware Update'),'link' => '/system_firmware.php'];
			endif;
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Packages'),'link' => '/system_packages.php'];
		else:
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Firmware Update'),'link' => '/system_firmware.php'];
		endif;
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Backup/Restore'),'link' => '/system_backup.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Factory Defaults'),'link' => '/system_defaults.php'];
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Reboot'),'link' => '/reboot.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Shutdown'),'link' => '/shutdown.php'];
		$menu_items[] = ['type' => 'separator','visible' => true,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => true,'description' => gettext('Logout'),'link' => '/logout.php'];
		unset($menu_items);
		//	network
		$menu_name = 'network';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Network'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Interface Management'),'link' => '/interfaces_assign.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('LAN Management'),'link' => '/interfaces_lan.php'];
		for($i = 1;isset($config['interfaces']['opt' . $i]);$i++):
			$desc = filter_var($config['interfaces']['opt' . $i]['descr'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => sprintf('OPT%d',$i),'regexp' => '/\S/']]);
			$link = '/interfaces_opt.php?index=' . $i;
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => $desc,'link' => $link];
		endfor;
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Hosts'),'link' => '/system_hosts.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Static Routes'),'link' => '/system_routes.php'];
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Proxy'),'link' => '/system_proxy.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Firewall'),'link' => '/system_firewall.php'];
		unset($menu_items);
		//	disks
		$menu_name = 'disks';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Disks'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Management'),'link' => '/disks_manage.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Software RAID'),'link' => '/disks_raid_geom.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('ZFS'),'link' => '/disks_zfs_zpool.php'];
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Encryption'),'link' => '/disks_crypt.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Mount Point'),'link' => '/disks_mount.php'];
		unset($menu_items);
		//	services
		$menu_name = 'services';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Services'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		if('dom0' !== $g['arch']):
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('HAST'),'link' => '/services_hast.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Samba AD'),'link' => '/services_samba_ad.php'];
			$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('CIFS/SMB'),'link' => '/services_samba.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('FTP'),'link' => '/services_ftp.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('TFTP'),'link' => '/services_tftp.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('SSH'),'link' => '/services_sshd.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('NFS'),'link' => '/services_nfs.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('AFP'),'link' => '/services_afp.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Rsync'),'link' => '/services_rsyncd.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Syncthing'),'link' => '/services_syncthing.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Unison'),'link' => '/services_unison.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('iSCSI Target CTL'),'link' => '/services_ctl.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('iSCSI Target ISTGT'),'link' => '/services_iscsitarget.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('DLNA/UPnP'),'link' => '/services_fuppes.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('iTunes/DAAP'),'link' => '/services_daap.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Dynamic DNS'),'link' => '/services_dynamicdns.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('SNMP'),'link' => '/services_snmp.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('UPS'),'link' => '/services_ups.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Webserver'),'link' => '/services_websrv.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('MariaDB'),'link' => '/services_mariadb.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('BitTorrent'),'link' => '/services_bittorrent.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('LCDproc'),'link' => '/services_lcdproc.php'];
		else:
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('SSH'),'link' => '/services_sshd.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('NFS'),'link' => '/services_nfs.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('iSCSI Target'),'link' => '/services_iscsitarget.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('UPS'),'link' => '/services_ups.php'];
		endif;
		unset($menu_items);
		//	virtualization
		if('x64' == $g['arch']):
			$menu_name = 'vm';
			$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Virtualization'),'link' => '','menuitem' => []];
			$menu_items = &$menu[$menu_name]['menuitem'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('VirtualBox'),'link' => '/vm_vbox.php'];
			unset($menu_items);
		endif;
		//	access
		$menu_name = 'access';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Access'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Users & Groups'),'link' => '/access_users.php'];
		if('dom0' !== $g['arch']):
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Active Directory'),'link' => '/access_ad.php'];
			$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('LDAP'),'link' => '/access_ldap.php'];
			$menu_items[] = ['type' => 'internal','visible' => false,'description' => gettext('NIS'),'link' => '/notavailable.php'];
		endif;
		unset($menu_items);
		//	status
		$menu_name = 'status';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Status'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('System'),'link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Processes'),'link' => '/status_process.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Services'),'link' => '/status_services.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Interfaces'),'link' => '/status_interfaces.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Disks'),'link' => '/status_disks.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Monitoring'),'link' => '/status_graph.php'];
		unset($menu_items);
		//	tools
		$menu_name = 'tools';
		$menu[$menu_name] = ['type' => 'nolink','visible' => true,'img' => '','description' => gettext('Tools'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('File Editor'),'link' => '/system_edit.php'];
		if(!isset($config['system']['disablefm'])):
			$menu_items[] = ['type' => 'internal','visible' => true,'description' => gettext('File Manager'),'link' => '/quixplorer/system_filemanager.php'];
		endif;
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Command'),'link' => '/exec.php'];
		unset($menu_items);
		//	extensions
		$menu_name = 'extensions';
		$menu[$menu_name] = ['type' => 'nolink','visible' => false,'img' => '','description' => gettext('Extensions'),'link' => '','menuitem' => []];
		//	diagnostics
		$menu_name = 'diagnostics';
		$menu[$menu_name] = ['type' => 'nolink','visible' => $isAdminSession,'img' => '','description' => gettext('Diagnostics'),'link' => '','menuitem' => []];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Log'),'link' => '/diag_log.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Information'),'link' => '/diag_infos_disks.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Updatenotifier'),'link' => '/diag_updatenotifier.php'];
		$menu_items[] = ['type' => 'separator','visible' => $isAdminSession,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Ping/Traceroute'),'link' => '/diag_ping.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('ARP Tables'),'link' => '/diag_arp.php'];
		$menu_items[] = ['type' => 'internal','visible' => $isAdminSession,'description' => gettext('Routes'),'link' => '/diag_routes.php'];
		unset($menu_items);
		//	help
		$menu_name = 'help';
		$menu[$menu_name] = ['type' => 'nolink','visible' => true,'img' => '','description' => gettext('Help'),'link' => ''];
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items[] = ['type' => 'external','visible' => true,'description' => gettext('Forum'),'link' => 'https://www.xigmanas.com/forums/'];
		$menu_items[] = ['type' => 'external','visible' => true,'description' => gettext('Information & Manual'),'link' => 'https://www.xigmanas.com/wiki/doku.php'];
		$menu_items[] = ['type' => 'external','visible' => true,'description' => gettext('IRC Live Support'),'link' => 'https://webchat.freenode.net/?channels=#xigmanas'];
		$menu_items[] = ['type' => 'separator','visible' => true,'description' => '','link' => '/index.php'];
		$menu_items[] = ['type' => 'internal','visible' => true,'description' => gettext('Release Notes'),'link' => '/changes.php'];
		$menu_items[] = ['type' => 'internal','visible' => true,'description' => gettext('License & Credits'),'link' => '/license.php'];
		$menu_items[] = ['type' => 'external','visible' => true,'description' => gettext('Donate'),'link' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=info%40xigmanas%2ecom&lc=US&item_name=XigmaNAS&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted'];
		unset($menu_items);
 		$_SESSION['g']['headermenu'] = $menu;
	endif;
	return $_SESSION['g']['headermenu'];
}
function make_headermenu_extensions(array &$menu) {
	global $g;
	global $config;

	$isAdminSession = Session::isAdmin();
	if(!$isAdminSession): // only admins allowed
		return;
	endif;
	if(isset($config['system']) && is_array($config['system']) && isset($config['system']['disableextensionmenu'])): // do nothing when view extension is disabled
		return;
	endif;
	$dir_path = $g['www_path'] . '/ext/'; // calculate the path to the extension folder
	if(!is_dir($dir_path)): // does it exist?
		return;
	endif;
	if(false !== ($dir_handle = @opendir($dir_path))): // folder exists, open it
		$language = $config['system']['language'] ?? 'en_US';
		$hard_link_regex = '~^[a-z]+://~';
		$menu_name = 'extensions';
		$menu_items = &$menu[$menu_name]['menuitem'];
		$menu_items = []; // wipe menuitems, just to be safe
		while(false !== ($file_found_name = readdir($dir_handle))): // scan through each name
			$file_found_full_name = $dir_path . $file_found_name; // calculate full path of the name
			if(($file_found_name === '.') || ($file_found_name === '..')):
				continue;
			endif;
			$menu_file_full_name = $file_found_full_name . '/menu.inc'; // calculate the expected menu name
			if(file_exists($menu_file_full_name)):
				$previous_setting = libxml_use_internal_errors(true); // suppress exeptions
				libxml_clear_errors();
				$dom = new DOMDocument(); // import menu into DOM object, let it do it's job
				if(false !== ($dom->loadHTMLFile($menu_file_full_name,LIBXML_NOERROR || LIBXML_NOWARNING || LIBXML_HTML_NOIMPLIED || LIBXML_HTML_NODEFDTD))):
					$file_node_list = $dom->getElementsByTagName('a'); // get <a> elements
					$node_list_nolang = [];
					$node_list_lang = [];
					$node_list_en_US = [];
					foreach($file_node_list as $file_node_item):
						$lang = trim($file_node_item->getAttribute('lang')); // extract attribute lang
						if(empty($lang)): // no lang attribute found
							$node_list_nolang[] = $file_node_item;
						elseif($lang === $language): // localised menu item found
							$node_list_lang[] = $file_node_item;
						elseif($lang === 'en_US'): // fallback for missing localisation
							$node_list_en_US[] = $file_node_item;
						else:
							continue;
						endif;
					endforeach;
					if(!empty($node_list_nolang)): // highest priority to ensure compatibility to existing menus
						$node_list = $node_list_nolang;
					elseif(!empty($node_list_lang)): // localised menus
						$node_list = $node_list_lang;
					elseif(!empty($node_list_en_US)): // fallback when new languages have been added but menu.inc doesn't have it.
						$node_list = $node_list_en_US;
					else:
						$node_list = [];
					endif;
					foreach($node_list as $node_item):
						$link = trim($node_item->getAttribute('href')); // extract attribute href
						$description = $node_item->nodeValue; // get display value
						if(!empty($link)): // add target attribute when link looks like a hard link
							if(preg_match($hard_link_regex,$link)): // hard link?
								$ext_menu_item = ['visible' => $isAdminSession,'description' => $description,'type' => 'external','link' => $link];
							elseif(preg_match('~^[\./]~',$link)):
								$ext_menu_item = ['visible' => $isAdminSession,'description' => $description,'type' => 'internal','link' => $link];
							else:
								$ext_menu_item = ['visible' => $isAdminSession,'description' => $description,'type' => 'internal','link' => sprintf('/%s',$link)];
							endif;
							$menu_items[] = $ext_menu_item;
							$menu[$menu_name]['visible'] = $isAdminSession; // extension menu found, make extension menu header visible
						endif;
					endforeach;
				endif;
				libxml_clear_errors();
				libxml_use_internal_errors($previous_setting);
			endif;
		endwhile;
		unset($menu_items);
		closedir($dir_handle);
	endif;
}
function make_headermenu() {
	$hard_link_regex = '~^[a-z]+://~';
	$output = [];
	$menu = get_headermenu();
	make_headermenu_extensions($menu); // function cares about access rights itself
	$menu_list = ['home','system','network','disks','access','services','vm','status','diagnostics','extensions','tools','help'];
	$output[] = '<nav id="navhdr">';
	$output[] = '<ul>';
	foreach($menu_list as $menuid):
		if($menu[$menuid]['visible']): // render menu when visible
			$output[] = '<li>';
			$a_tag = [];
			switch($menu[$menuid]['type']):
				case 'external':
					$a_tag['href'] = sprintf('href="%s"',htmlspecialchars($menu[$menuid]['link']));
					$a_tag['target'] = sprintf('target="%s"','_blank');
					break;
				case 'internal':
					$a_tag['href'] = sprintf('href="%s"',htmlspecialchars($menu[$menuid]['link']));
					$a_tag['onclick'] = sprintf('onclick="%s"','spinner()');
					break;
				case 'nolink':
					$a_tag['onclick'] = 'onclick=""';
					break;
			endswitch;
			$tags = implode(' ',$a_tag);
			if(empty($menu[$menuid]['img'])):
				$value = htmlspecialchars($menu[$menuid]['description']);
			else:
				$value = sprintf('<img src="%1$s" title="%2$s" alt="%2$s"/>',htmlspecialchars($menu[$menuid]['img']),htmlspecialchars($menu[$menuid]['description']));
			endif;
			if(empty($tags)):
				$output[] = sprintf('<a>%s</a>',$value);
			else:
				$output[] = sprintf('<a %s>%s</a>',$tags,$value);
			endif;
			if(!empty($menu[$menuid]['menuitem'])):
				$output[] = '<ul>';
				// Display menu items.
				foreach($menu[$menuid]['menuitem'] as $menu_item):
					if($menu_item['visible']): // render menuitem when visible
						$output[] = '<li>';
						$target = ('internal' === $menu_item['type']) ? '_self' : '_blank';
						if('separator' === $menu_item['type']):
							$output[] = '<span class="tabseparator"></span>';
						else:
							$link = $menu_item['link'];
							if(preg_match($hard_link_regex,$link)): // hard link = no spinner
								$output[] = sprintf('<a href="%1$s" target="%2$s" onclick="">%3$s</a>',htmlspecialchars($link),$target,htmlspecialchars($menu_item['description']));
							else: // local link = spinner
								$output[] = sprintf('<a href="%1$s" target="%2$s" onclick="spinner()">%3$s</a>',htmlspecialchars($link),$target,htmlspecialchars($menu_item['description']));
							endif;
						endif;
						$output[] = '</li>';
					endif;
				endforeach;
				$output[] = '</ul>';
			endif;
			$output[] = '</li>';
		endif;
	endforeach;
	$output[] = '</ul>';
	$output[] = '</nav>';
	return implode('',$output);
}
function display_headermenu() {
	echo make_headermenu();
	return;
}
