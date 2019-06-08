<?php
/*
	common\properties\property_text.php

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
 *	Text property
 */
class property_text extends property {
	protected $x_maxlength = 0;
	public function set_maxlength(int $maxlength = 0) {
		$this->x_maxlength = $maxlength;
		return $this;
	}
	public function get_maxlength() {
		return $this->x_maxlength;
	}
	protected $x_placeholder = null;
	public function set_placeholder(string $placeholder = null) {
		$this->x_placeholder = $placeholder;
		return $this;
	}
	public function get_placeholder() {
		return $this->x_placeholder;
	}
	protected $x_placeholderv = null;
	public function set_placeholderv(string $placeholderv = null) {
		$this->x_placeholderv = $placeholderv;
		return $this;
	}
	public function get_placeholderv() {
		return $this->x_placeholderv;
	}
	protected $x_size = 40;
	public function set_size(int $size = 40) {
		$this->x_size = $size;
		return $this;
	}
	public function get_size() {
		return $this->x_size;
	}
	public function filter_use_default() {
		//	not empty, must contain at least one printable character
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => '/\S/'],$filter_name);
		return $this;
	}
	public function filter_use_empty() {
		$filter_name = 'empty';
		$regexp = '/^$/';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => $regexp],$filter_name);
		return $this;
	}
	public function filter_use_default_or_empty() {
		$this->filter_use_default();
		$this->filter_use_empty();
		$this->set_filter_group('ui',['empty','ui']);
		return $this;
	}
}
