<?php
/*
	userportal_system_password.php

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
// Configure page permission
$pgperm['allowuser'] = true;

require_once 'auth.inc';
require_once 'guiconfig.inc';
require_once 'email.inc';

$a_user = &array_make_branch($config,'access','user');
//	Get user configuration. Ensure current logged in user is available,
//	otherwise exit immediatelly.
if(false === ($index_id = array_search_ex(Session::getUserId(),$a_user,'id'))):
	header('Location: logout.php');
	exit;
endif;
if($_POST):
	unset($input_errors);
	$reqdfields = ['password_old','password_new','password_confirm'];
	$reqdfieldsn = [gtext('Current password'),gtext('New password'),gtext('Password (confirmed)')];
	$reqdfieldst = ['password','password','password'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	//	Validate old password.
	if(!password_verify($_POST['password_old'],$a_user[$index_id]['passwordsha'])):
		$input_errors[] = gtext('The old password is not correct.');
	endif;
	//	Validate new password.
	if($_POST['password_new'] !== $_POST['password_confirm']):
		$input_errors[] = gtext('The confirmation password does not match. Please ensure the passwords match exactly.');
	endif;
	if(empty($input_errors)):
		$a_user[$index_id]['passwordsha'] = mkpasswd($_POST['password_new']);
		$a_user[$index_id]['passwordmd4'] = mkpasswdmd4($_POST['password_new']);
		write_config();
		updatenotify_set('userdb_user',UPDATENOTIFY_MODE_MODIFIED,$a_user[$index_id]['uuid']);
		//	Write syslog entry and send an email to the administrator
		$message = sprintf("The user [%s] has changed his password via user portal.\nPlease go to the user administration page and apply the changes.",Session::getUserName());
		write_log($message);
		if(0 == @email_validate_settings()):
			$subject = sprintf(gtext("Notification email from host: %s"),system_get_hostname());
			$sendto = $config['system']['email']['sendto'] ?? $config['system']['email']['from'] ?? '';
			if(preg_match('/\S/',$sendto)):
				@email_send($sendto,$subject,$message,$error);
			endif;
		endif;
		$savemsg = gtext('The administrator has been notified to apply your changes.');
	endif;
endif;
$pgtitle = [gtext('System'),gtext('Password')];
include 'fbegin.inc';
?>
<form action="userportal_system_password.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('User Password Settings'));
?>
		</thead>
		<tbody>
<?php
			html_passwordbox2('password_old',gettext('Current Password'),'','',true);
			html_passwordconfbox2('password_new','password_confirm',gettext('New Password'),'','','',true);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
