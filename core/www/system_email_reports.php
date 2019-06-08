<?php
/*
	system_email_report.php.

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
require_once 'report.inc';

$sphere_scriptname = basename(__FILE__);

array_make_branch($config,'statusreport');
$pconfig['enable'] = isset($config['statusreport']['enable']);
$pconfig['to'] = $config['statusreport']['to'];
$pconfig['subject'] = $config['statusreport']['subject'];
$pconfig['report'] = $config['statusreport']['report'];
$pconfig['report_scriptname'] = $config['statusreport']['report_scriptname'];
$pconfig['minute'] = $config['statusreport']['minute'];
$pconfig['hour'] = $config['statusreport']['hour'];
$pconfig['day'] = $config['statusreport']['day'];
$pconfig['month'] = $config['statusreport']['month'];
$pconfig['weekday'] = $config['statusreport']['weekday'];
$pconfig['all_mins'] = $config['statusreport']['all_mins'];
$pconfig['all_hours'] = $config['statusreport']['all_hours'];
$pconfig['all_days'] = $config['statusreport']['all_days'];
$pconfig['all_months'] = $config['statusreport']['all_months'];
$pconfig['all_weekdays'] = $config['statusreport']['all_weekdays'];
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	// Input validation.
	if(isset($_POST['enable']) && $_POST['enable']):
		$reqdfields = ['to'];
		$reqdfieldsn = [gtext('To Email')];
		$reqdfieldst = ['string'];
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if(isset($_POST['Submit']) && $_POST['Submit']):
			// Validate synchronization time
			do_input_validate_synctime($_POST,$input_errors);
		endif;
		// custom script
		if(is_array($_POST['report']) && in_array('script',$_POST['report'])):
			if($_POST['report_scriptname'] == ''):
				$input_errors[] = gtext('Custom script is required.');
			elseif(!file_exists($_POST['report_scriptname'])):
				$input_errors[] = gtext('Custom script not found.');
			endif;
		endif;
	endif;
	if(empty($input_errors)):
		$config['statusreport']['enable'] = isset($_POST['enable']) ? true : false;
		$config['statusreport']['to'] = $_POST['to'];
		$config['statusreport']['subject'] = $_POST['subject'];
		$config['statusreport']['report'] = $_POST['report'];
		$config['statusreport']['report_scriptname'] = $_POST['report_scriptname'];
		$config['statusreport']['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$config['statusreport']['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$config['statusreport']['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$config['statusreport']['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$config['statusreport']['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$config['statusreport']['all_mins'] = $_POST['all_mins'];
		$config['statusreport']['all_hours'] = $_POST['all_hours'];
		$config['statusreport']['all_days'] = $_POST['all_days'];
		$config['statusreport']['all_months'] = $_POST['all_months'];
		$config['statusreport']['all_weekdays'] = $_POST['all_weekdays'];
		write_config();
		if(isset($_POST['SendReportNow']) && $_POST['SendReportNow']):
			// Send an email status report now.
			$retval = @report_send_mail();
			if(0 == $retval):
				$savemsg = gtext('Status report successfully sent.');
			else:
				$failmsg = gtext('Failed to send status report.') . ' ' . '<a href="diag_log.php">' . gtext('Please check the log files') . '.</a>';
			endif;
		else:
			// Configure cron job.
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval = rc_update_service('cron');
				config_unlock();
			endif;
			$savemsg = get_std_save_message($retval);
		endif;
	endif;
endif;
$l_report = [
	'systeminfo' => gtext('System info'),
	'dmesg' => gtext('System message buffer'),
	'systemlog' => gtext('System log'),
	'ftplog' => gtext('FTP log'),
	'rsynclog' => gtext('RSYNC log'),
	'sshdlog' => gtext('SSHD log'),
	'smartdlog' => gtext('S.M.A.R.T. log'),
	'daemonlog' => gtext('Daemon log'),
	'script' => gtext('Custom script')
];
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Email Reports Setup')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
			ins_tabnav_record('system_email.php',gettext('Email'))->
			ins_tabnav_record('system_email_reports.php',gettext('Email Reports'),gettext('Reload page'),true)->
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
	if(0 !== email_validate_settings()):
		$helpinghand = '<a href="system_email.php">' . gtext('Your current email settings are either incomplete or insecure.') . '</a>';
		print_error_box($helpinghand);
	endif;
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
			html_titleline_checkbox2('enable',gettext('System Email Report Settings'),!empty($pconfig['enable']) ? true : false,gettext('Enable'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('to',gettext('To Email Address'),$pconfig['to'],gettext('Destination email address.') . ' ' . gettext('Separate email addresses by semi-colon.'),true,74,false,false,1024,gettext('Enter Email Address(es)'));
			$helpinghand = '<span>' . gettext('The subject of the email.') . ' ' . gettext('You can use the following parameters for substitution:') . '</span>' . "\n"
				. '<div id="enumeration"><ul><li>%d - ' . gettext('Date') . '</li><li>%h - ' . gettext('Hostname') . '</li></ul></div>';
			html_inputbox2('subject',gettext('Subject'),$pconfig['subject'],$helpinghand,false,74,false,false,0,gettext('Enter Subject'));
			html_checkboxbox2('report',gettext('Standard Reports'),$pconfig['report'] ?? [],$l_report,'',false,false);
			html_filechooser2('report_scriptname',gettext('Custom Script'),$pconfig['report_scriptname'],'','/mnt',false,65,false);
?>
			<tr>
				<td class="celltagreq"><?=gtext('Polling Time');?></td>
				<td class="celldatareq">
<?php
					include 'cs_scheduletime.php';
?>
				</td>
			</tr>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>"/>
		<input name="SendReportNow" id="sendnow" type="Submit" class="formbtn" value="<?=gtext('Send Now');?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';

