<?php
/*
	reboot_sched.php

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

$sphere_scriptname = basename(__FILE__);

array_make_branch($config,'reboot');
$pconfig['enable'] = isset($config['reboot']['enable']);
$pconfig['minute'] = $config['reboot']['minute'];
$pconfig['hour'] = $config['reboot']['hour'];
$pconfig['day'] = $config['reboot']['day'];
$pconfig['month'] = $config['reboot']['month'];
$pconfig['weekday'] = $config['reboot']['weekday'];
$pconfig['all_mins'] = $config['reboot']['all_mins'];
$pconfig['all_hours'] = $config['reboot']['all_hours'];
$pconfig['all_days'] = $config['reboot']['all_days'];
$pconfig['all_months'] = $config['reboot']['all_months'];
$pconfig['all_weekdays'] = $config['reboot']['all_weekdays'];
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	// Validate synchronization time
	if(isset($_POST['enable'])):
		do_input_validate_synctime($_POST,$input_errors);
	endif;
	if(empty($input_errors)):
		$config['reboot']['enable'] = isset($_POST['enable']) ? true : false;
		$config['reboot']['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$config['reboot']['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$config['reboot']['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$config['reboot']['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$config['reboot']['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$config['reboot']['all_mins'] = $_POST['all_mins'];
		$config['reboot']['all_hours'] = $_POST['all_hours'];
		$config['reboot']['all_days'] = $_POST['all_days'];
		$config['reboot']['all_months'] = $_POST['all_months'];
		$config['reboot']['all_weekdays'] = $_POST['all_weekdays'];
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service('cron');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
$pgtitle = [gtext('System'),gtext('Reboot'),gtext('Scheduled')];
include 'fbegin.inc';?>
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
<table id="area_navigator">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="reboot.php"><span><?=gtext('Now');?></span></a></li>
		<li class="tabact"><a href="reboot_sched.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Scheduled');?></span></a></li>
	</ul></td></tr>
</table>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline_checkbox2('enable',gettext('Scheduled Reboot'),!empty($pconfig['enable']) ? true : false,gettext('Enable'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltagreq"><?=gtext('Schedule Time');?></td>
				<td class="celldatareq">
<?php
					include 'cs_scheduletime.php';
?>
				</td>
			</tr>
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
?>
