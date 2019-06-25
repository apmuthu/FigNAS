<?php
/*
	globals.inc

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
require_once 'super_fun.inc';
/*
 *	system configuration parameter
 */
$g = [
	'varrun_path' => '/var/run',
	'varetc_path' => '/var/etc',
	'vardb_path' => '/var/db',
	"varlog_path" => '/var/log',
	'etc_path' => '/etc',
	'tmp_path' => '/tmp',
	'conf_path' => '/conf',
	'ftmp_path' => '/var/tmp/ftmp',
	'media_path' => '/mnt',
	'conf_default_path' => '/conf.default',
	'cf_path' => '/cf',
	'cf_conf_path' => '/cf/conf',
	'www_path' => '/usr/local/www',
	'xml_rootobj' => 'xigmanas',
	'debug' => false,
	'latest_config' => '45',
	'default_passwd' => 'xigmanas',
	'default_ip' => '192.168.1.250',
	'zroot' => false
];
/*
 *	wireless_regex is based on https://www.freebsd.org/releases/12.0R/hardware.html#wlan
 *	devd.conf has its own regex
 */
	$g['wireless_regex'] = '/^(ndis|wlan|an|ath|bwi|bwn|ipw|iwi|iwm|iwn|malo|mwl|otus|ral|rsu|rtwn|rum|run|uath|upgt|ural|urtw|wi|wpi|zyd)/';
/*
 *	directory of images.
 */
$g_img = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png',
	'mdn' => 'images/down.png',
	'mup' => 'images/up.png',
	'mle' => 'images/left.png',
	'mri' => 'images/right.png',
	'box.error' => 'images/error_box.png',
	'box.info' => 'images/info_box.png',
	'box.warning' => 'images/warn_box.png'
];
/*
 *	installation parameter
 */
$g_install = [
	'default_swap' => '1024',
	'default_max_swap' => '2048',   /* maximum recommended swap */
	'part1min_embedded' => '466',
	'part1size_embedded' => '2048',
	'part1size_full' => '3072'
];
/*
 *	available languages/locales. Function array_flip is used in system.inc
 */
$g_languages = [
	'auto'	=> 'auto',
	'af'    => 'af',
	'bg'    => 'bg',
	'ca'    => 'ca',
	'cs'    => 'cs',
	'zh_CN' => 'zh_cn',
	'zh_TW' => 'zh_tw',
	'nl'    => 'nl',
	'da'    => 'da',
	'en_US' => 'en_us',
	'fi'    => 'fi',
	'fr'    => 'fr',
	'de'    => 'de',
	'el'    => 'el',
	'hu'    => 'hu',
	'it'    => 'it',
	'ja'    => 'ja',
	'ko'    => 'ko',
	'nb'    => 'nb',
	'pl'    => 'pl',
	'pt_BR' => 'pt_br',
	'pt'    => 'pt',
	'ro'    => 'ro',
	'ru'    => 'ru',
	'sl'    => 'sl',
	'es'    => 'es',
	'sv'    => 'sv',
	'sk'    => 'sk',
	'tr'    => 'tr',
	'uk'    => 'uk'
];
$g_months = [
	1 => gtext('January'),
	2 => gtext('February'),
	3 => gtext('March'),
	4 => gtext('April'),
	5 => gtext('May'),
	6 => gtext('June'),
	7 => gtext('July'),
	8 => gtext('August'),
	9 => gtext('September'),
	10 => gtext('October'),
	11 => gtext('November'),
	12 => gtext('December')
];
$g_months_short = [
	1 => gtext('Jan'),
	2 => gtext('Feb'),
	3 => gtext('Mar'),
	4 => gtext('Apr'),
	5 => gtext('May'),
	6 => gtext('Jun'),
	7 => gtext('Jul'),
	8 => gtext('Aug'),
	9 => gtext('Sep'),
	10 => gtext('Oct'),
	11 => gtext('Nov'),
	12 => gtext('Dec')
];
$g_months_en = [
	1 => 'January',
	2 => 'February',
	3 => 'March',
	4 => 'April',
	5 => 'May',
	6 => 'June',
	7 => 'July',
	8 => 'August',
	9 => 'September',
	10 => 'October',
	11 => 'November',
	12 => 'December'
];
$g_months_en_short = [
	1 => 'Jan',
	2 => 'Feb',
	3 => 'Mar',
	4 => 'Apr',
	5 => 'May',
	6 => 'Jun',
	7 => 'Jul',
	8 => 'Aug',
	9 => 'Sep',
	10 => 'Oct',
	11 => 'Nov',
	12 => 'Dec'
];
$g_weekdays = [
	1 => gtext('Monday'),
	2 => gtext('Tuesday'),
	3 => gtext('Wednesday'),
	4 => gtext('Thursday'),
	5 => gtext('Friday'),
	6 => gtext('Saturday'),
	7 => gtext('Sunday')
];
$g_weekdays_short = [
	1 => gtext('Mon'),
	2 => gtext('Tue'),
	3 => gtext('Wed'),
	4 => gtext('Thu'),
	5 => gtext('Fri'),
	6 => gtext('Sat'),
	7 => gtext('Sun')
];
$g_weekdays_en = [
	1 => 'Monday',
	2 => 'Tuesday',
	3 => 'Wednesday',
	4 => 'Thursday',
	5 => 'Friday',
	6 => 'Saturday',
	7 => 'Sunday'
];
$g_weekdays_en_short = [
	1 => 'Mon',
	2 => 'Tue',
	3 => 'Wed',
	4 => 'Thu',
	5 => 'Fri',
	6 => 'Sat',
	7 => 'Sun'
];
/*
 *	supported filesystems
 */
$g_filesystems = [
	'' => gtext('Unformatted'),
	'ufs' => gtext('UFS with Soft Updates'),
	'ufs_no_su' => 'UFS',
	'ufsgpt' => gtext('UFS (GPT and Soft Updates)'),
	'ufsgpt_no_su' => 'UFS (EFI/GPT)',
	'msdos' => 'FAT32',
	'cd9660' => 'CD/DVD',
	'ntfs' => 'NTFS',
	'ext2' => 'EXT2',
	'exfat' => 'exFAT',
	'softraid' => 'Software RAID',
	'geli' => 'Encrypted',
	'zfs' => 'ZFS Storage Pool'
];
/*
 *	upload status messages
 */
$g_file_upload_error = [
	UPLOAD_ERR_OK => gtext('There is no error, the file uploaded with success.'),
	UPLOAD_ERR_INI_SIZE => gtext('The uploaded file exceeds the upload_max_filesize directive in php.ini.'),
	UPLOAD_ERR_FORM_SIZE => gtext('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.'),
	UPLOAD_ERR_PARTIAL => gtext('The uploaded file was only partially uploaded.'),
	UPLOAD_ERR_NO_FILE => gtext('No file was uploaded.'),
	UPLOAD_ERR_NO_TMP_DIR => gtext('Missing a temporary folder.'),
	UPLOAD_ERR_CANT_WRITE => gtext('Failed to write file to disk.'),
	UPLOAD_ERR_EXTENSION => gtext('File upload stopped by extension.')
];
