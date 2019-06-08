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
namespace services\ctld\hub\portal_group;

final class row_properties extends grid_properties {
	public function init_name() {
		$description = gettext('Name of the Portal Group.');
		$placeholder = gettext('Portal Group Name');
		$regexp = '/^\S{1,223}$/';
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
	public function init_auxparam() {
		$description = gettext('These parameters will be added to this portal-group.');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
	public function init_discovery_auth_group() {
		$description = gettext('Assign a previously defined authentication group to the portal group, to be used for target discovery.');
		$options = [
			'' => gettext('Deny discovery'),
			'no-authentication' => gettext('Permit discovery without authentication')
		];
		$property = parent::init_discovery_auth_group();
		$property->
			set_id('discovery_auth_group')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_discovery_filter() {
		$description = gettext('Determines which targets are returned during discovery.');
		$options = [
			'' => gettext('Discovery will return all targets assigned to this portal group.'),
			'portal' => gettext('Discovery will not return targets that cannot be accessed by the initiator because of their initiator-portal.'),
			'portal-name' => gettext('The check will include both initiator-portal and initiator-name.'),
			'portal-name-auth' => gettext('The check will include initiator-portal, initiator-name, and authentication credentials. The target is returned if it does not require CHAP authentication, or if the CHAP user and secret used during discovery match those used by the target.')
		];
		$property = parent::init_discovery_filter();
		$property->
			set_id('discovery_filter')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_foreign() {
		$caption = gettext('Specifies that this portal-group is listened by some other host. This host will announce it on discovery stage, but won\'t listen.');
		$property = parent::init_foreign();
		$property->
			set_id('foreign')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_offload() {
		$description = gettext('Define iSCSI hardware offload driver to use for this portal-group. The default is "none".');
		$placeholder = gettext('Driver');
		$regexp = '/^\S{0,60}$/';
		$property = parent::init_offload();
		$property->
			set_id('offload')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(60)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_redirect() {
		$description = gettext('IPv4 or IPv6 address to redirect initiators to. When configured, all initiators attempting to connect to portal belonging to this portal-group will get redirected using "Target moved temporarily" login response.');
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
	public function init_tag() {
		$description = gettext('Unique 16-bit tag value of this portal-group. If not specified, the value is generated automatically.');
		$placeholder = gettext('Tag');
		$property = parent::init_tag();
		$property->
			set_id('tag')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(20)->
			set_min(0)->
			set_max(65535)->
			filter_use_default()->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
}
