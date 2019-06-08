<?php
/*
	system_backup.php

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

//	omit no-cache headers because it confuses IE with file downloads
$omit_nocacheheaders = true;

$current_password = $config['system']['password'];
$old_default_password = decrypt_aes256cbc('xigmanas','U2FsdGVkX1/Sq3ZsgO/H88X9ItjefBcv5eJxCjvOqwg=');
$new_default_password = $g['default_passwd'];
if(password_verify($new_default_password,$current_password) || password_verify($old_default_password,$current_password)):
	$errormsg = gtext('Current system password is using the default password. You should choose a different password.');
endif;
if($_POST):
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'download':
				break;
			default:
				header('Location: system_backup.php');
				exit;
				break;
		endswitch;
	endif;
	unset($errormsg);
	unset($input_errors);
	$pconfig['encryption'] = isset($_POST['encryption']);
	$pconfig['encrypt_password'] = $_POST['encrypt_password'] ?? '';
	$pconfig['encrypt_password_confirm'] = $_POST['encrypt_password_confirm'] ?? '';
	if($pconfig['encryption']):
		$reqdfields = ['encrypt_password','encrypt_password_confirm'];
		$reqdfieldsn = [gtext('Encrypt password'),gtext('Encrypt password (confirmed)')];
		$reqdfieldst = ['password','password'];
		do_input_validation($pconfig,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($pconfig,$reqdfields, $reqdfieldsn,$reqdfieldst,$input_errors);
	endif;
	if(empty($input_errors)):
		if($pconfig['encryption']):
			if($pconfig['encrypt_password'] !== $pconfig['encrypt_password_confirm']):
				$input_errors[] = gtext('The passwords do not match. Please try again.');
			endif;
		endif;
	endif;
	if(empty($input_errors)):
		$config['lastconfigbackup'] = time();
		write_config();
		config_lock();
		if(function_exists('date_default_timezone_set') and function_exists('date_default_timezone_get')):
			@date_default_timezone_set(@date_default_timezone_get());
		endif;
		if($pconfig['encryption']):
			$destination_file_name = sprintf('config-%s.%s-%s.gz',$config['system']['hostname'],$config['system']['domain'],date('YmdHis'));
			$password = $_POST['encrypt_password'];
			$data = config_encrypt($password);
		else:
			$destination_file_name = sprintf('config-%s.%s-%s.xml',$config['system']['hostname'],$config['system']['domain'],date('YmdHis'));
			$source_file_name = sprintf('%s/config.xml',$g['conf_path']);
			$data = file_get_contents($source_file_name);
		endif;
		header('Content-Type: application/octet-stream');
		header(sprintf('Content-Disposition: attachment; filename="%s"',$destination_file_name));
		header(sprintf('Content-Length: %d',strlen($data)));
		header('Pragma: hack');
		echo $data;
		config_unlock();
		exit;
	endif;
else:
	$pconfig['encryption'] = true;
	$pconfig['encrypt_password'] = '';
	$pconfig['encrypt_password_confirm'] = '';
endif;
$pgtitle = [gtext('System'),gtext('Backup Configuration'),gtext('Backup')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	function encrypt_change(encrypt_change) {
		var val = !($('#encryption').prop('checked') || encrypt_change);
		$('#encrypt_password').prop('disabled', val);
		$('#encrypt_password_confirm').prop('disabled', val);
		if (!encrypt_change) {
			if (val) {
				// disabled
				$('#encrypt_password_tr td:first').removeClass('vncellreq').addClass('vncell');
			} else {
				// enabled
				$('#encrypt_password_tr td:first').removeClass('vncell').addClass('vncellreq');
			}
		}
	}
	$('#encryption').click(function(){
		encrypt_change(false);
	});
	$('input:submit').click(function(){
		encrypt_change(true);
	});
	encrypt_change(false);
});
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('system_backup.php',gettext('Backup Configuration'),gettext('Reload page'),true)->
			ins_tabnav_record('system_restore.php',gettext('Restore Configuration'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('system_backup.php',gettext('Backup'),gettext('Reload page'),true)->
			ins_tabnav_record('system_backup_settings.php',gettext('Settings'));
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="system_backup.php" method="post" enctype="multipart/form-data" name="iform" id="iform">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
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
			html_titleline2(gettext('Backup Configuration'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('encryption',gettext('Use Encryption'),$pconfig['encryption'],gettext('Enable encryption.'));
			html_passwordconfbox2('encrypt_password','encrypt_password_confirm',gettext('Encryption Password'),'','','',false,25,false,gettext('Enter Password'),gettext('Confirm Password'));
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		echo html_button('download',gettext('Download Configuration'),'download');
?>
	</div>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),gettext('Encrypted configuration is automatically gzipped.'));
		html_remark2('warning',gettext('Warning'),gettext('It is recommended to encrypt your configuration and store it in a safe location.'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</form></td></tr></tbody></table>
<?php
include 'fend.inc';
?>
