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
namespace services\ctld\hub\sub\initiator_portal;
use common\properties as myp;

final class row_properties extends grid_properties {
	public function init_ipaddress(): myp\property_ipaddress {
		$description = gettext('An IPv4 or IPv6 address of an iSCSI initiator portal.');
		$placeholder = gettext('IP Address');
		$property = parent::init_ipaddress();
		$property->
			set_id('ipaddress')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			filter_use_default();
		return $property;
	}
	public function init_prefixlen(): myp\property_int {
		$description = gettext('Enter IP address prefix length.');
		$placeholder = '';
		$property = parent::init_prefixlen();
		$property->
			set_id('prefixlen')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(10)->
			set_maxlength(3)->
			set_min(0)->
			set_max(128)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_group(): myp\property_list_multi {
		$description = gettext('Link initiator-portal to auth-groups. An Initiator matching this address will be allowed to connect to the selected auth-groups.');
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
}
