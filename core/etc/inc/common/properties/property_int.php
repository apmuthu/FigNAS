<?php
/*
	common\properties\property_int.php

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
 *	Int property
 */
class property_int extends property_text {
	public $x_min = NULL;
	public $x_max = NULL;

	public function set_min(int $min = NULL) {
		$this->x_min = $min;
		return $this;
	}
	public function get_min() {
		return $this->x_min;
	}
	public function set_max(int $max = NULL) {
		$this->x_max = $max;
		return $this;
	}
	public function get_max() {
		return $this->x_max;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$options = [];
		$options['default'] = NULL;
		$min = $this->get_min();
		if(isset($min)):
			$options['min_range'] = $min;
		endif;
		$max = $this->get_max();
		if(isset($max)):
			$options['max_range'] = $max;
		endif;
		$this->
			set_filter(FILTER_VALIDATE_INT,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options($options,$filter_name);
		return $this;
	}
}
