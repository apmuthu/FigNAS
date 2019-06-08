<?php
/*
	row_properties.php

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
namespace services\ctld\hub\sub\chap_mutual;

final class row_properties extends grid_properties {
	public function init_name() {
		$description = gettext('Enter user name.');
		$placeholder = gettext('User');
		$regexp = '/^\S{1,32}$/';
		$property = parent::init_name();
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(40)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_secret() {
		$description = gettext('Enter secret.');
		$placeholder = gettext('Secret');
		$regexp = '/^[^"]{1,32}$/';
		$property = parent::init_secret();
		$property->
			set_id('secret')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(40)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
	public function init_group() {
		$description = gettext('Link mutual-chap configuration to auth-groups. Note that an auth-group may contain either chap or chap-mutual entries, but not both.');
		$message_info = gettext('No auth groups found.');
		$options = [];
		$property = parent::init_group();
		$property->
			set_id('group')->
			set_description($description)->
			set_defaultvalue([])->
			set_options($options)->
			filter_use_default()->
			set_message_info($message_info);
		return $property;
	}
	public function init_mutual_name() {
		$description = gettext('Enter mutual user name.');
		$placeholder = gettext('Mutual User');
		$regexp = '/^\S{1,32}$/';
		$property = parent::init_mutual_name();
		$property->
			set_id('mutual_name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(40)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_mutual_secret() {
		$description = 'Enter mutual secret';
		$placeholder = gettext('Mutual Secret');
		$regexp = '/^[^"]{1,32}$/';
		$property = parent::init_mutual_secret();
		$property->
			set_id('secret')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(40)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
}
