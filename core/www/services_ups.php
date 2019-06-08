<?php
/*
	services_ups.php

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

array_make_branch($config,'ups','auxparam');
array_make_branch($config,'ups','ups2_auxparam');
$pconfig['enable'] = isset($config['ups']['enable']);
$pconfig['mode'] = $config['ups']['mode'];
$pconfig['masteruser'] = empty($config['ups']['masteruser']) ? 'root' : $config['ups']['masteruser']; // local master user
$pconfig['masterpassword'] = empty($config['ups']['masterpassword']) ? '' : $config['ups']['masterpassword']; // local master user password
$pconfig['upsname'] = $config['ups']['upsname'];
$pconfig['driver'] = !empty($config['ups']['driver']) ? $config['ups']['driver'] : '-----';
$pconfig['port'] = !empty($config['ups']['port']) ? $config['ups']['port'] : 'auto';
$pconfig['desc'] = !empty($config['ups']['desc']) ? $config['ups']['desc'] : '';
$pconfig['ups2'] = isset($config['ups']['ups2']);
$pconfig['ups2_upsname'] = !empty($config['ups']['ups2_upsname']) ? $config['ups']['ups2_upsname'] : '-----';
$pconfig['ups2_driver'] = !empty($config['ups']['ups2_driver']) ? $config['ups']['ups2_driver'] : '-----';
$pconfig['ups2_port'] = !empty($config['ups']['ups2_port']) ? $config['ups']['ups2_port'] : 'auto';
$pconfig['ups2_desc'] = !empty($config['ups']['ups2_desc']) ? $config['ups']['ups2_desc'] : '';
$pconfig['ip'] = !empty($config['ups']['ip']) ? $config['ups']['ip'] : 'localhost';
$pconfig['shutdownmode'] = $config['ups']['shutdownmode'];
$pconfig['shutdowntimer'] = $config['ups']['shutdowntimer'];
$pconfig['remotemonitor'] = isset($config['ups']['remotemonitor']);
$pconfig['monitoruser'] = !empty($config['ups']['monitoruser']) ? $config['ups']['monitoruser'] : 'upsmon'; // remote monitoring user name
if($pconfig['monitoruser'] == 'root'):
	$pconfig['monitoruser'] = 'upsmon'; // prevent using root twice - as master and monitoring user
endif;
$pconfig['monitorpassword'] = !empty($config['ups']['monitorpassword']) ? $config['ups']['monitorpassword'] : 'upsmon'; // remote monitoring user password
$pconfig['email_enable'] = isset($config['ups']['email']['enable']);
$pconfig['email_to'] = $config['ups']['email']['to'];
$pconfig['email_subject'] = $config['ups']['email']['subject'];
if(isset($config['ups']['auxparam']) && is_array($config['ups']['auxparam'])):
	$pconfig['auxparam'] = implode(PHP_EOL,$config['ups']['auxparam']);
endif;
if(isset($config['ups']['ups2_auxparam']['auxparam']) && is_array($config['ups']['ups2_auxparam']['auxparam'])):
	$pconfig['ups2_auxparam'] = implode(PHP_EOL,$config['ups']['ups2_auxparam']['auxparam']);
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
//	input validation.
	if(isset($_POST['enable']) && $_POST['enable']):
		$reqdfields = ['upsname','driver','port','shutdownmode','monitoruser','monitorpassword'];
		$reqdfieldsn = [gtext('Identifier'),gtext('Driver'),gtext('Port'),gtext('Shutdown Mode'),gtext('Username'),gtext('Password')];
		$reqdfieldst = ['alias','string','string','string','string','string'];
		if('onbatt' === $_POST['shutdownmode']):
			$reqdfields = array_merge($reqdfields,['shutdowntimer']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Shutdown Timer')]);
			$reqdfieldst = array_merge($reqdfieldst,['numericint']);
		endif;
		if(!empty($_POST['email_enable'])):
			$reqdfields = array_merge($reqdfields,['email_to','email_subject']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('To Email Address'),gtext('Subject')]);
			$reqdfieldst = array_merge($reqdfieldst,['string','string']);
		endif;
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
	endif;
	if(empty($input_errors)):
		$config['ups']['enable'] = isset($_POST['enable']);
		$config['ups']['mode'] = $_POST['mode'];
		$config['ups']['masteruser'] = $_POST['masteruser'] ?? $config['ups']['masteruser'] ?? 'root'; // local master user, usually root
		$config['ups']['masterpassword'] = $_POST['masterpassword'] ?? $config['ups']['masterpassword'] ?? ''; // local master user password
		$config['ups']['upsname'] = $_POST['upsname'];
		$config['ups']['driver'] = $_POST['driver'];
		$config['ups']['port'] = $_POST['port'];
		$config['ups']['desc'] = $_POST['desc'];
		$config['ups']['ups2'] = isset($_POST['ups2']);
		$config['ups']['ups2_upsname'] = $_POST['ups2_upsname'];
		$config['ups']['ups2_driver'] = $_POST['ups2_driver'];
		$config['ups']['ups2_port'] = $_POST['ups2_port'];
		$config['ups']['ups2_desc'] = $_POST['ups2_desc'];
		$config['ups']['ip'] = ($config['ups']['mode'] == 'master') ? 'localhost' : $_POST['ip'];
		$config['ups']['shutdownmode'] = $_POST['shutdownmode'];
		$config['ups']['shutdowntimer'] = $_POST['shutdowntimer'];
		$config['ups']['remotemonitor'] = isset($_POST['remotemonitor']);
		$config['ups']['monitoruser'] = $_POST['monitoruser'];
		$config['ups']['monitorpassword'] = $_POST['monitorpassword'];
		$config['ups']['email']['enable'] = isset($_POST['email_enable']);
		$config['ups']['email']['to'] = $_POST['email_to'];
		$config['ups']['email']['subject'] = $_POST['email_subject'];
		# Write additional parameters.
		unset($config['ups']['auxparam']);
		foreach (explode(PHP_EOL,$_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam,"\t\n\r");
			if(!empty($auxparam)):
				$config['ups']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		unset($config['ups']['ups2_auxparam']);
		if(isset($config['ups']['ups2'])):
			foreach(explode(PHP_EOL,$_POST['ups2_auxparam']) as $ups2_auxparam):
				$ups2_auxparam = trim($ups2_auxparam,"\t\n\r");
				if(!empty($ups2_auxparam)):
					$config['ups']['ups2_auxparam']['auxparam'][] = $ups2_auxparam;
				endif;
			endforeach;
		endif;
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service('nut');
			$retval |= rc_update_service('nut_upslog');
			$retval |= rc_update_service('nut_upsmon');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('UPS')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);

	if (enable_change.name == "email_enable") {
		endis = !enable_change.checked;
		document.iform.email_to.disabled = endis;
		document.iform.email_subject.disabled = endis;
	} else {
		document.iform.mode.disabled = endis;
		document.iform.masteruser.disabled = endis;
		document.iform.masterpassword.disabled = endis;
		document.iform.upsname.disabled = endis;
		document.iform.driver.disabled = endis;
		document.iform.port.disabled = endis;
		document.iform.auxparam.disabled = endis;
		document.iform.desc.disabled = endis;
		document.iform.ups2.disabled = endis;
		document.iform.ups2_upsname.disabled = endis;
		document.iform.ups2_driver.disabled = endis;
		document.iform.ups2_port.disabled = endis;
		document.iform.ups2_auxparam.disabled = endis;
		document.iform.ups2_desc.disabled = endis;
		document.iform.ip.disabled = endis;
		document.iform.shutdownmode.disabled = endis;
		document.iform.shutdowntimer.disabled = endis;
		document.iform.remotemonitor.disabled = endis;
		document.iform.monitoruser.disabled = endis;
		document.iform.monitorpassword.disabled = endis;
		document.iform.email_enable.disabled = endis;
		if (document.iform.enable.checked == true) {
			endis = !(document.iform.email_enable.checked || enable_change);
		}
		document.iform.email_to.disabled = endis;
		document.iform.email_subject.disabled = endis;
	}
}
function shutdownmode_change() {
	switch(document.iform.shutdownmode.value) {
		case "onbatt":
			showElementById('shutdowntimer_tr','show');
			break;
		default:
			showElementById('shutdowntimer_tr','hide');
			break;
	}
}
function mode_change() {
	switch(document.iform.mode.value) {
		case "slave":
			showElementById('masteruser_tr','hide');
			showElementById('masterpassword_tr','hide');
			showElementById('upsname_tr','show');
			showElementById('driver_tr','hide');
			showElementById('port_tr','hide');
			showElementById('auxparam_tr','hide');
			showElementById('desc_tr','show');
			showElementById('ups2_tr','hide');
			showElementById('ups2_upsname_tr','hide');
			showElementById('ups2_driver_tr','hide');
			showElementById('ups2_port_tr','hide');
			showElementById('ups2_auxparam_tr','hide');
			showElementById('ups2_desc_tr','hide');
			showElementById('ip_tr','show');
			showElementById('shutdownmode_tr','show');
			showElementById('shutdowntimer_tr','show');
			showElementById('remotemonitor_tr','hide');
			showElementById('monitoruser_tr','show');
			showElementById('monitorpassword_tr','show');
			break;
		default:
			showElementById('masteruser_tr','show');
			showElementById('masterpassword_tr','show');
			showElementById('upsname_tr','show');
			showElementById('driver_tr','show');
			showElementById('port_tr','show');
			showElementById('auxparam_tr','show');
			showElementById('desc_tr','show');
			showElementById('ups2_tr','show');
			if (document.iform.ups2.checked) {
				showElementById('ups2_upsname_tr','show');
				showElementById('ups2_driver_tr','show');
				showElementById('ups2_port_tr','show');
				showElementById('ups2_auxparam_tr','show');
				showElementById('ups2_desc_tr','show');
			} else {
				showElementById('ups2_upsname_tr','hide');
				showElementById('ups2_driver_tr','hide');
				showElementById('ups2_port_tr','hide');
				showElementById('ups2_auxparam_tr','hide');
				showElementById('ups2_desc_tr','hide');
			}
			showElementById('ip_tr','hide');
			showElementById('shutdownmode_tr','show');
			showElementById('shutdowntimer_tr','show');
			showElementById('remotemonitor_tr','show');
			if (document.iform.remotemonitor.checked) {
				showElementById('monitoruser_tr','show');
				showElementById('monitorpassword_tr','show');
			} else {
				showElementById('monitoruser_tr','hide');
				showElementById('monitorpassword_tr','hide');
			}
			break;
	}
}
function monitoring_change() {
	if (document.iform.remotemonitor.checked) {
		showElementById('monitoruser_tr','show');
		showElementById('monitorpassword_tr','show');
	} else {
		showElementById('monitoruser_tr','hide');
		showElementById('monitorpassword_tr','hide');
	}
}
function ups2_change() {
	if (document.iform.ups2.checked) {
		showElementById('ups2_upsname_tr','show');
		showElementById('ups2_driver_tr','show');
		showElementById('ups2_port_tr','show');
		showElementById('ups2_auxparam_tr','show');
		showElementById('ups2_desc_tr','show');
	} else {
		showElementById('ups2_upsname_tr','hide');
		showElementById('ups2_driver_tr','hide');
		showElementById('ups2_port_tr','hide');
		showElementById('ups2_auxparam_tr','hide');
		showElementById('ups2_desc_tr','hide');
	}
}
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_ups.php" method="post" name="iform" id="iform">
<?php
				if(!empty($pconfig['enable']) && !empty($pconfig['email_enable']) && (0 !== email_validate_settings())):
					$helpinghand = '<a href="' . 'system_email.php' . '">'
						. gtext('Make sure you have already configured your email settings')
						. '</a>.';
					print_error_box($helpinghand);
				endif;
				if(!empty($input_errors)):
					print_input_errors($input_errors);
				endif;
				if(!empty($savemsg)):
					print_info_box($savemsg);
				endif;
?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
<?php
					html_titleline_checkbox2('enable',gettext('Uninterruptible Power Supply'),!empty($pconfig['enable']) ? true : false,gettext('Enable'),'enable_change(false)');
					$l_mode = [
						'master' => gettext('Master'),
						'slave'=> gettext('Slave')
					];
					html_combobox2('mode',gettext('Mode'),!empty($config['ups']['mode']) ? $config['ups']['mode'] : 'Master',$l_mode,gettext('Choose UPS mode.'),true,false,'mode_change()');
					html_inputbox2('masteruser',gettext('Master User'),$pconfig['masteruser'],gettext('Enter the name of the master user account.'),false,25,false);
					html_passwordbox2('masterpassword',gettext('Master Password'),$pconfig['masterpassword'],gettext('Enter the password of the master user account.'),false,25);
					html_inputbox2('upsname',gettext('Identifier'),$pconfig['upsname'],gettext('This name is used to uniquely identify your UPS on this system.') . ' ' . gettext('In slave mode it is the UPS name (Identifier) at the UPS master.'),true,30);
					$helpinghand = gettext('The driver used to communicate with your UPS.')
						. ' '
						. '<a href="' . 'services_ups_drv.php' . '" target="_blank">'
						. gettext('Get a list of available drivers')
						. '</a>.';
					html_inputbox2('driver',gettext('Driver'),$pconfig['driver'],$helpinghand ,true,30);
					html_inputbox2('port',gettext('Port'),$pconfig['port'],gettext('The serial or USB port where your UPS is connected.'),true,30);
					html_textarea2('auxparam',gettext('Additional Parameters'),!empty($pconfig['auxparam']) ? $pconfig['auxparam'] : '',gettext('These parameters are added to the hardware-specific part of the driver.'),false,65,5,false,false);
					html_inputbox2('desc',gettext('Description'),$pconfig['desc'],gettext('You may enter a description here for your reference.'),false,65);
					html_checkbox2('ups2',gettext('UPS') . ' 2',!empty($pconfig['ups2']) ? true : false,gettext('Enable second local connected UPS.'),'',false,false,'ups2_change()');
					html_inputbox2('ups2_upsname',gettext('Identifier'),$pconfig['ups2_upsname'],gettext('This name is used to uniquely identify your second UPS on this system.') . ' ' . gettext('In slave mode it is the UPS name (Identifier) at the UPS master.'),true,30);
					$helpinghand = gettext('The driver used to communicate with your second UPS.')
						. ' '
						. '<a href="' . 'services_ups_drv.php' . '" target="_blank">'
						. gettext('Get a list of available drivers')
						. '</a>.';
					html_inputbox2('ups2_driver',gettext('Driver'),$pconfig['ups2_driver'],$helpinghand,true,30);
					html_inputbox2('ups2_port',gettext('Port'),$pconfig['ups2_port'],gettext('The serial or USB port where your second UPS is connected.'),true,30);
					html_textarea2('ups2_auxparam',gettext('Additional Parameters'),!empty($pconfig['ups2_auxparam']) ? $pconfig['ups2_auxparam'] : '',gettext('These parameters are added to the hardware-specific part of the driver for the second UPS.'),false,65,5,false,false);
					html_inputbox2('ups2_desc',gettext('Description'),$pconfig['ups2_desc'],gettext('You may enter a description here for your reference.'),false,65);
					html_inputbox2('ip',gettext('IP address'),$pconfig['ip'],gettext('The IP address of the UPS master.'),true,30);
					$l_shutdownmode = [
						'fsd' => gettext('UPS reaches low battery'),
						'onbatt' => gettext('UPS goes on battery')
					];
					html_combobox2('shutdownmode',gettext('Shutdown Mode'),$pconfig['shutdownmode'],$l_shutdownmode,gettext('Defines when the shutdown is initiated.'),true,false,'shutdownmode_change()');
					html_inputbox2('shutdowntimer',gettext('Shutdown Timer'),$pconfig['shutdowntimer'],gettext('The time in seconds until shutdown is initiated. If the UPS happens to come back before the time is up the shutdown is canceled.'),true,3);
					html_checkbox2('remotemonitor',gettext('Remote Monitoring'),!empty($pconfig['remotemonitor']) ? true : false,gettext('Enable remote monitoring of the local connected UPS.'),'',false,false,'monitoring_change()');
					html_inputbox2('monitoruser',gettext('Monitoring User'),$pconfig['monitoruser'],gettext('Remote monitoring username. Must be equal on both master and slave system.'),true,25);
					html_passwordbox2('monitorpassword',gettext('Monitoring Password'),$pconfig['monitorpassword'],gettext('Remote monitoring password. Must be equal on both master and slave system.'),true,25);
					html_separator2();
					html_titleline_checkbox2('email_enable',gettext('Email Report'),!empty($pconfig['email_enable']) ? true : false,gettext('Activate'),'enable_change(this)');
					html_inputbox2('email_to',gettext('To Email Address'),$pconfig['email_to'],sprintf('%s %s',gettext('Destination email address.'),gettext('Separate email addresses by semi-colon.')),true,65);
					html_inputbox2('email_subject',gettext('Subject'),$pconfig['email_subject'],gettext('The subject of the email.') . ' ' . gettext('You can use the following parameters for substitution:') . '</span><div id="enumeration"><ul><li>%d - ' . gettext('Date') . '</li><li>%h - ' . gettext('Hostname') . '</li></ul></div><span>',true,65);
?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
<?php
					$helpinghand = gettext('This configuration settings are used to generate the ups.conf configuration file which is required by the NUT UPS daemon.')
						. ' '
						. '<br><a href="' . 'https://www.networkupstools.org' . '" target="_blank">'
						. gettext('To get more information how to configure your UPS please check the NUT (Network UPS Tools) documentation')
						. '</a>.<br />';
					html_remark2('note',gettext('Note'),$helpinghand);
?>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
//<![CDATA[
monitoring_change();
mode_change();
shutdownmode_change();
enable_change(false);
ups2_change();
//]]>
</script>
<?php
include 'fend.inc';
