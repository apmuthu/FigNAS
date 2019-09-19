<?php
/*
	zfs.inc

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
require_once 'config.inc';
require_once 'util.inc';
require_once 'properties_zfs_dataset.php';

/**
 *	Get list of available zpools
 *	@return array An array containg the requested informations:
 *	@code
 *	[poolname] => array(
 *		[name] => pool
 *		[size] => 4.66G
 *		[alloc]
 *		[free]
 *		[expandsz]
 *		[frag] => 0%
 *		[cap] => 0%
 *		[dedup] => 1.00x
 *		[health] => ONLINE
 *		[altroot] => -)
 *		[used] => 112K
 *		[avail] => 4.66G
 *	@endcode
 */
function zfs_get_pool_list() {
	$sphere = [];
	$a_zpool = cli_zpool_info('','name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot,guid');
	if(!empty($a_zpool)):
		foreach($a_zpool as $r_pool):
//			map names of zpool list -o options to local options
			$pool_name = $r_pool['name'];
			$row = [];
			$row['name'] = $pool_name;
			$row['size'] = $r_pool['size'];
			$row['alloc'] = $r_pool['alloc'];
			$row['free'] = $r_pool['free'];
			$row['expandsz'] = $r_pool['expandsz'];
			$row['frag'] = $r_pool['frag'];
			$row['cap'] = $r_pool['cap'];
			$row['dedup'] = $r_pool['dedup'];
			$row['health'] = $r_pool['health'];
			$row['altroot'] = $r_pool['altroot'];
			$row['guid'] = $r_pool['guid'];
			$a_zfs = cli_zfs_info($pool_name,'used,avail');
			if(empty($a_zfs)):
				$row['used'] = 'Unknown';
				$row['avail'] = 'Unknown';
			else:
				foreach($a_zfs as $r_zfs):
//					there should be only one row
					$row['used'] = $r_zfs['used'];
					$row['avail'] = $r_zfs['avail'];
				endforeach;
			endif;
			$sphere[$pool_name] = $row;
		endforeach;
	endif;
	return $sphere;
}
/**
 *	Get zpool information via cli
 *	If you don't specify options the preset options will be returned.
 *	If you specify options they must be separated with a comma. Whitespaces are ignored
 *	Option keys returned are lowercase.
 *	The function returns an empty array on error or when the zpool is unknown to zfs.
 *	@param string $name
 *	@param string $options
 *	@return array
 */
function cli_zpool_info($name = '',string $options = '') {
	$a_return = [];
	$a_val = [];
	if(preg_match('/\S/',$name)):
		$name = ltrim($name);
		$cliname = escapeshellarg($name);
	else:
		$cliname = '';
	endif;
//	remove all whitespaces
	$clioptions = preg_replace('/\s/','',$options);
	if(empty($options)):
//		use default options
		$clioptions = 'name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot';
	endif;
//	get the key fields
	$a_key = explode(',',$clioptions);
//	has the all option been selected?
	if(false !== array_search('all',$a_key)):
//		reset other options, they are included in 'all'
		$clioptions = 'all';
		$a_key = [];
		$clicmd = sprintf('zpool list -p -o %s %s',$clioptions,$cliname);
		unset($a_line);
		mwexec2($clicmd,$a_line,$exitcode);
//		we need the first line to collect the header names
		if((0 == $exitcode) && (0 < count($a_line))):
			preg_match_all('/S+/',$a_line[0],$a_key);
		endif;
	endif;
	if(!empty($a_key)):
//		$a_key to lowercase
		$a_key = array_map('strtolower',$a_key);
		$clicmd = sprintf('zpool list -pH -o %s %s',$clioptions,$cliname);
		unset($a_line);
		mwexec2($clicmd,$a_line,$exitcode);
//		at least 1 line must be returned
		if((0 == $exitcode) && (0 < count($a_line))):
			foreach($a_line as $r_line):
				$a_val = explode("\t",$r_line);
				if(count($a_key) === count($a_val)):
					$a_return[] = array_combine($a_key,$a_val);
				endif;
			endforeach;
		endif;
	endif;
	return $a_return;
}
/**
 *	Get zfs option information via cli
 *	If you don't specify options the preset options will be returned.
 *	If you specify options they must be separated with a comma. Whitespaces are ignored
 *	Option keys returned are lowercase.
 *	The function returns an empty array on error or when the zpool is unknown to zfs.
 *	@param string $name
 *	@param string $options
 *	@return array
 */
function cli_zfs_info($name = '',$options = '') {
	$a_return = [];
	$a_val = [];
	if(preg_match('/\S/',$name)):
		$name = ltrim($name);
		$cliname = escapeshellarg($name);
	else:
		$cliname = '';
	endif;
//	remove all whitespaces
	$clioptions = preg_replace('/\s/','',$options);
//	options must not be blank
	if(empty($options)):
		$options = 'name,used,avail,refer,mountpoint';
	endif;
//	get the key fields
	$a_key = explode(',',$clioptions);
//	has the all option been selected?
	if(false !== array_search('all',$a_key)):
//		reset other options, they are included in 'all'
		$clioptions = 'all';
		$a_key = [];
		$clicmd = sprintf('zfs list -o %s %s',$clioptions,$cliname);
		unset($a_line);
		mwexec2($clicmd,$a_line,$exitcode);
//		we need the first line to collect the header names
		if((0 == $exitcode) && (0 < count($a_line))):
			preg_match_all('/S+/',$a_line[0],$a_key);
		endif;
	endif;
	if(!empty($a_key)):
//		$a_key to lowercase
		$a_key = array_map('strtolower',$a_key);
		$clicmd = sprintf('zfs list -pH -o %s %s',$clioptions,$cliname);
		unset($a_line);
		mwexec2($clicmd,$a_line,$exitcode);
//		at least 1 line must be returned
		if((0 == $exitcode) && (0 < count($a_line))):
			foreach($a_line as $r_line):
				$a_val = explode("\t",$r_line);
				if(count($a_key) === count($a_val)):
					$a_return[] = array_combine($a_key,$a_val);
				endif;
			endforeach;
		endif;
	endif;
	return $a_return;
}
/**
 *	Configure, create and start a zpool.
 *	@global array $config
 *	@param string $uuid The UUID of the zpool.
 *	@return int Return 0 if successful, otherwise 1
 */
function zfs_zpool_configure(string $uuid) {
	global $config;

	$zpool_features = [
//		FreeBSD 8.4
//		FreeBSD 9.2
		'async_destroy',
		'empty_bpobj',
		'lz4_compress',
//		FreeBSD 9.3
		'multi_vdev_crash_dump',
		'spacemap_histogram',
		'enabled_txg',
		'hole_birth',
		'extensible_dataset',
		'bookmarks',
		'filesystem_limits',
//		FreeBSD 10.2
		'embedded_data',
		'large_blocks',
//		FreeBSD 11.0
		'sha512',
		'skein',
//		FreeBSD 11.2
		'zpool_checkpoint',
		'device_removal',
		'obsolete_counts'
//		FreeBSD 11.3
//		'spacemap_v2',
//		FreeBSD 12
//		'large_dnode'
	];
	$pool_devices = [
		'stripe' => [],
		'mirror' => [],
		'raidz1' => [],
		'raidz2' => [],
		'raidz3' => [],
		'l2arc' => [],
		'log' => [],
		'log-mirror' => [],
		'spare' => []
	];
	$running_index = 0;
	$a_param = [];
	$sphere_grid = array_make_branch($config,'zfs','pools','pool');
	$a_vdevices = array_make_branch($config,'zfs','vdevices','vdevice');
//	get pool
	$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
	if(false === $sphere_row_id):
		return 1;
	endif;
	$pool = $sphere_grid[$sphere_row_id];
	if(!is_array($pool)):
		return 1;
	endif;
//	get pool name
	if(!(isset($pool['name']) && preg_match('/\S/',$pool['name']))):
		return 1;
	endif;
	$pool_name = ltrim($pool['name']);
//	get mount point
	if(isset($pool['mountpoint']) && preg_match('/\S/',$pool['mountpoint'])):
//		alternative mountpoint
		$mountpoint = ltrim($pool['mountpoint']);
	else:
//		set default mountpoint
		$mountpoint = sprintf('/mnt/%s',$pool_name);
	endif;
//	compose command
	$a_param[] = 'zpool';
	$a_param[] = 'create';
//	add force parameter if set
	if(isset($pool['force'])):
		$a_param[] = '-f';
	endif;
//	do not enable all available features
	$a_param[] = '-d';
//	enable selected feature flags
	foreach($zpool_features as $zpool_feature):
		$a_param[] = sprintf('-o feature@%s=enabled',$zpool_feature);
	endforeach;
	$a_param[] = '-m';
	$a_param[] = escapeshellarg($mountpoint);
//	add root parameter if set
	if(isset($pool['root']) && preg_match('/\S/',$pool['root'])):
		$a_param[] = '-R';
		$a_param[] = escapeshellarg($pool['root']);
	endif;
//	add pool name
	$a_param[] = escapeshellarg($pool_name);
//	add pool vdevs
	foreach($pool['vdevice'] as $vdevicev):
		$index = array_search_ex($vdevicev,$a_vdevices,'name');
		if(false === $index):
			return 1;
		endif;
		$vdevice = $a_vdevices[$index];
		if(array_key_exists('device',$vdevice) && is_array($vdevice['device']) && (count($vdevice['device']) > 0)):
			switch($vdevice['type']):
				case 'stripe':
				case 'disk':
				case 'file':
//					we can have many data devices, collect them in a single row
					$referer = 'stripe';
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][] = escapeshellarg($device_name);
					endforeach;
					break;
				case 'mirror':
				case 'raidz1':
				case 'raidz2':
				case 'raidz3':
//					we can have many vdevs, collect them in separate rows
					$referer = $vdevice['type'];
					$pool_devices[$referer][$running_index] = [];
					$pool_devices[$referer][$running_index][] = $referer;
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][$running_index][] = escapeshellarg($device_name);
					endforeach;
					$running_index++;
					break;
				case 'cache':
//					we can have many cache devices, collect them in a single row
					$referer = 'l2arc';
					if(count($pool_devices[$referer]) === 0):
						$pool_devices[$referer][] = 'cache';
					endif;
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][] = escapeshellarg($device_name);
					endforeach;
					break;
				case 'log':
//					only one log or log-mirror vdev definition allowed
					$referer = 'log';
					if(count($pool_devices['log-mirror']) !== 0):
						return 1;
					endif;
					if(count($pool_devices[$referer]) !== 0):
						return 1;
					endif;
					$pool_devices[$referer][] = 'log';
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][] = escapeshellarg($device_name);
					endforeach;
					break;
				case 'log-mirror':
//					only one log or log-mirror vdev definition allowed
					$referer = 'log-mirror';
					if(count($pool_devices['log']) !== 0):
						return 1;
					endif;
					if(count($pool_devices[$referer]) !== 0):
						return 1;
					endif;
					$pool_devices[$referer][] = 'log';
					$pool_devices[$referer][] = 'mirror';
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][] = escapeshellarg($device_name);
					endforeach;
					break;
				case 'spare':
//					we can have many spare devices, collect them in a sinle row
					$referer = 'spare';
					if(count($pool_devices[$referer]) === 0):
						$pool_devices[$referer][] = 'spare';
					endif;
					foreach($vdevice['device'] as $device_name):
						$pool_devices[$referer][] = escapeshellarg($device_name);
					endforeach;
					break;
				default:
					return 1;
					break;
			endswitch;
		endif;
	endforeach;
//	calculate parameter groups, stripe must be in first place
	foreach(['stripe','mirror','raidz1','raidz2','raidz3','log','log-mirror','l2arc','spare'] as $referer):
		if(count($pool_devices[$referer]) > 0):
			switch($referer):
				case 'mirror':
				case 'raidz1':
				case 'raidz2':
				case 'raidz3':
					foreach($pool_devices[$referer] as $row):
						$a_param[] = implode(' ',$row);
					endforeach;
					break;
				case 'stripe':
				case 'log':
				case 'log-mirror':
				case 'l2arc':
				case 'spare':
					$a_param[] = implode(' ',$pool_devices[$referer]);
					break;
			endswitch;
		endif;
	endforeach;
	$cmd = implode(' ',$a_param);
//	execute command
	write_log($cmd);
	$result = mwexec($cmd,true);
	if(0 != $result):
		return 1;
	endif;
//	modify access restrictions.
	@chmod($mountpoint,octdec(777));
	return 0;
}
/**
 *	Destroy a zpool by uuid.
 *	@global array $config System configuration
 *	@param string $uuid The UUID of the zpool.
 *	@return int Returns 0 if successful, otherwise 1
 */
function zfs_zpool_destroy(string $uuid) {
	global $config;

	$result = 1;
	if(is_uuid_v4($uuid)):
		$sphere_grid = array_make_branch($config,'zfs','pools','pool');
		$a_vdevices = array_make_branch($config,'zfs','vdevices','vdevice');
//		get pool configuration
		$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
		if(false !== $sphere_row_id):
			$sphere_row = $sphere_grid[$sphere_row_id];
			if(is_array($sphere_row)):
//				get pool name and mountpoint
				$pool_filter = [
					'name' => ['filter' => FILTER_VALIDATE_REGEXP,'flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']],
					'mountpoint' => ['filter' => FILTER_VALIDATE_REGEXP,'flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]
				];
				$pool_filtered = filter_var_array($sphere_row,$pool_filter,true);
				if(!is_null($pool_filtered['name'])):
					$pool_name = ltrim($pool_filtered['name']);
//					get mount point
					if(is_null($pool_filtered['mountpoint'])):
						$mountpoint = sprintf('/mnt/%s',$pool_name);
					else:
						$mountpoint = ltrim($pool_filtered['mountpoint']);
					endif;
//					try to unmount first
//					...
//					destroy pool
					$cmd_zpool = sprintf('zpool destroy %s',escapeshellarg($pool_name));
					write_log($cmd_zpool);
					$result = mwexec($cmd_zpool,true);
//					remove mount moint
					@rmdir($mountpoint);
					if($result == 0):
//					destroy gnop devices
						array_make_branch($sphere_row,'vdevice');
						foreach($sphere_row['vdevice'] as $vdevice_name):
							$a_vdevices_row_key = array_search_ex($vdevice_name,$a_vdevices,'name');
							if(false !== $a_vdevices_row_key):
								$a_vdevice = $a_vdevices[$a_vdevices_row_key];
								if(isset($a_vdevice['aft4k'])):
									array_make_branch($a_vdevice,'device');
									foreach($a_vdevice['device'] as $device_name):
										$device_name_nop = sprintf('%s.nop',$device_name);
										$cmd_gnop = sprintf('gnop destroy %s',escapeshellarg($device_name_nop));
										write_log($cmd_gnop);
										$result = mwexec($cmd_gnop,true);
									endforeach;
								endif;
							endif;
						endforeach;
//					else:
//						zpool destroy command returned an error
					endif;
//				else:
//					pool name invalid or does not exist in pool configuration record
				endif;
//			else:
//				pool configuration record error
			endif;
//		else:
//			uuid not found
		endif;
//	else:
//		invalid uuid format
	endif;
	return $result;
}
/**
 *	Update properties of a zpool.
 *	@global array $config
 *	@param string $uuid The UUID of the zpool to be updated.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function zfs_zpool_properties(string $uuid) {
	global $config;

	$result = 1;
	$supported_properties = [
		'mountpoint'
	];
	if(is_uuid_v4($uuid)):
		$sphere_grid = array_make_branch($config,'zfs','pools','pool');
		$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
		if(false !== $sphere_row_id):
			$sphere_row = $sphere_grid[$sphere_row_id];
			if(is_array($sphere_row)):
//				get pool name and mountpoint
				$pool_filter = [
					'name' => ['filter' => FILTER_VALIDATE_REGEXP,'flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']],
					'mountpoint' => ['filter' => FILTER_VALIDATE_REGEXP,'flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]
				];
				$pool_filtered = filter_var_array($sphere_row,$pool_filter,true);
				if(!is_null($pool_filtered['name'])):
					$result = 0;
					$pool_name = ltrim($pool_filtered['name']);
//					get mount point
					if(is_null($pool_filtered['mountpoint'])):
						$mountpoint = sprintf('/mnt/%s',$pool_name);
					else:
						$mountpoint = ltrim($pool_filtered['mountpoint']);
					endif;
//					unmount pools
					$cmd = 'zfs umount -a';
					write_log($cmd);
					$result |= mwexec($cmd,true);
//					update zpool properties
					foreach($supported_properties as $property):
						$param = '';
						switch($property):
							case 'mountpoint':
								$param = sprintf('mountpoint=%s',escapeshellarg($mountpoint));
								break;
						endswitch;
//						update zpool property
						if(!empty($param)):
							$cmd = sprintf('zfs set %s %s',$param,escapeshellarg($pool_name));
							write_log($cmd);
							$result |= mwexec($cmd,true);
						endif;
					endforeach;
//					remount pools
					$cmd = 'zfs mount -a';
					write_log($cmd);
					$result |= mwexec($cmd,true);
//				else:
//					pool name invalid or does not exist in pool configuration record
				endif;
//			else:
//				pool configuration record error
			endif;
//		else:
//			uuid not found
		endif;
//	else:
//		invalid uuid format
	endif;
	return $result;
}
/**
 *	Wrapper to execute zpool commands.
 *	@param string $command Command to execute (e.g. upgrade).
 *	@param string $param The command parameter.
 *	@param bool $verbose Display command results or hide them.
 *	@param bool $stderr Redirect stderr to stdout to display error messages too.
 *	@param bool $out Catch command output
 *	@param array $output
 *	@return int Returns 0 if successful, 1 if error.
 */
function zfs_zpool_cmd(string $command,string $param,bool $verbose = false,bool $stderr = true,bool $out = false,array &$output = []) {
	$result = 1;
	$cmd = sprintf('zpool %s %s',$command,$param);
	write_log($cmd);
	if(true === $verbose):
		if(true === $stderr):
//			redirect error message to stdout
			$cmd .= ' 2>&1';
		endif;
		system($cmd,$result);
	else:
		if(true === $out):
			mwexec2($cmd,$output,$result);
		else:
			$result = mwexec($cmd,true);
		endif;
	endif;
	return $result;
}
/**
 *	Configure, create and start a ZFS dataset.
 *	@global array $config
 *	@param string $uuid The UUID of the dataset to be configured.
 *	@return int Return 0 if successful, otherwise 1.
 */
function zfs_dataset_configure(string $uuid) {
	global $config;

//	get dataset
	$sphere_grid = array_make_branch($config,'zfs','datasets','dataset');
	$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
	if(false === $sphere_row_id):
		return 1;
	endif;
	$sphere_row = $sphere_grid[$sphere_row_id];
	if(!is_array($sphere_row)):
		return 1;
	endif;
//	get pool name
	if(!(array_key_exists('pool',$sphere_row) && is_array($sphere_row['pool']) && (0 < count($sphere_row['pool'])) && preg_match('/\S/',$sphere_row['pool'][0]))):
		return 1;
	endif;
	$pool_name = ltrim($sphere_row['pool'][0]);
//	get dataset name
	if(!(array_key_exists('name',$sphere_row) && preg_match('/\S/',$sphere_row['name']))):
		return 1;
	endif;
	$filesystem_name = ltrim($sphere_row['name']);
	$fq_filesystem_name = sprintf('%s/%s',$pool_name,$filesystem_name);
	$a_param = [
		'zfs',
		'create'
	];
//	additional parameter
	$supported_options = [
		'aclinherit',
		'aclmode',
		'atime',
		'canmount',
		'casesensitivity',
		'compression',
		'dedup',
		'quota',
		'readonly',
		'reservation',
		'snapdir',
		'sync'
	];
	foreach($supported_options as $referer):
		switch($referer):
			case 'aclinherit':
			case 'aclmode':
			case 'atime':
			case 'casesensitivity':
			case 'compression':
			case 'dedup':
			case 'quota':
			case 'reservation':
			case 'sync':
				if(array_key_exists($referer,$sphere_row) && preg_match('/\S/',$sphere_row[$referer])):
					$a_param[] = '-o';
					$a_param[] = sprintf('%s=%s',$referer,trim($sphere_row[$referer]));
				endif;
				break;
			case 'canmount':
//			case 'xattr': // not supported in FreeBSD
				if(!array_key_exists($referer,$sphere_row)):
					$a_param[] = '-o';
					$a_param[] = sprintf('%s=off',$referer);
				endif;
				break;
			case 'readonly':
				if(array_key_exists($referer,$sphere_row)):
					$a_param[] = '-o';
					$a_param[] = sprintf('%s=on',$referer);
				endif;
				break;
			case 'snapdir':
				if(array_key_exists($referer,$sphere_row)):
					$a_param[] = '-o';
					$a_param[] = sprintf('%s=visible',$referer);
				endif;
				break;
		endswitch;
	endforeach;
//	options
	$mounted = isset($sphere_row['canmount']) ? true : false;
//	add filesystem name
	$a_param[] = escapeshellarg($fq_filesystem_name);
//	create dataset
	$result = 0;
	$cmd = implode(' ',$a_param);
	write_log($cmd);
	$result |= mwexec($cmd,true);
	if($result != 0):
		write_log(sprintf('Error: Failed to create dataset %s',$fq_filesystem_name));
		return $result;
	endif;
//	check mountpoint
	$mountpoint = sprintf('/mnt/%s',$fq_filesystem_name);
	$cmd = sprintf('zfs list -H -t filesystem -o mountpoint %s',escapeshellarg($fq_filesystem_name));
	mwexec2($cmd,$rawdata2);
	if(!empty($rawdata2) && ($rawdata2[0] != 'none') && ($rawdata2[0] != 'legacy')):
		$mountpoint = $rawdata2[0];
	endif;
//	set access restrictions
	if(!empty($mountpoint) && isset($sphere_row['canmount'])):
		if(is_array($sphere_row['accessrestrictions'])):
			$mode = $sphere_row['accessrestrictions']['mode'];
			$owner = $sphere_row['accessrestrictions']['owner'];
			$group = $sphere_row['accessrestrictions']['group'][0];
			write_log(sprintf('Access restrictions to be set: %s %s %s %s',$mountpoint,$mode,$owner,$group));
			if(file_exists($mountpoint) && is_writable($mountpoint)):
				if(!@chmod($mountpoint,octdec($mode)) || !@chown($mountpoint,$owner) || !@chgrp($mountpoint,$group)):
					write_log(sprintf('Error: Failed to set access restrictions on directory %s',$mountpoint));
				endif;
			endif;
		endif;
	endif;
	return $result;
}
/**
 *	Delete a ZFS filesystem.
 *	@global array $config Configuration.
 *	@param string $uuid The UUID of the filesystem to be deleted.
 *	@param bool $recursively Recursvvly destroys all children.
 *	@return int 0 if successful, otherwise 1.
 */
function zfs_dataset_destroy(string $uuid,bool $recursively = false) {
	global $config;

	if(is_uuid_v4($uuid)):
//		get dataset
		$sphere_grid = array_make_branch($config,'zfs','datasets','dataset');
		$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
		if(false !== $sphere_row_id):
			$sphere_row = $sphere_grid[$sphere_row_id];
			if(is_array($sphere_row)):
//				get pool and filesystem name
				$pool_name = ltrim($sphere_row['pool'][0] ?? '');
				$filesystem_name = ltrim($sphere_row['name'] ?? '');
				if(preg_match('/\S/',$pool_name) && preg_match('/\S/',$filesystem_name)):
					$fq_filesystem_name = sprintf('%s/%s',$pool_name,$filesystem_name);
					$a_param = [
						'zfs',
						'destroy',
						'-f'
					];
//					additional parameter
					if($recursively):
						$a_param[] = '-r';
					endif;
//					add filesystem name
					$a_param[] = escapeshellarg($fq_filesystem_name);
					$cmd = implode(' ',$a_param);
					write_log($cmd);
//					destroy filesystem
					$result = 0;
					$result |= mwexec($cmd,true);
					if($result != 0):
						write_log(sprintf('Error: Failed to destroy filesystem %s',$fq_filesystem_name));
					endif;
					return $result;
				endif;
			endif;
		endif;
	endif;
	return 1;
}
/**
 *	Update properties of a ZFS dataset.
 *	@global array $config
 *	@param string $uuid The UUID of the dataset to be updated.
 *	@return int Return 0 if successful, otherwise 1.
 */
function zfs_dataset_properties(string $uuid) {
	global $config;

	$sphere_grid = array_make_branch($config,'zfs','datasets','dataset');
//	get dataset
	$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
	if(false === $sphere_row_id):
		return 1;
	endif;
	$sphere_row = $sphere_grid[$sphere_row_id];
	if(!is_array($sphere_row)):
		return 1;
	endif;
//	get pool name
	if(!(isset($sphere_row['pool']) && is_array($sphere_row['pool']) && (0 < count($sphere_row['pool'])) && preg_match('/\S/',$sphere_row['pool'][0]))):
		return 1;
	endif;
	$pool_name = ltrim($sphere_row['pool'][0]);
//	get dataset name
	if(!(isset($sphere_row['name']) && preg_match('/\S/',$sphere_row['name']))):
		return 1;
	endif;
	$filesystem_name = ltrim($sphere_row['name']);
	$fq_filesystem_name = sprintf('%s/%s',$pool_name,$filesystem_name);
//	is the dataset mounted?
	$mounted = false;
	$cmd = sprintf('zfs list -H -t filesystem -o mounted %s',escapeshellarg($fq_filesystem_name));
	mwexec2($cmd,$rawdata);
	if(!empty($rawdata) && $rawdata[0] == 'yes'):
		$mounted = true;
	endif;
//	determine mountpoint
	$mountpoint = sprintf('/mnt/%s',$fq_filesystem_name);
	$cmd = sprintf('zfs list -H -t filesystem -o mountpoint %s',escapeshellarg($fq_filesystem_name));
	mwexec2($cmd,$rawdata2);
	if(!empty($rawdata2) && ($rawdata2[0] != 'none') && ($rawdata2[0] != 'legacy')):
		$mountpoint = ltrim($rawdata2[0]);
	endif;
//	properties
	$result = 0;
	$supported_options = [
		'aclinherit',
		'aclmode',
		'atime',
		'canmount',
		'compression',
		'dedup',
		'quota',
		'readonly',
		'reservation',
		'snapdir',
		'sync',
		'xattr'
	];
	foreach($supported_options as $referer):
		$param = '';
		switch($referer):
			case 'aclinherit':
			case 'aclmode':
			case 'atime':
			case 'compression':
			case 'dedup':
			case 'sync':
				if(isset($sphere_row[$referer]) && preg_match('/\S/',$sphere_row[$referer])):
					$param = sprintf('%s=%s',$referer,trim($sphere_row[$referer]));
				endif;
				break;
			case 'canmount':
				if(!isset($sphere_row['canmount'])):
					$param = 'canmount=off';
				else:
					$param = 'canmount=on';
				endif;
				break;
			case 'quota':
				if(isset($sphere_row['quota']) && preg_match('/\S/',$sphere_row['quota'])):
					$param = sprintf('quota=%s',trim($sphere_row['quota']));
				else:
					$param = 'quota=none';
				endif;
				break;
			case 'readonly':
				if(isset($sphere_row['readonly'])):
					$param = 'readonly=on';
				else:
					$param = 'readonly=off';
				endif;
				break;
			case 'reservation':
				if(isset($sphere_row['reservation']) && preg_match('/\S/',$sphere_row['reservation'])):
					$param = sprintf('reservation=%s',trim($sphere_row['reservation']));
				else:
					$param = 'reservation=none';
				endif;
				break;
			case 'snapdir':
				if(!isset($sphere_row['snapdir'])):
					$param = 'snapdir=hidden';
				else:
					$param = 'snapdir=visible';
				endif;
				break;
			case 'xattr':
/*
				if(!isset($sphere_row['xattr'])):
					$param = 'xattr=off';
				else:
					$param = 'xattr=on';
				endif;
 */
				break;
		endswitch;
//		update dataset properties
		if(!empty($param)):
			$cmd = sprintf('zfs set %s %s',$param,escapeshellarg($fq_filesystem_name));
			write_log($cmd);
			$result |= mwexec($cmd,true);
		endif;
	endforeach;
	if(isset($sphere_row['canmount']) && !$mounted):
		$cmd = sprintf('zfs mount %s',escapeshellarg($fq_filesystem_name));
		write_log($cmd);
		$result |= mwexec($cmd,true);
	endif;
//	set access restrictions
	if(!empty($mountpoint) && isset($sphere_row['canmount'])):
		if(is_array($sphere_row['accessrestrictions'])):
			$mode = $sphere_row['accessrestrictions']['mode'];
			$owner = $sphere_row['accessrestrictions']['owner'];
			$group = $sphere_row['accessrestrictions']['group'][0];
			write_log(sprintf('Access restrictions to be set: %s %s %s %s',$mountpoint,$mode,$owner,$group));
			if(file_exists($mountpoint) && is_writable($mountpoint)):
				if(!@chmod($mountpoint,octdec($mode)) || !@chown($mountpoint,$owner) || !@chgrp($mountpoint,$group)):
					write_log(sprintf('Error: Failed to set access restrictions on directory %s',$mountpoint));
				endif;
			endif;
		endif;
	endif;
	return $result;
}
/**
 *	Configure, create and start a ZFS volume.
 *	@global array $config
 *	@param string $uuid The UUID of the volume to be configured.
 *	@return int Return 0 if successful, otherwise 1.
 */
function zfs_volume_configure(string $uuid) {
	global $config;

	$sphere_grid = array_make_branch($config,'zfs','volumes','volume');
	$prop = new properties_zfs_dataset();
	$supported_properties = [
		'sparse',
		'checksum',
		'compression',
		'dedup',
		'logbias',
		'primarycache',
		'secondarycache',
		'sync',
		'volblocksize',
		'volmode',
		'volsize'
	];
//	get volume
	$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
	if(false === $sphere_row_id):
		return 1;
	endif;
	$sphere_row = $sphere_grid[$sphere_row_id];
	if(!is_array($sphere_row)):
		return 1;
	endif;
//	get pool name
	if(!(isset($sphere_row['pool']) && is_array($sphere_row['pool']) && (0 < count($sphere_row['pool'])) && preg_match('/\S/',$sphere_row['pool'][0]))):
		return 1;
	endif;
	$pool_name = ltrim($sphere_row['pool'][0]);
//	get volume name
	if(!(isset($sphere_row['name']) && preg_match('/\S/',$sphere_row['name']))):
		return 1;
	endif;
	$volume_name = ltrim($sphere_row['name']);
	$fq_volume_name = sprintf('%s/%s',$pool_name,$volume_name);
	$a_param = [
		'zfs',
		'create'
	];
//	additional parameter
	$required_properties = 1;
	foreach($supported_properties as $referer):
		switch($referer):
			case 'sparse':
				if(isset($sphere_row[$referer])):
					$a_param[] = '-s';
				endif;
				break;
			case 'volsize':
				$val = $prop->$referer->validate_value($sphere_row[$referer]);
				if(isset($val) && preg_match('/\S/',$val)):
					$a_param[] = '-V';
					$a_param[] = $val;
					$required_properties--;
				endif;
				break;
			case 'checksum':
			case 'compression':
			case 'dedup':
			case 'logbias':
			case 'primarycache':
			case 'secondarycache':
			case 'sync':
			case 'volblocksize':
			case 'volmode':
				$val = $prop->$referer->validate_value($sphere_row[$referer]);
				if(isset($val) && preg_match('/\S/',$val)):
					$a_param[] = '-o';
					$a_param[] = sprintf('%s=%s',$referer,$val);
				endif;
				break;
		endswitch;
	endforeach;
	if(0 < $required_properties):
		return 1;
	endif;
//	add volume name
	$a_param[] = escapeshellarg($fq_volume_name);
	$cmd = implode(' ',$a_param);
//	create volume
	write_log($cmd);
	return mwexec($cmd,true);
}
/**
 *	Delete a ZFS volume.
 *	@global array $config Configuration.
 *	@param string $uuid The UUID of the volume to be deleted.
 *	@param bool $recursively Recursvvly destroys all children.
 *	@return int 0 if successful, otherwise 1.
 */
function zfs_volume_destroy(string $uuid,bool $recursively = false) {
	global $config;

	if(is_uuid_v4($uuid)):
//		get volume
		$sphere_grid = array_make_branch($config,'zfs','volumes','volume');
		$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
		if(false !== $sphere_row_id):
			$sphere_row = $sphere_grid[$sphere_row_id];
			if(is_array($sphere_row)):
//				get pool and volume name
				$pool_name = ltrim($sphere_row['pool'][0] ?? '');
				$volume_name =  ltrim($sphere_row['name'] ?? '');
				if(preg_match('/\S/',$pool_name) && preg_match('/\S/',$volume_name)):
					$fq_volume_name = sprintf('%s/%s',$pool_name,$volume_name);
					$a_param = [
						'zfs',
						'destroy'
					];
//					additional parameter
					if($recursively):
						$a_param[] = '-r';
					endif;
//					add volume name
					$a_param[] = escapeshellarg($fq_volume_name);
					$cmd = implode(' ',$a_param);
					write_log($cmd);
//					destroy volume
					$result = 0;
					$result |= mwexec($cmd,true);
					if($result != 0):
						write_log(sprintf('Error: Failed to destroy volume %s',$fq_volume_name));
					endif;
					return $result;
				endif;
			endif;
		endif;
	endif;
	return 1;
}
/**
 *	Calculates the reservation value
 *	@param string $volsize
 *	@return boolean|string
 */
function zfs_get_volume_reservation($volsize) {
	if(empty($volsize)):
		return '';
	endif;
	unset($matches);
	if(preg_match('/^([0-9]+(?:\.[0-9]+)?)(.*)$/',$volsize,$matches)):
//		increase by 3%
		$size = (float)$matches[1] * 1.03;
		$uom = $matches[2];
//		round up small values
		if(preg_match('/(m|k|^$)/',$uom)):
			$size = ceil($size);
		endif;
		return sprintf('%s%s',trim($size),trim($uom));
	endif;
	return false;
}
/**
 *	Update properties of a ZFS volume.
 *	@global array $config
 *	@param string $uuid The UUID of the volume to be updated.
 *	@return int Return 0 if successful, otherwise 1.
 */
function zfs_volume_properties(string $uuid) {
	global $config;

	$sphere_grid = array_make_branch($config,'zfs','volumes','volume');
	$prop = new properties_zfs_dataset();
//	get volume
	$sphere_row_id = array_search_ex($uuid,$sphere_grid,'uuid');
	if(false === $sphere_row_id):
		return 1;
	endif;
	$sphere_row = $sphere_grid[$sphere_row_id];
	if(!is_array($sphere_row)):
		return 1;
	endif;
//	get pool name
	if(!(isset($sphere_row['pool']) && is_array($sphere_row['pool']) && (0 < count($sphere_row['pool'])) && preg_match('/\S/',$sphere_row['pool'][0]))):
		return 1;
	endif;
	$pool_name = ltrim($sphere_row['pool'][0]);
//	get volume name
	if(!(isset($sphere_row['name']) && preg_match('/\S/',$sphere_row['name']))):
		return 1;
	endif;
	$volume_name = ltrim($sphere_row['name']);
	$fq_volume_name = sprintf('%s/%s',$pool_name,$volume_name);
	$referer = 'volsize';
	$volsize = $prop->$referer->validate_value($sphere_row[$referer]);
	if(isset($volsize)):
		if(false === ($reservation = zfs_get_volume_reservation($volsize))):
			return 1;
		endif;
	endif;
	$supported_properties = [
		'sparse',
		'checksum',
		'compression',
		'dedup',
		'logbias',
		'primarycache',
		'secondarycache',
		'sync',
		'volmode',
		'volsize'
	];
	$a_param = [];
	$required_properties = 0;
	foreach($supported_properties as $referer):
		switch($referer):
			case 'sparse':
				if(isset($sphere_row['sparse'])):
					$a_param[] = sprintf('%s=%s','refreservation','none');
				elseif(isset($volsize)):
//					this might fail
					$a_param[] = sprintf('%s=%s','refreservation',$volsize);
				endif;
				break;
			case 'checksum':
			case 'compression':
			case 'dedup':
			case 'logbias':
			case 'primarycache':
			case 'secondarycache':
			case 'sync':
			case 'volmode':
				$val = $prop->$referer->validate_value($sphere_row[$referer]);
				if(isset($val) && preg_match('/\S/',$val)):
					$a_param[] = sprintf('%s=%s',$referer,$val);
				endif;
				break;
			case 'volsize':
				if(isset($volsize)):
					$a_param[] = sprintf('%s=%s',$referer,$volsize);
				endif;
				break;
		endswitch;
	endforeach;
	if(0 < $required_properties):
		return 1;
	endif;
	$param = implode(' ',$a_param);
//	Update volume properties
	if(empty($param)):
		return 1;
	endif;
	$cmd = sprintf('zfs set %s %s',$param,escapeshellarg($fq_volume_name));
	write_log($cmd);
	return mwexec($cmd,true);
}
/**
 *	Configure, create and start a ZFS snapshot.
 *	@param array snapshot The name of the snapshot to be configured.
 *	@return array An array containg the result:
 *	@code
 *	[output] => ['lines']
 *	[retval] => 0 if successful, otherwise 1
 *	@endcode
 */
function zfs_snapshot_configure($snapshot) {
	$result = ['output' => ['error'],'retval' => -1];
	if(empty($snapshot) || empty($snapshot['snapshot'])):
		return $result;
	endif;
	if(!preg_match('/.+\@.+/',$snapshot['snapshot'])):
		return $result;
	endif;
//	prepare command
	$a_param = [
		'zfs',
		'snapshot'
	];
//	additional parameter
	if(!empty($snapshot['recursive'])):
		$a_param[] = '-r ';
	endif;
//	add snapshot name
	$a_param[] = escapeshellarg($snapshot['snapshot']);
//	redirect stderr to stdout
	$a_param[] = '2>&1';
//	compile command
	$cmd = implode(' ',$a_param);
	write_log($cmd);
//	execute the command and save the log
	exec($cmd,$output,$retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}
/**
 *	Delete a ZFS snapshot.
 *	@param array $snapshot The name of the snapshot to be deleted.
 *	@return array An array containg the result:
 *	@code
 *	[output] => ['lines']
 *	[retval] => 0 if successful, otherwise 1
 *	@endcode
 */
function zfs_snapshot_destroy($snapshot) {
	$result = ['output' => ['error'],'retval' => -1];
	if(empty($snapshot) || empty($snapshot['snapshot'])):
		return $result;
	endif;
	if(!preg_match('/.+\@.+/',$snapshot['snapshot'])):
		return $result;
	endif;
//	prepare command
	$a_param = [
		'zfs',
		'destroy'
	];
//	additional parameter
	if(!empty($snapshot['recursive'])):
		$a_param[] = '-r';
	endif;
//	add snapshot name
	$a_param[] = escapeshellarg($snapshot['snapshot']);
//	redirect stderr to stdout
	$a_param[] = '2>&1';
//	compile command
	$cmd = implode(' ',$a_param);
	write_log($cmd);
//	execute the command, and save the log
	exec($cmd,$output,$retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}
/**
 *	Rollback a ZFS snapshot.
 *	@param array $snapshot
 *	@return array
 */
function zfs_snapshot_rollback($snapshot) {
	$result = ['output' => ['error'],'retval' => -1];
	if(empty($snapshot) || empty($snapshot['snapshot'])):
		return $result;
	endif;
	if(!preg_match('/.+\@.+/',$snapshot['snapshot'])):
		return $result;
	endif;
//	prepare command
	$a_param = [
		'zfs',
		'rollback'
	];
//	additional parameter
	if(!empty($snapshot['force_delete'])):
		$a_param[] = '-r';
	endif;
//	add snapshot name
	$a_param[] = escapeshellarg($snapshot['snapshot']);
//	redirect stderr to stdout
	$a_param[] = '2>&1';
//	compile command
	$cmd = implode(' ',$a_param);
	write_log($cmd);
//	execute the command and save the log
	exec($cmd,$output,$retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}
/**
 *	Clone a ZFS snapshot.
 *	@param array $snapshot The name of the snapshot to be cloned.
 *	@return array An array containg the result:
 *	@code
 *	[output] => ['lines']
 *	[retval] => 0 if successful, otherwise 1
 *	@endcode
 */
function zfs_snapshot_clone($snapshot) {
	$result = ['output' => ['error'],'retval' => -1];
	if(empty($snapshot) || empty($snapshot['snapshot'])):
		return $result;
	endif;
	if(!preg_match('/.+\@.+/',$snapshot['snapshot'])):
		return $result;
	endif;
	if(empty($snapshot['path'])):
		return $result;
	endif;
//	prepare command
	$a_param = [
		'zfs',
		'clone'
	];
//	additional parameter
//	add snapshot name
	$a_param[] = escapeshellarg($snapshot['snapshot']);
//	add path
	$a_param[] = escapeshellarg($snapshot['path']);
//	redirect stderr to stdout
	$a_param[] = '2>&1';
//	compile command
	$cmd = implode(' ',$a_param);
	write_log($cmd);
//	execute the command and save the log
	exec($cmd,$output,$retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}
/**
 *	Update properties of a ZFS snapshot.
 *	@param array $snapshot The name of the snapshot to be updated.
 *	@return int Return 0 if successful, otherwise 1.
 */
function zfs_snapshot_properties($snapshot) {
	if(empty($snapshot) || empty($snapshot['snapshot'])):
		return -1;
	endif;
	if(!preg_match('/.+\@.+/',$snapshot['snapshot'])):
		return -1;
	endif;
	$result = 0;
	foreach(explode(' ','dummy') as $referer):
		$param = '';
		switch($referer):
		endswitch;
//		update snapshot properties
		if(!empty($param)):
//			prepare command
			$a_param = [
				'zfs',
				'set'
			];
//			additional parameter
			$a_param[] = $param;
//			add snapshot name
			$a_param[] = escapeshellarg($snapshot['snapshot']);
//			compile command
			$cmd = implode(' ',$a_param);
			write_log($cmd);
			$result |= mwexec($cmd,true);
		endif;
	endforeach;
	return $result;
}
/**
 *	Delete a ZFS clone filesystem, volume.
 *	@param string $clone dataset The name of the dataset to be deleted.
 *	@return array An array containg the result:
 *	@code
 *	[output] => ['lines']
 *	[retval] => 0 if successful, otherwise 1
 *	@endcode
 */
function zfs_clone_destroy($clone) {
	$result = ['output' => ['error'],'retval' => -1];
	if(empty($clone) || empty($clone['path'])):
		return $result;
	endif;
	if(!preg_match('/.+\/.+/',$clone['path'])):
		return $result;
	endif;
//	prepare command
	$a_param = [
		'zfs',
		'destroy'
	];
//	additional parameter
//	add clone name
	$a_param[] = escapeshellarg($clone['path']);
//	redirect stderr to stdout
	$a_param[] = '2>&1';
//	compile command
	$cmd = implode(' ',$a_param);
	write_log($cmd);
//	execute the command and save the log
	exec($cmd,$output,$retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}
/**
 *	update notify for ZFS functions
 *	@param string $key
 *	@param string $function
 *	@return mixed
 */
function zfs_updatenotify_process($key,$function) {
	$a_notification = updatenotify_get($key);
	if(!is_array($a_notification)):
		$result = ['output' => ['empty'],'retval' => 0];
		return $result;
	endif;
	foreach($a_notification as $notificationv):
		$ret = call_user_func($function,$notificationv['mode'],$notificationv['data']);
		if(0 != $ret['retval']):
			return $ret;
		endif;
	endforeach;
	$result = ['output' => [],'retval' => 0];
	return $result;
}
/**
 *	Validate the name for a ZFS pool..
 *	- Pool name must be of type string.
 *	- An empty pool name is not allowed.
 *	- The pool name must begin with a letter, followed by alphanumeric,
 *	  underscore, dash, blank and period characters.
 *	- The length of the pool name must be less than 256 characters.
 *	@param string $name ZFS pool name
 *	@return boolean
 */
function zfs_is_valid_poolname($name) {
	$helpinghand = preg_quote('.-_: ','/');
	$regexp = '/^[A-Za-z][0-9A-Za-z' . $helpinghand . '.-_: ]*$/';
	$result = filter_var($name,FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $regexp]]);
	if(isset($result)):
//		pool names beginning with "mirror", "raidz" and "spare" are not allowed, as are names beginning with the pattern "c[0-9]".
		if(preg_match('/^(mirror|raidz|spare|c[0-9])/',$name)):
			return false;
		endif;
//		log is a reserved word
		if(0 == strcmp('log',$name)):
			return false;
		endif;
		if(strlen($name) > 255):
			return false;
		endif;
		return true;
	endif;
	return false;
}
/**
 *	Validate the name for a ZFS filesystem.
 *	The name must begin with an alphanumeric character, followed by alphanumeric,
 *	underscore ("_"), dash ("-"), blank (" ") or period (".") characters.
 *	@param string $name ZFS filesystem name
 *	@return boolean
 */
function zfs_is_valid_dataset_name($name) {
//	only string with at least one non-whitespace character is allowed
	if(is_string($name) && preg_match('/\S/',$name)):
		$helpinghand = preg_quote('.-_ ','/');
		if(false !==  preg_match('/^[0-9a-z][0-9a-z' . $helpinghand . ']*(?:\/[0-9a-z][0-9a-z' . $helpinghand . ']*)*$/i',$name)):
			return true;
		endif;
	endif;
	return false;
}
/**
 *	The volume name must begin with an alphanumeric character.
 *	Following characters must be alphanumeric, underscore ("_"), dash ("-"), blank (" ") or period (".").
 *	@param string $name ZFS volume name
 *	@return boolean
 */
function zfs_is_valid_volume_name($name) {
//	only string with at least one non-whitespace characters allowed
	if(is_string($name) && preg_match('/\S/',$name)):
		$helpinghand = preg_quote('.-_ ', '/');
		if(false !==  preg_match('/^[0-9a-z][0-9a-z' . $helpinghand . ']*(?:\/[0-9a-z][0-9a-z' . $helpinghand . ']*)*$/i',$name)):
			return true;
		endif;
	endif;
	return false;
}
