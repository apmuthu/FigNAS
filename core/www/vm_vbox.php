<?php
/*
	vm_vbox.php

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

array_make_branch($config,'vbox');
$pconfig['enable'] = isset($config['vbox']['enable']);
$pconfig['homedir'] = $config['vbox']['homedir'] ?? '';
$pconfig['allowusb'] = isset($config['vbox']['allowusb']);
$pconfig['allowserial'] = isset($config['vbox']['allowserial']);
$pconfig['allowusbserial'] = isset($config['vbox']['allowusbserial']);
$vbox_user = rc_getenv_ex('vbox_user','vboxusers');
$vbox_group = rc_getenv_ex('vbox_group','vboxusers');
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['enable'])):
		$reqdfields = ['homedir'];
		$reqdfieldsn = [gtext('Home Directory')];
		$reqdfieldst = ['string'];
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
	else:
		// disable VirtualBox
		config_lock();
		$retval |= rc_exec_script('/etc/rc.d/vbox onestop');
		config_unlock();
	endif;
	if(empty($input_errors)):
		$config['vbox']['enable'] = isset($_POST['enable']) ? true : false;
		$config['vbox']['homedir'] = $_POST['homedir'];
		$config['vbox']['allowusb'] = isset($_POST['allowusb']) ? true : false;
		$config['vbox']['allowserial'] = isset($_POST['allowserial']) ? true : false;
		$config['vbox']['allowusbserial'] = isset($_POST['allowusbserial']) ? true : false;
		$dir = $config['vbox']['homedir'];
		if($dir == '' || !file_exists($dir)):
			$dir = '/nonexistent';
		endif;
		// update homedir
		$user = $vbox_user;
		$group = $vbox_group;
		$opt = sprintf('-c "Virtualbox user" -d "%1$s" -s /usr/sbin/nologin',$dir);
		$index = array_search_ex($user,$config['system']['usermanagement']['user'],'name');
		if($index != false):
			$config['system']['usermanagement']['user'][$index]['extraoptions'] = $opt;
		endif;
		write_config();
		$retval = 0;
		config_lock();
		$retval |= rc_exec_service('userdb');
		config_unlock();
		if($dir != '/nonexistent' && file_exists($dir)):
			// adjust permission
			chmod($dir,0755);
			chown($dir,$user);
			chgrp($dir,$group);
			// update auth method
			$cmd = sprintf('/usr/local/bin/sudo -u %1$s /usr/local/bin/VBoxManage setproperty websrvauthlibrary null 2>&1',$user);
			mwexec2($cmd,$rawdata,$result);
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval |= rc_update_service('vbox');
				config_unlock();
			endif;
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
$pgtitle = [gtext('Virtualization'),gtext('VirtualBox')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php
	//	Init spinner on submit for id iform.
?>
	$("#iform").submit(function() { spinner(); });
});
//]]>
</script>
<form action="vm_vbox.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
	$enabled = isset($config['vbox']['enable']);
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline_checkbox2('enable',gettext('VirtualBox'),!empty($pconfig['enable']) ? true : false,gettext('Enable'),'');
?>
		</thead>
		<tbody>
<?php			
			html_filechooser2('homedir',gettext('Home Directory'),$pconfig['homedir'],gettext('Enter the path to the home directory of VirtualBox. VM config and HDD image will be created under the specified directory.'),$g['media_path'],false,60);
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
			html_titleline2(gettext('Host Resources'));
?>
		</thead>
		<tbody>
<?php			
			html_checkbox2('allowusb',gettext('Allow USB'),!empty($pconfig['allowusb']),gettext('Enable this option to make host USB available to clients.'));
			html_checkbox2('allowserial',gettext('Allow Serial Ports'),!empty($pconfig['allowserial']),gettext('Enable this option to make host serial ports available to clients.'));
			html_checkbox2('allowusbserial',gettext('Allow USB Serial Ports'),!empty($pconfig['allowusbserial']),gettext('Enable this option to make host USB serial ports available to clients.'));
			$desc = '<strong><font color="red">' . gtext('Security Warning') . '!</font> ' . gtext('Be careful with turning on these options.') . '</strong>';
			html_text2('warning',gettext('Warning'),$desc);
?>
		</tbody>
	</table>
<?php			
	if($enabled):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(sprintf('%s (%s)',gettext('Administrative WebGUI'),gettext('phpVirtualBox')));
?>
			</thead>
			<tbody>
<?php
				$url = '/phpvirtualbox/index.html';
				$link = sprintf('<a href="%1$s" id="a_url1" target="_blank">%1$s</a>',$url);
				html_text2('url1',gettext('URL'),$link);
?>
			</tbody>
		</table>
<?php
	endif;
?>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>"/>
	</div>
<?php
	include 'formend.inc';?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
