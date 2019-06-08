<?php
/*
	services.inc

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

/**
 *	Check if the given port is already used by another service.
 *	@global array $config
 *	@param int $port The port to be checked.
 *	@param string $except The key to be ignored.
 *	@return bool True is returned if the port is already used by another service, otherwise false.
 */
function services_is_port_used($port,string $except = '') {
	global $config;

	//	integer between 0 and 65535
	$regex_port = '/^(?:6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{0,3}|0)$/';
	//	services and their default ports
	$services = [
		['name' => 'daap','default_port' => '3689'],
		['name' => 'websrv','default_port' => ''],
		['name' => 'upnp','default_port' => '49152'],
		['name' => 'minidlna','default_port' => '8200'],
		['name' => 'rsyncd','default_port' => '873'],
		['name' => 'bittorrent','default_port' => '9091']
	];
	//	list of ports
	$a_port = [];
	//	webgui port
	$test_port = is_string($test = $config['system']['webgui']['port'] ?? '') ? $test : '';
	if(1 === preg_match($regex_port,$test_port)):
		$a_port['webguiport'] = $test_port;
	else:
		$webgui_protocol = is_string($test = $config['system']['webgui']['protocol'] ?? '') ? $test : '';
		switch($webgui_protocol):
			case 'https':
				$a_port['webguiport'] = '443';
				break;
			default:
				$a_port['webguiport'] = '80';
				break;
		endswitch;
	endif;
	foreach($services as $service):
		$test_port = is_string($test = $config[$service['name']]['port'] ?? '') ? $test : '';
		$a_port[$service['name']] = (1 === preg_match($regex_port,$test_port)) ? $test_port : $service['default_port'];
	endforeach;
	if(0 < strlen($except)):
		unset($a_port[$except]);
	endif;
	return in_array($port,$a_port);
}
