<?php
/*
	common\properties\property_cidr_ipv6.php

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
 *	IPv6 CIDR property
 */
final class property_cidr_ipv6 extends property_text_callback {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(49)->
			set_placeholder(gettext('Network Address'))->
			set_size(60);
		return $this;
	}
	public function validate($cidr) {
		if(is_string($cidr)):
			list($ipaddress,$subnet) = explode('/',$cidr,2);
			if(!is_null(filter_var($ipaddress,FILTER_VALIDATE_IP,['flags' => FILTER_FLAG_IPV6,'options' => ['default' => NULL]]))):
				if(!is_null(filter_var($subnet,FILTER_VALIDATE_INT,['options' => ['default' => NULL,'min_range' => 0,'max_range' => 128]]))):
					return $cidr;
				endif;
			endif;
		endif;
		return NULL;
	}
}
