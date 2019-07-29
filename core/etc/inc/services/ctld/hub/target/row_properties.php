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
namespace services\ctld\hub\target;
use common\properties as myp;

final class row_properties extends grid_properties {
	public function init_name(): myp\property_text {
		$description = gettext('Name of the target.');
		$placeholder = gettext('Name');
		$regexp = '/^(?:iqn|eui|naa)\.\S{1,219}$/';
		$property = parent::init_name();
		$property->
			set_id('name')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(223)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to this target.');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
	public function init_alias(): myp\property_text {
		$description = gettext('Assign a human-readable description to the target.');
		$placeholder = gettext('Alias');
		$regexp = '/^.{0,128}$/';
		$property = parent::init_alias();
		$property->
			set_id('alias')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(128)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_auth_group(): myp\property_list {
		$description = gettext('Assign a previously defined authentication group to the target.');
		$options = [
			'' => gettext('Deny discovery'),
			'no-authentication' => gettext('Permit access without authentication')
		];
		$property = parent::init_auth_group();
		$property->
			set_id('auth_group')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_portal_group(): myp\property_list {
		$description = gettext('Assign a previously defined portal group to the target.');
		$options = [
			'' => gettext('Default')
		];
		$property = parent::init_portal_group();
		$property->
			set_id('portal_group')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_redirect(): myp\property_ipaddress {
		$description = gettext('IPv4 or IPv6 address to redirect initiators to. When configured, all initiators attempting to connect to this target will get redirected using "Target moved temporarily" login response.');
		$property = parent::init_redirect();
		$property->
			set_id('redirect')->
			set_description($description)->
			set_defaultvalue('')->
			filter_use_default()->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
}
