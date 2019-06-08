<?php
/*
	disks_zfs_config.php

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

$use_si = is_sidisksizevalues();
$zfs = [
	'vdevices' => ['vdevice' => []],
	'pools' => ['pool' => []],
	'datasets' => ['dataset' => []],
	'volumes' => ['volume' => []],
];

if (isset($_POST['import'])) {
	$cmd = 'zpool import -d /dev -a';
	if (isset($_POST['import_force'])) {
		$cmd .= ' -f';
	}
	$retval = mwexec($cmd);
	// remove existing pool cache
	conf_mount_rw();
	unlink_if_exists("{$g['cf_path']}/boot/zfs/zpool.cache");
	conf_mount_ro();
}
$cmd = 'zfs list -H -t filesystem -o name,mountpoint,compression,canmount,quota,used,available,xattr,snapdir,readonly,origin,reservation,dedup,sync,atime,aclinherit,aclmode,primarycache,secondarycache';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if(0 == $retval):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		list($fname,$mpoint,$compress,$canmount,$quota,$used,$avail,$xattr,$snapdir,$readonly,$origin,$reservation,$dedup,$sync,$atime,$aclinherit,$aclmode,$primarycache,$secondarycache) = explode("\t",$line);
		if(strpos($fname,'/') !== false): // dataset
			if(empty($origin) || $origin != '-'):
				continue;
			endif;
			list($pool,$name) = explode('/',$fname,2);
			$zfs['datasets']['dataset'][$fname] = [
				'identifier' => $fname,
				'uuid' => uuid(),
				'name' => $name,
				'pool' => $pool,
				'compression' => $compress,
				'canmount' => ($canmount == 'on') ? null : $canmount,
				'quota' => ($quota == 'none') ? null : $quota,
				'reservation' => ($reservation == 'none') ? null : $reservation,
				'xattr' => ($xattr == 'on'),
				'snapdir' => ($snapdir == 'visible'),
				'readonly' => ($readonly == 'on'),
				'dedup' => $dedup,
				'sync' => $sync,
				'atime' => $atime,
				'aclinherit' => $aclinherit,
				'aclmode' => $aclmode,
				'primarycache' => $primarycache,
				'secondarycache' => $secondarycache,
				'desc' => '',
			];
			list($mp_owner,$mp_group,$mp_mode) = ['root','wheel',0777];
			if($canmount == 'on' && !empty($mpoint) && file_exists($mpoint)):
				$mp_uid = fileowner($mpoint);
				$mp_gid = filegroup($mpoint);
				$mp_perm = (fileperms($mpoint) & 0777);
				$tmp = posix_getpwuid($mp_uid);
				if(!empty($tmp) && !empty($tmp['name'])):
					$mp_owner = $tmp['name'];
				endif;
				$tmp = posix_getgrgid($mp_gid);
				if(!empty($tmp) && !empty($tmp['name'])):
					$mp_group = $tmp['name'];
				endif;
				$mp_mode = sprintf("0%o",$mp_perm);
			endif;
			$zfs['datasets']['dataset'][$fname]['accessrestrictions'] = [
				'owner' => $mp_owner,
				'group' => $mp_group,
				'mode' => $mp_mode,
			];
		else: // zpool
			$zfs['pools']['pool'][$fname] = [
				'uuid' => uuid(),
				'name' => $fname,
				'vdevice' => [],
				'root' => null,
				'mountpoint' => ($mpoint == "/mnt/{$fname}") ? null : $mpoint,
				'desc' => '',
			];
			$zfs['extra']['pools']['pool'][$fname] = [
				'size' => null,
				'used' => $used,
				'avail' => $avail,
				'cap' => null,
				'health' => null,
			];
		endif;
	endforeach;
endif;
$cmd = 'zfs list -pH -t volume -o name,volsize,volblocksize,compression,origin,dedup,sync,refreservation';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if(0 == $retval):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		list($fname,$volsize,$volblocksize,$compress,$origin,$dedup,$sync,$refreservation) = explode("\t",$line);
		if(strpos($fname,'/') !== false): // volume
			if(empty($origin) || $origin != '-'):
				continue;
			endif;
			list($pool,$name) = explode('/',$fname,2);
			$zfs['volumes']['volume'][$fname] = [
				'identifier' => $fname,
				'uuid' => uuid(),
				'name' => $name,
				'pool' => $pool,
				'volsize' => format_bytes($volsize,2,false,$use_si),
				'volblocksize' => format_bytes($volblocksize,0,false,false),
				'compression' => $compress,
				'dedup' => $dedup,
				'sync' => $sync,
				'sparse' => ($refreservation == "none") ? true : false,
				'desc' => '',
			];
		endif;
	endforeach;
endif;
$cmd = 'zpool list -pH -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if(0 == $retval):
	foreach($rawdata as $line):
		if($line == 'no pools available'):
			continue; 
		endif;
		list($pool,$root,$size,$alloc,$free,$cap,$expandsz,$frag,$health,$dedup) = explode("\t",$line);
		if ($root != '-'):
			$zfs['pools']['pool'][$pool]['root'] = $root;
		endif;
		$zfs['extra']['pools']['pool'][$pool]['size'] = format_bytes($size,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['alloc'] = format_bytes($alloc,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['free'] = format_bytes($free,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['expandsz'] = $expandsz;
		$zfs['extra']['pools']['pool'][$pool]['frag'] = $frag;
		$zfs['extra']['pools']['pool'][$pool]['cap'] = sprintf('%d%%',$cap);
		$zfs['extra']['pools']['pool'][$pool]['health'] = $health;
		$zfs['extra']['pools']['pool'][$pool]['dedup'] = $dedup;
	endforeach;
endif;
$pool = null;
$vdev = null;
$type = null;
$i = 0;
$vdev_type = array('mirror','raidz1','raidz2','raidz3');
$cmd = 'zpool status';
unset($rawdata);
mwexec2($cmd,$rawdata);
foreach($rawdata as $line):
	if(empty($line[0]) || $line[0] != "\t"):
		continue;
	endif;
	if(!is_null($vdev) && preg_match('/^\t    (\S+)/',$line,$m)): // dev
		$dev = $m[1];
		if(preg_match("/^(.+)\.nop$/",$dev,$m)):
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
		elseif(preg_match("/^(.+)\.eli$/",$dev,$m)):
			//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		else:
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		endif;
	elseif(!is_null($pool) && preg_match('/^\t  (\S+)/',$line,$m)): // vdev or dev (type disk)
		$is_vdev_type = true;
		if($type == 'spare'): // disk in vdev type spares
			$dev = $m[1];
		elseif($type == 'cache'):
			$dev = $m[1];
		elseif($type == 'log'):
			$dev = $m[1];
			if(preg_match("/^mirror-([0-9]+)$/",$dev,$m)):
				$type = "log-mirror";
			endif;
		else: // vdev or dev (type disk)
			$type = $m[1];
			if(preg_match("/^(.*)\-\d+$/",$type,$m)):
				$tmp = $m[1];
				$is_vdev_type = in_array($tmp,$vdev_type);
				if($is_vdev_type):
					$type = $tmp;
				endif;
			else:
				$is_vdev_type = in_array($type,$vdev_type);
			endif;
			if(!$is_vdev_type): // type disk
				$dev = $type;
				$type = 'disk';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
			else: // vdev
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
			endif;
		endif;
		if(!array_key_exists($vdev,$zfs['vdevices']['vdevice'])):
			$zfs['vdevices']['vdevice'][$vdev] = [
				'uuid' => uuid(),
				'name' => $vdev,
				'type' => $type,
				'device' => [],
				'desc' => '',
			];
			$zfs['extra']['vdevices']['vdevice'][$vdev]['pool'] = $pool;
			$zfs['pools']['pool'][$pool]['vdevice'][] = $vdev;
		endif;
		if($type == 'spare' || $type == 'cache' || $type == 'log' || $type == 'disk'):
			if(preg_match("/^(.+)\.nop$/",$dev,$m)):
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
			elseif(preg_match("/^(.+)\.eli$/",$dev,$m)):
				//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			else:
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			endif;
		endif;
	elseif(preg_match('/^\t(\S+)/',$line,$m)): // zpool or spares
		$vdev = null;
		$type = null;
		switch($m[1]):
			case 'spares':
				$type = 'spare';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			case 'cache':
				$type = 'cache';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			case 'logs':
				$type = 'log';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			default:
				$pool = $m[1];
				break;
		endswitch;
	endif;
endforeach;
if(count($zfs['pools']['pool']) <= 0):
	$import_button_value = gtext('Import on-disk ZFS config');
	if(isset($_POST['import'])):
		$message_box_type = 'warning';
		$message_box_text = gtext('No pool was found.');
		if(isset($retval) && $retval != 0):
			if(isset($_POST['import_force'])):
				$message_box_text = 'error';
			else:
				$authToken = Session::getAuthToken();
				$message_box_text .= ' ';
				$message_box_text .= gtext('Try to force import.');
				$message_box_text = <<<HTML
<br />
<form action="disks_zfs_config.php" method="post">
	{$message_box_text}<br />
	<input type="submit" name="import" value="{$import_button_value}" />
	<input type="hidden" name="import_force" value="true" />
	<input name="authtoken" type="hidden" value="{$authToken}" autocomplete="off">
</form>
HTML;
			endif;
		endif;
	else:
		$authToken = Session::getAuthToken();
		$message_box_type = 'info';
		$text = gtext('No pool was found.').' '.gtext('Try to import from on-disk ZFS config.');
		$message_box_text = <<<HTML
<form action="disks_zfs_config.php" method="post">
	{$text}<br />
	<input type="submit" name="import" value="{$import_button_value}" />
	<input name="authtoken" type="hidden" value="{$authToken}" autocomplete="off">
</form>
HTML;
	endif;
endif;
$health = true;
if(!empty($zfs['extra']) && !empty($zfs['extra']['pools']) && !empty($zfs['extra']['pools']['pool'])):
	$health &= (bool)!array_search_ex('DEGRADED',$zfs['extra']['pools']['pool'],'health');
	$health &= (bool)!array_search_ex('FAULTED',$zfs['extra']['pools']['pool'],'health');
endif;
if(!$health):
	$message_box_type = 'warning';
	$message_box_text = gtext('Your ZFS system is not healthy.');
endif;
$showusedavail = isset($config['zfs']['settings']['showusedavail']);
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Configuration'),gtext('Detected')];
include 'fbegin.inc';
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_config_current.php',gettext('Current'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Detected'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config_sync.php',gettext('Synchronize'));
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php 
	if(!empty($message_box_text)):
		print_core_box($message_box_type,$message_box_text);
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:16%">
			<col style="width:10%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Pools'),count($zfs['pools']['pool'])),10);
?>
			<tr>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
<?php
				if($showusedavail):
?>
					<th class="lhell"><?=gtext('Used');?></th>
					<th class="lhell"><?=gtext('Avail');?></th>
<?php
				else:
?>
					<th class="lhell"><?=gtext('Alloc');?></th>
					<th class="lhell"><?=gtext('Free');?></th>
<?php
				endif;
?>
				<th class="lhell"><?=gtext('Expandsz');?></th>
				<th class="lhell"><?=gtext('Frag');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhell"><?=gtext('Health');?></th>
				<th class="lhell"><?=gtext('Mount Point');?></th>
				<th class="lhebl"><?=gtext('AltRoot');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($zfs['pools']['pool'] as $key => $pool):
?>
				<tr>
					<td class="lcell"><?=$pool['name'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['size'];?></td>
<?php
				if($showusedavail):
?>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['used'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['avail'];?></td>
<?php
				else:
?>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['alloc'];?> (<?=$zfs['extra']['pools']['pool'][$key]['cap'];?>)</td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['free'];?></td>
<?php
				endif;
?>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['expandsz'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['frag'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['dedup'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['health'];?></td>
					<td class="lcell"><?=empty($pool['mountpoint']) ? "/mnt/{$pool['name']}" : $pool['mountpoint'];?></td>
					<td class="lcebl"><?=empty($pool['root']) ? '-' : $pool['root'];?></td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="10"></td>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:16%">
			<col style="width:21%">
			<col style="width:21%">
			<col style="width:42%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Virtual Devices'),count($zfs['vdevices']['vdevice'])),4);
?>
			<tr>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhebl"><?=gtext('Devices');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($zfs['vdevices']['vdevice'] as $key => $vdevice):
?>
				<tr>
					<td class="lcell"><?=$vdevice['name'];?></td>
					<td class="lcell"><?=$vdevice['type'];?></td>
					<td class="lcell"><?=$zfs['extra']['vdevices']['vdevice'][$key]['pool'];?></td>
					<td class="lcebl"><?=implode(',',$vdevice['device']);?></td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="4"></td>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:14%">
			<col style="width:14%">
			<col style="width:7%">
			<col style="width:7%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:7%">
			<col style="width:8%">
<!--
			<col style="width:8%">
-->
			<col style="width:7%"><!-- // Readonly -->
			<col style="width:9%"><!-- // Snapshot Visibility -->
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Datasets'),count($zfs['datasets']['dataset'])),11);
?>
			<tr>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhell"><?=gtext('Compression');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhell"><?=gtext('Sync');?></th>
				<th class="lhell"><?=gtext('ACL Inherit');?></th>
				<th class="lhell"><?=gtext('ACL Mode');?></th>
				<th class="lhell"><?=gtext('Canmount');?></th>
				<th class="lhell"><?=gtext('Quota');?></th>
<!--
				<th class="lhell"><?=gtext('Extended Attributes');?></th>
-->
				<th class="lhell"><?=gtext('Readonly');?></th>
				<th class="lhebl"><?=gtext('Snapshot Visibility');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($zfs['datasets']['dataset'] as $dataset):
?>
				<tr>
					<td class="lcell"><?=$dataset['name'];?></td>
					<td class="lcell"><?=$dataset['pool'];?></td>
					<td class="lcell"><?=$dataset['compression'];?></td>
					<td class="lcell"><?=$dataset['dedup'];?></td>
					<td class="lcell"><?=$dataset['sync'];?></td>
					<td class="lcell"><?=$dataset['aclinherit'];?></td>
					<td class="lcell"><?=$dataset['aclmode'];?></td>
					<td class="lcell"><?=empty($dataset['canmount']) ? 'on' : $dataset['canmount'];?></td>
					<td class="lcell"><?=empty($dataset['quota']) ? 'none' : $dataset['quota'];?></td>
<!--
					<td class="lcell"><?=empty($dataset['xattr']) ? 'off' : 'on';?></td>
-->
					<td class="lcell"><?=empty($dataset['readonly']) ? 'off' : 'on';?></td>
					<td class="lcebl"><?=empty($dataset['snapdir']) ? 'hidden' : 'visible';?></td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="11"></th>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:16%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:12%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Volumes'),count($zfs['volumes']['volume'])),8);
?>
			<tr>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Blocksize');?></th>
				<th class="lhell"><?=gtext('Sparse');?></th>
				<th class="lhell"><?=gtext('Compression');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhebl"><?=gtext('Sync');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($zfs['volumes']['volume'] as $volume):
?>
				<tr>
					<td class="lcell"><?=$volume['name'];?></td>
					<td class="lcell"><?=$volume['pool'];?></td>
					<td class="lcell"><?=$volume['volsize'];?></td>
					<td class="lcell"><?=$volume['volblocksize'];?></td>
					<td class="lcell"><?=empty($volume['sparse']) ? '-' : 'on';?></td>
					<td class="lcell"><?=$volume['compression'];?></td>
					<td class="lcell"><?=$volume['dedup'];?></td>
					<td class="lcebl"><?=$volume['sync'];?></td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
	</table>
	<div id="remarks">
<?php
		html_remark2('note',gettext('Note'),gettext('This page reflects the current system configuration. It may be different to the configuration which has been created with the WebGUI if changes has been done via command line'));
?>
	</div>
</td></tr></tbody></table>
<?php
include 'fend.inc';
