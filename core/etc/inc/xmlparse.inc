<?php
/*
	xmlparse.inc

	functions to parse/dump configuration files in XML format

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

//	Tags that are always to be handled as lists.
//	These tags can occur multiple times in the XML config file.
$listtags = [
	'dnsserver' => 'dnsserver',
	'winsserver' => 'winsserver',
	'disk' => 'disk',
	'vdisk' => 'vdisk',
	'diskr' => 'diskr',
	'sharetosync' => 'sharetosync',
	'encryption-algorithm-option' => 'encryption-algorithm-option',
	'hash-algorithm-option' => 'hash-algorithm-option',
	'hosts' => 'hosts',
	'onetoone' => 'onetoone',
	'hidemount' => 'hidemount',
	'staticmap' => 'staticmap',
	'route' => 'route',
	'pipe' => 'pipe',
	'queue' => 'queue',
	'shellcmd' => 'shellcmd',
	'cacert' => 'cacert',
	'earlyshellcmd' => 'earlyshellcmd',
	'user' => 'user',
	'group' => 'group',
	'servernat' => 'servernat',
	'nfsnetworks' => 'nfsnetworks',
	'passthrumac' => 'passthrumac',
	'allowedip' => 'allowedip',
	'mount' => 'mount',
	'vlan' => 'vlan',
	'domainoverrides' => 'domainoverrides',
	'minute' => 'minute',
	'hour' => 'hour',
	'day' => 'day',
	'month' => 'month',
	'weekday' => 'weekday',
	'content' => 'content',
	'rsyncclient' => 'rsyncclient',
	'rsynclocal' => 'rsynclocal',
	'package' => 'package',
	'ipv6dnsserver' => 'ipv6dnsserver',
	'share' => 'share',
	'auxparam' => 'auxparam',
	'extent' => 'extent',
	'device' => 'device',
	'target' => 'target',
	'storage' => 'storage',
	'group' => 'group',
	'job' => 'job',
	'report' => 'report',
	'cmd' => 'cmd',
	'module' => 'module',
	'selftest' => 'selftest',
	'url' => 'url',
	'param' => 'param',
	'lagg' => 'lagg',
	'laggport' => 'laggport',
	'vdevice' => 'vdevice',
	'pool' => 'pool',
	'dataset' => 'dataset',
	'volume' => 'volume',
	'bridge' => 'bridge',
	'bridgeif' => 'bridgeif',
	'carp' => 'carp',
	'hastresource' => 'hastresource',
	'wlan' => 'wlan',
	'snapshot' => 'snapshot',
	'autosnapshot' => 'autosnapshot',
	'rule' => 'rule',
	'portalgroup' => 'portalgroup',
	'portal' => 'portal',
	'initiatorgroup' => 'initiatorgroup',
	'iginitiatorname' => 'iginitiatorname',
	'ignetmask' => 'ignetmask',
	'authgroup' => 'authgroup',
	'agauth' => 'agauth',
	'pgigmap' => 'pgigmap',
	'agmap' => 'agmap',
	'lunmap' => 'lunmap'
];
class config_xml_reader {
	private $parser;
	public $depth;
	public $curpath;
	public $config;
	public $havedata;
	public $attributes;
	
	public function __construct() {
		//	Create XML parser.
		$this->parser = xml_parser_create('UTF-8');
		xml_set_object($this->parser,$this);
		//	Initialize start element handler and end element handler.
		xml_set_element_handler($this->parser,'startelementhandler','endelementhandler');
		//	Initialize data handler.
		xml_set_character_data_handler($this->parser,'datahandler');
	}
	public function __destruct() {
		//	Destroy parser
		xml_parser_free($this->parser);
		//	Free memory
		unset($this->parser);
	}
	public function startelementhandler($parser,$name,$attributes) {
		global $listtags;
		
		$lowercase_name = strtolower($name);
		//	Push start element
		array_push($this->curpath,$lowercase_name);
		$ptr = &$this->config;
		foreach($this->curpath as $path):
			$ptr = &$ptr[$path];
		endforeach;
		//	Is it an element that belongs to a list?
		if(array_key_exists($lowercase_name,$listtags)):
			if(!is_array($ptr)): // is there an array already?
				$ptr = []; // make an array
			endif;
			//	Push array index
			array_push($this->curpath,count($ptr));
		elseif(isset($ptr)):
			//	multiple entries not allowed for this element, bail out
			die(sprintf("XML error: %s at line %d cannot occur more than once\n",$name,xml_get_current_line_number($parser)));
		endif;
		$this->depth++;
		$this->havedata = $this->depth;
		$this->attributes = [];
		foreach($attributes as $key => $value):
			$this->attributes[strtolower($key)] = $value;
		endforeach;
	}
	public function endelementhandler($parser,$name) {
		global $listtags;
		
		$lowercase_name = strtolower($name);
		if($this->havedata == $this->depth):
			$ptr = &$this->config;
			foreach($this->curpath as $path):
				$ptr = &$ptr[$path];
			endforeach;
			$ptr = '';
		endif;
		array_pop($this->curpath);
		if(array_key_exists($lowercase_name,$listtags)):
			array_pop($this->curpath);
		endif;
		$this->depth--;
	}
	public function datahandler($parser,$data) {
		$trimmed_data = trim($data,"\t\n\r");
		if($trimmed_data != ''):
			$ptr = &$this->config;
			foreach($this->curpath as $path):
				$ptr = &$ptr[$path];
			endforeach;
			if(is_string($ptr)):
				//	Multiline
				$ptr .= $trimmed_data;
			elseif(preg_match('/\S/',$trimmed_data)):
				//	Data is not empty, check for type attribute
				if(array_key_exists('type',$this->attributes)):
					switch($this->attributes['type']):
						case 'bool':
							$ptr = (bool)$trimmed_data;
							break;
						default:
							$ptr = $trimmed_data;
							break;
					endswitch;
				else:
					$ptr = $trimmed_data;
				endif;
				$this->havedata++;
			endif;
		endif;
	}
	public function parse(string $cffile) {
		$this->config = [];
		$this->curpath = [];
		$this->depth = 0;
		$this->havedata = 0;
		$this->attributes = [];
		//	Read configuration file.
		$data = file_get_contents($cffile);
		if(false === $data):
			die("Error: could not open XML input\n");
		endif;
		//	Detect and set output character encoding. Default output encoding is UTF-8
		if(preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m',$data,$m)):
			$encoding = strtoupper($m[1]);
			xml_parser_set_option($this->parser,XML_OPTION_TARGET_ENCODING,$encoding);
		else:
			$encoding = 'UTF-8';
		endif;
		//	Parse configuration.
		if(!xml_parse($this->parser,$data,true)):
			die(sprintf("XML error: %s at line %d\n",xml_error_string(xml_get_error_code($this->parser)),xml_get_current_line_number($this->parser)));
		endif;
		//	Remember document encoding on each root node
		foreach($this->config as &$root_node):
			$root_node['encoding'] = $encoding;
		endforeach;
		return $this->config;
	}
}
function parse_xml_config_raw($cffile) {
	$o = new config_xml_reader();
	return $o->parse($cffile);
}
function parse_xml_config($cffile,bool $allowtodie,string ...$root_objects) {
	$raw_config = parse_xml_config_raw($cffile);
	foreach($root_objects as $rootobj):
		if(array_key_exists($rootobj,$raw_config)):
			return $raw_config[$rootobj];
		endif;
	endforeach;
	if($allowtodie):
		die('XML error: no valid root object found!' . PHP_EOL);
	endif;
	return false;
}
/**
 *	Dump configuration - recursion module
 *	A charset conversion may appear when switching from ISO-8859-1
 *	(e.g. English) to UTF-8 (e.g. French). If this is not done
 *	we will get an parser error.
 *	@global array $listtags
 *	@param array $arr Array to be dumped
 *	@param int $indent Count of indents
 *	@param string $inbound_charset The input charset, e.g. ISO-8859-1
 *	@param string $outbound_charset The output charset, e.g. UTF-8
 *	@return string
 */
function dump_xml_config_sub($arr,$indent,string $inbound_charset,string $outbound_charset) {
	global $listtags;

	$xmlconfig = '';
	$s_indent = str_repeat("\t",$indent);
	$must_convert = ($inbound_charset !== $outbound_charset);
	foreach($arr as $ent => $val):
		if(is_array($val)):
			if(array_key_exists(strtolower($ent),$listtags)): // is it a list of multiple values?
				foreach($val as $cval):
					if(is_array($cval)):
						$xmlconfig .= $s_indent . sprintf('<%s>',$ent) . PHP_EOL;
						$xmlconfig .= dump_xml_config_sub($cval,$indent + 1,$inbound_charset,$outbound_charset);
						$xmlconfig .= $s_indent . sprintf('</%s>',$ent) . PHP_EOL;
					elseif(is_bool($cval)):
						if($cval):
							$xmlconfig .= $s_indent . sprintf('<%1$s type="bool">1</%1$s>',$ent) . PHP_EOL;
//						else:
//							$xmlconfig .= $s_indent . sprintf('<%1$s type="bool">0</%1$s>',$ent) . PHP_EOL;
						endif;
					elseif('' === $cval):
						$xmlconfig .= $s_indent . sprintf('<%s/>',$ent) . PHP_EOL;
					elseif($must_convert):
						$xmlconfig .= $s_indent . sprintf('<%s>',$ent);
						$xmlconfig .= htmlspecialchars(iconv($inbound_charset,$outbound_charset . '//IGNORE',$cval));
						$xmlconfig .= sprintf('</%s>',$ent) . PHP_EOL;
					else:
						$xmlconfig .= $s_indent . sprintf('<%s>',$ent);
						$xmlconfig .= htmlspecialchars($cval);
						$xmlconfig .= sprintf('</%s>',$ent) . PHP_EOL;
					endif;
				endforeach;
			else: // it's an array
				$xmlconfig .= $s_indent . sprintf('<%s>',$ent) . PHP_EOL;
				$xmlconfig .= dump_xml_config_sub($val,$indent + 1,$inbound_charset,$outbound_charset);
				$xmlconfig .= $s_indent . sprintf('</%s>',$ent) . PHP_EOL;
			endif;
		elseif(is_bool($val)):
			if($val):
				$xmlconfig .= $s_indent . sprintf('<%1$s type="bool">1</%1$s>',$ent) . PHP_EOL;
//			else:
//				$xmlconfig .= $s_indent . sprintf('<%1$s type="bool">0</%1$s>',$ent) . PHP_EOL;
			endif;
		elseif('' === $val):
			$xmlconfig .= $s_indent . sprintf('<%s/>',$ent) . PHP_EOL;
		elseif($must_convert):
			$xmlconfig .= $s_indent . sprintf('<%s>',$ent);
			$xmlconfig .= htmlspecialchars(iconv($inbound_charset,$outbound_charset . '//IGNORE',$val));
			$xmlconfig .= sprintf('</%s>',$ent) . PHP_EOL;
		else:
			$xmlconfig .= $s_indent . sprintf("<%s>",$ent);
			$xmlconfig .= htmlspecialchars($val);
			$xmlconfig .= sprintf('</%s>',$ent) . PHP_EOL;
		endif;
	endforeach;
	return $xmlconfig;
}
function dump_xml_config($arr,$rootobj,$encoding) {
	//	Set inbound / outbound encoding
	$inbound_charset = $arr['encoding'] ?? 'UTF-8';
	if(1 !== preg_match('/\S/',$inbound_charset)):
		$inbound_charset = 'UTF-8';
	endif;
	$outbound_charset = $encoding;
	//	Do not dump temporary encoding attribute
	unset($arr['encoding']);
	$xmlconfig = sprintf('<?xml version="1.0" encoding="%s"?>',$encoding) . PHP_EOL;
	$xmlconfig .= sprintf('<%s>',$rootobj) . PHP_EOL;
	$xmlconfig .= dump_xml_config_sub($arr,1,$inbound_charset,$outbound_charset);
	$xmlconfig .= sprintf('</%s>',$rootobj) . PHP_EOL;
	return $xmlconfig;
}
/**
 *	Validate the configuration xml file.
 *	@param string cffile Path to the file to validate.
 *	@param string root_objects The names of the allowed root objects.
 *	@return bool True if valid, otherwise false.
 */
function validate_xml_config($cffile,string ...$root_objects) {
	//	Parse the configuration
	$raw_config = parse_xml_config_raw($cffile);
	foreach($root_objects as $rootobj):
		if(array_key_exists($rootobj,$raw_config)):
			$xml_config = $raw_config[$rootobj];
			if(array_key_exists('version',$xml_config)):
				return true;
			endif;
		endif;
	endforeach;
	return false;
}
