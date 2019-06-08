<?php
/*
	diag_traceroute.php

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
require_once 'co_sphere.php';

function diag_traceroute_get_sphere() {
	$sphere = new co_sphere_settings('diag_traceroute','php');
	return $sphere;
}
$sphere = diag_traceroute_get_sphere();
$do_traceroute = false;
if($_POST):
	unset($input_errors);
	// Input validation
	$reqdfields = ['target','max_ttl'];
	$reqdfieldsn = [gtext('Target'),gtext('Count')];
	$reqdfieldst = ['string','numeric'];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	if(empty($input_errors)):
		$do_traceroute = true;
		$target = $_POST['target'];
		$resolve = isset($_POST['resolve']);
		$max_ttl = $_POST['max_ttl'];
		if($resolve):
			$cmd = sprintf('/usr/sbin/traceroute -w 2 -m %1$s %2$s 2>&1',escapeshellarg($max_ttl),escapeshellarg($target));
		else:
			$cmd = sprintf('/usr/sbin/traceroute -n -w 2 -m %1$s %2$s 2>&1',escapeshellarg($max_ttl),escapeshellarg($target));
		endif;
	endif;
endif;
if(!$do_traceroute):
	$target = '';
	$max_ttl = 10;
	$resolve = false;
endif;
$pgtitle = [gtext('Diagnostics'),gtext('Traceroute')];
include 'fbegin.inc';
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_ping.php',gettext('Ping'))->
			ins_tabnav_record('diag_traceroute.php',gettext('Traceroute'),gettext('Reload page'),true);
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Traceroute Host'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('target',gettext('Target'),$target,gettext('Enter hostname or IP address.'),true,32);
			html_checkbox2('resolve',gettext('Resolve IP'),$resolve ? true : false,gettext('Resolve IP addresses to hostnames.'),'',false);
			$a_max_ttl = [];
			for($i = 1;$i <= 64;$i++):
				$a_max_ttl[$i] = $i;
			endfor;
			html_combobox2('max_ttl',gettext('Count'),$max_ttl,$a_max_ttl,gettext('Select max time-to-live (max number of hops) used in outgoing probe packets.'),true);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Traceroute');?>"/>
	</div>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),gettext('Traceroute may take a while, please be patient.'));
?>
	</div>
<?php
	if($do_traceroute):?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gettext('Traceroute Output'));
?>
			</thead>
			<tbody><tr>
				<td class="celltag"><?=gtext('Output');?></td>
				<td class="celldata">
<?php
					echo '<pre class="cmdoutput">';
					flush();
					system($cmd);
					echo '</pre>';
?>
				</td>
			</tr></tbody>
		</table>
<?php
	endif;
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
