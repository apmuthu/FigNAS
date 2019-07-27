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
namespace system\webgui;
use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_cssfcfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom File Chooser CSS file.');
		$placeholder = '/usr/local/www/css/fc.css';
		$property = parent::init_cssfcfile();
		$property->
			set_id('cssfcfile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssguifile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom GUI CSS file.');
		$placeholder = '/usr/local/www/css/gui.css';
		$property = parent::init_cssguifile();
		$property->
			set_id('cssguifile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssloginfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom Login CSS file.');
		$placeholder = '/usr/local/www/css/login.css';
		$property = parent::init_cssloginfile();
		$property->
			set_id('cssloginfile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssnavbarfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom NavBar CSS file.');
		$placeholder = '/usr/local/www/css/navbar.css';
		$property = parent::init_cssnavbarfile();
		$property->
			set_id('cssnavbarfile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_csstabsfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom Tabs CSS file.');
		$placeholder = '/usr/local/www/css/tabs.css';
		$property = parent::init_csstabsfile();
		$property->
			set_id('csstabsfile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssstylefile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom Quixplorer CSS file.');
		$placeholder = '/usr/local/www/quixplorer/_style/style.css';
		$property = parent::init_cssstylefile();
		$property->
			set_id('cssstylefile')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_enabletogglemode(): myp\property_bool {
		$caption = gettext('Use toggle button instead of enable/disable buttons.');
		$property = parent::init_enabletogglemode();
		$property->
			set_id('enabletogglemode')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_skipviewmode(): myp\property_bool {
		$caption = gettext('Enable this option if you want to edit configuration pages directly without the need to switch to edit mode.');
		$property = parent::init_skipviewmode();
		$property->
			set_id('skipviewmode')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_adddivsubmittodataframe(): myp\property_bool {
		$caption = gettext('Display action buttons in the scrollable area instead of the footer area.');
		$property = parent::init_adddivsubmittodataframe();
		$property->
			set_id('adddivsubmittodataframe')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
