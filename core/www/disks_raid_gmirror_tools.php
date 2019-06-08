<?php
/*
	disks_raid_gmirror_tools.php

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

$a_raid = &array_make_branch($config,'gmirror','vdisk');
if(empty($a_raid)):
else: 
	array_sort_key($a_raid,'name');
endif;
if($_POST):
	unset($input_errors);
	unset($do_action);
	//	input validation
	$reqdfields = ['action','raid','disk'];
	$reqdfieldsn = [gtext('Command'),gtext('Volume Name'),gtext('Disk')];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	if(empty($input_errors)):
		$do_action = true;
		$action = $_POST['action'];
		$raid = $_POST['raid'];
		$disk = $_POST['disk'];
	endif;
endif;
if(!isset($do_action)):
	$do_action = false;
	$action = 'status';
	$object = '';
	$raid = '';
	$disk = '';
endif;
$l_action = [
	'rebuild' => gettext('Rebuild the selected device forcibly.'),
	'list' => gettext('Print detailed information.'),
	'status' => gettext('Print general information.'),
	'remove' => gettext('Remove the selected device from the mirror.'),
	'activate' => gettext('Activate the selected device.'),
	'deactivate' => gettext('Mark the selected device as inactive.'),
	'forget' => gettext('Forget about devices which are not connected.'),
	'insert' => gettext('Add the selected device to the existing mirror.'),
	'clear' => gettext('Clear metadata on the selected device.'),
	'stop' => gettext('Stop the selected mirror.')
];
$pgtitle = [gtext('Disks'),gtext('Software RAID'),gtext('RAID-1'),gtext('Maintenance')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
function raid_change() {
	var next = null;
<?php
	// Remove all entries from partition combobox.
?>
	document.iform.disk.length = 0;
<?php
	//	Insert entries for disk combobox.
?>
	switch(document.iform.raid.value) {
<?php
	  foreach ($a_raid as $raidv):
?>
		case "<?=$raidv['name'];?>":
<?php
		  foreach($raidv['device'] as $devicen => $devicev):
			$name = str_replace("/dev/","",$devicev);
?>
			if(document.all)<?php // MS IE workaround. ?>
				next = document.iform.disk.length;
			document.iform.disk.add(new Option("<?=$name;?>","<?=$name;?>",false,<?php if($name === $disk){echo "true";}else{echo "false";};?>),next);
<?php
		  endforeach;
?>
			break;
<?php
		endforeach;
?>
	}
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_raid_geom.php',gettext('GEOM'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_raid_gvinum.php',gettext('RAID 0/1/5'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_raid_geom.php',gettext('Management'))->
			ins_tabnav_record('disks_raid_gmirror_tools.php',gettext('Maintenance'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_raid_gmirror_info.php',gettext('Information'));
$document->render();
?>
<form action="disks_raid_gmirror_tools.php" method="post" id="iform" name="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('RAID-1 Maintenance'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltagreq"><?=gtext('Volume Name');?></td>
				<td class="celldatareq">
					<select name="raid" class="formfld" id="raid" onchange="raid_change()">
						<option value=""><?=gtext('Must choose one');?></option>
<?php
						foreach ($a_raid as $raidv):
?>
						<option value="<?=$raidv['name'];?>" <?php if ($raid === $raidv['name']) echo "selected=\"selected\"";?>><?=htmlspecialchars($raidv['name']);?></option>
<?php
						endforeach;
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="celltagreq"><?=gtext('Disk');?></td>
				<td class="celldatareq">
					<select name="disk" class="formfld" id="disk"></select>
				</td>
			</tr>
<?php
			html_radiobox2('action',gettext('Command'),$action,$l_action,'',true);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Submit Command');?>" />
	</div>
<?php
	if($do_action):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2('Information');
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('Command Output');?></td>
					<td class="celldata"><pre class="cmdoutput">
<?php
						//	ob_end_flush();
						switch($action):
							case 'rebuild':
								disks_geom_cmd('mirror','rebuild -v',"{$raid} {$disk}",true);
								break;
							case 'list':
								disks_geom_cmd('mirror','list',$raid,true);
								break;
							case 'status':
								disks_geom_cmd('mirror','status',$raid,true);
								break;
							case 'remove':
								disks_geom_cmd('mirror','remove -v',"{$raid} {$disk}",true);
								break;
							case 'activate':
								disks_geom_cmd('mirror','activate -v',"{$raid} {$disk}",true);
								break;
							case 'deactivate':
								disks_geom_cmd('mirror','deactivate -v',"{$raid} {$disk}",true);
								break;
							case 'forget':
								disks_geom_cmd('mirror','forget -v',$raid,true);
								break;
							case 'insert':
								disks_geom_cmd('mirror','insert -v',"{$raid} {$disk}",true);
								break;
							case 'clear':
								disks_geom_cmd('mirror','clear -v',$disk,true);
								break;
							case 'stop':
								disks_geom_cmd('mirror','stop -v',$raid,true);
								break;
						endswitch;
?>
					</pre></td>
				</tr>
			</tbody>
		</table>
<?php
	endif;
?>
	<div id="remarks">
<?php
		$helpinghand = 
			'1. ' . gettext('Use these specials actions for debugging only!') .
			'<br />' .
			'2. ' . gettext('There is no need to start a RAID volume from here (It starts automatically).');
		html_remark2('warning',gettext('Warning'),$helpinghand);
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<script type="text/javascript">
//<![CDATA[
raid_change();
//]]>
</script>
<?php
include 'fend.inc';
