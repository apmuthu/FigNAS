<?php
/*
	tui.inc

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Copyright (c) 2006-2008 Volker Theile (votdev@gmx.de)
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
require_once 'util.inc';
require_once 'co_geom_info.inc';

// Display a yes|no dialog.
// $text - The dialog text
// $width - Width of the dialog
// $height - Height of the dialog
// Returns 0 if yes, 1 if no or 255 if aborted via ESC.
function tui_display_yesno($text, $width, $height, $defaultyes = true) {
	$param = "";
	if (false === $defaultyes) $param = "--defaultno";
	passthru("/usr/local/bin/cdialog --clear {$param} --yesno \"{$text}\" {$height} {$width}", $retval);
	return $retval;
}

// Display a message box.
// $title - The dialog title
// $text - The dialog text
// $width - Width of the dialog
// $height - Height of the dialog
// Returns 0 if ok has been pressed or 255 if aborted via ESC.
function tui_display_message($title, $text, $width, $height) {
	passthru("/usr/local/bin/cdialog --clear --title \"{$title}\" --msgbox \"{$text}\" {$height} {$width}", $retval);
	return $retval;
}

// Display a message box.
// $title - The dialog title
// $text - The dialog text
// $width - Width of the dialog
// $height - Height of the dialog
// Returns 0 if OK, 1 if Cancel or 255 if aborted via ESC.
function tui_display_message_ex($title, $text, $width, $height) {
	passthru("/usr/local/bin/cdialog --clear --yes-label \"OK\" --no-label \"Cancel\" --title \"{$title}\" --yesno \"{$text}\" {$height} {$width}", $retval);
	return $retval;
}

// Display a input box.
// $text - The dialog text
// $width - Width of the dialog
// $height - Height of the dialog
// $value [in|out]- Initial value. Also contains result.
// Returns 0 if ok has been pressed or 255 if aborted via ESC.
function tui_display_input($text, $width, $height, &$value) {
	$tmpfile = "/tmp/tui" . rand();
	passthru("/usr/local/bin/cdialog --input-fd 2 --output-fd 1 --clear --inputbox \"{$text}\" {$height} {$width} \"{$value}\" 1>{$tmpfile}", $retval);
	$value = @file_get_contents($tmpfile);
	@unlink($tmpfile);
	return $retval;
}

// Display a menu.
// $title - The dialog title
// $menutitle - The menu title
// $width - Width of the dialog
// $height - Height of the dialog
// $menuheight - Height of the menu
// $amenuitem - The menu item array => Array
// (
// 	[tag]  => "x"
// 	[item] => "xy zxy yxz"
// )
// $value [out] - Contains the selected item tag.
// Returns 0 if ok has been pressed or 255 if aborted via ESC.
function tui_display_menu($title, $menutitle, $width, $height, $menuheight, $amenuitem, &$value) {
	$tmpfile = "/tmp/tui" . rand();
	$cmd = "/usr/local/bin/cdialog --input-fd 2 --output-fd 1 --clear --title \"{$title}\" --menu \"{$menutitle}\" {$height} {$width} {$menuheight}";
	foreach($amenuitem as $menuitemv) {
		$cmd .= " \"{$menuitemv['tag']}\" \"{$menuitemv['item']}\"";
	}
	$cmd .= " 1>{$tmpfile}";
	passthru($cmd, $retval);
	$value = @file_get_contents($tmpfile);
	@unlink($tmpfile);
	return $retval;
}

// Wait until user has pressed 'RETURN'.
function tui_wait_keypress($message = "") {
	if (!empty($message))
		write_console("$message\n");
	write_console("\nPress ENTER to continue.");
	$fp = fopen('php://stdin', 'r');
	fgets($fp);
	fclose($fp);
}
/**
 *	Undo changes of a list of device names.
 *	@param array $device_list
 *	@return boolean
 */
function geom_part_undo(array $device_list = []) {
	$return_value = true;
	$param = [
		'run' => '/sbin/gpart',
		'command' => 'undo',
		'geom' => ''
	];
	foreach(array_reverse($device_list) as $devicename):
		$param['geom'] = escapeshellarg($devicename);
		$command = implode(' ',$param);
		if(mwexec($command)):
			$return_value = false;
		endif;
	endforeach;
	return $return_value;
}
/**
 *	Commits changes of a list of device names.
 *	@param array $device_list
 *	@return boolean
 */
function geom_part_commit(array $device_list) {
	$return_value = true;
	$param = [
		'run' => '/sbin/gpart',
		'command' => 'commit',
		'geom' => ''
	];
	foreach($device_list as $devicename):
		$param['geom'] = escapeshellarg($devicename);
		$command = implode(' ',$param);
		if(mwexec($command)):
			$return_value = false;
		endif;
	endforeach;
	return $return_value;
}
/**
 *	TUI dialog for flexible partitions
 *	The partition layout is an array with partition records:
 *
 *	'enable'        => true           // Process partition
 *	'partition'     => 4,             // Partition Number
 *	'volname'       => 'data',        // Volume Name
 *	'fstype'        => 'freebsd-ufs', // File System Type
 *	'align'         => '1m',          // Alignment
 *	'size'          => '2g',          // Size
 *	'minimum'       => '1g',          // Minimum Size
 *	'maximum'       => '16g'          // Maximum Size
 *	'allow_edit'    => true,          // Is editing allowed?
 *	'allow_star'    => true,          // Allowed to enter *?
 *	'friendly_name' => 'DATA',        // Displayed Name
 *
 *	@param string $destination The name of the device to install the partition scheme
 *	@param array $partlayout The preconfigured partition table
 *	@param array $base_settings GPT/MBR scheme information
 *	@return bool false on failure
 */
function tui_partition_layout(string $destination,array $partlayout,array $base_settings = []) {
	$test_mode = '-f x';
	$param_geom_part = ['run' => '/sbin/gpart'];
	$uncommitted_device_names = [];
	$base = [];
//	geom object to gather information
	$geom = new co_geom_info();
	$diskinfo = $geom->gather_provider_by_name($destination);
	if(empty($diskinfo)):
		$message = 'The selected device is invalid.';
		write_console($message . PHP_EOL);
		tui_wait_keypress($message);
		return false;
	endif;
//	do the math for sectorsize
	$base['sectorsize'] = [
		'shift' => 0,
		'bytes' => (int)$diskinfo[$destination]->sectorsize
	];
//	gather mediasize
	$base['mediasize'] = [
		'bytes' => (int)$diskinfo[$destination]->mediasize
	];
	unset($diskinfo);
	if(0 === $base['sectorsize']['bytes']):
		$message = 'The destination device reported a sector size of 0.';
		write_console($message . PHP_EOL);
		tui_wait_keypress($message);
		return false;
	endif;
//	calculate shift
	$test = $base['sectorsize']['bytes'];
	while($test > 1):
		$base['sectorsize']['shift']++;
		$test >>= 1;
	endwhile;
//	prepare scheme information
	$base['scheme'] = strtolower(filter_var($base_settings['scheme'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'gpt','regexp' => '/^(gpt|mbr)$/i']]));
	switch($base['scheme']):
		case 'gpt':
			$base['entries'] = filter_var($base_settings['entries'],FILTER_VALIDATE_INT,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '128','min_range' => 128,'max_range' => 4096]]);
			break;
		case 'mbr':
			$base['entries'] = 4;
			$message = 'MBR partitioning scheme is not supported by this function.';
			write_console($message . PHP_EOL);
			tui_display_message('Error',$message,60,5);
			return false;
			break;
	endswitch;
//	initialize layout
	$partition_layout = [];
	foreach($partlayout as $key => $row):
		if(is_bool($test = $row['enabled'] ?? false) ? $test : false):
			$partition = [
				'partition' => $row['partition'],
				'fstype' => is_string($test = $row['fstype'] ?? NULL) ? $test : NULL,
				'volname' => is_string($test = $row['volname'] ?? NULL) ? $test : NULL,
				'friendly_name' => is_string($test = $row['friendly_name'] ?? 'unknown') ? $test : 'unknown',
				'allow_edit' => is_bool($test = $row['allow_edit'] ?? false) ? $test : false,
				'allow_star' => is_bool($test = $row['allow_star'] ?? false) ? $test : false,
				'alignment' => NULL,
				'minimum' => NULL,
				'maximum' => NULL,
				'size' => NULL,
				'is_last' => false
			];
/*
 *			parameter align can be
 *			- empty,zero or missing
 *			- one of the following numbers: 1,2,4,8,16,32,64,128,256 or 512
 *			- one of the above numbers followed by a case insensitive SI prefix: k,m,g,t,p,e,z,y
 */
			$details = [];
			$regexp = '/^(128|16|1|256|2|32|4|512|64|8)([kmgtpezy]?)$/i';
			$details['size'] = filter_var($row['align'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $regexp]]);
			if(isset($details['size'])):
				unset($matches);
//				$regexp = '/^(128|16|1|256|2|32|4|512|64|8)([kmgtpezy]?)$/i';
				preg_match($regexp,$details['size'],$matches);
				$details['value'] = $matches[1];
				$details['uom'] = strtoupper($matches[2]);
				unset($matches);
				$details['shift'] = 0;
				//	set shift from SI prefix
				if(!empty($details['uom'])):
					if(false !== ($test = strpos('_KMGTPEZY',$details['uom']))):
						$details['shift'] = 10 * $test;
					endif;
				endif;
				//	add shift from value
				$test = $details['value'];
				while($test > 1):
					$details['shift']++;
					$test >>= 1;
				endwhile;
				$details['bytes'] = 1 << $details['shift'];
//				alignment must be greater or equal sectorsize
				if($details['bytes'] < $base['sectorsize']['bytes']):
					$details['bytes'] = $base['sectorsize']['bytes'];
					$details['shift'] = $base['sectorsize']['shift'];
				endif;
				$details['aligned'] = $details['bytes'];
				$partition['alignment'] = $details;
			endif;
			unset($details,$regexp);
/*
 *			parameter minimum, maximum, size can be
 *			- invalid or missing
 *			- an integer greater than zero
 *			- an integer greater than zero followed by a case insensitive SI prefix: k,m,g,t,p,e,z,y.
 */
			$regexp = '/^([1-9][0-9]*)([kmgtpezy]?)$/i';
			foreach(['minimum','maximum','size'] as $parameter):
				$details = [];
				$details['size'] = filter_var($row[$parameter],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $regexp]]);
				if(isset($details['size'])):
					unset($matches);
					preg_match($regexp,$details['size'],$matches);
					$details['value'] = $matches[1];
					$details['uom'] = strtoupper($matches[2]);
					$details['uomshift'] = 0;
					if(!empty($details['uom'])):
						// set power of 2 of SI prefix
						if(false !== ($test = strpos('_KMGTPEZY',$details['uom']))):
							$details['uomshift'] = 10 * $test;
						endif;
					endif;
					$details['bytes'] = $details['value'] << $details['uomshift'];
					$mask = isset($partition['alignment']) ? $partition['alignment']['bytes'] - 1 : $base['sectorsize']['bytes'] - 1;
					$details['aligned'] = ~$mask & ($details['bytes'] + $mask);
					$partition[$parameter] = $details;
				endif;
				unset($details);
			endforeach;
			unset($regexp);
			if($partition['allow_edit']):
			elseif(isset($partition['size'])):
				$partition['minimum'] = $partition['size'];
			endif;
			$partition_layout[$key] = $partition;
			unset($partition);
		endif;
	endforeach;
	if(isset($key)):
		$partition_layout[$key]['is_last'] = true;
	endif;
//	switch off all swap partitions on the destination device, ignore errors
	$command = '/sbin/swapctl -hl';
	unset($output);
	mwexec2($command,$output);
	foreach($output as $row):
		unset($matches);
		if(preg_match(sprintf('~^(/dev/%s[a-z]\S*)~',$destination),$row,$matches)):
			$command = sprintf('/sbin/swapoff %s',$matches[1]);
			mwexec($command);
		endif;
	endforeach;
	unset($matches,$row,$output);
//	shields down
	set_kern_geom_debugflags(0x10);
//	force delete existing partitions (ignore errors)
	$partition_info = $geom->gather_part_by_name($destination);
	if(false !== $partition_info):
		$current_scheme = (string)$partition_info[$destination]->config->scheme;
	else:
		$current_scheme = '';
	endif;
	switch($current_scheme):
		case 'GPT':
//			nothing to do
			break;
		case 'MBR':
//			scan for slices and undo pending commits
			$reported_slices = [];
			foreach($partition_info[$destination]->provider as $r_partition_info):
				$reported_slices[] = (string)$r_partition_info->name;
			endforeach;
			geom_part_undo($reported_slices);
			unset($reported_slices,$r_partition_info);
			break;
		default:
//			wipe using dd
//			count = mbr (lba 0) + gpt header (lba 1) + sectors for gpt-entries (max 4096, 128 bytes per entry)
			$count = 2 + ((4096 * 128) >> $base['sectorsize']['shift']);
			$param = [
				'run' => '/bin/dd',
				'if' => 'if=/dev/zero',
				'of' => sprintf('of=/dev/%s',$destination),
				'bs' => sprintf('bs=%s',$base['sectorsize']['bytes']),
				'count' => sprintf('count=%s',$count)
			];
			$command = implode(' ',$param);
			$message = sprintf('Wiping first %d sectors on %s.',$count,$destination);
			write_console($message . PHP_EOL);
			mwexec($command);
			$param['seek'] = sprintf('seek=%d',($base['mediasize']['bytes'] >> $base['sectorsize']['shift']) - $count);
			$command = implode(' ',$param);
			$message = sprintf('Wiping last %d sectors on %s.',$count,$destination);
			write_console($message . PHP_EOL);
			mwexec($command);
			break;
	endswitch;
	unset($current_scheme,$partition_info);
	$param = $param_geom_part;
	$param['command'] = 'destroy';
	$param['testmode'] = $test_mode;
	$param['force'] = '-F';
	$param['geom'] = escapeshellarg($destination);
	$uncommitted_device_names[$destination] = $destination;
	$command = implode(' ',$param);
	mwexec($command);
	switch($base['scheme']):
		case 'gpt':
//			create gpt scheme
			$param = $param_geom_part;
			$param['command'] = 'create';
			$param['testmode'] = $test_mode;
			$param['scheme'] = '-s gpt';
			$param['entries'] = sprintf('-n %s',$base['entries']);
			$param['geom'] = escapeshellarg($destination);
			$command = implode(' ',$param);
			if(mwexec($command)):
				geom_part_undo($uncommitted_device_names);
				set_kern_geom_debugflags(0);
				$message = sprintf('Failed to create GPT partitioning scheme on %s.',$destination);
				write_console($message . PHP_EOL);
				tui_display_message('Error',$message,60,5);
				return false;
			endif;
//			install pmbr
			$param = $param_geom_part;
			$param['command'] = 'bootcode';
			$param['testmode'] = $test_mode;
			$param['pmbr'] = '-b /boot/pmbr';
			$param['geom'] = escapeshellarg($destination);
			$command = implode(' ',$param);
			if(mwexec($command)):
				geom_part_undo($uncommitted_device_names);
				set_kern_geom_debugflags(0);
				$message = sprintf('Failed to install PMBR on %s.',$destination);
				write_console($message . PHP_EOL);
				tui_display_message('Error',$message,60,5);
				return false;
			endif;
			break;
/*
		case 'mbr':
//			create mbr scheme
			$param = $param_geom_part;
			$param['command'] = 'create';
			$param['testmode'] = $test_mode;
			$param['scheme'] = '-s mbr';
			$param['entries'] = sprintf('-n %s',$base['entries']);
			$param['geom'] = escapeshellarg($destination);
			$command = implode(' ',$param);
			if(mwexec($command)):
				geom_part_undo($uncommitted_device_names);
				set_kern_geom_debugflags(0);
				$message = sprintf('Failed to create MBR partitioning scheme on %s.',$destination);
				write_console($message . PHP_EOL);
				tui_display_message('Error',$message,60,5);
				return false;
			endif;
//			install bootcode
			$param = $param_geom_part;
			$param['command'] = 'bootcode';
			$param['testmode'] = $test_mode;
			$param['mbr'] = '-b /boot/mbr';
			$param['geom'] = escapeshellarg($destination);
			$command = implode(' ',$param);
			if(mwexec($command)):
				geom_part_undo($uncommitted_device_names);
				set_kern_geom_debugflags(0);
				$message = sprintf('Failed to install MBR bootcode on %s.',$destination);
				write_console($message . PHP_EOL);
				tui_display_message('Error',$message,60,5);
				return false;
			endif;
			break;
 */
	endswitch;		
	$partition_layout_index = 0;
	foreach($partition_layout as $k_partition => &$r_partition):
//		gather first and last sector from geom
		$geom->scan();
		$partition_info = $geom->gather_part_by_name($destination);
		$first_available_sector = (int)$partition_info[$destination]->config->first;
		$last_available_sector = (int)$partition_info[$destination]->config->last;
//		walk through reported partitions and find the highest end-sector
		foreach($partition_info[$destination]->provider as $r_partition_info):
			$first_available_sector = max($first_available_sector,1 + (int)$r_partition_info->config->end);
		endforeach;
		unset($partition_info);
		$virtual_first = $first_available_sector;
//		calculate minimum required sectors
		foreach(array_slice($partition_layout,$partition_layout_index) as $r_slice):
//			align start sector of the partition
			if(isset($r_slice['alignment'])):
				$mask = $r_slice['alignment']['bytes'] - 1;
				$virtual_first = (~$mask & (($virtual_first << $base['sectorsize']['shift']) + $mask)) >> $base['sectorsize']['shift'];
			endif;
//			add required sectors of aligned minimum partition size
			if(isset($r_slice['minimum'])):
				$virtual_first += $r_slice['minimum']['aligned'] >> $base['sectorsize']['shift'];
			endif;
		endforeach;
//		exit when all space is allocated
		if($virtual_first > ($last_available_sector + 1)):
			geom_part_undo($uncommitted_device_names);
			set_kern_geom_debugflags(0);
			$message = sprintf('Not enough space available on device %s.',$destination);
			write_console($message . PHP_EOL);
			tui_display_message('Error',$message,60,5);
			return false;
		endif;
//		determine minimum and maximum bytes
		if(isset($r_partition['minimum'])):
			$minimum_bytes = $r_partition['minimum']['bytes'];
			$maximum_bytes = $r_partition['minimum']['aligned'];
			$maximum_bytes += ($last_available_sector - $virtual_first + 1) << $base['sectorsize']['shift'];
		else:
			$minimum_bytes = 0;
			$maximum_bytes = ($last_available_sector - $virtual_first + 1) << $base['sectorsize']['shift'];
		endif;
		if($r_partition['allow_edit']):
//			prepare info string
			$input_text = sprintf('Enter the size of the %s partition',$r_partition['friendly_name']);
			if($r_partition['is_last'] && $r_partition['allow_star']):
				$input_text .= ' or enter \'ALL\' to take what\'s available';
			endif;
			$boundaries = [];
			if(isset($r_partition['minimum']['size'])):
				$boundaries[] = sprintf('minimum: %s',$r_partition['minimum']['size'] ?? 'n/a');
			endif;
			if(isset($r_partition['size']['size'])):
				$boundaries[] = sprintf('recommended: %s',$r_partition['size']['size'] ?? 'n/a');
			endif;
			if(isset($r_partition['maximum']['size'])):
				$boundaries[] = sprintf('maximum: %s',$r_partition['maximum']['size'] ?? 'n/a');
			endif;
			if(!empty($boundaries)):
				$input_text .= PHP_EOL . '(' . implode(', ',$boundaries) . ')';
			endif;
//			user input
			$nocigar = true;
			$entered_size = $r_partition['size']['size'];
			unset($error_message);
			while($nocigar):
				if(isset($error_message)):
					$enhanced_input_text =  $error_message . PHP_EOL . PHP_EOL . $input_text;
					unset($error_message);
				else:
					$enhanced_input_text = $input_text;
				endif;
				$result = tui_display_input($enhanced_input_text,60,11,$entered_size);
				if(0 !== $result):
					geom_part_undo($uncommitted_device_names);
					set_kern_geom_debugflags(0);
					return false;
				endif;
				$details = [];
				$regexp = '/^([1-9][0-9]*)([kmgtpezy]?)$/i';
				$details['size'] = filter_var($entered_size,FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => $regexp]]);
				if(isset($details['size'])):
					unset($matches);
					preg_match($regexp,$details['size'],$matches);
					$details['value'] = $matches[1];
					$details['uom'] = strtoupper($matches[2]);
					$details['uomshift'] = 0;
					if(!empty($details['uom'])):
//						set power of 2 of SI prefix
						if(false !== ($test = strpos('_KMGTPEZY',$details['uom']))):
							$details['uomshift'] = 10 * $test;
						endif;
					endif;
					$details['bytes'] = $details['value'] << $details['uomshift'];
					if(($details['bytes'] >= $minimum_bytes) && ($details['bytes'] <= $maximum_bytes)):
						$r_partition['size']['size'] = $entered_size;
						$nocigar = false;
					elseif($details['bytes'] < $minimum_bytes):
						$error_message = 'Error: The entered value is too low.';
					elseif($details['bytes'] > $maximum_bytes):
						$error_message = 'Error: The entered value exceeds what\'s available.';
					endif;
				elseif($r_partition['is_last'] && $r_partition['allow_star'] && is_string($entered_size) && in_array(strtolower($entered_size),['*','all','max'])):
					$r_partition['size']['size'] = '*';
					$nocigar = false;
				elseif('' === $entered_size):
					$error_message = 'Error: No value was entered.';
				else:
					$error_message = 'Error: The entered value is invalid.';
				endif;
			endwhile;
			unset($input_text);
		endif;
		switch($base['scheme']):
			case 'gpt':
				$partition_index = sprintf('-i %s',$r_partition['partition']);
				$param = $param_geom_part;
				$param['command'] = 'add';
				$param['testmode'] = $test_mode;
				$param['type'] = '';
				if(isset($r_partition['volname'])):
					$param['label'] = sprintf('-l %s',$r_partition['volname']);
				endif;
				$param['index'] = $partition_index;
				if(isset($r_partition['alignment'])):
					$param['align'] = sprintf('-a %s',$r_partition['alignment']['size']);
				endif;
				if(isset($r_partition['size']) && ($r_partition['size']['size'] !== '*')):
					$param['size'] = sprintf('-s %s',$r_partition['size']['size']);
				endif;
				$param['geom'] = escapeshellarg($destination);
				switch($k_partition):
					case 'boot':
					case 'zfsboot':	//	add boot partition
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-boot');
						break;
					case 'efi':	//	add efi partition
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'efi');
						break;
					case 'root':	//	add root partition
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-ufs');
						break;
					case 'swap':	//	add swap partition
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-swap');
						break;
					case 'zfsroot':	//	add root partition
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-zfs');
						break;
					default:	//	add other partitions
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-ufs');
						break;
				endswitch;
				$command = implode(' ',$param);
				if(mwexec($command)):
					geom_part_undo($uncommitted_device_names);
					set_kern_geom_debugflags(0);
					$message = sprintf('Failed to add %s partition on %s.',$r_partition['friendly_name'],$destination);
					write_console($message . PHP_EOL);
					tui_display_message('Error',$message,60,5);
					return false;
				endif;
				switch($k_partition):
					case 'boot':
//						install gpt boot code
						$param = $param_geom_part;
						$param['command'] = 'bootcode';
						$param['testmode'] = $test_mode;
						$param['boot'] = '-p /boot/gptboot';
						$param['index'] = $partition_index;
						$param['geom'] = escapeshellarg($destination);
						$command = implode(' ',$param);
						if(mwexec($command)):
							geom_part_undo($uncommitted_device_names);
							set_kern_geom_debugflags(0);
							$message = sprintf('Failed to install GPT bootcode on %s.',$destination);
							write_console($message . PHP_EOL);
							tui_display_message('Error',$message,60,5);
							return false;
						endif;
						break;
					case 'zfsboot':
//						install gpt zfs boot code
						$param = $param_geom_part;
						$param['command'] = 'bootcode';
						$param['testmode'] = $test_mode;
						$param['boot'] = '-p /boot/gptzfsboot';
						$param['index'] = $partition_index;
						$param['geom'] = escapeshellarg($destination);
						$command = implode(' ',$param);
						if(mwexec($command)):
							geom_part_undo($uncommitted_device_names);
							set_kern_geom_debugflags(0);
							$message = sprintf('Failed to install GPT ZFS bootcode on %s.',$destination);
							write_console($message . PHP_EOL);
							tui_display_message('Error',$message,60,5);
							return false;
						endif;
						break;
					case 'efi':
//						install efi boot code
						$param = $param_geom_part;
						$param['command'] = 'bootcode';
						$param['testmode'] = $test_mode;
						$param['boot'] = '-p /boot/boot1.efifat';
						$param['index'] = $partition_index;
						$param['geom'] = escapeshellarg($destination);
						$command = implode(' ',$param);
						if(mwexec($command)):
							geom_part_undo($uncommitted_device_names);
							set_kern_geom_debugflags(0);
							$message = sprintf('Failed to install EFI bootcode on partition %sp%s.',$destination,$partition_index);
							write_console($message . PHP_EOL);
							tui_display_message('Error',$message,60,5);
							return false;
						endif;
						break;
				endswitch;
				break;
/*
			case 'mbr':
				$partition_index = sprintf('-i %s',$r_partition['partition']);
				$destination_slice = sprintf('%ss%s',$destination,$r_partition['partition']);
				$param = $param_geom_part;
				$param['command'] = 'add';
				$param['testmode'] = $test_mode;
				$param['type'] = '-t freebsd';
				$param['index'] = $partition_index;
				if(isset($r_partition['alignment'])):
					$param['align'] = sprintf('-a %s',$r_partition['alignment']['size']);
				endif;
				if(isset($r_partition['size']) && ($r_partition['size']['size'] !== '*')):
					$param['size'] = sprintf('-s %s',$r_partition['size']['size']);
				endif;
				$param['geom'] = escapeshellarg($destination);
				$command = implode(' ',$param);
				if(mwexec($command)):
					geom_part_undo($uncommitted_device_names);
					set_kern_geom_debugflags(0);
					$message = sprintf('Failed to add %s partition on %s.',$r_partition['friendly_name'],$destination);
					write_console($message . PHP_EOL);
					tui_display_message('Error',$message,60,5);
					return false;
				endif;
//				destroy any zombies
				$param = $param_geom_part;
				$param['command'] = 'destroy';
				$param['testmode'] = $test_mode;
				$param['force'] = '-F';
				$param['geom'] = escapeshellarg($destination_slice);
				$uncommitted_device_names[$destination_slice] = $destination_slice;
				$command = implode(' ',$param);
				mwexec($command);
//				Create BSD scheme on slice
				$param = $param_geom_part;
				$param['command'] = 'create';
				$param['testmode'] = $test_mode;
				$param['scheme'] = '-s BSD';
				$param['geom'] = escapeshellarg($destination_slice);
				$command = implode(' ',$param);
				if(mwexec($command)):
					geom_part_undo($uncommitted_device_names);
					set_kern_geom_debugflags(0);
					$message = sprintf('Failed to create BSD scheme on %s.',$destination_slice);
					write_console($message . PHP_EOL);
					tui_display_message('Error',$message,60,5);
					return false;
				endif;
				switch($k_partition):
					case 'root':
//						install boot code
						$param = $param_geom_part;
						$param['command'] = 'bootcode';
						$param['testmode'] = $test_mode;
						$param['boot'] = '-b /boot/boot';
						$param['geom'] = escapeshellarg($destination_slice);
						$command = implode(' ',$param);
						if(mwexec($command)):
							geom_part_undo($uncommitted_device_names);
							set_kern_geom_debugflags(0);
							$message = sprintf('Failed to install bootcode on %s.',$destination_slice);
							write_console($message . PHP_EOL);
							tui_display_message('Error',$message,60,5);
							return false;
						endif;
						$param = $param_geom_part;
						$param['command'] = 'set';
						$param['testmode'] = $test_mode;
						$param['setting'] = '-a active';
						$param['index'] = $partition_index;
						$param['geom'] = escapeshellarg($destination);
						$command = implode(' ',$param);
						if(mwexec($command)):
							geom_part_undo($uncommitted_device_names);
							set_kern_geom_debugflags(0);
							$message = sprintf('Failed to activate partition on %s.',$destination);
							write_console($message . PHP_EOL);
							tui_display_message('Error',$message,60,5);
							return false;
						endif;
						break;
				endswitch;
				$param = $param_geom_part;
				$param['command'] = 'add';
				$param['testmode'] = $test_mode;
				$param['type'] = '';
				$param['geom'] = escapeshellarg($destination_slice);
				switch($k_partition):
					case 'root':
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-ufs');
						break;
					case 'swap':
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-swap');
						break;
					default:
						$param['type'] = sprintf('-t %s',$r_partition['fstype'] ?? 'freebsd-ufs');
						break;
				endswitch;
				$command = implode(' ',$param);
				if(mwexec($command)):
					geom_part_undo($uncommitted_device_names);
					set_kern_geom_debugflags(0);
					$message = sprintf('Failed to add %s partition on %s.',$r_partition['friendly_name'],$destination_slice);
					write_console($message . PHP_EOL);
					tui_display_message('Error',$message,60,5);
					return false;
				endif;
				break;
 */
		endswitch;
		$partition_layout_index++;
	endforeach;
	unset($r_partition);
//	commit changes
	if(!geom_part_commit($uncommitted_device_names)):
		geom_part_undo($uncommitted_device_names);
		set_kern_geom_debugflags(0);
		$message = sprintf('Failed to commit all changes on %s.' . PHP_EOL,$destination);
		write_console($message . PHP_EOL);
		tui_display_message('Error',$message,60,5);
		return false;
	endif;
//	create file systems and set volume label.
	$message = 'Create file system on partition:';
	write_console($message . PHP_EOL);
	foreach($partition_layout as $k_partition => $r_partition):
		switch($base['scheme']):
			case 'gpt':
				$destination_partition = sprintf('%sp%s',$destination,$r_partition['partition']);
				break;
/*
			case 'mbr':
				$destination_partition = sprintf('%ss%sa',$destination,$r_partition['partition']);
				break;
 */
		endswitch;
		$message = sprintf('  %s:',$destination_partition);
		write_console($message);
		switch($r_partition['fstype']):
			case 'freebsd-ufs':
				$param = [];
				$param['run'] = '/sbin/newfs';
				$param['softupdates'] = '-U';
				$param['blocksize'] = '-b 32768';
				$param['sectorsize'] = '-S 4096';
				$param['fragmentsize'] = '-f 4096';
				$param['trim'] = '-t';
				if(isset($r_partition['volname'])):
					$param['label'] = sprintf('-L %s',$r_partition['volname']);
				endif;
				$param['geom'] = sprintf('/dev/%s',$destination_partition);
				$command = implode(' ',$param);
				if(mwexec($command)):
					set_kern_geom_debugflags(0);
					$message = ' error';
					write_console($message . PHP_EOL);
					return false;
				else:
					$message = ' success';
					write_console($message . PHP_EOL);
				endif;
				break;
			default:
				$message = ' skipped';
				write_console($message . PHP_EOL);
				break;
		endswitch;
	endforeach;
//	shields up
	set_kern_geom_debugflags(0);
	unset($r_partition,$k_partition,$partition_layout,$geom);
	return true;
}
