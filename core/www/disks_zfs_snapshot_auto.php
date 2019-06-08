<?php
/*
	disks_zfs_snapshot_auto.php

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

$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Auto Snapshot')];
$a_autosnapshot = &array_make_branch($config,'zfs','autosnapshots','autosnapshot');
if(empty($a_autosnapshot)):
else:
	array_sort_key($a_autosnapshot,'path');
endif;
$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)):
else:
	array_sort_key($a_pool,'name');
endif;
if(!isset($uuid) && (!sizeof($a_pool))):
	$link = sprintf('<a href="%1$s">%2$s</a>', 'disks_zfs_zpool.php', gtext('pools'));
	$helpinghand = gtext('No configured pools.') . ' ' . gtext('Please add new %s first.');
	$helpinghand = sprintf($helpinghand, $link);
	$errormsg = $helpinghand;
endif;
$a_timehour = [];
foreach(range(0, 23) as $hour):
	$min = 0;
	$a_timehour[sprintf("%02.2d%02.2d",$hour,$min)] = sprintf("%02.2d:%02.2d",$hour,$min);
endforeach;
$a_lifetime = [
		'0' => gtext('infinity'),
	    '1w' => sprintf(gtext('%d week'),1),
	    '2w' => sprintf(gtext('%d weeks'),2),
	    '30d' => sprintf(gtext('%d days'),30),
	    '60d' => sprintf(gtext('%d days'),60),
	    '90d' => sprintf(gtext('%d days'),90),
	    '180d' => sprintf(gtext('%d days'),180),
	    '1y' => sprintf(gtext('%d year'),1),
	    '2y' => sprintf(gtext('%d years'),2)
	];
if($_POST):
	$pconfig = $_POST;
	if(isset($_POST['apply']) && $_POST['apply']):
		$ret = ['output' => [], 'retval' => 0];
		if(!file_exists($d_sysrebootreqd_path)):
			// Process notifications
			$ret = zfs_updatenotify_process("zfsautosnapshot", "zfsautosnapshot_process_updatenotification");
			config_lock();
			$ret['retval'] |= rc_update_service("autosnapshot");
			config_unlock();
		endif;
		$savemsg = get_std_save_message($ret['retval']);
		if($ret['retval'] == 0):
			updatenotify_delete("zfsautosnapshot");
			header("Location: disks_zfs_snapshot_auto.php");
			exit;
		endif;
		updatenotify_delete("zfsautosnapshot");
		$errormsg = implode("\n", $ret['output']);
	endif;
endif;
if(isset($_GET['act']) && $_GET['act'] === "del"):
	$autosnapshot = [];
	$autosnapshot['uuid'] = $_GET['uuid'];
	updatenotify_set('zfsautosnapshot',UPDATENOTIFY_MODE_DIRTY,serialize($autosnapshot));
	header('Location: disks_zfs_snapshot_auto.php');
	exit;
endif;
function zfsautosnapshot_process_updatenotification($mode, $data) {
	global $config;

	$ret = ['output' => [],'retval' => 0];
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			$data = unserialize($data);
			//$ret = zfs_snapshot_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$data = unserialize($data);
			//$ret = zfs_snapshot_properties($data);
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$data = unserialize($data);
			$cnid = array_search_ex($data['uuid'], $config['zfs']['autosnapshots']['autosnapshot'],'uuid');
			if(false !== $cnid):
				unset($config['zfs']['autosnapshots']['autosnapshot'][$cnid]);
				write_config();
			endif;
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
			ins_tabnav_record('disks_zfs_snapshot_clone.php',gettext('Clone'))->
			ins_tabnav_record('disks_zfs_snapshot_auto.php',gettext('Auto Snapshot'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_snapshot_info.php',gettext('Information'));
$document->render();
?>
<form action="disks_zfs_snapshot_auto.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists('zfsautosnapshot')):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:30%">
			<col style="width:20%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'), 7);
?>
			<tr>
				<th class="lhell"><?=gtext('Path');?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Recursive');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhell"><?=gtext('Schedule Time');?></th>
				<th class="lhell"><?=gtext('Life Time');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($a_autosnapshot as $autosnapshotv):
				$notificationmode = updatenotify_get_mode('zfsautosnapshot',serialize(['uuid' => $autosnapshotv['uuid']]));
?>
				<tr>
					<td class="lcell"><?=htmlspecialchars($autosnapshotv['path']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($autosnapshotv['name']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars(isset($autosnapshotv['recursive']) ? "yes" : "no");?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($autosnapshotv['type']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($a_timehour[$autosnapshotv['timehour']]);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($a_lifetime[$autosnapshotv['lifetime']]);?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
<?php
							if(UPDATENOTIFY_MODE_DIRTY != $notificationmode):
?>
								<td>
									<a href="disks_zfs_snapshot_auto_edit.php?uuid=<?=$autosnapshotv['uuid'];?>">
										<img src="images/edit.png" title="<?=gtext('Edit auto snapshot');?>" border="0" alt="<?=gtext('Edit auto snapshot');?>" />
									</a>
								</td>
								<td>
									<a href="disks_zfs_snapshot_auto.php?act=del&amp;uuid=<?=$autosnapshotv['uuid'];?>" onclick="return confirm('<?=gtext('Do you really want to delete this auto snapshot?');?>')">
										<img src="images/delete.png" title="<?=gtext("Delete auto snapshot");?>" border="0" alt="<?=gtext('Delete auto snapshot');?>" />
									</a>
								</td>
								<td></td>
<?php
							else:
?>
								<td>
									<img src="images/delete.png" border="0" alt=""/>
								</td>
								<td></td>
								<td></td>
<?php
							endif;
?>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="6"></th>
				<th class="lceadd">
					<a href="disks_zfs_snapshot_auto_edit.php">
						<img src="images/add.png" title="<?=gtext("Add auto snapshot");?>" alt="<?=gtext("Add auto snapshot");?>"/>
					</a>
				</th>
			</tr>
		</tfoot>
	</table>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
