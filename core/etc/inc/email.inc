<?php
/*
	email.inc

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
require_once 'system.inc';

/**
 *	Checks if email settings are valid.
 *	@global array $config
 *	@return int Return 0 if successful, otherwise 1.
 */
function email_validate_settings() {
	global $config;

	$email = array_make_branch($config,'system','email');
	if(empty($email['server']) || empty($email['port']) || empty($email['from'])):
		return 1;
	endif;
	if(isset($email['auth'])):
		if(empty($email['username']) || empty($email['password'])):
			return 1;
		endif;
	endif;
	return 0;
}
/**
 *	Calculates the email header field 'From'
 *	@global array $config
 *	@return string Email header field 'From'
 */
function get_email_header_from() {
	global $config;

	array_make_branch($config,'system','email');
	$cfg_hostname = $config['system']['hostname'] ?? '';
	$flt_hostname = filter_var($cfg_hostname,FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]);
	if(is_null($flt_hostname)):
		$flt_hostname = get_product_name();
	endif;
	$cfg_from = $config['system']['email']['from'] ?? '';
	$flt_from = filter_var($cfg_from,FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]);
	if(is_null($flt_from)):
		$cfg_domain = $config['system']['domain'] ?? '';
		$flt_domain = filter_var($cfg_domain,FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]);
		if(is_null($flt_domain)):
			$flt_domain = get_product_name() . '.local';
		endif;
		$flt_from = sprintf('%s@%s',$flt_hostname,$flt_domain);
	endif;
	return sprintf('"%s" <%s>',$flt_hostname,$flt_from);
}
/**
 *	Send an email.
 *	@global array $g
 *	@param string $address The email adress(es)
 *	@param string $subject The email subject
 *	@param string $message The message
 *	@param string $error Contains an error message if submission fails.
 *	@return int Return 0 if successful, otherwise 1.
 */
function email_send($address,$subject,$message,&$error) {
	global $g;

	$retval = 0;
	$msgfilepath = tempnam($g['tmp_path'],'msg');
	if(false !== $msgfilepath):
//		get date in RFC 2882 format
		$rfcdate = date('r');
//		get the language configured
		$preferences = [
			'input-charset' => 'UTF-8',
			'output-charset' => 'UTF-8',
			'line-length' => 76,
			'line-break-chars' => PHP_EOL
		];
		$email_from = get_email_header_from();
//		substitute special characters in subject
		$replace_needle = $replace_with = [];
		$replace_needle[] = '%d';$replace_with[] = trim(shell_exec('date'));
		$replace_needle[] = '%h';$replace_with[] = system_get_hostname();
		$subject_substituted = str_replace($replace_needle,$replace_with,$subject);
		$subject_final = iconv_mime_encode('Subject',$subject_substituted,$preferences);
//		convert adresses for msmtp
		$address = strtr($address,[';' => ' ',',' => ' ']);
//		generate message content
		$data = '';
		foreach(explode(' ',$address) as $addressv):
			$data .= 'To: ' . $addressv . PHP_EOL;
		endforeach;
		$data .= 'From: ' . $email_from . PHP_EOL;
		$data .= $subject_final . PHP_EOL;
		$data .= 'Date: ' . $rfcdate . PHP_EOL;
		$data .= 'MIME-Version: 1.0' . PHP_EOL;
		$data .= 'Content-Type: text/plain; charset=UTF-8' . PHP_EOL;
		$data .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL;
		$data .= PHP_EOL;
		$data .= $message;
		$data .= PHP_EOL;
//		write message to file
		if(false !== file_put_contents($msgfilepath,$data)):
//			send email via external SMTP client
			$cmd = sprintf('/usr/local/bin/msmtp -C /var/etc/msmtp.conf -t < %s',escapeshellarg($msgfilepath));
			mwexec2($cmd,$output,$retval);
			if(0 !== $retval):
				$error = $output;
			endif;
		else:
			write_log(sprintf("Error: Failed to write '%s'",$msgfilepath));
			$retval = 1;
		endif;
//		cleanup message
		@unlink($msgfilepath);
	else:
		$retval = 1;
	endif;
	return $retval;
}
