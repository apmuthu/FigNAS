<?php
/*
	setting_properties.php

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
namespace services\sshd;
use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_port(): myp\property_int {
		$caption = gettext('Port of the SSH service. Leave blank to use the default port.');
		$description = gettext('Enter a custom port number if you do not want to use default port 22.');
		$placeholder = gettext('22');
		$property = parent::init_port();
		$property->
			set_id('port')->
			set_caption($caption)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			set_size(10)->
			set_maxlength(5)->
			set_min(1024)->
			set_max(65535)->
			filter_use_default()->
			filter_use_empty()->
			set_filter(FILTER_VALIDATE_INT,'22')->
			set_filter_flags(FILTER_REQUIRE_SCALAR,'69')->
			set_filter_options(['default' => null,'min_range' => 22,'max_range' => 22],'22')->
			set_filter_group('ui',['empty','ui','22']);
		return $property;
	}
	public function init_allowpa(): myp\property_bool {
		$caption = gettext('Allow password authentication.');
		$property = parent::init_allowpa();
		$property->
			set_id('passwordauthentication')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_allowcra(): myp\property_bool {
		$caption = gettext('Allow challenge-response authentication.');
		$property = parent::init_allowcra();
		$property->
			set_id('challengeresponseauthentication')->
			set_caption($caption)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_allowkia(): myp\property_bool {
		$caption = gettext('Allow keyboard-interactive authentication.');
		$property = parent::init_allowkia();
		$property->
			set_id('kbdinteractiveauthentication')->
			set_caption($caption)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_allowpka(): myp\property_bool {
		$caption = gettext('Allow public key authentication.');
		$property = parent::init_allowpka();
		$property->
			set_id('pubkeyauthentication')->
			set_caption($caption)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_allowga(): myp\property_bool {
		$caption = gettext('Allow GSSAPI authentication.');
		$property = parent::init_allowga();
		$property->
			set_id('gssapiauthentication')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_permitrootlogin(): myp\property_bool {
		$caption = gettext('Allow root to login via ssh.');
		$property = parent::init_permitrootlogin();
		$property->
			set_id('permitrootlogin')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_allowtcpforwarding(): myp\property_bool {
		$caption = gettext('Allow TCP forwarding.');
		$property = parent::init_allowtcpforwarding();
		$property->
			set_id('tcpforwarding')->
			set_caption($caption)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_compression(): myp\property_bool {
		$caption = gettext('Enable compression after the user has authenticated successfully.');
		$property = parent::init_compression();
		$property->
			set_id('compression')->
			set_caption($caption)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_privatekey(): myp\property_text {
		$property = parent::init_privatekey();
		$property->
			set_defaultvalue('')->
			set_id('private-key')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_rawprivatekey(): myp\property_textarea {
		$placeholder = gettext('Paste a RSA PRIVATE KEY in PEM format');
		$placeholderv = gettext(' No private key');
		$property = parent::init_rawprivatekey();
		$property->
			set_defaultvalue('')->
			set_id('rawprivatekey')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			filter_use_default();
		return $property;
	}
	public function init_subsystem(): myp\property_text {
		$description = gettext('Configures an external subsystem (e.g. file transfer daemon). Arguments should be a subsystem name and a command (with optional arguments) to execute upon subsystem request. Leave this field empty to use the default settings.');
		$placeholder = gettext('Subsystem');
		$placeholderv = gettext('Using defaults');
		$property = parent::init_subsystem();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('subsystem')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_loglevel(): myp\property_list {
		$description = gettext('Gives the verbosity level that is used when logging messages from sshd. Logging with a DEBUG level violates the privacy of users and is not recommended.');
		$options = [
			'' => gettext('Default'),
			'QUIET' => gettext('QUIET - disable logging'),
			'FATAL' => gettext('FATAL - log fatal errors only'),
			'ERROR' => gettext('ERROR - log errors'),
			'INFO' => gettext('INFO - log status messages'),
			'VERBOSE' => gettext('VERBOSE - log detailed information'),
			'DEBUG' => gettext('DEBUG - log debugging information'),
			'DEBUG1' => gettext('DEBUG1 - same as DEBUG'),
			'DEBUG2' => gettext('DEBUG2 - log detailed debugging information'),
			'DEBUG3' => gettext('DEBUG3 - log all debugging information')
		];
		$property = parent::init_loglevel();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('loglevel')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters are appended to /etc/ssh/sshd_config.');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
}
