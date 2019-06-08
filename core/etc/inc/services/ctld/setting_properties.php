<?php
/*
	setting_properties.php

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
namespace services\ctld;

use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_debug(): myp\property_int {
		$description = gettext('The debug verbosity level. The default is 0.');
		$placeholder = '0';
		$property = parent::init_debug();
		$property->
			set_id('debug')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_maxlength(5)->
			set_size(4)->
			set_defaultvalue('')->
			set_min(0)->
			set_max(99)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_maxproc(): myp\property_int {
		$description = gettext('The limit for concurrently running child processes handling incoming connections. The default is 30. A setting of 0 disables the limit.');
		$placeholder = '30';
		$property = parent::init_maxproc();
		$property->
			set_id('maxproc')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_size(10)->
			set_maxlength(5)->
			set_defaultvalue('')->
			set_min(0)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_timeout(): myp\property_int {
		$description = gettext('The timeout for login sessions, after which the connection will be forcibly terminated. The default is 60. A setting of 0 disables the timeout.');
		$placeholder = '60';
		$property = parent::init_timeout();
		$property->
			set_id('timeout')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_size(10)->
			set_maxlength(5)->
			set_defaultvalue('')->
			set_min(0)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_isns_period(): myp\property_int {
		$description = gettext('iSNS registration period. Registered Network Entity not updated during this period will be unregistered. The default is 900.');
		$placeholder = '900';
		$property = parent::init_isns_period();
		$property->
			set_id('isns_period')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_maxlength(5)->
			set_size(10)->
			set_defaultvalue('')->
			set_min(0)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_isns_timeout(): myp\property_int {
		$description = gettext('Timeout for iSNS requests. The default is 5.');
		$placeholder = '5';
		$property = parent::init_isns_timeout();
		$property->
			set_id('isns_timeout')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_maxlength(5)->
			set_size(10)->
			set_defaultvalue('')->
			set_min(0)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to the global section of ctl.conf');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
}
