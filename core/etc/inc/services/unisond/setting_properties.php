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
namespace services\unisond;

use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_workdir(): myp\property_text {
		global $g;

		$description = gettext('Location where work files will be stored.');
		$placeholder = gettext('Enter a directory');
		$property = parent::init_workdir();
		$property->
			set_id('workdir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue($g['media_path'])->
			set_size(60)->
			set_maxlength(4096)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => $g['media_path']]);
		return $property;
	}
	public function init_mkdir(): myp\property_bool {
		$caption = gettext("Create work directory if it doesn't exist.");
		$property = parent::init_mkdir();
		$property->
			set_caption($caption)->
			set_defaultvalue(false)->
			set_id('mkdir')->
			filter_use_default();
		return $property;
	}
}
