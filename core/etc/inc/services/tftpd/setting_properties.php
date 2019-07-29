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
namespace services\tftpd;
use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_allowfilecreation(): myp\property_bool {
		$caption = gettext('Allow new files to be created.');
		$description = gettext('By default, only already existing files can be uploaded.');
		$property = parent::init_allowfilecreation();
		$property->
			set_id('allowfilecreation')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default();
		return $property;
	}
	public function init_dir(): myp\property_text {
		global $g;

		$description = gettext('The directory containing the files you want to publish. The remote host does not need to pass along the directory as part of the transfer.');
		$placeholder = gettext('Enter a directory');
		$property = parent::init_dir();
		$property->
			set_id('dir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue($g['media_path'])->
			set_size(60)->
			set_maxlength(4096)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => $g['media_path']]);
		return $property;
	}
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_extraoptions(): myp\property_text {
		$description = gettext('Extra options (usually empty).');
		$placeholder = gettext('Enter extra options');
		$property = parent::init_extraoptions();
		$property->
			set_id('extraoptions')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(4096)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => '']);
		return $property;
	}
	public function init_maxblocksize(): myp\property_int {
		$description = gettext('Specifies the maximum permitted block size. The permitted range for this parameter is from 512 to 65464.');
		$placeholderv =$placeholder = gettext('16384');
		$property = parent::init_maxblocksize();
		$property->
			set_id('maxblocksize')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_defaultvalue('')->
			set_size(10)->
			set_maxlength(5)->
			set_min(512)->
			set_max(65464)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_port(): myp\property_int {
		$caption = gettext('Port of the TFTP service. Leave blank to use the default port.');
		$description = gettext('Enter a custom port number if you do not want to use default port 69.');
		$placeholder = gettext('69');
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
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default()->
			filter_use_empty()->
			set_filter(FILTER_VALIDATE_INT,'69')->
			set_filter_flags(FILTER_REQUIRE_SCALAR,'69')->
			set_filter_options(['default' => null,'min_range' => 69,'max_range' => 69],'69')->
			set_filter_group('ui',['empty','ui','69']);
		return $property;
	}
	public function init_timeout(): myp\property_int {
		$description = gettext('Determine the default timeout, in microseconds, before the first packet is retransmitted. The default is 1000000 (1 second).');
		$placeholder = gettext('10000');
		$property = parent::init_timeout();
		$property->
			set_id('timeout')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			set_size(10)->
			set_maxlength(9)->
			set_min(10000)->
			set_max(255000000)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default_or_empty()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Timeout must be a number between 10000 and 255000000.')));
		return $property;
	}
	public function init_umask(): myp\property_text {
		$description = gettext('Sets the umask for newly created files. The default is 000 (anyone can read or write).');
		$placeholder = gettext('000');
		$property = parent::init_umask();
		$property->
			set_id('umask')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			set_size(4)->
			set_maxlength(3)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => '/^(|[0-7]{3})$/']);
		return $property;
	}
	public function init_username(): myp\property_list {
		$caption = gettext('Specifies the username under which the TFTP service should run.');
		$property = parent::init_username();
		$property->
			set_id('username')->
			set_caption($caption)->
			set_defaultvalue('nobody')->
			filter_use_default();
		return $property;
	}
}
