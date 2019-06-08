<?php
/*
	config.inc

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
require_once 'globals.inc';
require_once 'util.inc';
require_once 'xmlparse.inc';
require_once 'system.inc';
require_once 'array.inc';
require_once 'rc.inc';
require_once 'tui.inc';

//	Get product information
$productname = get_product_name();
//	read platform
if(file_exists("{$g['etc_path']}/platform")):
	$g['fullplatform'] = strtolower(chop(file_get_contents("{$g['etc_path']}/platform")));
	$data = explode('-',$g['fullplatform']);
	$g['arch'] = $data[0];
	$g['platform'] = $data[1];
else:
	$g['arch'] = 'unknown';
	$g['platform'] = 'unknown';
	$g['fullplatform'] = 'unknown';
endif;
//	RootOnZFS
$g['zroot'] = (0 == mwexec('/sbin/mount | grep -q zroot')) ? true : false;
if(is_booting()):
	//	Find the device where config.xml resides and write out an fstab
	unset($cfgdevice);
	//	Check if there's already an fstab ('full' release booting?)
	if(!file_exists("{$g['etc_path']}/fstab")):
		//	Display platform type.
		echo "Booting platform: {$g['platform']}",PHP_EOL;
		echo 'Looking for configuration file:';
		//	Get list of kernel known disks
		$disks = explode(' ',exec('/sbin/sysctl -q -n kern.disks'));
		if(stristr($g['platform'],'livecd') || stristr($g['platform'],'liveusb')):
			//	Config is on floppy disk/USB stick for LiveCD version per default
			$cfgdevice = $cfgpartition = 'fd0';
			$cfgfstype = 'msdos';
			//	Display progress...
			echo ' fd0';
			//	Check if there is any USB device that can be used
			//	Set minimum capacity of a media to 128KiB for storing config.xml
			$min_media_size = 131072;
			foreach($disks as $disk):
				$found = false;
				//	Skip non USB (SCSI) device. The filesystem type must be MSDOS
				if(false == preg_match('/da[0-9]/',$disk)):
					continue;
				endif;
				//	Display progress...
				echo " {$disk}";
				//	Try to mount device using file system MSDOS (FAT16/32)
				if(!$found):
					$test_dev = sprintf('/dev/%ss1',$disk);
					$cmd = sprintf('/sbin/mount -r -t %s %s %s','msdos',$test_dev,$g['cf_path']);
					if(0 == mwexec($cmd)):
						//	Validate partition size, should be at least $min_size
						$diskinfo = disks_get_diskinfo($test_dev);
						if(!empty($diskinfo)):
							$media_size = $diskinfo['mediasize_bytes'] ?? 0;
							if($media_size >= $min_media_size):
								$cfgdevice = $disk;
								$cfgpartition = sprintf('%ss1',$cfgdevice);
								$found = true;
							endif;
						endif;
						$cmd = sprintf('/sbin/umount -f %s',$g['cf_path']);
						mwexec($cmd);
					endif;
				endif;
				if(!$found):
					$test_dev = sprintf('/dev/%s',$disk);
					$cmd = sprintf('/sbin/mount -r -t %s %s %s','msdos',$test_dev,$g['cf_path']);
					if(0 == mwexec($cmd)):
						//	Validate disk size, should be at least $min_size
						$diskinfo = disks_get_diskinfo($test_dev);
						if(!empty($diskinfo)):
							$media_size = $diskinfo['mediasize_bytes'] ?? 0;
							if($media_size >= $min_media_size):
								$cfgdevice = $disk;
								$cfgpartition = $cfgdevice;
								$found = true;
							endif;
						endif;
						$cmd = sprintf('/sbin/umount -f %s',$g['cf_path']);
						mwexec($cmd);
					endif;
				endif;
				//	Exit loop when found
				if($found):
					break;
				endif;
			endforeach;
			//	Display device information used to store the configuration
			echo PHP_EOL;
			echo "Using device=/dev/{$cfgpartition} fstype={$cfgfstype} to store configuration.",PHP_EOL;
		else:
			$bootdevice = get_bootdevice();
			if(!empty($bootdevice)):
				if(preg_match('/^([a-z]+[0-9]+)/i',$bootdevice,$matches)):
					$bootdisk = $matches[1];
					$new_disks = [];
					$new_disks[] = $bootdisk;
					foreach($disks as $disk):
						if($disk != $bootdisk):
							$new_disks[] = $disk;
						endif;
					endforeach;
					$disks = $new_disks;
				endif;
			endif;
			//	Probe kernel known disks until we find one with config.xml
			foreach($disks as $disk):
				//	Skip mfs-mounted filesystems
				if(false != preg_match('/md[0-9]/',$disk)):
					continue;
				endif;
				//	Display progress...
				echo " {$disk}";
				//	Try to mount device using UFS
				if(0 == mwexec("/sbin/mount -r /dev/{$disk}s1a {$g['cf_path']}")): // Search on slice 1
					if(file_exists("{$g['cf_conf_path']}/config.xml")):
						$cfgdevice = $disk;
						$cfgpartition = "{$cfgdevice}s1a";
						$cfgfstype = 'ufs';
					endif;
					//	Unmount device
					mwexec("/sbin/umount -f {$g['cf_path']}");
				elseif(0 == mwexec("/sbin/mount -r /dev/{$disk}p2 {$g['cf_path']}")): // Search on GPT/p2
					if(file_exists("{$g['cf_conf_path']}/config.xml")):
						$cfgdevice = $disk;
						$cfgpartition = "{$cfgdevice}p2";
						$cfgfstype = 'ufs';
					endif;
					//	Unmount device
					mwexec("/sbin/umount -f {$g['cf_path']}");
				elseif(0 == mwexec("/sbin/mount -r /dev/{$disk}a {$g['cf_path']}")): // Search on partition 'a'
					if(file_exists("{$g['cf_conf_path']}/config.xml")):
						$cfgdevice = $disk;
						$cfgpartition = "{$cfgdevice}a";
						$cfgfstype = 'ufs';
					endif;
					//	Unmount device
					mwexec("/sbin/umount -f {$g['cf_path']}");
				endif;
				//	Device found, exit
				if($cfgdevice):
					break;
				endif;
			endforeach;
			if(!$cfgdevice):
				//	Check if there is any USB device that can be used
				foreach($disks as $disk):
					//	Skip non USB (SCSI) device. The filesystem type must be MSDOS
					if(false == preg_match('/da[0-9]/',$disk)):
						continue;
					endif;
					//	Display progress...
					echo " {$disk}";
					//	Try to mount device using file system MSDOS (FAT16/32)
					if(0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk}s1 {$g['cf_path']}")):
						if(file_exists("{$g['cf_conf_path']}/config.xml")):
							$cfgdevice = $disk;
							$cfgpartition = "{$cfgdevice}s1";
							$cfgfstype = 'msdos';
						endif;
						//	Unmount device
						mwexec("/sbin/umount -f {$g['cf_path']}");
					elseif(0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk} {$g['cf_path']}")):
						if(file_exists("{$g['cf_conf_path']}/config.xml")):
							$cfgdevice = $disk;
							$cfgpartition = $cfgdevice;
							$cfgfstype = 'msdos';
						endif;
						//	Unmount device
						mwexec("/sbin/umount -f {$g['cf_path']}");
					endif;
					//	Device found, exit
					if($cfgdevice):
						break;
					endif;
				endforeach;
			endif;
			//	Display device information where configuration is found
			echo PHP_EOL;
			if($cfgdevice):
				echo "Found configuration on device=/dev/{$cfgpartition} fstype={$cfgfstype}.",PHP_EOL;
			endif;
		endif;
		if(!$cfgdevice):
			//	No device found, print an error and die
			echo PHP_EOL;
			echo "*******************************************************************************",PHP_EOL;
			echo "FATAL ERROR!",PHP_EOL;
			echo "The device that contains the configuration file (config.xml) could not be",PHP_EOL;
			echo "found. {$productname} cannot continue booting.",PHP_EOL;
			echo "*******************************************************************************",PHP_EOL;
			echo PHP_EOL;
			mwexec('/sbin/halt');
			exit;
		endif;
		//	Write device name to a file (used by rc.firmware for example)
		@file_put_contents("{$g['etc_path']}/cfdevice",$cfgpartition,FILE_TEXT);
		//	Write /etc/fstab
		$data = "/dev/{$cfgpartition} {$g['cf_path']} {$cfgfstype} ro 1 1\n";
		@file_put_contents("{$g['etc_path']}/fstab",$data,FILE_TEXT);
	endif;
	//	Append if not found
	if(mwexec("/usr/bin/grep '^proc ' {$g['etc_path']}/fstab") != 0):
		//	Append process file system to /etc/fstab
		$data = "proc /proc procfs rw 0 0\n";
		@file_put_contents("{$g['etc_path']}/fstab",$data,FILE_TEXT | FILE_APPEND);
	endif;
	//	Mount all filesystems
	mwexec('/sbin/mount -a');
endif;
//	parse configuration
if(!isset($noparseconfig) || !$noparseconfig):
	config_lock();
	//	See if there's a newer cache file
	if(file_exists("{$g['tmp_path']}/config.cache") && (@filemtime("{$g['tmp_path']}/config.cache") >= @filemtime("{$g['conf_path']}/config.xml")) && !is_booting()):
		//	Read cache
		$config = unserialize(file_get_contents("{$g['tmp_path']}/config.cache"));
	else:
		if(!file_exists("{$g['conf_path']}/config.xml")):
			if(is_booting()):
				if(stristr($g['platform'],'livecd') || stristr($g['platform'],'liveusb')):
					//	Try copying the default config.xml to the floppy disk/USB stick media
					config_unlock();
					reset_factory_defaults();
					config_lock();
					echo PHP_EOL;
					echo "*******************************************************************************",PHP_EOL;
					echo "No XML configuration file found - using factory defaults.",PHP_EOL;
					echo "Make sure that the media for holding the configuration is MSDOS FAT formatted",PHP_EOL;
					echo "and that the config.xml file is inserted. If it isn't, your configuration changes",PHP_EOL;
					echo "will be lost on reboot.",PHP_EOL;
					echo "*******************************************************************************",PHP_EOL;
					echo PHP_EOL;
				else:
					echo PHP_EOL;
					echo "*******************************************************************************",PHP_EOL;
					echo "XML configuration file not found. {$productname} cannot continue booting.",PHP_EOL;
					echo "*******************************************************************************",PHP_EOL;
					echo PHP_EOL;
					mwexec('/sbin/halt');
					exit;
				endif;
			else:
				config_unlock();
				exit(0);
			endif;
		endif;
		$config_filename = $g['conf_path'] . DIRECTORY_SEPARATOR . 'config.xml';
		if(isset($load_raw_config) && $load_raw_config != 0):
			//	Load all root objects, but never write to cache
			$config = parse_xml_config_raw($config_filename);
			if(false === $config):
				echo "Error: Failed to load '$config_filename' file. {$productname} cannot continue booting.",PHP_EOL;
				mwexec('/sbin/halt');
				exit;
			endif;
		else:
			//	Load configuration file into array.
			$config_filename = $g['conf_path'] . DIRECTORY_SEPARATOR . 'config.xml';
			$config = parse_xml_config($config_filename,false,$g['xml_rootobj'],'nas4free');
			if(false === $config):
				echo "Error: Failed to load '{$g['conf_path']}/config.xml' file. {$productname} cannot continue booting.",PHP_EOL;
				mwexec('/sbin/halt');
				exit;
			endif;
			if((float)$config['version'] > (float)$g['latest_config']):
				if(is_booting()):
					echo "*******************************************************************************",PHP_EOL;
					echo "WARNING!",PHP_EOL;
					echo "The current configuration has been created with a newer version of",PHP_EOL;
					echo "{$productname} than this one! This can lead to serious misbehaviour and",PHP_EOL;
					echo "even security holes! You are urged to either upgrade to a newer version of",PHP_EOL;
					echo "{$productname} or revert to the default configuration immediately!",PHP_EOL;
					echo "*******************************************************************************",PHP_EOL;
				endif;
			endif;
			//	Write configuration cache file
			config_write_cache($config);
		endif;
	endif;
	config_unlock();
	global $config_disks;
	//	get S.M.A.R.T. arguments from config and store it in a hash to get better performance:
	if(is_array($config['disks']) && is_array($config['disks']['disk'])):
		foreach ($config['disks']['disk'] as $disk):
			$config_disks[$disk['devicespecialfile']]['smart']['devicefilepath'] = $disk['smart']['devicefilepath'];
			$config_disks[$disk['devicespecialfile']]['smart']['devicetypearg'] = $disk['smart']['devicetypearg'];
			$config_disks[$disk['devicespecialfile']]['desc'] = $disk['desc'];
		endforeach;
	endif;
endif;

/* mount flash card read/write */
function conf_mount_rw() {
	global $g;

	if ($g['platform'] != "full") {
		/* don't use mount -u anymore
		(doesn't sync the files properly and /bin/sync won't help either) */
		mwexec("/sbin/umount -f {$g['cf_path']}");
		mwexec("/sbin/mount -w -o noatime {$g['cf_path']}");
	}
}

/* mount flash card read only */
function conf_mount_ro() {
	global $g;

	if ($g['platform'] != "full") {
		mwexec("/sbin/umount -f {$g['cf_path']}");
		mwexec("/sbin/mount -r {$g['cf_path']}");
	}
}

/**
 * Save the system configuration
 */
function write_config() {
	global $config, $g;

	$bom = ''; // "\xEF\xBB\xBF";
	config_lock();
	conf_mount_rw();
	//	Make sure the clock settings is plausible
	if(function_exists('date_default_timezone_set') and function_exists('date_default_timezone_get')):
		@date_default_timezone_set(@date_default_timezone_get());
	endif;
	if(time() > mktime(0,0,0,9,1,2004)):
		$config['lastchange'] = time();
	endif;
	//	Dump configuration as XML data
	$xmlconfig =  $bom . dump_xml_config($config,$g['xml_rootobj'],system_get_language_codeset());
	//	Save current configuration file
	$filename = $g['cf_conf_path'] . DIRECTORY_SEPARATOR . 'config.xml';
	if(file_exists($filename)):
		for($n = 6;--$n > 0;):
			$filename_bk2 = preg_replace('/.xml$/','.bk' . $n,$filename);
			$filename_bk1 = preg_replace('/.xml$/','.bk' . ($n-1),$filename);
			if(file_exists($filename_bk2) && unlink($filename_bk2) === false):
				die(sprintf('Error: Failed to delete configuration file %s',escapeshellarg($filename_bk2)));
			endif;
			if(file_exists($filename_bk1) && rename($filename_bk1,$filename_bk2) === false):
				die(sprintf('Error: Failed to rename configuration file %s',escapeshellarg($filename_bk1)));
			endif;
		endfor;
		$filename_bk0 = preg_replace('/.xml$/','.bk0',$filename);
		if(rename($filename,$filename_bk0) === false):
			die(sprintf('Error: Failed to rename configuration file %s',escapeshellarg($filename)));
		endif;
	endif;
	//	Write configuration file
	if(!file_put_contents_safe($filename,$xmlconfig)):
		die(sprintf('Error: Failed to write configuration file %s',escapeshellarg($filename)));
	endif;
	//	Modify file permissions
	@chmod($filename,0600);
	conf_mount_ro();
	//	Re-read configuration
	$config_filename = $g['conf_path'] . DIRECTORY_SEPARATOR . 'config.xml';
	$config = parse_xml_config($config_filename,true,$g['xml_rootobj'],'nas4free');
	//	Write config cache (to prevent parsing on every access)
	config_write_cache($config);
	//	Force completion of pending disk writes
	mwexec('/bin/sync');
	config_unlock();
}
/**
 *
 *	@global array $g
 *	@global array $config
 */
function write_loader_config(){
	global $g,$config;

	// Make sure the clock settings is plausible
	if(function_exists('date_default_timezone_set') and function_exists('date_default_timezone_get')):
		@date_default_timezone_set(@date_default_timezone_get());
	endif;
	if(time() > mktime(0,0,0,9,1,2004)):
		$config['lastchange'] = time();
	endif;
	if(!isset($config['system']['loaderconf']['param'])):
		//	Load configuration file into array.
		$config_filename = $g['conf_path'] . DIRECTORY_SEPARATOR . 'config.xml';
		$config = parse_xml_config($config_filename,true,$g['xml_rootobj'],'nas4free');
		//	read platform
		$platform_filename = sprintf('%s/platform',$g['etc_path']);
		if(file_exists($platform_filename)):
			$g['fullplatform'] = strtolower(chop(file_get_contents($platform_filename)));
			$data = explode('-',$g['fullplatform']);
			$g['arch'] = $data[0] ?? 'unknown';
			$g['platform'] = $data[1] ?? 'unknown';
		endif;
	endif;
	if(isset($config['system']['loaderconf']['param']) && is_array($config['system']['loaderconf']['param'])):
		config_lock();
		conf_mount_rw();
		//	Set loader.conf.local location
		if($g['platform'] === 'full' ):
			$loader_filename = '/boot/loader.conf.local';
		else:
			$loader_filename = sprintf('%s/boot/loader.conf.local',$g['cf_path']);
		endif;
		array_sort_key($config['system']['loaderconf']['param'],'name');
		$lines = '';
		$loader_param_list = &$config['system']['loaderconf']['param'];
		foreach($loader_param_list as $param):
			if(is_bool($test = $param['enable'] ?? false) ? $test : true):
				$name = is_string($test = $param['name'] ?? '') ? $test : '';
				$value = is_string($test = $param['value'] ?? '') ? $test : '';
				if(preg_match('/\S/',$name)):
					$lines .= sprintf('%s="%s"',trim($name),trim($value)) . PHP_EOL;
				endif;
			endif;
		endforeach;
		if(strlen($lines) == 0):
			if(file_exists($loader_filename)):
				@unlink($loader_filename);
			endif;
		else:
			//	Write configuration file
			if(false === file_put_contents_safe($loader_filename,$lines)):
				die(sprintf('Error: Failed to write configuration file %s',escapeshellarg($loader_filename)));
			endif;
		endif;
		//	Modify file permissions
		@chmod($loader_filename,0600);
		conf_mount_ro();
		//	Force completion of pending disk writes
		mwexec('/bin/sync');
		config_unlock();
	endif;
}
/**
 *
 * @global array $g
 * @return int Returns 0 on success or 1 on failure.
 */
function reset_factory_defaults() {
	global $g;

	config_lock();
	conf_mount_rw();
	//	create conf directory, if necessary
	if(!file_exists("{$g['cf_conf_path']}")):
		@mkdir("{$g['cf_conf_path']}");
	endif;
	//	clear /conf
	$dh = opendir($g['conf_path']);
	while($filename = readdir($dh)) {
		if(($filename != '.') && ($filename != '..')):
			unlink($g['conf_path'] . '/' . $filename);
		endif;
	}
	closedir($dh);
	//	copy default configuration
	@copy("{$g['conf_default_path']}/config.xml", "{$g['conf_path']}/config.xml");
	//	modify file permissions
	@chmod("{$g['cf_conf_path']}/config.xml", 0600);
	//	remove existing pool cache
	unlink_if_exists("{$g['cf_path']}/boot/zfs/zpool.cache");
	$loader_filename = "{$g['cf_path']}/boot/loader.conf.local";
	if(file_exists($loader_filename) && unlink($loader_filename) === false):
		die("Error: Failed to delete configuration file '{$loader_filename}'");
	endif;
	mwexec2("/bin/echo loader filename is ".$loader_filename);
	sleep(30);
	conf_mount_ro();
	config_unlock();
	return 0;
}

/**
 *	Install the given configuration file.
 *	@param[in] conffile The path to the file to be installed.
 *	@return int Returns 0 on success or 1 on failure.
 */
function config_install($conffile) {
	global $g,$config;

	if(!file_exists($conffile)):
		return 1;
	endif;
	config_lock();
	conf_mount_rw();
	$result = copy($conffile,"{$g['conf_path']}/config.xml");
	conf_mount_ro();
	config_unlock();
	if(true === $result):
		write_loader_config();
		return 0;
	endif;
	return 1;
}
/**
 *	Lock configuration file, decide that the lock file is stale after 10 seconds
 *	@global array $g
 */
function config_lock() {
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";
	$n = 0;
	while($n < 15) {
		//	open the lock file in append mode to avoid race condition
		if($fd = @fopen($lockfile, "x")):
			//	succeeded
			fclose($fd);
			return;
		else:
			//	file locked, wait and try again
			sleep(1);
			$n++;
		endif;
	}
	mwexec("/usr/bin/logger 'Lock error {$lockfile}'");
	while(1) {
		/* can't continue without lock */
		sleep(1);
	}
	exit;
}
/**
 *	Unlock configuration file
 *	@global array $g
 */
function config_unlock() {
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";
	if(file_exists($lockfile)):
		unlink($lockfile);
	endif;
}
/**
 * Write configuration cache file.
 * @param[in] data	The data to write.
 * @return True if successful, otherwise false.
 */
function config_write_cache($data) {
	global $g;

	$filename = "{$g['tmp_path']}/config.cache";

	// Ensure configuration cache file is written
	if (!file_put_contents_safe($filename, serialize($data), TRUE)) {
		write_log("Error: Failed to write configuration cache file '{$filename}'.");
		return FALSE;
	}

	// Modify file permissions
	@chmod($filename, 0600);

	return TRUE;
}
/**
 *	THIS IS FOR A BLACKLISTED CONFIGURATION
 */
function config_upgrade_blacklisted() {
	putenv('TERM=xterm'); // not defined at boot
	$result = tui_display_yesno("The version of your configuration file is blacklisted and therefore not valid. You must reset the config or reinstall.\n\nDo you want to reset to Factory Default now?",50,8,false);
	if(0 == $result):
		config_unlock();
		reset_factory_defaults();
		write_console("\nRebooting now.\n");
		mwexec('/sbin/reboot');
		exit;
	endif;
	write_console("\nHalting system.\n");
	mwexec('/sbin/halt');
	exit;
}
/**
 *	Upgrade configuration from version 0.9
 *	@global array $config
 */
function config_upgrade_0dot9() {
	global $config;

	array_make_branch($config,'system');
	$config['system']['hostname'] = 'xigmanas';
	array_make_branch($config,'samba');
	$config['samba']['netbiosname'] = 'xigmanas';
	$config['samba']['serverdesc'] = 'XigmaNAS Server';
}
/**
 *	Upgrade configuration from version 1.0
 *	@global array $config
 */
function config_upgrade_1dot0() {
}
/**
 *	Upgrade configuration from version 1.1
 *	@global array $config
 */
function config_upgrade_1dot1() {
	global $config;

	array_make_branch($config,'lcdproc','param');
	unset($config['lcdproc']['param']);
	$params = [
		'# USB connected HD44780 based LCD',
		'ConnectionType=lcd2usb',
		'Contrast=825',
		'Brightness=4',
		'OffBrightness=0',
		'Keypad=yes',
		'Backlight=yes',
		'Size=20x4',
		'KeyDirect_1=Enter',
		'KeyDirect_2=Down',
		'KeyDirect_3=Escape',
	];
	foreach($params as $param):
		$config['lcdproc']['param'][] = $param;
	endforeach;
	$config['lcdproc']['titlespeed'] = 10;
}
/**
 *	Upgrade configuration from version 1.2
 *	@global array $config
 */
function config_upgrade_1dot2() {
	global $config;

	array_make_branch($config,'samba');
	if($config['samba']['aiorsize'] < 4096):
		$config['samba']['aiorsize'] = 4096;
	endif;
	if($config['samba']['aiowsize'] < 4096):
		$config['samba']['aiowsize'] = 4096;
	endif;
	array_make_branch($config,'iscsitarget');
	if(empty($config['iscsitarget']['uctladdress'])):
		$config['iscsitarget']['uctladdress'] = '127.0.0.1';
		$config['iscsitarget']['uctlport'] = '3261';
		$config['iscsitarget']['uctlnetmask'] = '127.0.0.1/8';
		$config['iscsitarget']['uctlauthmethod'] = 'CHAP';
		$config['iscsitarget']['uctlauthgroup'] = 0;
		$config['iscsitarget']['mediadirectory'] = '/mnt';
	endif;
	array_make_branch($config,'system','sysctl','param');
	$index = array_search_ex('kern.maxfilesperproc',$config['system']['sysctl']['param'],'name');
	if($index !== false):
		if($config['system']['sysctl']['param'][$index]['value'] === '32768'):
			$config['system']['sysctl']['param'][$index]['value'] = '50000';
		endif;
	endif;
	//	remove unused key
	$index = array_search_ex('hw.ata.to',$config['system']['sysctl']['param'],'name');
	if($index !== false):
		unset($config['system']['sysctl']['param'][$index]);
	endif;
	//	remove empty entry
	if(isset($config['system']['hosts'])):
		$hosts_tmp = [];
		foreach($config['system']['hosts'] as &$host):
			if(empty($host) || !isset($host['uuid'])):
				continue;
			endif;
			$hosts_tmp[] = $host;
		endforeach;
		$config['system']['hosts'] = $hosts_tmp;
	endif;
	//	add missing values
	array_make_branch($config,'zfs','pools','pool');
	foreach($config['zfs']['pools']['pool'] as &$pool):
		$pool['desc'] = $pool['desc'] ?? '';
	endforeach;
	array_make_branch($config,'zfs','vdevices','vdevice');
	foreach($config['zfs']['vdevices']['vdevice'] as &$vdevice):
		$vdevice['desc'] = $vdevice['desc'] ?? '';
	endforeach;
	array_make_branch($config,'zfs','datasets','dataset');
	foreach($config['zfs']['datasets']['dataset'] as &$dataset):
		$dataset['desc'] = $dataset['desc'] ?? '';
		$dataset['atime'] = $dataset['atime'] ?? 'on';
	endforeach;
	array_make_branch($config,'zfs','volumes','volume');
	foreach($config['zfs']['volumes']['volume'] as &$volume):
		$volume['desc'] = $volume['desc'] ?? '';
	endforeach;
	//	replace certificate and privatekey
	array_make_branch($config,'system','webgui');
	if(md5($config['system']['webgui']['certificate']) === 'ee3e3c2ddc0f186e8d40e797e8af6f36'):
		$config['system']['webgui']['certificate'] = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpNSUlEWURDQ0FrZ0NDUUNZaG9rNlRyekZCakFOQmdrcWhraUc5dzBCQVFVRkFEQnlNUXN3Q1FZRFZRUUdFd0pLDQpVREVUTUJFR0ExVUVDQk1LVTI5dFpTMVRkR0YwWlRFUk1BOEdBMVVFQ2hNSVRrRlRORVp5WldVeEZ6QVZCZ05WDQpCQU1URG01aGN6Um1jbVZsTG14dlkyRnNNU0l3SUFZSktvWklodmNOQVFrQkZoTnBibVp2UUc1aGN6Um1jbVZsDQpMbXh2WTJGc01CNFhEVEV5TURreE56RXlNVEkxTmxvWERUTXlNRGt4TWpFeU1USTFObG93Y2pFTE1Ba0dBMVVFDQpCaE1DU2xBeEV6QVJCZ05WQkFnVENsTnZiV1V0VTNSaGRHVXhFVEFQQmdOVkJBb1RDRTVCVXpSR2NtVmxNUmN3DQpGUVlEVlFRREV3NXVZWE0wWm5KbFpTNXNiMk5oYkRFaU1DQUdDU3FHU0liM0RRRUpBUllUYVc1bWIwQnVZWE0wDQpabkpsWlM1c2IyTmhiRENDQVNJd0RRWUpLb1pJaHZjTkFRRUJCUUFEZ2dFUEFEQ0NBUW9DZ2dFQkFMSCtTR1hrDQpjSzRnNVp2MWRSRFJKQVh0NVhHMVVpZVEzZ1UzdlIvZjBYZnJ5RFBPNjJ3WkhnTWdONTJocGVRL1VvNXl4dTRMDQp1azJtUlpTYlpPai92b0xSZWlpSy8zY0grUStRVml4SFpaMlpQNThBeWpHTXNUOGJ0T3pwYlN4MWtkQkEvUkhqDQpmYmpPamRFK0FocDJiWkVoQzVmaXlYVjlrdjFzTmZmMnFlM0REMUZtd0RGbDM0cVdPT1g2SEFYNHpWOWcrY1dJDQo0S0FRdEYwZ3FJWWd5Z29OckM5dDI4VVc4Z2dqcWs4T0EycnNMUFRsMmkzMk5DanVJSThjL1EzOUhYNkZBRUdvDQo1VGZGL3V0WTlBaFBaaHpWbk0zazJtUFR1RGdMbVZaZmtieml1eHVES293ZzBGUC9IZlZabWRoMkV3M3Y1NmZGDQpDLzh3N1F2QkdPRjF2SXNDQXdFQUFUQU5CZ2txaGtpRzl3MEJBUVVGQUFPQ0FRRUFidWxJNlJCTXFJTzFMT21jDQo1OGg5aHJ1Z25vTFkxNndIRFZQdTE4WmpUSzZYcDBDYWlONVYyR1MwdWdUNzJ2QzVrcXQ0cW1CUWFBa2MwbVdWDQpQQi9YUXhyaEFTd25VMzh1TGVJeWlnMzFCWkx3Yll5S3IzTUJ4cVI5dWwvdWY1MWErR2dVOGFETk42VDRQbGJJDQp5Z0M2dDUxa2E1VzBLbFFGeWhwNTlXT21GOElLOURRTlZpQWpTWkk0WUZsZXhJc1dFTC9DNCtMTGNhTVp3YUUzDQpLUTVKK1lFcG5qMFBKOUYvU1Y4MHFNdnE0RWJ4YmUzbGpFSGlMSVU0VktwV2I0VWJMSWxVNEZNeWtTSzNYMGp4DQpESzJGcERPRnUxL3c2d0FrYW5KanFSQXozUVViRVVocFhCNnVUVUVGcTVJSHNqNkZkbTBkMThkU2NiT0xWcmJGDQpBYlYrZHc9PQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==';
		$config['system']['webgui']['privatekey'] = 'LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJRXBBSUJBQUtDQVFFQXNmNUlaZVJ3cmlEbG0vVjFFTkVrQmUzbGNiVlNKNURlQlRlOUg5L1JkK3ZJTTg3cg0KYkJrZUF5QTNuYUdsNUQ5U2puTEc3Z3U2VGFaRmxKdGs2UCsrZ3RGNktJci9kd2Y1RDVCV0xFZGxuWmsvbndESw0KTVl5eFB4dTA3T2x0TEhXUjBFRDlFZU45dU02TjBUNENHblp0a1NFTGwrTEpkWDJTL1d3MTkvYXA3Y01QVVdiQQ0KTVdYZmlwWTQ1Zm9jQmZqTlgyRDV4WWpnb0JDMFhTQ29oaURLQ2cyc0wyM2J4UmJ5Q0NPcVR3NERhdXdzOU9YYQ0KTGZZMEtPNGdqeHo5RGYwZGZvVUFRYWpsTjhYKzYxajBDRTltSE5XY3plVGFZOU80T0F1WlZsK1J2T0s3RzRNcQ0KakNEUVUvOGQ5Vm1aMkhZVERlL25wOFVML3pEdEM4RVk0WFc4aXdJREFRQUJBb0lCQUU0TTFTNXBCOGd6Z0x2cw0KM0pLdFF3Q2VteGhMakdUeEw0cFdNTWFEWE5VYlBVVUtuVTFmamp1TXF4YWlSWjVhN2hhczJmS1VqZk55ejl3aw0KY0JHRDQ4UjFtdjJnUmZtcU0xclJ6aGZJanhTV21NRmVsVXM1Q0poRnpBWi9MWEpyYzY5QWNEaWg2d1ZZL2ZNZA0KSDhyKzEzam1ackVKcjVHbnFRY2JwbWEvY1RCaXR2TnNib3VSZmlQSFB5ck84UlBEdXVIc3k1VXovSVB6cDFMTA0KWmNNd2hwK1RsSjlnVmNDMHRvN2VlemxUMEZ6a2hsMTgwVTlla09wTlB1WUJGc1NsdWZyVEhqTnBzRVE5aGtmKw0KeGJjQjdlZTFvZXJtUjdOSmdBRWVHeS9FeXVuK0UvOWZpOUovb1JCbjZnVXhiaDkvQ1dPd2VGeVRZVVZvd1d1RA0KUC9NY0prRUNnWUVBNUs4YzJKdmtQbWhpWGtNRU9GQkxZQkxvaFBKa2hqdU0rQWlMaERxeENpRTg4S0pod3d3WQ0KdWFpaU0rbXkraTcrbzBDZVF5NlJvb0d2UVRYVHRFUkhEMldtaS9JZzlDdFF0cWNibnRsbEtGYVQrcnZHdmp0RA0KZ3dzV1Y0N09Ia3pwUThWUkZueVZOMnBLZVNudzVnR1F3a2RXaFdIR0wwdXBJNklIakExVVI1c0NnWUVBeDBFYg0KY01BYXVkNXZlZW1NQzN5ZmZTMEx0Z2dtR2dGMGxLSFBJTm5XbXBWdkhjTkZZaGtXMENKMVdNQm5sTlhESkdJKw0KWHkyU3lBNVlteWZCN2FTWWdxVTI5S3ZSejJVR0kvOUlJY1NTM09TK2NEdjcyQjljQjlQN3VMUVBSN281S0s3eA0KSEM4NkxuWVIrb2lqbDBXYzJYUmxrdmt4U0h0YkhVVm8yUXIzeGRFQ2dZRUFubmgyN2VnQm4vbW9DUmc1eVdoag0KQTEyM0FjSVRSUzNwUDB4MG1ob3Q2dzRqYzhHaks0MnJhS1RpV2tVZGdOK21GNjFFdnR0N2tuaDU0UitTNkQzVQ0KWEdnZHBLY1NqUUdqT2gvTk5TUURSSlBTa2hNajQxbWdMQ1EzeEU4eXFLNjFpd0FtZEFXN1JaNkRvemtjTmRkcA0KNENlZFhtcmw4b3BrMkV1RkhWbHpTUGNDZ1lCdFRsb3ZVRE5aSTUwajArcnZvajRiL1VvZllma0U2eWQ1SWdydQ0KaXBJZjJ6cEpxWVcyZXV6NDRPNWU4YTNoQ0NNMUltLzA1cHRQQkErM2plZms1aGFFRlA1QTJzRU9kTzBaY2tHdg0KeEphTWR5L0JnaVoyT1QzVFRZY1puQzJGdTdML2pualdiVklOaXRmeUNHTTE2UXBTZ0hmNFdKdWdsNHMwVTQ5aA0KQVpOTEFRS0JnUURKZzNlUWV0TWR3WUpKbjFzZmVTbUVnTVFNMjZOLzNidURSUG1rTUVtdTgvcVJRYTM4T3VXOA0KUUFmTFVNOC83Vm5OUlhvbmZMR2s0eUpKdG5RSlQ0TlZLdjEvUzgweldNSXJ5azM4L3ZreGVsbDA1Q1Y2Rnp5eA0KdGVtcjRZcE8yMDBxWFl5MjR0aC9QM0RhbFYwcDhWcXJvemJ4eExiS1k5TUpLSWZpWHo5bnV3PT0NCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t';
	endif;
}
/**
 *	Upgrade configuration from version 1.3
 *	@global array $config
 */
function config_upgrade_1dot3() {
	global $config;

	//	add missing values
	array_make_branch($config,'zfs','datasets','dataset');
	foreach($config['zfs']['datasets']['dataset'] as &$dataset):
		$dataset['dedup'] = $dataset['dedup'] ?? 'off';
		$dataset['sync'] = $dataset['sync'] ?? 'standard';
	endforeach;
	array_make_branch($config,'zfs','volumes','volume');
	foreach($config['zfs']['volumes']['volume'] as &$volume):
		$volume['dedup'] = $volume['dedup'] ?? 'off';
		$volume['sync'] = $volume['sync'] ?? 'standard';
	endforeach;
}
/**
 *	Upgrade configuration from version 1.4
 *	@global array $config
 */
function config_upgrade_1dot4() {
	global $config;

	// add missing values
	array_make_branch($config,'zfs','datasets','dataset');
	foreach($config['zfs']['datasets']['dataset'] as &$dataset):
		$dataset['reservation'] = $dataset['reservation'] ?? '';
		array_make_branch($dataset,'accessrestrictions');
		$dataset['accessrestrictions']['owner'] = $dataset['accessrestrictions']['owner'] ?? 'root';
		$dataset['accessrestrictions']['group'] = $dataset['accessrestrictions']['group'] ?? 'wheel';
		$dataset['accessrestrictions']['mode'] = $dataset['accessrestrictions']['mode'] ?? '0777';
	endforeach;
}
/**
 *	Upgrade configuration from version 1.5
 *	@global array $config
 */
function config_upgrade_1dot5() {
	global $config;

	array_make_branch($config,'system');
	$config['system']['pwmode'] = $config['system']['pwmode'] ?? 'hiadaptive';
	$config['system']['pwmax'] = $config['system']['pwmax'] ?? '';
	$config['system']['pwmin'] = $config['system']['pwmin'] ?? '';
	$pwopt = '-a hiadaptive -b hiadaptive -n hiadaptive';
	array_make_branch($config,'system','rcconf','param');
	$index = array_search_ex('powerd_flags',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		$config['system']['rcconf']['param'][$index]['value'] = $pwopt;
	else:
		$config['system']['rcconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'powerd_flags',
			'value' => $pwopt,
			'comment' => 'System power control options',
			'enable' => true
		];
	endif;
}
/**
 *	Upgrade configuration from version 1.6
 *	@global array $config
 */
function config_upgrade_1dot6() {
	global $config;

	array_make_branch($config,'system','sysctl','param');
	$index = array_search_ex('kern.maxfilesperproc',$config['system']['sysctl']['param'],'name');
	if($index !== false):
		if($config['system']['sysctl']['param'][$index]['value'] < 50000):
			$config['system']['sysctl']['param'][$index]['value'] = '50000';
		endif;
	endif;
	array_make_branch($config,'ldap');
	if(!empty($config['ldap']['hostname'])):
		$config['ldap']['hostname'] = sprintf('ldap://%s',$config['ldap']['hostname']);
	endif;
}
/**
 *	Upgrade configuration from version 1.7
 *	@global array $config
 */
function config_upgrade_1dot7() {
	global $config;

	array_make_branch($config,'system','sysctl','param');
	$a_mib = [
		'net.inet.tcp.sendspace' => 131072,
		'net.inet.tcp.recvspace' => 131072,
		'net.inet.tcp.sendbuf_inc' => 131072,
		'net.inet.tcp.recvbuf_inc' => 131072,
		'kern.ipc.nmbclusters' => 131072,
		'kern.ipc.nmbjumbop' => 131072,
		'kern.ipc.nmbjumbo9' => 32768,
		'kern.ipc.nmbjumbo16' => 16384,
		'kern.maxfilesperproc' => 60000
	];
	foreach ($a_mib as $key => $val):
		$index = array_search_ex($key,$config['system']['sysctl']['param'],'name');
		if($index !== false):
			$config['system']['sysctl']['param'][$index]['value'] = $val;
		endif;
	endforeach;
	array_make_branch($config,'system','rcconf','param');
	$index = array_search_ex('varsize',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		$config['system']['rcconf']['param'][$index]['value'] = '128m';
	else:
		$config['system']['rcconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'varsize',
			'value' => '128m',
			'comment' => 'Default /var size',
			'enable' => true
		];
	endif;
	$index = array_search_ex('vartmpsize',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		$config['system']['rcconf']['param'][$index]['value'] = '64m';
	else:
		$config['system']['rcconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'vartmpsize',
			'value' => '64m',
			'comment' => 'Default /var/tmp size',
			'enable' => true ];
	endif;
	array_make_branch($config,'samba');
	if($config['samba']['security'] == 'share'):
		$config['samba']['security'] = 'user';
		$config['samba']['maptoguest'] = 'Bad User';
	endif;
}
/**
 *	Upgrade configuration from version 1.8
 *	@global array $config
 */
function config_upgrade_1dot8() {
	global $config;

	array_make_branch($config,'system','rcconf','param');
	$index = array_search_ex('varsize',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		$config['system']['rcconf']['param'][$index]['value'] = '256m';
	else:
		$config['system']['rcconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'varsize',
			'value' => '256m',
			'comment' => 'Default /var size',
			'enable' => true
		];
	endif;
	$index = array_search_ex('vartmpsize',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		$config['system']['rcconf']['param'][$index]['value'] = '128m';
	else:
		$config['system']['rcconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'vartmpsize',
			'value' => '128m',
			'comment' => 'Default /var/tmp size',
			'enable' => true
		];
	endif;
	array_make_branch($config,'samba');
	if($config['samba']['rcvbuf'] < 128480):
		$config['samba']['rcvbuf'] = 128480;
	endif;
	if($config['samba']['sndbuf'] < 128480):
		$config['samba']['sndbuf'] = 128480;
	endif;
	if($config['samba']['aiorsize'] == 4096):
		$config['samba']['aiorsize'] = 1024;
	endif;
	if($config['samba']['aiowsize'] == 4096):
		$config['samba']['aiowsize'] = 1024;
	endif;
}
/**
 *	Upgrade configuration from version 1.9
 *	@global array $config
 */
function config_upgrade_1dot9() {
	global $config;

	array_make_branch($config,'system','firewall','rule');
	array_sort_key($config['system']['firewall']['rule'],'ruleno');
	$ruleno = 10100;
	if($config['system']['firewall']['rule'][0]['ruleno'] < $ruleno):
		foreach($config['system']['firewall']['rule'] as $k => &$v):
			$v['ruleno'] = $ruleno;
			$ruleno += 100;
		endforeach;
		unset($v);
	endif;
	array_make_branch($config,'afp','share');
	foreach($config['afp']['share'] as &$a):
		if(isset($a['adisk']) && isset($a['adisk']['advf'])):
			$a['timemachine'] = true;
		endif;
		unset($a['cnidscheme']);
		unset($a['options']);
		unset($a['adisk']);
	endforeach;
	unset($a);
	array_make_branch($config,'samba','share');
	foreach($config['samba']['share'] as &$a):
		if(empty($a['aiomodule'])):
			$a['aiomodule'] = 'aio_pthread';
		endif;
	endforeach;
	unset($a);
	array_make_branch($config,'system');
	if(!preg_match('/^\$6\$/',$config['system']['password'])):
		$config['system']['password'] = mkpasswd($config['system']['password']);
	endif;
}
/**
 *	Upgrade configuration from version 2.0
 *	@global array $config
 */
function config_upgrade_2dot0() {
	global $config;

	$user = [
		'uuid' => uuid(),
		'name' => 'dlna',
		'id' => '933',
		'primarygroup' => '933',
		'extraoptions' => '-c "DLNA Daemon" -d /nonexistent -s /usr/sbin/nologin'
	];
	array_make_branch($config,'system','usermanagement','user');
	$index = array_search_ex('dlna',$config['system']['usermanagement']['user'],'name');
	if($index !== false):
		$config['system']['usermanagement']['user'][$index] = $user;
	else:
		$config['system']['usermanagement']['user'][] = $user;
	endif;
	$group = [
		'uuid' => uuid(),
		'name' => 'dlna',
		'id' => '933'
	];
	array_make_branch($config,'system','usermanagement','group');
	$index = array_search_ex('dlna',$config['system']['usermanagement']['group'],'name');
	if($index !== false):
		$config['system']['usermanagement']['group'][$index] = $group;
	else:
		$config['system']['usermanagement']['group'][] = $group;
	endif;
	array_make_branch($config,'samba');
	unset($config['samba']['usesendfile']);
}
/**
 *	Upgrade configuration from version 2.1
 *	@global array $config
 */
function config_upgrade_2dot1() {
	global $config;

	$user = [
		'uuid' => uuid(),
		'name' => 'vboxusers',
		'id' => '920',
		'primarygroup' => '920',
		'extraoptions' => '-c "Virtualbox user" -d /nonexistent -s /usr/sbin/nologin'
	];
	array_make_branch($config,'system','usermanagement','user');
	$index = array_search_ex('vboxusers',$config['system']['usermanagement']['user'],'name');
	if($index !== false):
		$config['system']['usermanagement']['user'][$index] = $user;
	else:
		$config['system']['usermanagement']['user'][] = $user;
	endif;
	$group = [
		'uuid' => uuid(),
		'name' => 'vboxusers',
		'id' => '920'
	];
	array_make_branch($config,'system','usermanagement','group');
	$index = array_search('vboxusers',$config['system']['usermanagement']['group'],'name');
	if($index !== false):
		$config['system']['usermanagement']['group'][$index] = $group;
	else:
		$config['system']['usermanagement']['group'][] = $group;
	endif;
	array_make_branch($config,'vbox');
	$config['vbox']['homedir'] = $config['vbox']['homedir'] ?? '';
}
/**
 *	Upgrade configuration from version 2.2
 *	@global array $config
 */
function config_upgrade_2dot2() {
	global $config;

	$user = [
		'uuid' => uuid(),
		'name' => 'syncthing',
		'id' => '983',
		'primarygroup' => '983',
		'extraoptions' => '-c "Syncthing Daemon" -d /var/tmp/syncthing -s /usr/sbin/nologin'
	];
	array_make_branch($config,'system','usermanagement','user');
	$index = array_search_ex('syncthing',$config['system']['usermanagement']['user'],'name');
	if($index !== false):
		$config['system']['usermanagement']['user'][$index] = $user;
	else:
		$config['system']['usermanagement']['user'][] = $user;
	endif;
	$group = [
		'uuid' => uuid(),
		'name' => 'syncthing',
		'id' => '983'
	];
	array_make_branch($config,'system','usermanagement','group');
	$index = array_search('syncthing',$config['system']['usermanagement']['group'],'name');
	if($index !== false):
		$config['system']['usermanagement']['group'][$index] = $group;
	else:
		$config['system']['usermanagement']['group'][] = $group;
	endif;
	array_make_branch($config,'minidlna');
	$config['minidlna']['port'] = $config['minidlna']['port'] ?? '8200';
	$config['minidlna']['notify_int'] = $config['minidlna']['notify_int'] ?? '300';
	$config['minidlna']['container'] = $config['minidlna']['container'] ?? 'B';
	$config['minidlna']['loglevel'] = $config['minidlna']['loglevel'] ?? 'info';
	$config['minidlna']['inotify'] = $config['minidlna']['inotify'] ?? true;
	array_make_branch($config,'samba');
	$config['samba']['timesrv'] = $config['samba']['timesrv'] ?? 'yes';
	$config['samba']['doscharset'] = $config['samba']['doscharset'] ?? 'CP437';
	$config['samba']['unixcharset'] = $config['samba']['unixcharset'] ?? 'UTF-8';
	$config['samba']['loglevel'] = $config['samba']['loglevel'] ?? '1';
}
/**
 *	Upgrade configuration from version 2.3
 *	@global array $config
 */
function config_upgrade_2dot3() {
	global $config;

	$user_tmp = [];
	$user_syncthing_found = false;
	array_make_branch($config,'system','usermanagement','user');
	foreach($config['system']['usermanagement']['user'] as $user):
		if($user['name'] != 'syncthing'):
			$user_tmp[] = $user;
		elseif(!$user_syncthing_found):
			$user_tmp[] = $user;
			$user_syncthing_found = true;
		else:
			$user_tmp[] = $user;
		endif;
	endforeach;
	$config['system']['usermanagement']['user'] = $user_tmp;
	$group_tmp = [];
	$group_syncthing_found = false;
	array_make_branch($config,'system','usermanagement','group');
	foreach($config['system']['usermanagement']['group'] as $group):
		if($group['name'] != 'syncthing'):
			$group_tmp[] = $group;
		elseif(!$group_syncthing_found):
			$group_tmp[] = $group;
			$group_syncthing_found = true;
		endif;
	endforeach;
	$config['system']['usermanagement']['group'] = $group_tmp;
	array_make_branch($config,'system','rcconf','param');
	$index = array_search_ex('varsize',$config['system']['rcconf']['param'],'name');
	if($index !== false):
		if($config['system']['rcconf']['param'][$index]['value'] == '256m'):
			$config['system']['rcconf']['param'][$index]['value'] = '512m';
		endif;
	endif;
	$index = array_search_ex('vartmpsize', $config['system']['rcconf']['param'],'name');
	if($index !== false):
		if($config['system']['rcconf']['param'][$index]['value'] == '128m'):
			$config['system']['rcconf']['param'][$index]['value'] = '256m';
		endif;
	endif;
	array_make_branch($config,'system','loaderconf','param');
	$index = array_search_ex('vfs.zfs.arc_max',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		$config['system']['loaderconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'vfs.zfs.arc_max',
			'value' => '2G',
			'comment' => 'ZFS maximum ARC size'
		];
	endif;
	$index = array_search_ex('vfs.zfs.arc_min',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		$config['system']['loaderconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'vfs.zfs.arc_min',
			'value' => '1G',
			'comment' => 'ZFS minimum ARC size'
		];
	endif;
	$index = array_search_ex('vfs.zfs.prefetch_disable',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		$config['system']['loaderconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'vfs.zfs.prefetch_disable',
			'value' => '1',
			'comment' => 'ZFS prefech disable'
		];
	endif;
}
/**
 *	Upgrade configuration from version 2.4
 *	@global array $config
 */
function config_upgrade_2dot4() {
	global $config;

	array_make_branch($config,'ups');
	if($config['ups']['upsname'] === 'ups'):
		$config['ups']['upsname'] = 'ups1';
	endif;
	array_make_branch($config,'system','email');
	$config['system']['email']['tls_certcheck'] = 'tls_certcheck off';
	array_make_branch($config,'system','loaderconf','param');
	$index = array_search_ex('arcsas_load',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		$config['system']['loaderconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'arcsas_load',
			'value' => 'YES',
			'comment' => 'Areca non-RAID HBAs ARC-1300/ARC-1320 series Driver'
		];
	endif;
	$index = array_search_ex('ipmi_load',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		$config['system']['loaderconf']['param'][] = [
			'uuid' => uuid(),
			'name' => 'ipmi_load',
			'value' => 'YES',
			'comment' => 'IPMItool Server Management Utility'
		];
	endif;
}
/**
 *	Upgrade configuration from version 2.5
 *	@global array $config
 */
function config_upgrade_2dot5() {
	global $config;

	array_make_branch($config,'system','sysctl','param');
	$index = array_search_ex('a4bd7970-1c77-43d7-8bc8-7924d9f86077',$config['system']['sysctl']['param'],'uuid');
	if(false !== $index):
		unset($config['system']['sysctl']['param'][$index]);
	endif;
	$index = array_search_ex('kern.maxfiles',$config['system']['sysctl']['param'],'name');
	if($index !== false):
		$config['system']['sysctl']['param'][$index]['value'] = '6289573';
	endif;
}
/**
 *	Upgrade configuration from version 2.6
 *	@global array $config
 */
function config_upgrade_2dot6() {
	global $config;

	array_make_branch($config,'system','loaderconf','param');
	$index = array_search_ex('arcsas_load',$config['system']['loaderconf']['param'],'name');
	if(false !== $index):
		unset($config['system']['loaderconf']['param'][$index]);
	endif;
}
/**
 *	Upgrade configuration from version 2.7
 *	@global array $config
 */
function config_upgrade_2dot7() {
	global $config;

	//	convert command scripts
	array_make_branch($config,'rc','param');
	array_make_branch($config,'rc','preinit','cmd');
	foreach($config['rc']['preinit']['cmd'] as $cmd):
		$config['rc']['param'][] = [
			'uuid' => uuid(),
			'name' => '',
			'value' => $cmd,
			'comment' => '',
			'typeid' => 1,
			'enable' => true,
			'protected' => false,
		];
	endforeach;
	unset($config['rc']['preinit']);
	array_make_branch($config,'rc','postinit','cmd');
	foreach($config['rc']['postinit']['cmd'] as $cmd):
		$config['rc']['param'][] = [
			'uuid' => uuid(),
			'name' => '',
			'value' => $cmd,
			'comment' => '',
			'typeid' => 2,
			'enable' => true,
			'protected' => false,
		];
	endforeach;
	unset($config['rc']['postinit']);
	array_make_branch($config,'rc','shutdown','cmd');
	foreach($config['rc']['shutdown']['cmd'] as $cmd):
		$config['rc']['param'][] = [
			'uuid' => uuid(),
			'name' => '',
			'value' => $cmd,
			'comment' => '',
			'typeid' => 3,
			'enable' => true,
			'protected' => false,
		];
	endforeach;
	unset($config['rc']['shutdown']);
}
/**
 *	Upgrade configuration from version 2.8
 *	@global array $config
 */
function config_upgrade_2dot8() {
	global $config;

	$grid = &array_make_branch($config,'bittorrent');
	if(!isset($grid['preallocation'])):
		$grid['preallocation'] = '1';
	endif;
}
/**
 *	Upgrade configuration from version 2.9
 */
function config_upgrade_2dot9() {
}
/**
 *	Upgrade configuration from version 3.0
 *	@global array $config
 */
function config_upgrade_3dot0() {
	global $config;

	//	Remove attribute monitorpassword.
	$grid = &array_make_branch($config,'ups');
	if(isset($grid['monitorpassword'])):
		unset($grid['monitorpassword']);
	endif;
}
/**
 *	Upgrade configuration from version 3.1
 *	@global array $config
 */
function config_upgrade_3dot1() {
	global $config;

	//	Encrypt user passwords
	$grid = &array_make_branch($config,'access','user');
	foreach($grid as $k_user => $r_user):
		if(isset($grid[$k_user]['password'])):
			if(is_string($grid[$k_user]['password'])):
				$grid[$k_user]['passwordmd4'] = mkpasswdmd4($grid[$k_user]['password']);
				$grid[$k_user]['passwordsha'] = mkpasswd($grid[$k_user]['password']);
			endif;
			unset($grid[$k_user]['password']);
		endif;
	endforeach;
}
/**
 *	Upgrade configuration from version 3.2
 *	@global array $config
 */
function config_upgrade_3dot2() {
	global $config;

	//	Add min_auto_ashift for zfs because of code changes
	$grid = &array_make_branch($config,'system','sysctl','param');
	$param_name = 'vfs.zfs.min_auto_ashift';
	if(false === ($index = array_search_ex($param_name,$grid,'name'))):
		$grid[] = [
			'uuid' => uuid(),
			'enable' => true,
			'name' => $param_name,
			'value' => '12',
			'comment' => gtext('System tuning')
		];
	endif;
}
/**
 *	Upgrade configuration from version 3.3
 *	@global array $config
 */
function config_upgrade_3dot3() {
	global $config;

	//	netatalk authentication methods
	$grid = &array_make_branch($config,'afp');
	array_make_branch($grid,'auxparam');
	if(isset($grid['guest'])):
		$grid['uams_guest'] = true;
		unset($grid['guest']);
	endif;
	if(isset($grid['local'])):
		$grid['uams_dhx_passwd'] = true;
		$grid['uams_dhx2_passwd'] = true;
		unset($grid['local']);
	else:
		$grid['uams_dhx_pam'] = true;
		$grid['uams_dhx2_pam'] = true;
	endif;
}
/**
 *	Upgrade configuration from version 3.4
 *	@global array $config
 */
function config_upgrade_3dot4() {
	global $config;

	//	syslogd parameter
	//	find existing console records, skip configuration when found
	$record_not_found = true;
	$grid = &array_make_branch($config,'system','syslogconf','param');
	foreach($grid as $row):
		if(is_array($row)):
			$test = sprintf('%s.%s',trim($row['facility'] ?? ''),trim($row['level'] ?? ''));
			if(preg_match('/^(\S*;)*console\.\S+\s+\S+/i',$test)):
				$record_not_found = false;
				break;
			endif;
		endif;
	endforeach;
	if($record_not_found):
		$grid[] = [
			'uuid' => uuid(),
			'facility' => 'console',
			'level' => 'debug',
			'value' => '/dev/console',
			'comment' => 'default setting',
			'enable' => true
		];
	endif;
}
/**
 *	Upgrade configuration from version 3.5
 *	@global array $config
 */
function config_upgrade_3dot5() {
	global $config;

	//	email parameter
	$grid = &array_make_branch($config,'system','email');
	$grid['security'] = $grid['security'] ?? true;
	switch($grid['security']):
		default:
			$grid['security'] = true;
			break;
		case 'none':
			$grid['security'] = false;
			break;
	endswitch;
	$grid['tls_certcheck'] = $grid['tls_certcheck'] ?? true;
	switch($grid['tls_certcheck']):
		default:
			$grid['tls_certcheck'] = true;
			break;
		case 'tls_certcheck off':
			$grid['tls_certcheck'] = false;
			break;
	endswitch;
}
/**
 *	Upgrade configuration from version 36
 *	@global array $config
 */
function config_upgrade_36() {
	global $config;

	//	ZFS Settings
	$grid = &array_make_branch($config,'zfs','settings');
	if(!array_key_exists('showusedavail',$grid)):
		$grid['showusedavail'] = false;
	endif;
	if(!array_key_exists('capacity_warning',$grid)):
		$grid['capacity_warning'] = 80;
	endif;
	if(!array_key_exists('capacity_critical',$grid)):
		$grid['capacity_critical'] = 90;
	endif;
}
/**
 *	Upgrade configuration from version 37
 *	@global array $config
 */
function config_upgrade_37() {
	global $config;

	//	Update WebGUI certificate and private key.
	$grid_webgui = &array_make_branch($config,'system','webgui');
	$install_new_cert = false;
	if(isset($grid_webgui['certificate']) && isset($grid_webgui['privatekey'])):
		if($grid_webgui['certificate'] === 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpNSUlFT1RDQ0F5R2dBd0lCQWdJSkFQUnVoc3FVRWdKcE1BMEdDU3FHU0liM0RRRUJDd1VBTUhBeEN6QUpCZ05WDQpCQVlUQWtwUU1STXdFUVlEVlFRSUV3cFRiMjFsTFZOMFlYUmxNUkV3RHdZRFZRUUtFd2hPUVZNMFJuSmxaVEVYDQpNQlVHQTFVRUF4TU9ibUZ6TkdaeVpXVXViRzlqWVd3eElEQWVCZ2txaGtpRzl3MEJDUUVXRVdsdVptOUFibUZ6DQpOR1p5WldVdWIzSm5NQjRYRFRFME1USXdNekF3TURJd01sb1hEVE0wTVRJd016QXdNREl3TWxvd2NERUxNQWtHDQpBMVVFQmhNQ1NsQXhFekFSQmdOVkJBZ1RDbE52YldVdFUzUmhkR1V4RVRBUEJnTlZCQW9UQ0U1QlV6UkdjbVZsDQpNUmN3RlFZRFZRUURFdzV1WVhNMFpuSmxaUzVzYjJOaGJERWdNQjRHQ1NxR1NJYjNEUUVKQVJZUmFXNW1iMEJ1DQpZWE0wWm5KbFpTNXZjbWN3Z2dFaU1BMEdDU3FHU0liM0RRRUJBUVVBQTRJQkR3QXdnZ0VLQW9JQkFRQ3RqVTRnDQpWY1hWSG1BUzBVbW5mRzZ0K050N2JzZ3Y1TytSR3dRa1NSVk0rdTZ0d0dZc2Jmck4zSHpNV3I1elBHUGJxNU9kDQpiN1JZa0plVVBIM1kxRXhoMlUwNVA5YmorWkpXVGxrTXZSbXVJU2hlZWJ2Qlp2WjNlQXhuaC9IWVozWDVabisyDQpibVR0bzVTOVRNMXNoeEFtenNGdVBMZFRuSFhRWjdWZzl4VHZXNFYvUklMWGZNQ3hjVUtwVjZyQjVpN1BjVW9SDQpmNWErNEpWWjBNMlVPdTZSdllHdmQvT09yUkhWbEd0cUpIclBiOVo1ODBkMFFzOCt4cVgzWThHc3l2VmYrTUJtDQpycmQ1UzZqNGdSWlpGeUJzeS9QcGhEcWt0bks2VHBjMlN1VE5xbWtLd0JadTZyZzlDNTB4a011dVp5dFA3UkpRDQpYS3lNVWs3bHg2MU1Na0xSQWdNQkFBR2pnZFV3Z2RJd0hRWURWUjBPQkJZRUZHcEZWMHV6RGJYK3dNc3FjQkVFDQpJOTArMER1dE1JR2lCZ05WSFNNRWdab3dnWmVBRkdwRlYwdXpEYlgrd01zcWNCRUVJOTArMER1dG9YU2tjakJ3DQpNUXN3Q1FZRFZRUUdFd0pLVURFVE1CRUdBMVVFQ0JNS1UyOXRaUzFUZEdGMFpURVJNQThHQTFVRUNoTUlUa0ZUDQpORVp5WldVeEZ6QVZCZ05WQkFNVERtNWhjelJtY21WbExteHZZMkZzTVNBd0hnWUpLb1pJaHZjTkFRa0JGaEZwDQpibVp2UUc1aGN6Um1jbVZsTG05eVo0SUpBUFJ1aHNxVUVnSnBNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJDQpodmNOQVFFTEJRQURnZ0VCQUQ3aUxLaWVsRHBVVk9BWGVCeDk0MDVmZ0p4ZUtLVXZUMDVBNWtSQVhTanFnYkdjDQp2eWkySUpWaTNadlV4OWJtL3RyME5MclhQaVBMRlU5T3pTd0tQQXJnbGdjY2JKbDlBV0xTN3d3RTdaQzRnTTE5DQpBSmYyR09RcEwzYnoyaXk0TTBkdGJjUnBHdDRHV2szdDZ6NXozZTdJblNaNDdTcWhtbVNIekUwdTlhWTRJUWZwDQovVHJ1ZTBVYTVYNGJsVUhvTnhVb2xmQ1ptdTU3VkZpWlJLSlhEZGhVSHFKbEhMT1BUYmxtZWZtSTg3RVhDTjFBDQo5YWIyVEhWWVZhSnlVM2p5VUo3by9xNmtzYlB5UDlTeFBSZCsvSmszbHVJemp4V1NHRXZoNDhZc0dlWWo2MVN1DQpkVUZoUWZ6S3FzeXNUN21ZQ1NuL1RqUkpnYnZOaWpVaGZiemtMZUE9DQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0t'):
			$install_new_cert = true;
		endif;
	else:
		$install_new_cert = true;
	endif;
	if($install_new_cert):
		$grid_webgui['certificate'] = "LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpNSUlEMnpDQ0FzT2dBd0lCQWdJSkFMWm9teUJQZkpIbU1BMEdDU3FHU0liM0RRRUJDd1VBTUlHRE1Rc3dDUVlEDQpWUVFHRXdKT1RERVVNQklHQTFVRUNBd0xUbVYwYUdWeWJHRnVaSE14RWpBUUJnTlZCQWNNQ1VGdGMzUmxjbVJoDQpiVEVSTUE4R0ExVUVDZ3dJV0dsbmJXRk9RVk14RlRBVEJnTlZCQU1NREhocFoyMWhibUZ6TG1OdmJURWdNQjRHDQpDU3FHU0liM0RRRUpBUllSYVc1bWIwQjRhV2R0WVc1aGN5NWpiMjB3SGhjTk1UZ3dOekE1TVRNME16QXpXaGNODQpNamd3TnpBMk1UTTBNekF6V2pDQmd6RUxNQWtHQTFVRUJoTUNUa3d4RkRBU0JnTlZCQWdNQzA1bGRHaGxjbXhoDQpibVJ6TVJJd0VBWURWUVFIREFsQmJYTjBaWEprWVcweEVUQVBCZ05WQkFvTUNGaHBaMjFoVGtGVE1SVXdFd1lEDQpWUVFEREF4NGFXZHRZVzVoY3k1amIyMHhJREFlQmdrcWhraUc5dzBCQ1FFV0VXbHVabTlBZUdsbmJXRnVZWE11DQpZMjl0TUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUEyQkloV1Q2NjZQemlLeW1CDQpNaGRMNUJRaWd0YkFUMjNYTi9MZmErcExrZW91YitQdmduS0pnd25mcGd6Ty9JZXFuYXhSVmQ4bnRMSlRqaGh5DQovVU5LU0dkQy9OUjZGSWt5QWZlM25IOVM2eGZRaU92MjBsUXBGV2lqUzg4TmVRZEpXUVpsSVlwZmloWVJXemdBDQpvWEp4dWkxcHd0Y1dUODN1WDg5VXBhbjhTRUNGcFJDNURXTTNCWjRRYzk2K05VdDR4N2puaWszMURNTHE5ZGhlDQo3RjdhT0ZEWnNZV2xpM0REbi9va01HVmF5MzMzVHpDMGxSNzJISVo0SkRLcmJjNUFFbWN3dkFuRnVFWC80RTQzDQpwWHFsY2lhQzRDeWNlQ2JQSUlJWHcyNDArQ3lNQm9BYlBqTkZVcTBCTEhzYlVyOWdidnFlcDkxWWdUamxUZUtMDQo0QTR2VVFJREFRQUJvMUF3VGpBZEJnTlZIUTRFRmdRVUlmUThDMFY5cFpkZjVWUkdUWkNvcC92WUlQVXdId1lEDQpWUjBqQkJnd0ZvQVVJZlE4QzBWOXBaZGY1VlJHVFpDb3AvdllJUFV3REFZRFZSMFRCQVV3QXdFQi96QU5CZ2txDQpoa2lHOXcwQkFRc0ZBQU9DQVFFQUpzNlo5dTA3Y3cvRFpyRFRyQVpnYzYxeDhORHRwSUhUdExYVjZvUkY0WFZMDQp6M0xPaXJaU2REUHZLRWNLM2RqRmxzU3VkckxabFpnQUJzTHVpSEhjTTBYL2VKdG0vOExYQjA2K0ZuYmtvbHhzDQpVWHpXNlRSSEg4L3JHMTFjZGhpNUpkR2xuNEYzd2FQYzRtVGJHSW41eUNNNU13UTdUbUtvcFV6WjlMdUxmQndKDQpyRzlJWUozTEVJZFYrbDJ6SHgvYjErdjFnYlE3ZGFuamxxYksyNnJ6aUJxSHBnUkYvcjFJWDZ0c0t1bVgveENjDQpZR0NRcU16T0EvMyt2QWphSDdYdUU2emo2ZGVZWExoNGRzekFLSlRURU95cXBNQWZmc1JISkJoc2p0RkFmN0syDQorSFExakJ6TDBhUHZGaFhNczBMYy9icFo0NWh1Rk1GaHFWS0t5aHorUEE9PQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==";
		$grid_webgui['privatekey'] = "LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJRXBRSUJBQUtDQVFFQTJCSWhXVDY2NlB6aUt5bUJNaGRMNUJRaWd0YkFUMjNYTi9MZmErcExrZW91YitQdg0KZ25LSmd3bmZwZ3pPL0llcW5heFJWZDhudExKVGpoaHkvVU5LU0dkQy9OUjZGSWt5QWZlM25IOVM2eGZRaU92Mg0KMGxRcEZXaWpTODhOZVFkSldRWmxJWXBmaWhZUld6Z0FvWEp4dWkxcHd0Y1dUODN1WDg5VXBhbjhTRUNGcFJDNQ0KRFdNM0JaNFFjOTYrTlV0NHg3am5pazMxRE1McTlkaGU3RjdhT0ZEWnNZV2xpM0REbi9va01HVmF5MzMzVHpDMA0KbFI3MkhJWjRKREtyYmM1QUVtY3d2QW5GdUVYLzRFNDNwWHFsY2lhQzRDeWNlQ2JQSUlJWHcyNDArQ3lNQm9BYg0KUGpORlVxMEJMSHNiVXI5Z2J2cWVwOTFZZ1RqbFRlS0w0QTR2VVFJREFRQUJBb0lCQVFEQ2o4alJ1T2ZMaG5NTg0KMUtqT2R5WFNNY05GdDZTaG9WSEx3bm9taldvUThTTnhnM2tYS0pYa2xnNTZVa3hNSHNtbWd4VVdVd1JJOU03Qw0KUkFlL1FFbHlXdkY2QXNZUEdkOGE0SG5JaE9vZ1lXaFk4c2xsYVgwNUZ1ajhDV3lqVDhsZlRaL2hETjJzMU9RNg0KRkhxVTVoMjhQTXBnTlJlVk11VDBCeGk4dUExQTdKZjlsUzkrNmxPbDIvdDRqYnByMWdEZzd6d1ZzZGljRFBnaA0KUFdacm9JMGJZbVY5Si8yRVlwdG1Sdm5lNEpseWtMWU8vdThuMmxsWFV4TEV5eHdjZHlLcU9JeVppVlRsY21FZw0KWlU2ZkhnV2xqdmRFanJTL05XZko0ejRjZGhPOXhjelo3MUZ0ZWNLMGVHWjk4WDhVcXdZUm4wTEJtV1FNVWJ1Mg0KSFIzUU1BYXhBb0dCQVB6bkRadTNZSk13QmtTSVVhczFnWHAwREJCTWVWSlZTbUpFYjduSXNpMHRRbkowQVk2OA0KOHJadXZoSVdmMzl5aW04dW80VXRjRDQyYWNlQ1V1NmdxdDlyS2wvWm9JRjB5K1FDT2VENGVTcW5YK1orQU9lRw0KTWlYMGlEM0V4VGs0NWtWZUdiR3poUEI4RnVGdlVsc1p4bDl0ckM5WUpUTXFhU0doRUdNNlluRDFBb0dCQU5xMw0KbUhBVldsQTdLQTFJZnl6ZmdPdXVxaStTbnpxVk1ZM3JYd1pabjFJQVZKb08wY0l2K3ZkSktWMVBaOGllbHNYNQ0KeGg4Q0dNSzE3WVNTVVg1M09QVnJoeXpEY2xnaCtTUWpBa0h5WmY0bVBZOXgrV29zM3FyRE40SUhqNWVQb3VQaQ0KN0hPUElxUk5IQXNiRXJ3N0dRelJyN240TG45WWcxWThhaXI3NlZ0dEFvR0JBTFRVVUxxaXAvOFdPNlVOQTdneg0KamQwTTFRTmlhdTBmNXd2VWV6NlA3amEzUWVlc1l1QzQ0YUxvRkNmQ3htOU9hd1dNaDRkV01nZUp5S0RLNHBtVg0KY0JoSC9BaUZCMnNKSkRCb0Z4SnhGOUlUMDFKdmpoMzlxaVhSYndrYmhSbWhRYVVmMzZkY0FSWWk1QWxWRzhIZg0KOENPVHU2WXVsSXE5TEdiTFQzaGo2cmR4QW9HQkFLRVFydnRyWGhUVFc0MWNvL3JocE1qcTJYMHJGb05IdHpSeA0KN29wNG1YRUR1VW15UEJvcXgwQXY2bnhDcTRUNzlNQmFUZUlyNHh3UWFaZEZRU3BHbGZWMWVCZ28zaDIwOXduNw0KbGdBR2g4Ri9XN28xRHAxMzZZb0xOT2RXdGhIQXZ3M2U0STNCREp1K0JUYlFleDJPejhXMjdSZWdnbTFaTzI3WQ0KRXI4UXdDcUpBb0dBWlZBdnlmUmhEMjUzTC9xQTJXaDRaNGg0WlJVZTRNZythTC9xQUF3OG5VTnhtdjlEdlVEWA0KZGVxRnBPczlrVHpLMjdUWmZhbkxuN1VRV05remtqOXdDR2FtelA2RWFXT05Na1JFUGtwOFZjbFVra1V3OVN5aQ0KUFMyOVhKQ2dKazIzVnJLOE5hejFtZ25QMm5pd3pHaTBDdUZCcE1EN2h6dWp1S0lmOGlNOVdUaz0NCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t";
	endif;
	//	Update motd
	$grid_system = &array_make_branch($config,'system');
	if(array_key_exists('motd',$grid_system) && is_string($grid_system['motd'])):
		$helpinghand = base64_decode($grid_system['motd']);
	else:
		$helpinghand = 'Welcome to XigmaNAS!' . PHP_EOL;
	endif;
	$grid_system['motd'] = base64_encode(str_ireplace('NAS4Free','XigmaNAS',$helpinghand));
}
/**
 *	Upgrade configuration from version 38
 *	@global array $config
 */
function config_upgrade_38() {
	global $config;

	//	Encode ftp welcome message
	array_make_branch($config,'ftpd');
	$banner = $config['ftpd']['banner'] ?? '';
	//	Detect base64 string.
	if(preg_match('~^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$~',$banner)):
		$encoded_banner = $banner;
	else:
		$encoded_banner = base64_encode($banner);
	endif;
	$config['ftpd']['banner'] = $encoded_banner;
	//	remove duplicate users
	$user_tmp = [];
	$user_as_key = [];
	array_make_branch($config,'system','usermanagement','user');
	foreach($config['system']['usermanagement']['user'] as $user):
		if(array_key_exists('name',$user) && is_string($user['name']) && !array_key_exists($user['name'],$user_as_key)):
			$user_tmp[] = $user;
			$user_as_key[$user['name']] = $user['name'];
		endif;
	endforeach;
	$config['system']['usermanagement']['user'] = $user_tmp;
	//	remove duplicate groups
	$group_tmp = [];
	$group_as_key = [];
	array_make_branch($config,'system','usermanagement','group');
	foreach($config['system']['usermanagement']['group'] as $group):
		if(array_key_exists('name',$group) && is_string($group['name']) && !array_key_exists($group['name'],$group_as_key)):
			$group_tmp[] = $group;
			$group_as_key[$group['name']] = $group['name'];
		endif;
	endforeach;
	$config['system']['usermanagement']['group'] = $group_tmp;
}
/**
 *	Upgrade configuration from version 39
 *	@global array $config
 */
function config_upgrade_39() {
	global $config;

	$groups = &array_make_branch($config,'system','usermanagement','group');
	$group_name = 'mysql';
	$index = array_search_ex($group_name,$groups,'name');
	if(false === $index):
		$group = [
			'uuid' => uuid(),
			'name' => $group_name,
			'id' => 88,
		];
		$groups[] = $group;
	endif;
	$users = &array_make_branch($config,'system','usermanagement','user');
	$user_name = 'mysql';
	$index = array_search_ex($user_name,$users,'name');
	if(false === $index):
		$user = [
			'uuid' => uuid(),
			'name' => $user_name,
			'id' => 88,
			'primarygroup' => 88,
			'extraoptions' => '-c "MySQL user" -d /nonexistent -s /usr/sbin/nologin'
		];
		$users[] = $user;
	endif;
}
/**
 *	Upgrade configuration from 40
 *	@global type $config
 */
function config_upgrade_40() {
	global $config;

	$a_share = &array_make_branch($config,'nfsd','share');
	$a_export = &array_make_branch($config,'nfsd','export','param');
	$a_root = &array_make_branch($config,'nfsd','root','param');
	foreach($a_share as $r_share):
		$r_param = [];
		$r_param['enable'] = true;
		$r_param['uuid'] = $r_share['uuid'] ?? uuid();
		$r_param['path'] = $r_share['path'] ?? '/';
		$r_param['network'] = $r_share['network'] ?? '';
		$r_param['description'] = $r_share['comment'] ?? '';
		if(array_key_exists('v4rootdir',$r_share)):
			$a_root[] = $r_param;
		else:
			$r_param['mapall'] = $r_share['mapall'] ?? 'nomap';
			$r_param['opt_alldirs'] = isset($r_share['options']['alldirs']);
			$r_param['opt_readonly'] = isset($r_share['options']['ro']);
			$r_param['opt_quiet'] = isset($r_share['options']['quiet']);
			$a_export[] = $r_param;
		endif;
	endforeach;
	unset($config['nfsd']['share']);
}
/**
 *	Upgrade configuration from 41
 *	@global type $config
 */
function config_upgrade_41() {
	global $config;

	$a_source = &array_make_branch($config,'minidlna','content');
	$a_destination = &array_make_branch($config,'minidlna','media','param');
	$r_param = [
		'enable' => true,
		'description' => ''
	];
	foreach($a_source as $r_source):
		unset($matches);
		if(is_string($r_source) && (1 === preg_match('/^(?:(A|P|V|PV),)?(.*)$/',$r_source,$matches))):
			$r_param['uuid'] = uuid();
			$r_param['type'] = $matches[1];
			$r_param['path'] = $matches[2];
			$a_destination[] = $r_param;
		endif;
	endforeach;
	unset($config['minidlna']['content']);
}
/**
 *	Upgrade configuration from 42
 *	@global type $config
 */
function config_upgrade_42() {
	global $config;

	$a_source = &array_make_branch($config,'access','user');
	foreach($a_source as $r_source):
		if(array_key_exists('enable',$r_source)):
			return;
		endif;
	endforeach;
	foreach($a_source as &$r_source):
		$r_source['enable'] = true;
	endforeach;
}
/**
 *	Upgrade configuration from 43
 *	@global array $config
 */
function config_upgrade_43() {
	global $config;

	$a_source = &array_make_branch($config,'system','rcconf','param');
	$rcconf_param_name = 'netwait_enable';
	$index = array_search_ex($rcconf_param_name,$a_source,'name');
	if(false === $index):
		$rcconf_param = [
			'uuid' => uuid(),
			'name' => $rcconf_param_name,
			'value' => 'YES',
			'comment' => 'Wait for interface and gateway'
		];
		$a_source[] = $rcconf_param;
	endif;
}
/**
 *	Upgrade the configuration file to the latest version.
 *	@global array $g
 *	@global array $config
 *	@return int Returns 0 if successful, otherwise 1
 */
function config_upgrade() {
	global $config;

	$kf = 0;
	$kv = 1;
	$config_upgrade_matrix = [
		'9.6' => [$kf => 'config_upgrade_blacklisted',$kv => '9.6'], // blacklisted
		'0.9' => [$kf => 'config_upgrade_0dot9',$kv => '1.0'],
		'1.0' => [$kf => 'config_upgrade_1dot0',$kv => '1.1'],
		'1.1' => [$kf => 'config_upgrade_1dot1',$kv => '1.2'],
		'1.2' => [$kf => 'config_upgrade_1dot2',$kv => '1.3'],
		'1.3' => [$kf => 'config_upgrade_1dot3',$kv => '1.4'],
		'1.4' => [$kf => 'config_upgrade_1dot4',$kv => '1.5'],
		'1.5' => [$kf => 'config_upgrade_1dot5',$kv => '1.6'],
		'1.6' => [$kf => 'config_upgrade_1dot6',$kv => '1.7'],
		'1.7' => [$kf => 'config_upgrade_1dot7',$kv => '1.8'],
		'1.8' => [$kf => 'config_upgrade_1dot8',$kv => '1.9'],
		'1.9' => [$kf => 'config_upgrade_1dot9',$kv => '2.0'],
		'2.0' => [$kf => 'config_upgrade_2dot0',$kv => '2.1'],
		'2.1' => [$kf => 'config_upgrade_2dot1',$kv => '2.2'],
		'2.2' => [$kf => 'config_upgrade_2dot2',$kv => '2.3'],
		'2.3' => [$kf => 'config_upgrade_2dot3',$kv => '2.4'],
		'2.4' => [$kf => 'config_upgrade_2dot4',$kv => '2.5'],
		'2.5' => [$kf => 'config_upgrade_2dot5',$kv => '2.6'],
		'2.6' => [$kf => 'config_upgrade_2dot6',$kv => '2.7'],
		'2.7' => [$kf => 'config_upgrade_2dot7',$kv => '2.8'],
		'2.8' => [$kf => 'config_upgrade_2dot8',$kv => '2.9'],
		'2.9' => [$kf => 'config_upgrade_2dot9',$kv => '3.1'], // skip 3.0
		'3.0' => [$kf => 'config_upgrade_3dot0',$kv => '3.1'],
		'3.1' => [$kf => 'config_upgrade_3dot1',$kv => '3.2'],
		'3.2' => [$kf => 'config_upgrade_3dot2',$kv => '3.3'],
		'3.3' => [$kf => 'config_upgrade_3dot3',$kv => '3.4'],
		'3.4' => [$kf => 'config_upgrade_3dot4',$kv => '3.5'],
		'3.5' => [$kf => 'config_upgrade_3dot5',$kv => '36'],
		'36' => [$kf => 'config_upgrade_36',$kv => '37'],
		'37' => [$kf => 'config_upgrade_37',$kv => '38'],
		'38' => [$kf => 'config_upgrade_38',$kv => '39'],
		'39' => [$kf => 'config_upgrade_39',$kv => '40'],
		'40' => [$kf => 'config_upgrade_40',$kv => '41'],
		'41' => [$kf => 'config_upgrade_41',$kv => '42'],
		'42' => [$kf => 'config_upgrade_42',$kv => '43'],
		'43' => [$kf => 'config_upgrade_43',$kv => '44'],
		'44' => [$kf => NULL,$kv => NULL]
	];
	$version = $config['version'] ?? 'notset';
	$config_has_been_updated = false;
	do {
		$stay_in_loop = false;
		if(array_key_exists($version,$config_upgrade_matrix)): // known version
			if(array_key_exists($kf,$config_upgrade_matrix[$version]) && array_key_exists($kv,$config_upgrade_matrix[$version])): // check existing fields
				$function_name = $config_upgrade_matrix[$version][$kf];
				$new_version = $config_upgrade_matrix[$version][$kv];
				if(isset($function_name) && isset($new_version) && function_exists($function_name)): // handler and new version set?
					if(is_booting()):
						echo 'Configuration upgrade from version ',$version,' to version ',$new_version,':';
						call_user_func($function_name); // call conversion
						echo ' completed.',PHP_EOL;
					else:
						call_user_func($function_name); // call conversion
					endif;
					$config['version'] = $version = $new_version; // update version
					$config_has_been_updated = true; // flag write config
					$stay_in_loop = true;
				endif;
			elseif(is_booting()):
				echo 'Configuration upgrade matrix is missing an element.',PHP_EOL;
			endif;
		elseif(is_booting()):
			echo 'Configuration upgrade has found an unknown version (',$version,').',PHP_EOL;
		endif;
	} while($stay_in_loop);
	write_config();
	return 0;
}
/**
 *	Hook for preinit
 *	@global array $g
 *	@global array $config
 */
function config_preinit() {
	global $g,$config;

	$need_write_config = false;
	if($need_write_config):
		echo 'Saving configuration',PHP_EOL;
		write_config();
	endif;
}
/**
 *	Hook for postinit
 *	@global array $g
 *	@global array $config
 */
function config_postinit() {
	global $g,$config;

	$need_write_config = false;
	//	save current uuid if not found in config
	if(stristr($g['platform'],'embedded')):
		$uuid = @exec('/bin/kenv -q smbios.system.uuid');
		$hostuuid = @exec('/sbin/sysctl -qn kern.hostuuid');
		if(empty($uuid) && !empty($hostuuid) && empty($config['system']['uuid'])):
			$config['system']['uuid'] = $hostuuid;
			$need_write_config = true;
		endif;
	endif;
	if($need_write_config):
		echo 'Saving configuration.',PHP_EOL;
		write_config();
	endif;
}
/**
 *	encrypt and gzcompress
 *	@global array $g
 *	@param string $password
 *	@return mixed The encrypted string is returned or false on failure.
 */
function config_encrypt($password) {
	global $g;

	$config = file_get_contents("{$g['conf_path']}/config.xml");
	if (false === $config):
		return false;
	endif;
	$b64enc_config = encrypt_aes256cbc($password,$config);
	if(false === $b64enc_config):
		return false;
	endif;
	$gz_config = gzencode($b64enc_config,9);
	if(false === $gz_config):
		return false;
	endif;
	return $gz_config;
}
/**
 *	gzdecompress and decrypt
 *	@param string $password
 *	@param string $gz_config
 *	@return mixed The decryted string is returned or false on failure.
 */
function config_decrypt($password,$gz_config) {
	$b64enc_data = gzdecode($gz_config);
	if(false === $b64enc_data):
		return false;
	endif;
	$config = decrypt_aes256cbc($password,$b64enc_data);
	if(false === $config):
		return false;
	endif;
	return $config;
}
