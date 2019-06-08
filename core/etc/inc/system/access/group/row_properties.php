<?php
/*
	row_properties.php

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
namespace system\access\group;

use common\properties as myp;

final class row_properties extends grid_properties {
	public function init_name(): myp\property_text {
		$description = gettext('Enter group name.');
		$placeholder = gettext('Group Name');
		$property = parent::init_name();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_editableonmodify(false)->
			set_id('id')->
			set_maxlength(16)->
			set_placeholder($placeholder)->
			set_size(18)->
			set_filter(FILTER_CALLBACK)->
			set_filter_options(function($subject) {
//				FreeBSD reference for regular expression: usr.sbin/pw/pw_user.c -> pw_checkname
				$regexp = '/^[0-9A-Za-z\.;\[\]_\{\}][0-9A-Za-z\-\.;\[\]_\{\}]*\$?$/';
				$result = null;
				if(is_string($subject)):
					if(strlen($subject) > 0 && strlen($subject) < 17):
						if(1 === preg_match($regexp,$subject)):
							return $subject;
						endif;
					endif;
				endif;
				return $result;
			});
		return $property;
	}
	public function init_gid(): myp\property_int {
		$caption = gettext('Specify the group numeric id.');
		$description = gettext('The recommended range for the group id is between 1000 and 32000.');
		$placeholder = gettext('1000');
		$property = parent::init_gid();
		$property->
			set_caption($caption)->
			set_defaultvalue('')->
			set_description($description)->
			set_id('id')->
			set_maxlength(10)->
			set_min(0)->
			set_max(2147483647)->
			set_placeholder($placeholder)->
			set_size(12)->
			filter_use_default();
		return $property;
	}
}
