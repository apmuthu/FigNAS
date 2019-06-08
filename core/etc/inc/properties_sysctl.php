<?php
/*
	properties_sysctl.php

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
require_once 'properties.php';

class sysctl_properties extends co_property_container_param {
	protected $x_comment;
	protected $x_name;
	protected $x_value;
	
	public function get_comment() {
		return $this->x_comment ?? $this->init_comment();
	}
	public function init_comment() {
		$property = $this->x_comment = new property_text($this);
		$property->
			set_name('comment')->
			set_title(gettext('Description'));
		return $property;
	}
	public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	public function init_name() {
		$property = $this->x_name = new property_list();
		$property->
			set_name('name')->
			set_title(gettext('Variable'));
		return $property;
	}
	public function get_value() {
		return $this->x_value ?? $this->init_value();
	}
	public function init_value() {
		$property = $this->x_value = new property_text($this);
		$property->
			set_name('value')->
			set_title(gettext('Value'));
		return $property;
	}
}
class sysctl_edit_properties extends sysctl_properties {
	public function init_comment() {
		$property = parent::init_comment();
		$description = '';
		$placeholder = gettext('Enter a description');
		$property->
			set_id('comment')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(60)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => ''])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_name() {
		$property = parent::init_name();
		$options = [];
		$property->
			set_id('name')->
			set_defaultvalue('')->
			set_options($options)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_value() {
		$property = parent::init_value();
		$description = gettext('Enter a valid value.');
		$placeholder = gettext('Value');
		$property->
			set_id('value')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(60)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
}
class systcl_info_properties extends sysctl_properties {
	protected $x_sysctlinfo;
	public function init_sysctlinfo() {
		$property = $this->x_sysctlinfo = new property_text($this);
		$property->
			set_name('sysctlinfo')->
			set_title(gettext('Description'));
		return $property;
	}
	public function get_sysctlinfo() {
		return $this->x_sysctlinfo ?? $this->init_sysctlinfo();
	}
	protected $x_sysctltype;
	public function init_sysctltype() {
		$property = $this->x_sysctltype = new property_text($this);
		$property->
			set_name('sysctltype')->
			set_title(gettext('Type'));
		return $property;
	}
	public function get_sysctltype() {
		return $this->x_sysctltype ?? $this->init_sysctltype();
	}
}
