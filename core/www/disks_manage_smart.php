<?php
/*
	disks_manage_smart.php

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

$pgtitle = [gtext('Disks'),gtext('Management'),gtext('S.M.A.R.T.')];

$pconfig['enable'] = isset($config['smartd']['enable']);
$pconfig['interval'] = $config['smartd']['interval'];
$pconfig['powermode'] = $config['smartd']['powermode'];
$pconfig['temp_diff'] = $config['smartd']['temp']['diff'];
$pconfig['temp_info'] = $config['smartd']['temp']['info'];
$pconfig['temp_crit'] = $config['smartd']['temp']['crit'];
$pconfig['email_enable'] = isset($config['smartd']['email']['enable']);
$pconfig['email_to'] = $config['smartd']['email']['to'];
$pconfig['email_testemail'] = isset($config['smartd']['email']['testemail']);
$pconfig['enablesmartmonondevice'] = isset($config['smartd']['enablesmartmonondevice']);

if($_POST):
	unset($input_errors);
	$pconfig = $_POST;

	if(isset($_POST['enable']) && $_POST['enable']):
		$reqdfields = ['interval','powermode','temp_diff','temp_info','temp_crit'];
		$reqdfieldsn = [gtext('Interval'),gtext('Power Mode'),gtext('Difference'),gtext('Informal'),gtext('Critical')];
		$reqdfieldst = ['numericint','string','numericint','numericint','numericint'];

		if(isset($_POST['email_enable']) && $_POST['email_enable']):
			$reqdfields = array_merge($reqdfields, ['email_to']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('To Email Address')]);
			$reqdfieldst = array_merge($reqdfieldst, ['string']);
		endif;

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		if(10 > $_POST['interval']):
			$input_errors[] = gtext("Interval must be greater or equal than 10 seconds.");
		endif;
	endif;

	if(empty($input_errors)):
		$config['smartd']['enable'] = isset($_POST['enable']) ? true : false;
		$config['smartd']['interval'] = $_POST['interval'];
		$config['smartd']['powermode'] = $_POST['powermode'];
		$config['smartd']['temp']['diff'] = $_POST['temp_diff'];
		$config['smartd']['temp']['info'] = $_POST['temp_info'];
		$config['smartd']['temp']['crit'] = $_POST['temp_crit'];
		$config['smartd']['email']['enable'] = isset($_POST['email_enable']) ? true : false;
		$config['smartd']['email']['to'] = !empty($_POST['email_to']) ? $_POST['email_to'] : "";
		$config['smartd']['email']['testemail'] = isset($_POST['email_testemail']) ? true : false;
		$config['smartd']['enablesmartmonondevice'] = isset($_POST['enablesmartmonondevice']) ? true : false;

		write_config();

		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process('smartssd','smartssd_process_updatenotification');
			config_lock();
			$retval |= rc_update_service('smartd');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete('smartssd');
		endif;
	endif;
endif;
array_make_branch($config,'disks','disk');
$a_selftest = &array_make_branch($config,'smartd','selftest');
$a_type = ['S' => gtext('Short Self-Test'),'L' => gtext('Long Self-Test'),'C' => gtext('Conveyance Self-Test'),'O' => gtext('Offline Immediate Test')];

if(isset($_GET['act']) && $_GET['act'] === "del"):
	if($_GET['uuid'] === 'all'):
		foreach ($a_selftest as $selftestv):
			updatenotify_set("smartssd", UPDATENOTIFY_MODE_DIRTY, $selftestv['uuid']);
		endforeach;
	else:
		updatenotify_set('smartssd',UPDATENOTIFY_MODE_DIRTY,$_GET['uuid']);
	endif;
	header('Location: disks_manage_smart.php');
	exit;
endif;

function smartssd_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if(is_array($config['smartd']['selftest'])):
				$index = array_search_ex($data, $config['smartd']['selftest'], "uuid");
				if(false !== $index):
					unset($config['smartd']['selftest'][$index]);
					write_config();
				endif;
			endif;
			break;
	endswitch;

	return $retval;
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//![CDATA[
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);

	if (enable_change.name == "email_enable") {
		endis = !enable_change.checked;

		document.iform.email_to.disabled = endis;
		document.iform.email_testemail.disabled = endis;
	} else {
		document.iform.interval.disabled = endis;
		document.iform.powermode.disabled = endis;
		document.iform.temp_diff.disabled = endis;
		document.iform.temp_info.disabled = endis;
		document.iform.temp_crit.disabled = endis;
		document.iform.email_enable.disabled = endis;
		document.iform.enablesmartmonondevice.disabled = endis;

		if (document.iform.enable.checked == true) {
			endis = !(document.iform.email_enable.checked || enable_change);
		}

		document.iform.email_to.disabled = endis;
		document.iform.email_testemail.disabled = endis;
	}
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->add_tabnav_upper()->
			ins_tabnav_record('disks_manage.php',gettext('HDD Management'))->
			ins_tabnav_record('disks_init.php',gettext('HDD Format'))->
			ins_tabnav_record('disks_manage_smart.php',gettext('S.M.A.R.T.'),gettext('Reload Page'),true)->
			ins_tabnav_record('disks_manage_iscsi.php',gettext('iSCSI Initiator'))->
		pop()->add_tabnav_lower()->
			ins_tabnav_record('disks_manage_smart.php',gettext('Settings'),gettext('Reload Page'),true)->
			ins_tabnav_record('smartmontools_umass.php',gettext('USB Mass Storage Devices'));
$document->render();
?>
<form action="disks_manage_smart.php" method="post" name="iform" id="iform" onsubmit="spinner()"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($pconfig['enable']) && !empty($pconfig['email_enable']) && (0 !== email_validate_settings())):
		$helpinghand = '<a href="'
			. 'system_email.php'
			. '">'
			. gtext('Make sure you have already configured your email settings')
			. '</a>.';
		print_error_box($helpinghand);
	endif;
	$smart = false;
	foreach($config['disks']['disk'] as $device):
		if(isset($device['smart'])):
			$smart = true;
			break;
		endif;
	endforeach;
	if(false === $smart):
		print_error_box(gtext('Make sure you have activated S.M.A.R.T. for your devices.'));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists('smartssd')):
		print_config_change_box();
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline_checkbox2('enable',gettext('Self-Monitoring, Analysis & Reporting Technology'),!empty($pconfig['enable']) ? true : false,gettext('Enable'),'enable_change(this)');
?>
		</thead>
		<tbody>
<?php

			html_inputbox2('interval',gettext('Interval'),$pconfig['interval'],gettext('Set interval between disk checks to N seconds. The minimum allowed value is 10.'),true,5);
			$l_powermode = [
				'never' => gettext('Never - Poll (check) the device regardless of its power mode. This may cause a disk which is spun-down to be spun-up when it is checked.'),
				'sleep' => gettext('Sleep - Check the device unless it is in SLEEP mode.'),
				'standby' => gettext('Standby - Check the device unless it is in SLEEP or STANDBY mode. In these modes most disks are not spinning, so if you want to prevent a laptop disk from spinning up each poll, this is probably what you want.'),
				'idle' => gettext('Idle - Check the device unless it is in SLEEP, STANDBY or IDLE mode. In the IDLE state, most disks are still spinning, so this is probably not what you want.')
			];
			html_radiobox2('powermode',gettext('Power Mode'),$pconfig['powermode'],$l_powermode,'',false,false);
			html_separator2();
			html_titleline2(gettext('Default Device Settings'));
			html_checkbox2('enablesmartmonondevice',gettext('S.M.A.R.T. Monitoring'),!empty($pconfig['enablesmartmonondevice']) ? true : false,gettext('Enable S.M.A.R.T. monitoring of S.M.A.R.T. capable devices when they are added to the configuration.'));
			html_separator2();
			html_titleline2(gettext('Temperature Monitoring'));
			html_inputbox2('temp_diff',gettext('Difference'),$pconfig['temp_diff'],gettext('Report if the temperature had changed by at least N degrees Celsius since last report. Set to 0 to disable this report.'),true,5);
			html_inputbox2('temp_info',gettext('Informal'),$pconfig['temp_info'],gettext('Report if the temperature is greater or equal than N degrees Celsius. Set to 0 to disable this report.'),true,5);
			html_inputbox2('temp_crit',gettext('Critical'),$pconfig['temp_crit'],gettext('Report if the temperature is greater or equal than N degrees Celsius. Set to 0 to disable this report.'),true,5);
			html_separator2();
			html_titleline2(gettext('Self-Test Schedules'));
?>
			<tr>
				<td class="celltag"><?=gtext('Scheduled Tests');?></td>
				<td class="celldata">
					<table class="area_data_selection">
						<colgroup>
							<col style="width:20%">
							<col style="width:30%">
							<col style="width:40%">
							<col style="width:10%">
						</colgroup>
						<thead>
							<tr>
								<th class="lhell"><?=gtext('Disk');?></th>
								<th class="lhell"><?=gtext('Type');?></th>
								<th class="lhell"><?=gtext('Description');?></th>
								<th class="lhebl"><?=gtext('Toolbox');?></th>
							</tr>
						</thead>
						<tbody>
<?php 
							foreach($a_selftest as $selftest):
								$notificationmode = updatenotify_get_mode('smartssd',$selftest['uuid']);
?>
								<tr>
									<td class="lcell"><?=htmlspecialchars($selftest['devicespecialfile']);?>&nbsp;</td>
									<td class="lcell"><?=$a_type[$selftest['type']];?>&nbsp;</td>
									<td class="lcell"><?=htmlspecialchars($selftest['desc']);?>&nbsp;</td>
<?php
									if(UPDATENOTIFY_MODE_DIRTY != $notificationmode):
?>
										<td valign="middle" nowrap="nowrap" class="lcebl">
											<a href="disks_manage_smart_edit.php?uuid=<?=$selftest['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit self-test");?>" border="0" alt="<?=gtext('Edit self-test');?>" /></a>
											<a href="disks_manage_smart.php?act=del&amp;uuid=<?=$selftest['uuid'];?>" onclick="return confirm('<?=gtext('Do you really want to delete this scheduled self-test?');?>')"><img src="images/delete.png" title="<?=gtext('Delete self-test');?>" border="0" alt="<?=gtext('Delete self-test');?>" /></a>
										</td>
<?php
									else:
?>
										<td valign="middle" nowrap="nowrap" class="lcebl">
											<img src="images/delete.png" border="0" alt="" />
										</td>
<?php
									endif;
?>
								</tr>
<?php
							endforeach;
?>
						</tbody>
						<tfoot>
							<tr>
								<th class="lcenl" colspan="3"></th>
								<th class="lceadd">
									<a href="disks_manage_smart_edit.php"><img src="images/add.png" title="<?=gtext('Add self-test');?>" border="0" alt="<?=gtext('Add self-test');?>" /></a>
<?php
									if(!empty($a_selftest)):
?>
										<a href="disks_manage_smart.php?act=del&amp;uuid=all" onclick="return confirm('<?=gtext('Do you really want to delete all scheduled self-tests?');?>')"><img src="images/delete.png" title="<?=gtext('Delete all self-tests');?>" border="0" alt="<?=gtext('Delete all self-tests');?>" /></a>
<?php
									endif;
?>
								</th>
							</tr>
						</tfoot>
					</table>
					<span class="vexpl"><?=gtext("Add additional scheduled self-test.");?></span>
				</td>
			</tr>
<?php
			html_separator2();
			html_titleline_checkbox2('email_enable',gettext('Email Report'),!empty($pconfig['email_enable']) ? true : false,gettext('Activate'),'enable_change(this)');
			html_inputbox2('email_to',gettext('To Email Address'),!empty($pconfig['email_to']) ? $pconfig['email_to'] : '',sprintf('%s %s',gettext('Destination email address.'),gettext('Separate email addresses by semi-colon.')),true,60);
			html_checkbox2('email_testemail',gettext('Test Email'),!empty($pconfig['email_testemail']) ? true : false,gettext('Send a TEST warning email on startup.'));
?>
		</tbody>		
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save and Restart');?>" onclick="enable_change(true)" />
	</div>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),gettext('Activate email report if you want to be notified if a failure or a new error has been detected, or if a S.M.A.R.T. command to a disk fails.'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<script type="text/javascript">
//![CDATA[
enable_change(false);
//]]>
</script>
<?php
include 'fend.inc';
?>
