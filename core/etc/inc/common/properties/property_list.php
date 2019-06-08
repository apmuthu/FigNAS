<?php
/*
	common\properties\property_list.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright © 2018-2019 XigmaNAS <info@xigmanas.com>.
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
namespace common\properties;
/**
 *	List property
 */
class property_list extends property {
	protected $x_options = [];
	public function set_options(array $options) {
		$this->x_options = $options;
		return $this;
	}
	public function upsert_option(string $key,string $value) {
		$this->x_options[$key] = $value;
	}
	public function get_options() {
		return $this->x_options;
	}
	public function validate($option) {
		if(array_key_exists($option,$this->get_options())):
			return $option;
		else:
			return NULL;
		endif;
	}
	public function validate_config(array $source) {
		$return_data = '';
		$key = $this->get_name();
		if(array_key_exists($key,$source)):
			$option = $source[$key];
			if(array_key_exists($option,$this->get_options())):
				$return_data = $option;
			endif;
		else:
			$return_data = $this->get_defaultvalue();
		endif;
		return $return_data;
	}
/**
 * Method to apply the default class filter to a filter name.
 * The filter is a regex to match any of the option array keys.
 * @return object Returns $this.
 */
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_CALLBACK,$filter_name)->
			set_filter_options([$this,'validate'],$filter_name);
		return $this;
	}
}
