<?php
/*
	disks_zfs_snapshot_clone.php

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
require_once 'zfs.inc';

$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Clone')];

$a_snapshot = &array_make_branch($config,'zfs','snapshots','snapshot');
if(empty($a_snapshot)):
else:
	array_sort_key($a_snapshot,'name');
endif;

function get_zfs_clones() {
	$result = [];
	$cmd = 'zfs list -pH -o name,origin,creation -t filesystem,volume 2>&1';
	mwexec2($cmd,$rawdata);
	foreach($rawdata as $line):
		$a = preg_split('/\t/', $line);
		$r = [];
		$name = $a[0];
		$r['path'] = $name;
		if (preg_match('/^([^\/\@]+)(\/([^\@]+))?$/', $name, $m)) {
			$r['pool'] = $m[1];
		} else {
			$r['pool'] = 'unknown'; // XXX
		}
		$r['origin'] = $a[1];
		if ($r['origin'] == '-'):
			continue;
		endif;
		$r['creation'] = $a[2];
		$result[] = $r;
	endforeach;
	return $result;
}
$a_clone = get_zfs_clones();

if ($_POST) {
	$pconfig = $_POST;

	if (isset($_POST['apply']) && $_POST['apply']) {
		$ret = ['output' => [],'retval' => 0];

		if (!file_exists($d_sysrebootreqd_path)) {
			// Process notifications
			$ret = zfs_updatenotify_process("zfsclone", "zfsclone_process_updatenotification");

		}
		$savemsg = get_std_save_message($ret['retval']);
		if ($ret['retval'] == 0) {
			updatenotify_delete("zfsclone");
			header("Location: disks_zfs_snapshot_clone.php");
			exit;
		}
		updatenotify_delete("zfsclone");
		$errormsg = implode("\n", $ret['output']);
	}
}

if (isset($_GET['act']) && $_GET['act'] === "del") {
	$clone = [];
	$clone['path'] = $_GET['path'];
	updatenotify_set("zfsclone", UPDATENOTIFY_MODE_DIRTY, serialize($clone));
	header("Location: disks_zfs_snapshot_clone.php");
	exit;
}
function zfsclone_process_updatenotification($mode, $data) {
	global $config;

	$ret = ['output' => [],'retval' => 0];
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			//$data = unserialize($data);
			//$ret = zfs_clone_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			//$data = unserialize($data);
			//$ret = zfs_clone_properties($data);
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$data = unserialize($data);
			$ret = zfs_clone_destroy($data);
			break;
	endswitch;
	return $ret;
}
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshot'))->
			ins_tabnav_record('disks_zfs_snapshot_clone.php',gettext('Clone'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_snapshot_auto.php',gettext('Auto Snapshot'))->
			ins_tabnav_record('disks_zfs_snapshot_info.php',gettext('Information'));
$document->render();
?>
<form action="disks_zfs_snapshot_clone.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists('zfsclone')):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:30%">
			<col style="width:40%">
			<col style="width:20%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'),4);
?>
			<tr>
				<th class="lhell"><?=gtext('Path');?></th>
				<th class="lhell"><?=gtext('Origin');?></th>
				<th class="lhell"><?=gtext('Creation');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($a_clone as $clonev):
				$notificationmode = updatenotify_get_mode('zfsclone',serialize(['path' => $clonev['path']]));
?>
				<tr>
					<td class="lcell"><?=htmlspecialchars($clonev['path']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($clonev['origin']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars(get_datetime_locale($clonev['creation']));?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
<?php
								if(UPDATENOTIFY_MODE_DIRTY != $notificationmode):
?>
									<a href="disks_zfs_snapshot_clone.php?act=del&amp;path=<?=urlencode($clonev['path']);?>" onclick="return confirm('<?=gtext('Do you really want to delete this clone?');?>')"><img src="images/delete.png" title="<?=gtext('Delete clone');?>" border="0" alt="<?=gtext('Delete clone');?>" /></a>
<?php
								else:
?>
									<img src="images/delete.png" border="0" alt=""/>
<?php
								endif;
?>
							</td>
							<td></td>
							<td></td>
						</tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
	</table>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
