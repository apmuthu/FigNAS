<?php
/*
	system.inc

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
require_once 'functions.inc';
require_once 'util.inc';
require_once 'rc.inc';

function system_reboot() {
	// Initiate halt. Everything will be done automatically
	// in /etc/rc.shutdown by executing rc-init scripts in
	// reverse order (the 'KEYWORD: shutdown' must be defined).
	mwexec("/sbin/shutdown -r now");
}

function system_halt() {
	// Initiate halt. Everything will be done automatically
	// in /etc/rc.shutdown by executing rc-init scripts in
	// reverse order (the 'KEYWORD: shutdown' must be defined).
	mwexec("/sbin/shutdown -p now");
}
function get_http_accept_language(): array {
	$accepted_languages = [];
	$result = [];
	$accepted_language_string = filter_input(INPUT_SERVER,'HTTP_ACCEPT_LANGUAGE',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL]]);
	if(isset($accepted_language_string)):
		$matches = NULL;
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',$accepted_language_string,$matches);
		$combined = array_combine($matches[1],$matches[4]);
		foreach($combined as $key => $val):
			$updated_key = strtolower(str_replace('-','_',$key));
			if('' == $val):
				$updated_val = 1.0;
			else:
				$updated_val = (float)$val;
			endif;
			$accepted_languages[$updated_key] = $updated_val;
		endforeach;
		//	add languages with stripped country code to the list of accepted languages if necessary
		foreach($accepted_languages as $key => $q):
			$result[$key] = $q; // transfer original row
			if(false !== strpos($key,'_')): // check for underscore character
				$additional_key = strstr($key,'_',true); // get the good portion the string i.e. en-US -> en
				if(!isset($accepted_language_string[$additional_key])): // don't process if the additional key already exists...
					if(!isset($result[$additional_key])): // in accepted_languages or result lists.
						$result[$additional_key] = $q; // no duplicate found, add with the same q factor
					endif;
				endif;
			endif;
		endforeach;
	endif;
	return $result;
}
/* Init language environment */
function system_language_load() {
	global $config,$g_languages;

	$language = NULL;
	if(!isset($language)):
		//	Get the language from configuration.
		$configured_language = filter_var($config['system']['language'],FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL]]);
		if(isset($configured_language) && key_exists($configured_language,$g_languages)):
			if('auto' !== $configured_language):
				$language = $configured_language;
			endif;
		endif;
	endif;
	if(!isset($language)):
		//	Get the language from client.
		$accepted_languages = get_http_accept_language();
		if(!empty($accepted_languages)):
			$available_languages = array_flip($g_languages);
			$matching_languages = array_intersect_key($accepted_languages,$available_languages);
			$first_highest_q = 0.0;
			foreach($matching_languages as $key => $val):
				if($val > $first_highest_q):
					$language = $available_languages[$key];
					$first_highest_q = $val;
				endif;
			endforeach;
		endif;
	endif;
	if(!isset($language)):
		$language = 'en_US';
	endif;
	$domain = strtolower(get_product_name());
	$codeset = 'UTF-8';
	putenv(sprintf('LANGUAGE=%s',$language));
	putenv(sprintf('LANG=%s',$language));
	setlocale(LC_MESSAGES,sprintf('%s.%s',$language,$codeset));
	bindtextdomain($domain,'/usr/local/share/locale');
	bind_textdomain_codeset($domain,$codeset);
	textdomain($domain);
}
/* Get the codeset of the current configured language. */
/* Return: String containing codeset of current language. */
function system_get_language_codeset() {
	return 'UTF-8';
}

/* Get the entity of LanguageCode of the current configured language. */
/* Return: String containing language code of current laguage. */
function system_get_language_code() {
	// a language code, as per [RFC3066]
	$language = getenv('LANG');
	$code = str_replace('_','-',$language);
	if(empty($code)):
		$code = "en-US"; // Set default code.
	endif;
	return $code;
}
// Get list of available groups from /etc/group.
// Result:
//	Array (
//		[wheel] => 0
//		[sshd] => 22
//		[www] => 80
//		[nobody] => 65534
//		[admin] => 1000
//		...
//	)
function system_get_group_list() {
	// List of groups to filter from result list.
	$filterlist = ['_dhcp','_pflogd'];
	$grouplist = [];
	preg_match_all('/(\S+):\*:(\d+):.*\n/',@file_get_contents('/etc/group'),$matches,PREG_SET_ORDER);
	if(is_array($matches)):
		foreach($matches as $group):
			if(false === in_array($group[1],$filterlist)):
				$grouplist[$group[1]] = $group[2];
			endif;
		endforeach;
		ksort($grouplist);
	endif;
	return $grouplist;
}
// Get list of available users from /etc/master.passwd.
// Result:
//  Array (
//    [test] => Array ( [name] => test
//                      [password] => $1$yuQLaTPN$lkwYlZEB7B8n85flXPkHd0
//                      [uid] => 1001
//                      [gid] => 1001
//                      [class] =>
//                      [change] => 0
//                      [expire] => 0
//                      [gecos] => test
//                      [home_dir] => /mnt
//                      [shell] => /usr/local/bin/scponly
//                    )
//    [root] => ...
//    [toor] => ..
//    [daemon] => ...
//    [operator] => ...
//    ...
//  )
function system_get_user_list() {
	$userlist = [];
	foreach(explode("\n",@file_get_contents('/etc/master.passwd')) as $userinfov):
		if(empty($userinfov)):
			continue;
		else:
			// Extract user information
			$userinfo = explode(':',$userinfov);
			$user = [];
			$user['name'] = $userinfo[0];
			$user['password'] = $userinfo[1];
			$user['uid'] = $userinfo[2];
			$user['gid'] = $userinfo[3];
			$user['class'] = $userinfo[4];
			$user['change'] = $userinfo[5];
			$user['expire'] = $userinfo[6];
			$user['gecos'] = $userinfo[7];
			$user['home_dir'] = $userinfo[8];
			$user['shell'] = $userinfo[9];
			$userlist[$user['name']] = $user;
		endif;
	endforeach;
	return $userlist;
}

// SMBIOS get various info
function get_smbios_info() {
	$smbios = [];
	$cmd = '/bin/kenv -q';
	unset($rawdata,$rc);
	mwexec2($cmd,$rawdata,$rc);
	if($rc != 0):
		return $smbios;
	endif;
	foreach($rawdata as $line):
		if(preg_match("/^smbios\.([^\.=]+)\.([^\.=]+)=\"(.*)\"/",$line,$match)):
			$smbios[$match[1]][$match[2]] = $match[3];
		endif;
	endforeach;
	$platform = get_platform_type();
	if($smbios['bios']['vendor'] == 'BHYVE'):
		$smbios['bios']['reldate'] = '01/01/2015';
		$smbios['bios']['vendor'] = 'BHyVe';
		$smbios['bios']['version'] = '1.0';
		$smbios['planar']['maker'] = '';
		$smbios['planar']['product'] = 'BHyVe';
	endif;
	return $smbios;
}

// Get number of CPUs
function system_get_cpus() {
	$cpus = @exec("/sbin/sysctl -q -n kern.smp.cpus");
	if($cpus == '' || $cpus < 1):
		$cpus = 1;
	endif;
	return $cpus;
}
/**
 *	Calculate cpu usage (cores) and returns an array containing
 *	values between 0 and 100 for each core, representing 0% - 100% usage
 *	@return array
 */
function system_get_smp_cpu_usage(): array {
	$cpus = rtrim(`sysctl -nq kern.smp.cpus`);
	$c_stats_1 = rtrim(`/sbin/sysctl -nq kern.cp_times`);
	sleep(1);
	$c_stats_2 = rtrim(`/sbin/sysctl -nq kern.cp_times`);
	$a_stats_1 = explode(' ',$c_stats_1);
	$a_stats_2 = explode(' ',$c_stats_2);
	$a_diff = array_map(function($a,$b) { return $b - $a; },$a_stats_1,$a_stats_2);
	$a_usage = array_fill_keys(range(0,$cpus - 1),0);
	for($i = 0;$i < $cpus;$i++):
//		CPUSTATES: 0 => user, 1 => nice, 2 => system, 3 => interrupt, 4 => idle
		$a = array_splice($a_diff,0,5);
		$a_usage[$i] = floor(100 * ( 1 - $a[4] / array_sum($a)));
	endfor;
	return $a_usage;
}
/**
 *	Calculate the cpu usage and returns a value between 0 and 100, representing 0% - 100% usage
 *	@return float
 */
function system_get_cpu_usage(): float {
	$c_stats_1 = rtrim(`/sbin/sysctl -nq kern.cp_times`);
	sleep(1);
	$c_stats_2 = rtrim(`/sbin/sysctl -nq kern.cp_times`);
	$a_stats_1 = explode(' ',$c_stats_1);
	$a_stats_2 = explode(' ',$c_stats_2);
	$a = array_map(function($a,$b) { return $b - $a; },$a_stats_1,$a_stats_2);
//	CPUSTATES: 0 => user, 1 => nice, 2 => system, 3 => interrupt, 4 => idle
	$f_usage = floor(100 * ( 1 - $a[4] / array_sum($a)));
	return $f_usage;
}
/**
 *	Get various CPU informations.
 *	@return array Array with CPU information
 */
function system_get_cpu_info() {
	$platform = '';
	$cpuinfo = [];
	$cpuinfo['number'] = rtrim(`/sbin/sysctl -n hw.ncpu`);
	$cpuinfo['model'] = rtrim(`/sbin/sysctl -n hw.model`);
	$cpuinfo['clockrate'] = rtrim(`/sbin/sysctl -n hw.clockrate`);
	$cpuinfo['temperature'] = rtrim(`/sbin/sysctl -n hw.acpi.thermal.tz0.temperature`);
	$cpus = system_get_cpus();
	$cpuinfo['temperature2'] = [];
	$temp = @exec("/sbin/sysctl -q -n dev.cpu.0.temperature");
	if($temp != '' && $temp != '-1'):
		$adj = 0;
		for($i = 0; $i < $cpus; $i++):
			$temp = @exec("/sbin/sysctl -q -n dev.cpu.{$i}.temperature");
			if($temp != ''):
				$temp = sprintf("%.1f",($temp + $adj));
			endif;
			$cpuinfo['temperature2'][$i] = $temp;
		endfor;
	endif;
	$cpuinfo['freq'] = rtrim(`/sbin/sysctl -n dev.cpu.0.freq`);
	$cpuinfo['freqlevels'] = rtrim(`/sbin/sysctl -n dev.cpu.0.freq_levels`);
	return $cpuinfo;
}
/**
 *	Get the system uptime (how long the system is already running).
 *	@return The current uptime as string.
 */
function system_get_uptime() {
	exec("/sbin/sysctl -n kern.boottime",$boottime);
	preg_match("/sec = (\d+)/",$boottime[0],$matches);
	$boottime = $matches[1];
	$uptime = time() - $boottime;

	if($uptime > 60):
		$uptime += 30;
	endif;
	$updays = (int)($uptime / 86400);
	$uptime %= 86400;
	$uphours = (int)($uptime / 3600);
	$uptime %= 3600;
	$upmins = (int)($uptime / 60);
	$upsecs = $uptime % 60;

	$gt_seconds = (1 === $upsecs) ? gettext('Second') : gettext('Seconds');
	$gt_minutes = (1 === $upmins) ? gettext('Minute') : gettext('Minutes');
	$gt_hours = (1 === $uphours) ? gettext('Hour') : gettext('Hours');
	$gt_days = (1 === $updays) ? gettext('Day') : gettext('Days');

	if(($updays < 1) && ($uphours < 1)):
		$uptime = sprintf('%d %s %d %s',$upmins,$gt_minutes,$upsecs,$gt_seconds);
	elseif($updays < 1):
		$uptime = sprintf('%d %s %d %s %d %s',$uphours,$gt_hours,$upmins,$gt_minutes,$upsecs,$gt_seconds);
	else:
		$uptime = sprintf('%d %s %d %s %d %s %d %s',$updays,$gt_days,$uphours,$gt_hours,$upmins,$gt_minutes,$upsecs,$gt_seconds);
	endif;
	return htmlspecialchars($uptime);
}
/**
 * Get the system date/time for conversion strings.
 * @return The current uptime as string.
 */
function get_datetime_locale_samples() {
	$retval = [];
	$sphere_array = [
		'D M d H:i:s T Y',    // Thu Feb 25 22:52:29 CET 2016
		'D d M Y H:i:s',      // Thu 25 Feb 2016 22:52:29
		'D d M Y H:i:s T',    // Thu 25 Feb 2016 22:52:29 CET
		'D d M Y H:i:s T O',  // Thu 25 Feb 2016 22:52:29 CET +0100
		'l F d H:i:s T Y',    // Thursday February 25 22:52:29 BST 2016
		'l d F Y H:i:s',      // Thursday 25 February 2016 22:52:29
		'l d F Y H:i:s T',    // Thursday 25 February 2016 22:52:29 BST
		'l d F Y H:i:s T O',  // Thursday 25 February 2016 22:52:29 BST +0100
		'D M d h:i:s A T Y',  // Thu Feb 25 10:52:29 PM PT 2016
		'l F d h:i:s A T Y',  // Thursday February 25 10:52:29 PM BST 2016
		'd.m.Y H:i:s',        // 25.02.2016 22:31:42
		'd/m/Y H:i:s',        // 25/02/2016 22:31:42
		'd/m/Y h:i:s A',      // 25/02/2016 10:31:42 PM
		'm/d/Y H:i:s',        // 02/25/2016 22:31:42
		'm/d/Y h:i:s A',      // 02/25/2016 10:31:42 PM
		'Y-m-d H:i:s'         // 2016-02-02 22:31:42
	];
	$now = shell_exec('date "+%s"');
	foreach($sphere_array as $sphere_record):
		$retval[$sphere_record] = get_datetime_locale($now,$sphere_record);
	endforeach;
	return $retval;
}

function get_datetime_locale(int $timestamp = null,string $format = NULL) {
	global $config;
	$retval = '';
	$timestamp = $timestamp ?? shell_exec('date "+%s"');
	$format = $format ?? $config['system']['datetimeformat'] ?? 'D M d H:i:s T Y';
	$dto = new DateTime();
	$dto->setTimestamp($timestamp);
	$format_symbols = ['D','l','M','F'];
	$replacement_symbols = ['%1','%2','%3','%4'];
	$localised_names = [
		gtext($dto->format($format_symbols[0])),
		gtext($dto->format($format_symbols[1])),
		gtext($dto->format($format_symbols[2])),
		gtext($dto->format($format_symbols[3]))
	];
	$retval = str_replace($replacement_symbols,$localised_names,$dto->format(str_replace($format_symbols,$replacement_symbols,$format))); // Do the whole thing in one go
	return $retval;
}
/**
 *	Get the current RAM information.
 *	@return array Returns an array with memory status information.
 */
function system_get_ram_info() {
	$sphere = [];
	unset($memory);
	exec('/sbin/sysctl -n vm.stats.vm.v_inactive_count vm.stats.vm.v_free_count vm.stats.vm.v_page_count vm.stats.vm.v_page_size hw.physmem hw.realmem',$memory);
	$sphere['pagesize'] = $memory[3];
	$sphere['physical'] = $memory[4];
	$sphere['real'] = $memory[5];
	$sphere['vt'] = $memory[3] * $memory[2];
	$sphere['vf'] = $memory[3] * ($memory[1] + $memory[0]);
	$sphere['vu'] = $sphere['vt'] - $sphere['vf'];
	$sphere['vth'] = format_bytes($sphere['vt'],2,false,false);
	$sphere['vuh'] = format_bytes($sphere['vu'],2,false,false);
	$sphere['vfh'] = format_bytes($sphere['vf'],2,false,false);
	$sphere['pf'] = round($sphere['vf'] * 100 / $sphere['vt'],0);
	$sphere['pu'] = 100 - $sphere['pf'];
	$sphere['pfh'] = sprintf('%u%%',$sphere['pf']);
	$sphere['puh'] = sprintf('%u%%',$sphere['pu']);
	$sphere['tu'] = sprintf('%s',$sphere['puh']);
	$sphere['tf'] = sprintf('%s',$sphere['pfh']);
	$sphere['tt'] = sprintf(gettext('%1$s managed, %2$s used, %3$s available'),$sphere['vth'],$sphere['vuh'],$sphere['vfh']);
	return $sphere;
}
// Get the current swap information.
// Result:
//  Array (
//    [device] => "/dev/ad0s2b"
//    [total] => "20M"
//    [used] => "0B"
//    [avail] => "20M"
//    [capacity] => "0%"
//  )
function system_get_swap_info() {
	$use_si = is_sidisksizevalues();
	exec('/usr/sbin/swapinfo -k',$swap);
	//	blocks
	$val = 512;
	if(preg_match("/(?:Device)(?:\s+)(\d+)(?:K-blocks|-blocks)(?:\s+)(?:Used)(?:\s+)(?:Avail)(?:\s)(?:Capacity)/",$swap[0],$matches)):
		if($matches[1] == 1):
			$val = $matches[1] * 1024;
		else:
			$val = $matches[1];
		endif;
	endif;
	array_shift($swap);
	$swapinfo = [];
	foreach($swap as $swapv):
		if(preg_match("/(\/dev\/.*)(?:\s+)(\d+)(?:\s+)(\d+)(?:\s+)(\d+)(?:\s)(.+)/",$swapv,$matches)):
			$devswap = [];
			$devswap['device'] = trim($matches[1]);
			$devswap['vt'] = $matches[2] * $val;
			$devswap['vu'] = $matches[3] * $val;
			$devswap['vf'] = $matches[4] * $val;
			$devswap['total'] = format_bytes($devswap['vt'],2,false,$use_si);
			$devswap['used'] = format_bytes($devswap['vu'],2,false,$use_si);
			$devswap['avail'] = format_bytes($devswap['vf'],2,false,$use_si);
			$devswap['capacity'] = trim($matches[5]);
			$swapinfo[] = $devswap;
		endif;
	endforeach;
	return $swapinfo;
}

/**
 * Get the system hostname.
 * @return The hostname
 */
function system_get_hostname() {
	return @exec("hostname");
}

/**
 * Get device I/O statistics.
 * @param[in] device	The device name, e.g. ad1
 * @return	An array containing the I/O statistics kpt (kilobytes per transfer),
 *					tps (transfers per second) and mps (megabytes per second). On
 *					failure, FALSE will be returned.
 * @code
 * array(
 *   [kpt] => 11.93
 *   [tps] => 61
 *   [mps] => 0.71)
 * @endcode
 */
function system_get_device_iostat($device) {
	$result = false;
	mwexec2("iostat -d {$device}",$rawdata);
	if(preg_match("/^\s*(\S+)\s*(\S+)\s*(\S+)/",$rawdata[2],$matches)):
		$result = [];
		$result['kpt'] = $matches[1];
		$result['tps'] = $matches[2];
		$result['mps'] = $matches[3];
	endif;
	return $result;
}

/**
 * Get device temperature.
 * @param[in] device	The device name, e.g. ad1
 * @return	The temperature in C if available, otherwise FALSE.
 */
function system_get_device_temp($devicespecialfile) {
	global $config;
	global $config_disks;

	$result = false;
	//	without smart
	$cmd = sprintf("ataidle %s 2>/dev/null | grep 'SMART Enabled'",$devicespecialfile);
	mwexec2($cmd,$rawdata);
	if(!empty($rawdata) && 1 === preg_match("/^SMART Enabled:\s+(\S+)/",$rawdata[0],$matches)):
		if(strcasecmp($matches[1],'no') == 0):
			//	SMART disabled
			return $result;
		endif;
	endif;
	if(empty($_SESSION[$devicespecialfile]['temp']['value']) or time() - $_SESSION[$devicespecialfile]['temp']['timestamp'] > 120):
		$type_arg = '';
		if(!empty($config_disks[$devicespecialfile]['smart']['devicetypearg'])):
			$type_arg = sprintf('-d %s ',$config_disks[$devicespecialfile]['smart']['devicetypearg']);
		endif;
		//	get temperature from S.M.A.R.T.
		$smartd_drivedb_arg = get_smartmontools_drivedb_arg();
		mwexec2('smartctl ' . $smartd_drivedb_arg . '-A ' . $type_arg . escapeshellarg($config_disks[$devicespecialfile]['smart']['devicefilepath']) . " | grep -i 'Temperature'" ,$rawdata);
		foreach($rawdata as $rawdatav):
			$arawdatav = preg_split("/\s+/",$rawdatav);
			if(!empty($arawdatav[1]) && ((0 == strncmp(trim($arawdatav[1]),"Temperature_",12)) || (0 == strncmp(trim($arawdatav[1]),"Airflow_Temperature_",20))) &&
				((0 != strcmp(trim($arawdatav[0]),'190')) || (0 != strcmp(trim($arawdatav[0]),'194')) )):
				$result = rtrim($arawdatav[9]);
				break;
			elseif(preg_match("/Current Drive Temperature:\s+(\d+) C.*/",$rawdatav,$matches)):
				$result = $matches[1];
				break;
			endif;
		endforeach;
		$_SESSION[$devicespecialfile]['temp']['value'] = $result;
		$_SESSION[$devicespecialfile]['temp']['timestamp'] = time();
		return $result;
	else:
		return $_SESSION[$devicespecialfile]['temp']['value'];
	endif;
	return $result;
}

/**
 * Get controller
 * @param[in] device	The device name, e.g. /dev/ad1
 * @return The disk device serial number.
 */
function system_get_controller($devicespecialfile) {
	$devlist = [];
	$device = '';
	$controller = '';
	$controller_id = '';
	$target_id = '';
	$lun_id = '';
	mwexec2("/sbin/camcontrol devlist -v 2> /dev/null",$rawdata);
	foreach($rawdata as $line):
		// Get controller
		if(preg_match("/(?<= on )[-A-z0-9]*(?= bus )+/",$line,$matches)):
			$controller = preg_split( '/([-A-z]+)/',$matches[0],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$device = '';
		elseif(!strncmp($line,'<>' ,2) == 0):
			// Get device name
			// Parse substring between parentheses: (pass0,da0) or (da0,pass0)
			preg_match("/.*\(([^>]*)\).*/",$line,$match);
			$temp = preg_split("/,/",$match[1]);
			// Check whether device name is at the first (da0,pass0) or the second (pass0,da0) arguement
			$device = preg_split( '/([-A-z]+)/',$temp[1],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			if(strncmp($device[0],'pass',4) == 0):
				$device = preg_split( '/([-A-z]+)/',$temp[0],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			endif;
			if(strcmp($devicespecialfile,'/dev/'.$device[0].$device[1]) == 0):
				return $controller[0].$controller[1];
			endif;
		endif;
	endforeach;
	return gtext('n/a');
}

/**
 * Get volume serial number.
 * @param[in] device	The device name, e.g. /dev/ad1
 * @return The disk device serial number.
 */
function system_get_volume_serial($devicespecialfile) {
	global $config;

	// get serial from config
	global $config_disks;
	/* get serial from S.M.A.R.T. */
	$smart_arg = get_smartctl_info($config_disks[$devicespecialfile]);
	if(!empty($smart_arg['serial'])):
		return $smart_arg['serial'];
	endif;
	/* without smart */
	mwexec2("ataidle {$devicespecialfile} 2>/dev/null | grep 'Serial'",$rawdata);
	if(!empty($rawdata) && 1 === preg_match("/^Serial:\s+(.+)$/",$rawdata[0],$matches)):
		$serial = preg_replace("/[\x01-\x1f\x7f]/","",$matches[1]);
		if(!empty($serial)):
			return $serial;
		endif;
	endif;
	/* get serial from CAM */
	mwexec2("camcontrol inquiry {$devicespecialfile} -S 2>/dev/null",$rawdata2);
	if(!empty($rawdata2) && !empty($rawdata2[0])):
		$serial = preg_replace("/[\x01-\x1f\x7f]/","",$rawdata2[0]);
		if(!empty($serial)):
			return $serial;
		endif;
	endif;
	return gtext('n/a');
}

/**
 * Get volume device model.
 * @param[in] device	The device name, e.g. /dev/ad1
 * @return The disk device model.
 */
function system_get_volume_model($devicespecialfile) {
	global $config;

	// get device model from config
	global $config_disks;
	if(!empty($config_disks[$devicespecialfile]['model'])):
		return $config_disks[$devicespecialfile]['model'];
	else:
		/* get device model from S.M.A.R.T. */
		$smart_arg = get_smartctl_info($config_disks[$devicespecialfile]);
		if(!empty($smart_arg['model'])):
			return $smart_arg['model'];
		endif;
	endif;
	/* without smart */
	mwexec2("ataidle {$devicespecialfile} 2>/dev/null | grep 'Model'",$rawdata);
	if(!empty($rawdata) && 1 === preg_match("/^Model:\s+(.+)$/",$rawdata[0],$matches)):
		$model = preg_replace("/[\x01-\x1f\x7f]/","",$matches[1]);
		if(!empty($model)):
			return $model;
		endif;
	endif;
	/* get device model from CAM */
	mwexec2("camcontrol inquiry {$devicespecialfile} -D 2>/dev/null",$rawdata2);
	if(!empty($rawdata2) && 1 === preg_match("/<([^>]+)>/",$rawdata2[0],$matches)):
		$model = preg_replace("/[\x01-\x1f\x7f]/","",$matches[1]);
		if(!empty($model)):
			return $model;
		endif;
	endif;
	return gtext("n/a");
}

/**
 * Get mount point usage information.
 * @return An array containg the requested informations:
 * @code
 * [mountpoint] => [
 *   [mountpoint] => /mnt/xyz
 *   [name] => xyz
 *   [filesystem] => /dev/ad0a
 *   [capacity] => 48%
 *   [used] => 2.4G
 *   [avail] => 2.6G
 *   [size] => 5.4G]
 * @endcode
 */
function system_get_mount_usage() {
	global $config,$g;

	$use_si = is_sidisksizevalues();
	$sphere = [];
	$a_mount = array_make_branch($config,'mounts','mount');
	if(count($a_mount) > 0):
		foreach($a_mount as &$r_mount):
			$r_mount['fqmn'] = sprintf('%s/%s',$g['media_path'],$r_mount['sharename']);
//			$r_mount['fsid'] = '';
		endforeach;
		unset($r_mount);
//		query df with 512B block counts, retrieve json
		$cmd = '/bin/df --libxo:J -P';
		$json_string = shell_exec($cmd);
		$rawdata = json_decode($json_string,true) ?? [];
		if(is_array($rawdata)):
			foreach($rawdata['storage-system-information']['filesystem'] as $r_rawdata):
				foreach($a_mount as $r_mount):
					if(0 == strcmp($r_rawdata['mounted-on'],$r_mount['fqmn'])):
						$row = [];
//						$row['id'] = $r_mount['fsid'];
						$row['mountpoint'] = $r_rawdata['mounted-on'];
						$row['name'] = $r_mount['sharename'];
						$row['filesystem'] = $r_rawdata['name'];
						$row['vt'] = $r_rawdata['total-blocks'] << 9;
						$row['vu'] = $r_rawdata['used-blocks'] << 9;
						$row['vf'] = $r_rawdata['available-blocks'] << 9;
						$row['pu'] = $r_rawdata['used-percent'];
						$row['pf'] = 100 - $row['pu'];
						$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
						$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
						$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
						$row['puh'] = sprintf('%u%%',$row['pu']);
						$row['pfh'] = sprintf('%u%%',$row['pf']);
						$row['tu'] = $row['puh'];
						$row['tf'] = $row['pfh'];
						$row['tt'] = sprintf(gettext('%1$s total, %2$s used, %3$s available'),$row['vth'],$row['vuh'],$row['vfh']);
						$sphere[$r_rawdata['mounted-on']] = $row;
					endif;
				endforeach;
			endforeach;
		endif;
	endif;
	return $sphere;
}
/*
 *	supporting functions for index.php
 */
function get_vip_status() {
	global $config;

	if(empty($config['vinterfaces']['carp'])):
		return '';
	endif;
	$a_vipaddrs = [];
	foreach($config['vinterfaces']['carp'] as $carp):
		$ifinfo = get_carp_info($carp['if']);
		//$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
		$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
	endforeach;
	return implode(', ',$a_vipaddrs);
}
function get_vbox_vminfo($user,$uuid) {
	$vminfo = [];
	unset($rawdata);
	mwexec2("/usr/local/bin/sudo -u {$user} /usr/local/bin/VBoxManage showvminfo --machinereadable {$uuid}",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^=]+)=(\"([^\"]+)\"|[^\"]+)/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = $match[1];
			$a['value'] = isset($match[3]) ? $match[3] : $match[2];
			$vminfo[$a['key']] = $a;
		endif;
	endforeach;
	return $vminfo;
}
function get_xen_info() {
	$info = [];
	unset($rawdata);
	mwexec2("/usr/local/sbin/xl info",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^:]+)\s+:\s+(.+)\s*$/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		endif;
	endforeach;
	return $info;
}
function get_xen_console($domid) {
	$info = [];
	unset($rawdata);
	mwexec2("/usr/local/bin/xenstore-ls /local/domain/{$domid}/console",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^=]+)\s+=\s+\"(.+)\"$/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		endif;
	endforeach;
	return $info;
}
/**
 *	Translates the UPS status code into a human readable string
 *	@param type $ups_status
 *	@return string
 */
function get_ups_disp_status($ups_status) {
	if(empty($ups_status)):
		return '';
	endif;
	$upstodisplay = [
		'WAIT' => gtext('UPS Waiting'),
		'OFF' => gtext('UPS Off Line'),
		'OL' => gtext('UPS On Line'),
		'OB' => gtext('UPS On Battery'),
		'TRIM' => gtext('SmartTrim'),
		'BOOST' => gtext('SmartBoost'),
		'OVER' => gtext('Overload'),
		'LB' => gtext('Battery Low'),
		'RB' => gtext('Replace Battery UPS'),
		'CAL' => gtext('Calibration Battery'),
		'CHRG' => gtext('Charging Battery')
	];
	$status = explode(' ',$ups_status);
	$disp_status = [];
	foreach($status as $condition):
		$disp_status[] = $upstodisplay[$condition] ?? $condition;
	endforeach;
	return implode(', ',$disp_status);
}
/**
 *	Collects UPS status information from UPS 1 and UPS 2
 *	@global array $config
 *	@return array Array with UPS information
 */
function get_upsinfo() {
	global $config;

	array_make_branch($config,'ups');
	$sphere = [];
	foreach([1,2] as $ups_index):
		$row = [];
		$ups_fqn = null;
		switch($ups_index):
			case 1:
				if(isset($config['ups']['enable'])):
					$ups_name = $config['ups']['upsname'];
					$ups_fqn = sprintf('%s@%s',$ups_name,$config['ups']['ip']);
				endif;
				break;
			case 2:
				if(isset($config['ups']['enable']) && isset($config['ups']['ups2'])):
					$ups_name = $config['ups']['ups2_upsname'];
					$ups_fqn = sprintf('%s@%s',$ups_name,$config['ups']['ip']);
				endif;
				break;
		endswitch;
		if(is_null($ups_fqn)):
		else:
			$row['id'] = $ups_index;
			$row['name'] = $ups_name;
			$ups_query = [];
			$cmd = sprintf('/usr/local/bin/upsc %s',escapeshellarg($ups_fqn));
			unset($rawdata);
			exec($cmd,$rawdata);
			foreach($rawdata as $line):
				$line = explode(':',$line);
				$ups_query[$line[0]] = trim($line[1]);
			endforeach;
//			collect load status
			if(array_key_exists('ups.load',$ups_query) && !empty($ups_query['ups.load'])):
				$load_pct = $ups_query['ups.load'];
			else:
				$load_pct = 0;
			endif;
			$row_load = [];
			$row_load['vt'] = 100;
			$row_load['vu'] = $load_pct;
			$row_load['vf'] = 100 - $row_load['vu'];
			$row_load['vth'] = sprintf('%u%%',$row_load['vt']);
			$row_load['vuh'] = sprintf('%u%%',$row_load['vu']);
			$row_load['vfh'] = sprintf('%u%%',$row_load['vf']);
			$row_load['pu'] = round($row_load['vu'] * 100 / $row_load['vt'],0);;
			$row_load['pf'] = 100 - $row_load['pu'];
			$row_load['puh'] = sprintf('%u%%',$row_load['pu']);
			$row_load['pfh'] = sprintf('%u%%',$row_load['pf']);
			$row_load['tu'] = sprintf('%s',$row_load['puh']);
			$row_load['tf'] = sprintf('%s',$row_load['pfh']);
			$row_load['tt'] = sprintf(gettext('%2$s used, %3$s available'),$row_load['vth'],$row_load['vuh'],$row_load['vfh']);
			$row['load'] = $row_load;
			unset($row_load);
//			collect battery status
			if(array_key_exists('battery.charge',$ups_query) && !empty($ups_query['battery.charge'])):
				$battery_charge_pct = $ups_query['battery.charge'];
			else:
				$battery_charge_pct = 0;
			endif;
			$row_battery = [];
			$row_battery['vt'] = 100;
			$row_battery['vu'] = $battery_charge_pct;
			$row_battery['vf'] = $row_battery['vt'] - $row_battery['vu'];
			$row_battery['vth'] = sprintf('%u%%',$row_battery['vt']);
			$row_battery['vuh'] = sprintf('%u%%',$row_battery['vu']);
			$row_battery['vfh'] = sprintf('%u%%',$row_battery['vf']);
			$row_battery['pu'] = round($row_battery['vu'] * 100 / $row_battery['vt'],0);
			$row_battery['pf'] = 100 - $row_battery['pu'];
			$row_battery['puh'] = sprintf('%u%%',$row_battery['pu']);
			$row_battery['pfh'] = sprintf('%u%%',$row_battery['pf']);
			$row_battery['tu'] = sprintf('%s',$row_battery['puh']);
			$row_battery['tf'] = sprintf('%s',$row_battery['pfh']);
			$row_battery['tt'] = sprintf(gettext('%2$s used, %3$s available'),$row_battery['vth'],$row_battery['vuh'],$row_battery['vfh']);
			$row['battery'] = $row_battery;
			unset($row_battery);
//			collect UPS status
			if(array_key_exists('ups.status',$ups_query)):
				$disp_status = get_ups_disp_status($ups_query['ups.status']);
			else:
				$disp_status = gtext('Communication Error');
			endif;
			$row['disp_status'] = $disp_status;
			$sphere[] = $row;
			unset($row);
		endif;
	endforeach;
	return $sphere;
}
/**
 *	Collect disk usage information
 *	@return array Array with disk usage information
 */
function get_disk_usage() {
	$sphere = [];
	$a_diskinfo = system_get_mount_usage();
	ksort($a_diskinfo);
	foreach($a_diskinfo as $k_diskinfo => $r_diskinfo):
		$id = get_mount_fsid($r_diskinfo['filesystem'],$k_diskinfo);
		$row = $r_diskinfo;
		$row['id'] = $id;
		$sphere[] = $row;
		unset($row);
	endforeach;
	return $sphere;
}
/**
 *	Collect pool usage information
 *	@global array $config
 *	@return array Array containing pool usage information
 */
function get_pool_usage() {
	global $config;

	$sphere = [];
	$use_si = is_sidisksizevalues();
	$a_poolinfo = zfs_get_pool_list();
	if(!empty($a_poolinfo)):
		foreach($a_poolinfo as $r_poolinfo):
			$row = [];
			$row['id'] = $r_poolinfo['guid'];
			$row['name'] = $r_poolinfo['name'];
			$row['pu'] = $r_poolinfo['cap'];
			$row['pf'] = 100 - $row['pu'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			if(isset($config['zfs']['settings']['showusedavail'])):
				$row['vu'] = $r_poolinfo['used'];
				$row['vf'] = $r_poolinfo['avail'];
				$row['vt'] = $row['vu'] + $row['vf'];
				$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
				$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
				$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
				$row['tt'] = sprintf(gettext('%1$s total, %2$s used, %3$s available'),$row['vth'],$row['vuh'],$row['vfh']);
			else: // this is the default setting
				$row['vu'] = $r_poolinfo['alloc'];
				$row['vf'] = $r_poolinfo['free'];
				$row['vt'] = $r_poolinfo['size'];
				$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
				$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
				$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
				$row['tt'] = sprintf(gettext('%1$s total, %2$s allocated, %3$s free'),$row['vth'],$row['vuh'],$row['vfh']);
			endif;
			$row['health'] = $r_poolinfo['health'];
			$sphere[] = $row;
			unset($row);
		endforeach;
		array_sort_key($sphere,'name');
	endif;
	return $sphere;
}
/**
 *	Collect swap usage information
 *	@return array Array containing swap usage information
 */
function get_swap_usage() {
	$sphere = [];
	$a_swapinfo= system_get_swap_info();
	if(!empty($a_swapinfo)):
		$id = 0;
		foreach($a_swapinfo as $r_swapinfo):
			$row = [];
			$row['id'] = $id++;
			$row['name'] = $r_swapinfo['device'];
			$row['vt'] = $r_swapinfo['vt'];
			$row['vu'] = $r_swapinfo['vu'];
			$row['vf'] = $r_swapinfo['vf'];
			$row['pu'] = round($row['vu'] * 100 / $row['vt'],0);
			$row['pf'] = 100 - $row['pu'];
			$row['vth'] = $r_swapinfo['total'];
			$row['vuh'] = $r_swapinfo['used'];
			$row['vfh'] = $r_swapinfo['avail'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			$row['tt'] = sprintf(gettext('%1$s total, %2$s used, %3$s available'),$row['vth'],$row['vuh'],$row['vfh']);
			$sphere[] = $row;
			unset($row);
		endforeach;
		array_sort_key($sphere,'name');
	endif;
	return $sphere;
}
/**
 *	Collect various system information.
 *	@return array Array containing system information.
 */
function system_get_sysinfo() {
	$sysinfo = [];
	$sysinfo['vipstatus'] = get_vip_status();
	$sysinfo['hostname'] = system_get_hostname();
	$sysinfo['date'] = get_datetime_locale();
	$sysinfo['uptime'] = system_get_uptime();
	if(Session::isAdmin()):
//		get number of CPU's
		$sysinfo['cpus'] = system_get_cpus();
//		get RAM usage
		$sysinfo['memusage'] = system_get_ram_info();
//		get load average
		$sysinfo['loadaverage'] = implode(' ',array_map(function($e) { return sprintf('%.2f',$e); },sys_getloadavg()));
//		get up-to-date CPU informations.
		$cpuinfo = system_get_cpu_info();
		$sysinfo['cpumodel'] = $cpuinfo['model'];
		$sysinfo['cpufreq'] = $cpuinfo['freq'];
//		get CPU temperatures
		$cpu_t = $cpuinfo['temperature'];
		if(empty($cpuinfo['temperature2'])):
			$a_cpu_t = $sysinfo['cputemp2'] = [$cpuinfo['temperature']];
		else:
			$a_cpu_t = $sysinfo['cputemp2'] = $cpuinfo['temperature2'];
		endif;
		$row = [];
		$row['vu'] = $cpu_t;
		$row['vuh'] = sprintf('%u°C',$row['vu']);
		$row['tu'] = $row['vuh'];
		$sysinfo['cputemp'] = $row;
		unset($row);
		$sysinfo['cputemp2'] = [];
		foreach($a_cpu_t as $r_cpu_t):
			$row = [];
			$row['vu'] = $r_cpu_t;
			$row['vuh'] = sprintf('%u°C',$row['vu']);
			$row['tu'] = $row['vuh'];
			$sysinfo['cputemp2'][] = $row;
			unset($row);
		endforeach;
//		get CPU usage
		if($sysinfo['cpus'] > 1):
			$a_cpu_u = system_get_smp_cpu_usage();
			$cpu_u = floor(array_sum($a_cpu_u) / $sysinfo['cpus']);
		else:
			$cpu_u = system_get_cpu_usage();
			$a_cpu_u = [$cpu_u];
		endif;
		$row = [];
		$row['pu'] = $cpu_u;
		$row['pf'] = 100 - $row['pu'];
		$row['puh'] = sprintf('%u%%',$row['pu']);
		$row['pfh'] = sprintf('%u%%',$row['pf']);
		$row['tu'] = $row['puh'];
		$row['tf'] = $row['pfh'];
		$row['tt'] = $row['puh'];
		$sysinfo['cpuusage'] = $row;
		unset($row);
		$sysinfo['cpuusage2'] = [];
		foreach($a_cpu_u as $r_cpu_u):
			$row = [];
			$row['pu'] = $r_cpu_u;
			$row['pf'] = 100 - $row['pu'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			$row['tt'] = $row['tu'];
			$sysinfo['cpuusage2'][] = $row;
			unset($row);
		endforeach;
//		get storage usage
		$sysinfo['diskusage'] = get_disk_usage();
		$sysinfo['poolusage'] = get_pool_usage();
		$sysinfo['swapusage'] = get_swap_usage();
//		get UPS status
		$sysinfo['upsinfo'] = get_upsinfo();
	endif;
	return $sysinfo;
}
