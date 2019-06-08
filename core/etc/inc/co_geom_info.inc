<?php
/*
	co_geom_info.inc

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

class co_geom_info {
	private $a_xml;

	public function __construct() {
		$this->scan();
		return true;
	}
	public function scan() {
		unset($this->a_xml);
		$cmd = 'sysctl -b kern.geom.confxml';
		$a_row = [];
		$retval = 0;
		mwexec2($cmd,$a_row,$retval);
		$c_xml = implode('',$a_row);
		$this->a_xml = new SimpleXMLElement($c_xml);
		unset($c_xml,$retval,$a_row,$cmd);
		return true;
	}
	public function get_disk(&$a_ret = []) {
		$x_query_string = '/mesh/class[name="DISK"]/geom';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result['id']] = [
					'geom_id' => (string)$r_query_result['id'],
					'rank' => (int)$r_query_result->rank,
					'name' => (string)$r_query_result->name
				];
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function get_dev(&$a_ret = []) {
		$x_query_string = '/mesh/class[name="DEV"]/geom';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result['id']] = [
					'geom.id' => (string)$r_query_result['id'],
					'rank' => (int)$r_query_result->rank,
					'name' => (string)$r_query_result->name,
				];
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function get_part(array &$a_ret = []) {
		$x_query_string = '/mesh/class[name="PART"]/geom';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result['id']] = [
					'geom.id' => (string)$r_query_result['id'],
					'rank' => (int)$r_query_result->rank,
					'name' => (string)$r_query_result->name
				];
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function get_provider(array &$a_ret = []) {
		$x_query_string = '//provider[@id]';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result['id']] = [
					'provider.id' => (string)$r_query_result['id'],
					'name' => (string)$r_query_result->name,
					'mediasize' => (int)$r_query_result->mediasize,
					'sectorsize' => (int)$r_query_result->sectorsize,
					'stripesize' => (int)$r_query_result->stripesize,
					'stripeoffset' => (int)$r_query_result->stripeoffset
				];
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function get_provider_by_name(array &$a_ret = [],string $name = NULL) {
		if(!is_null($name)):
			$x_query_string = sprintf('//provider[@id and name="%s"]',$name);
			if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
				foreach($a_query_result as $r_query_result):
					$a_ret[(string)$r_query_result['id']] = [
						'provider.id' => (string)$r_query_result['id'],
						'name' => (string)$r_query_result->name,
						'mediasize' => (int)$r_query_result->mediasize,
						'sectorsize' => (int)$r_query_result->sectorsize,
						'stripesize' => (int)$r_query_result->stripesize,
						'stripeoffset' => (int)$r_query_result->stripeoffset
					];
				endforeach;
			endif;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function get_provider_ref(array &$a_ret = []) {
		$x_query_string = '//provider[@ref]';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$key = (string)$r_query_result['ref'];
				if(array_key_exists($key,$a_ret)):
					$a_ret[$key]['count']++;
				else:
					$a_ret[$key] = ['provider.ref' => $key,'count' => 1];
				endif;
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	/**
	 *	Read all provider and referenced provider. A reference count of 1 indicates
	 *	an available provider (a dev has always a reference to the provider).
	 *	@param array $a_ret
	 *	@return array
	 */
	function get_available_provider(array &$a_ret = []) {
		$a_ref = $this->get_provider_ref();
		$a_provider = $this->get_provider();
		foreach($a_ref as $r_ref_key => $r_ref_data):
			if(1 === $r_ref_data['count'] && array_key_exists($r_ref_key,$a_provider)):
				$a_ret[] = $a_provider[$r_ref_key];
			endif;
		endforeach;
		return $a_ret;
	}
	public function get_class_names(array &$a_ret = []) {
		$x_query_string = '/mesh/class[@id]';
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result->name] = (string)$r_query_result->name;
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function gather_part_by_name(string $name = NULL,array &$a_ret = []) {
		$x_query_string = (is_null($name) ? '/mesh/class[name="PART"]/geom' : sprintf('/mesh/class[name="PART"]/geom[name="%s"]',$name));
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result->name] = $r_query_result;
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function gather_provider_by_name(string $name = NULL,array &$a_ret = []) {
		$x_query_string = (is_null($name) ? sprintf('//provider[@id]"]') : sprintf('//provider[@id and name="%s"]',$name));
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result->name] = $r_query_result;
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
	public function gather_disk_by_name(string $name = NULL,array &$a_ret = []) {
		$x_query_string = (is_null($name) ? '/mesh/class[name="DISK"]/geom' : sprintf('/mesh/class[name="DISK"]/geom[name="%s"]',$name));
		if(false !== ($a_query_result = $this->a_xml->xpath($x_query_string))):
			foreach($a_query_result as $r_query_result):
				$a_ret[(string)$r_query_result->name] = $r_query_result;
			endforeach;
		endif;
		unset($r_query_result,$a_query_result,$x_query_string);
		return $a_ret;
	}
}
