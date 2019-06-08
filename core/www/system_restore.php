<?php
/*
	system_restore.php

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
$cmd_system_reboot = false;
if($_POST):
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'restore':
				break;
			default:
				header('Location: system_restore.php');
				exit;
				break;
		endswitch;
	endif;
	unset($errormsg);
	unset($input_errors);

	$encrypted = false;
	if(is_uploaded_file($_FILES['conffile']['tmp_name'])):
		// Validate configuration backup
		$valid_config = false;
		if(pathinfo($_FILES['conffile']['name'],PATHINFO_EXTENSION) == 'gz'):
			$encrypted = true;
			$gz_config = file_get_contents($_FILES['conffile']['tmp_name']);
			$password = $_POST['decrypt_password'];
			$data = config_decrypt($password,$gz_config);
			if($data !== false):
				$tempfile = tempnam(sys_get_temp_dir(),'cnf');
				file_put_contents($tempfile, $data);
				$valid_config = validate_xml_config($tempfile,$g['xml_rootobj'],'nas4free');
				if(!$valid_config):
					unlink($tempfile);
				endif;
			endif;
		else:
			$valid_config = validate_xml_config($_FILES['conffile']['tmp_name'],$g['xml_rootobj'],'nas4free');
		endif;
		if(!$valid_config):
			$errormsg = gtext('The configuration could not be restored.') . ' ' . gtext('Invalid file format or incorrect password.');
		else:
			//	void loaderconf section to force read $config
			$loaderconf = &array_make_branch($config,'system','loaderconf');
			$loaderconf = [];
			// Install configuration backup
			if($encrypted):
				$ret = config_install($tempfile);
				unlink($tempfile);
			else:
				$ret = config_install($_FILES['conffile']['tmp_name']);
			endif;
			if($ret == 0):
				$cmd_system_reboot = true;
				$savemsg = sprintf(gtext('The configuration has been restored. The server is now rebooting.'));
			else:
				$errormsg = gtext('The configuration could not be restored.');
			endif;
		endif;
	else:
		$errormsg = sprintf(gtext('The configuration could not be restored. No file was uploaded!'),$g_file_upload_error[$_FILES['conffile']['error']]);
	endif;
endif;
$pgtitle = [gtext('System'),gtext('Restore Configuration')];
include 'fbegin.inc';
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('system_backup.php',gettext('Backup Configuration'))->
			ins_tabnav_record('system_restore.php',gettext('Restore Configuration'),gettext('Reload page'),true);
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="system_restore.php" method="post" enctype="multipart/form-data" name="iform" id="iform">
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
			html_titleline2(gettext('Restore Configuration'));
?>
		</thead>
		<tbody>
<?php
			html_passwordbox2('decrypt_password',gettext('Decryption Password'),'','',false,25,false,gettext('Enter Password'));
?>
		</tbody>
	</table>
	<div id="submit">
		<strong><?=gtext('Select configuration file:');?></strong>&nbsp;<input name="conffile" type="file" class="formfld" id="conffile" size="40"/>
	</div>
	<div id="submit">
<?php
		echo html_button('restore',gettext('Restore Configuration'),'restore');
?>
	</div>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),gettext('The server will reboot after the configuration was successfully restored.'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</form></td></tr></tbody></table>
<?php
include 'fend.inc';
if($cmd_system_reboot):
	ob_flush();
	flush();
	sleep(5);
	system_reboot();
endif;
?>
