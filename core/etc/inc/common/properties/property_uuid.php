<?php
/*
	common\properties\property_uuid.php

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
 *	UUID property
 */
final class property_uuid extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_name('uuid')->
			set_title(gettext('Universally Unique Identifier'));
		$this->
			set_id('uuid')->
			set_description(gettext('The UUID of the record.'))->
			set_size(45)->
			set_maxlength(36)->
			set_placeholder(gettext('Enter Universally Unique Identifier'))->
			filter_use_default()->
			set_editableonadd(false)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$this->get_title(),gettext('The value is invalid.')));
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => '/^[\da-f]{4}([\da-f]{4}-){2}4[\da-f]{3}-[89ab][\da-f]{3}-[\da-f]{12}$/i'],$filter_name);
		return $this;
	}
	public function get_defaultvalue() {
		return uuid();
	}
}
