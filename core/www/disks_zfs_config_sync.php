<?php
/*
	disks_zfs_config_sync.php

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

function get_geli_info($device) {
	$result = [];
	exec("/sbin/geli dump {$device}",$rawdata);
	array_shift($rawdata);
	foreach($rawdata as $line):
		$a = preg_split("/:\s+/",$line);
		$key = trim($a[0]);
		$val = trim($a[1]);
		$result[$key] = $val;
	endforeach;
	return $result;
}
array_make_branch($config,'disks','disk');
array_make_branch($config,'geli','vdisk');
array_make_branch($config,'zfs','vdevices','vdevice');
array_make_branch($config,'zfs','pools','pool');
array_make_branch($config,'zfs','datasets','dataset');
array_make_branch($config,'zfs','volumes','volume');
$zfs = [];
array_make_branch($zfs,'vdevices','vdevice');
array_make_branch($zfs,'pools','pool');
array_make_branch($zfs,'datasets','dataset');
array_make_branch($zfs,'volumes','volume');
$properties_filesystem = [
	'name',
	'mountpoint',
	'compression',
	'canmount',
	'quota',
	'used',
	'available',
	'xattr',
	'snapdir',
	'readonly',
	'origin',
	'reservation',
	'dedup',
	'sync',
	'atime',
	'aclinherit',
	'aclmode',
	'primarycache',
	'secondarycache'
];
$cmd = sprintf('zfs list -H -t filesystem -o %s',implode(',',$properties_filesystem));
unset($rawdata);
unset($retdat);
mwexec2($cmd,$rawdata,$retval);
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
$properties_volume = [
	'name',
	'checksum',
	'compression',
	'dedup',
	'logbias',
	'origin',
	'primarycache',
	'refreservation',
	'secondarycache',
	'sync',
	'volblocksize',
	'volmode',
	'volsize',
];
$cmd = sprintf('zfs list -H -t volume -o %s',implode(',',$properties_volume));
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
foreach($rawdata as $line):
	if($line == 'no datasets available'):
		continue;
	endif;
	list(
		$fname,
		$checksum,
		$compression,
		$dedup,
		$logbias,
		$origin,
		$primarycache,
		$refreservation,
		$secondarycache,
		$sync,
		$volblocksize,
		$volmode,
		$volsize
	) = explode("\t",$line);
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
			'volsize' => $volsize,
			'volmode' => $volmode,
			'volblocksize' => $volblocksize,
			'compression' => $compression,
			'dedup' => $dedup,
			'sync' => $sync,
			'sparse' => ($refreservation == 'none') ? true : false,
			'primarycache' => $primarycache,
			'secondarycache' => $secondarycache,
			'desc' => '',
			'checksum' => $checksum,
			'logbias' => $logbias,
		];
	endif;
endforeach;
$cmd = 'zpool list -H -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
foreach($rawdata as $line):
	if($line == 'no pools available'):
		continue;
	endif;
	list($pool,$root,$size,$alloc,$free,$cap,$expandsz,$frag,$health,$dedup) = explode("\t",$line);
	if($root != '-'):
		$zfs['pools']['pool'][$pool]['root'] = $root;
	endif;
	$zfs['extra']['pools']['pool'][$pool]['size'] = $size;
	$zfs['extra']['pools']['pool'][$pool]['alloc'] = $alloc;
	$zfs['extra']['pools']['pool'][$pool]['free'] = $free;
	$zfs['extra']['pools']['pool'][$pool]['expandsz'] = $expandsz;
	$zfs['extra']['pools']['pool'][$pool]['frag'] = $frag;
	$zfs['extra']['pools']['pool'][$pool]['cap'] = $cap;
	$zfs['extra']['pools']['pool'][$pool]['health'] = $health;
	$zfs['extra']['pools']['pool'][$pool]['dedup'] = $dedup;
endforeach;
$pool = null;
$vdev = null;
$type = null;
$i = 0;
$vdev_type = ['mirror','raidz1','raidz2','raidz3'];
$cmd = 'zpool status';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
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
				'desc' => ''
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
		if($m[1] == 'spares'):
			$type = 'spare';
			$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
		elseif($m[1] == 'cache'):
			$type = 'cache';
			$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
		elseif($m[1] == 'logs'):
			$type = 'log';
			$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
		else:
			$pool = $m[1];
		endif;
	endif;
endforeach;
if(isset($_POST['import_config'])):
	$import = false;
	$cfg = [];
	array_make_branch($cfg,'zfs','vdevices','vdevice');
	array_make_branch($cfg,'zfs','pools','pool');
	array_make_branch($cfg,'zfs','datasets','dataset');
	array_make_branch($cfg,'zfs','volumes','volume');
	array_make_branch($cfg,'zfs','autosnapshots');
	$a_posted = [];
	foreach(['vdev','pool','dset','vol'] as $ref):
		$a_posted[$ref] = filter_input(INPUT_POST,$ref,FILTER_UNSAFE_RAW,['flags' => FILTER_FORCE_ARRAY,'options' => ['default' => []]]);
	endforeach;
	foreach($a_posted['vol'] as $vol):
		$import |= true;
		$tmp = $zfs['volumes']['volume'][$vol];
		unset($tmp['identifier']); // no longer required
		$cfg['zfs']['volumes']['volume'][] = $tmp;
		if(!in_array($zfs['volumes']['volume'][$vol]['pool'],$a_posted['pool'])):
			$a_posted['pool'][] = $zfs['volumes']['volume'][$vol]['pool'];
		endif;
	endforeach;
	foreach($a_posted['dset'] as $dset):
		$import |= true;
		$tmp = $zfs['datasets']['dataset'][$dset];
		unset($tmp['identifier']); // no longer required
		$cfg['zfs']['datasets']['dataset'][] = $tmp;
		if(!in_array($zfs['datasets']['dataset'][$dset]['pool'],$a_posted['pool'])):
			$a_posted['pool'][] = $zfs['datasets']['dataset'][$dset]['pool'];
		endif;
	endforeach;
	foreach($a_posted['pool'] as $pool):
		$import |= true;
		$hastpool = false;
		foreach($zfs['pools']['pool'][$pool]['vdevice'] as $vdev):
			if(!in_array($vdev,$a_posted['vdev'])):
				$a_posted['vdev'][] = $vdev;
			endif;
			foreach($zfs['vdevices']['vdevice'][$vdev]['device'] as $device):
				if(preg_match('/^\/dev\/hast\//',$device)):
					$hastpool = true;
				endif;
			endforeach;
		endforeach;
		$zfs['pools']['pool'][$pool]['hastpool'] = $hastpool;
		$cfg['zfs']['pools']['pool'][] = $zfs['pools']['pool'][$pool];
	endforeach;
	foreach($a_posted['vdev'] as $vdev):
		$import |= true;
		$cfg['zfs']['vdevices']['vdevice'][] = $zfs['vdevices']['vdevice'][$vdev];
	endforeach;
	if($import):
		$cfg['disks'] = $config['disks'];
		$cfg['geli'] = $config['geli'];
		$disks = get_physical_disks_list();
		foreach($cfg['zfs']['vdevices']['vdevice'] as $vdev):
			foreach($vdev['device'] as $device):
				$encrypted = false;
				$device = disks_label_to_device($device);
				if(preg_match("/^(.+)\.eli$/",$device,$m)):
					$device = $m[1];
					$encrypted = true;
				endif;
				if(preg_match("/^(.*)p\d+$/",$device,$m)):
					$device = $m[1];
				endif;
				$index = false;
				if(!empty($cfg['disks']['disk'])):
					$index = array_search_ex($device,$cfg['disks']['disk'],'devicespecialfile');
				endif;
				if($index === false && isset($_POST['import_disks'])):
					$disk = array_search_ex($device,$disks,'devicespecialfile');
					$disk = $disks[$disk];
					$serial = "";
					if(!empty($disk['serial'])):
						$serial = $disk['serial'];
					endif;
					if(($serial == "n/a") || ($serial == gtext("n/a"))):
						$serial = "";
					endif;
					$cfg['disks']['disk'][] = [
						'uuid' => uuid(),
						'name' => $disk['name'],
						'id' => $disk['id'],
						'devicespecialfile' => $disk['devicespecialfile'],
						'model' => !empty($disk['model']) ? $disk['model'] : "",
						'desc' => !empty($disk['desc']) ? $disk['desc'] : "",
						'type' => $disk['type'],
						'serial' => $serial,
						'size' => $disk['size'],
						'harddiskstandby' => 0,
						'acoustic' => 0,
						'apm' => 0,
						'transfermode' => 'auto',
						'fstype' => $encrypted ? 'geli' : 'zfs',
						'controller' => $disk['controller'],
						'controller_id' => $disk['controller_id'],
						'controller_desc' => $disk['controller_desc'],
						'smart' => [
							'devicefilepath' => $disk['smart']['devicefilepath'],
							'devicetype' => $disk['smart']['devicetype'],
							'devicetypearg' => $disk['smart']['devicetypearg'],
							'enable' => false,
							'extraoptions' => "",
						],
					];
				elseif($index !== false && isset($_POST['import_disks_overwrite'])):
					if($encrypted):
						$cfg['disks']['disk'][$index]['fstype'] = 'geli';
					else:
						$cfg['disks']['disk'][$index]['fstype'] = 'zfs';
					endif;
				endif;
				if($encrypted):
					$index = array_search_ex($device,$cfg['geli']['vdisk'],'device');
					$geli_info = get_geli_info($device);
					if($index === false && !empty($geli_info) && isset($_POST['import_disks'])):
						$disk = array_search_ex($device,$disks,'devicespecialfile');
						$disk = $disks[$disk];
						$cfg['geli']['vdisk'][] = [
							'uuid' => uuid(),
							'name' => $disk['name'],
							'device' => $disk['devicespecialfile'],
							'devicespecialfile' => $disk['devicespecialfile'].".eli",
							'desc' => "Encrypted disk",
							'size' => $disk['size'],
							'aalgo' => "none",
							'ealgo' => $geli_info['ealgo'],
							'fstype' => 'zfs',
						];
					elseif($index !== false && isset($_POST['import_disks_overwrite'])):
						$cfg['geli']['vdisk'][$index]['fstype'] = 'zfs';
					endif;
				endif;
			endforeach;
		endforeach;
		if(isset($_GET['zfs']['autosnapshots'])):
			$pconfig['zfs']['autosnapshots'] = $_GET['zfs']['autosnapshots'];
		endif;
		if(isset($_POST['leave_autosnapshots'])):
			$cfg['zfs']['autosnapshots'] = !empty($config['zfs']['autosnapshots']) ? $config['zfs']['autosnapshots'] : [];
		endif;
		//	use below to keep other subnodes, $config['zfs'] = $cfg['zfs']; will not keep them
		foreach($cfg['zfs'] as $zfs_key => $zfs_value):
			$config['zfs'][$zfs_key] = $zfs_value;
		endforeach;
		$config['disks'] = $cfg['disks'];
		$config['geli'] = $cfg['geli'];
		updatenotify_set('zfs_import_config',UPDATENOTIFY_MODE_UNKNOWN,true);
		write_config();
		// remove existing pool cache
		conf_mount_rw();
		unlink_if_exists("{$g['cf_path']}/boot/zfs/zpool.cache");
		conf_mount_ro();
		header('Location: disks_zfs_config_current.php');
		exit();
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
	$message_box_text .= ' ';
	$message_box_text .= gtext('It is not recommanded to import non healthy pools nor virtual devices that are part of a non healthy pool.');
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Configuration'),gtext('Synchronize')];
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
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_config_current.php',gettext('Current'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Detected'))->
			ins_tabnav_record('disks_zfs_config_sync.php',gettext('Synchronize'),gettext('Reload page'),true);
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($message_box_text)):
		print_core_box($message_box_type,$message_box_text);
	endif;
	if(isset($import) && $import === false):
		print_error_box(gtext('Nothing to synchronize'));
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:14%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
			<col style="width:9%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Pools'),count($zfs['pools']['pool'])),11);
?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Alloc');?></th>
				<th class="lhell"><?=gtext('Free');?></th>
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
			foreach($zfs['pools']['pool'] as $key => $pool):
?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="pool[]" value="<?=$pool['name'];?>" id="pool_<?=$pool['uuid'];?>"/></td>
					<td class="lcell"><label for="pool_<?=$pool['uuid'];?>"><?=$pool['name'];?></label></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['size'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['alloc'];?>(<?=$zfs['extra']['pools']['pool'][$key]['cap'];?>)</td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['free'];?></td>
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
				<td class="lcenl" colspan="11"></td>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:20%">
			<col style="width:20%">
			<col style="width:40%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Virtual Devices'),count($zfs['vdevices']['vdevice'])),5);
?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhebl"><?=gtext('Devices');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($zfs['vdevices']['vdevice'] as $key => $vdevice):
?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="vdev[]" value="<?=$vdevice['name'];?>" id="vdev_<?=$vdevice['uuid'];?>"/></td>
					<td class="lcell"><?=$vdevice['name'];?></td>
					<td class="lcell"><?=$vdevice['type'];?></td>
					<td class="lcell"><?=$zfs['extra']['vdevices']['vdevice'][$key]['pool'];?></td>
					<td class="lcebl"><?=implode(', ',$vdevice['device']);?></td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="5"></th>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
			<col style="width:8%">
<!--
			<col style="width:8%">
-->
			<col style="width:8%">
			<col style="width:8%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Datasets'),count($zfs['datasets']['dataset'])),12);
?>
			<tr>
				<th class="lhelc">&nbsp;</th>
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
			foreach($zfs['datasets']['dataset'] as $dataset):
?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="dset[]" value="<?=$dataset['identifier'];?>" id="ds_<?=$dataset['uuid'];?>"/></td>
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
				<td class="lcenl" colspan="12"></td>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:14%">
			<col style="width:11%">
			<col style="width:11%">
			<col style="width:11%">
			<col style="width:11%">
			<col style="width:11%">
			<col style="width:11%">
		</colgroup>
		<thead>
<?php
			html_titleline2(sprintf('%s (%d)',gettext('Volumes'),count($zfs['volumes']['volume'])),9);
?>
			<tr>
				<th class="lhelc">&nbsp;</th>
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
			foreach($zfs['volumes']['volume'] as $volume):
?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="vol[]" value="<?=$volume['identifier'];?>" id="vol_<?=$volume['uuid'];?>"/></td>
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
		<tfoot>
			<tr>
				<td class="lcenl" colspan="8"></td>
			</tr>
		</tfoot>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Options'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('leave_autosnapshots',gettext('Leave auto snapshot configuration'),true,gettext('Leave already configured auto snapshots.'),'',false);
			html_checkbox2('import_disks',gettext('Import disks'),true,gettext('Import disks used in configuration.'),'',false);
			html_checkbox2('import_disks_overwrite',gettext('Overwrite disks configuration'),false,gettext('Overwrite already configured disks (only affects filesystem value).'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input type="submit" name="import_config" value="<?=gtext('Synchronize');?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
