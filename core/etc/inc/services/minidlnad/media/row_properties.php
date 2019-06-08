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
namespace services\minidlnad\media;

use common\properties as myp;

final class row_properties extends grid_properties {
	public function init_filesystem(): myp\property_text {
		global $g;

		$description = gettext('Path to be included.');
		$placeholder = $placeholderv = gettext('Path');
		$property = parent::init_filesystem();
		$property->
			set_defaultvalue($g['media_path'])->
			set_description($description)->
			set_id('path')->
			set_maxlength(128)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default();
		return $property;
	}
	public function init_type(): myp\property_list {
		$description = gettext('Restrict folder to a specific media type.');
		$options = [
			'' => gettext('Scan folder for all media types.'),
			'A' => gettext('Scan folder for audio files.'),
			'V' => gettext('Scan folder for video files.'),
			'P' => gettext('Scan folder for image files.'),
			'PV' => gettext('Scan folder for video and image files.')
		];
		$property = parent::init_type();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('type')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
}
