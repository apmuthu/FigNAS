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
namespace services\minidlnad;
use common\properties as myp;

final class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->
			set_defaultvalue(false);
		return $property;
	}
	public function init_friendlyname(): myp\property_text {
		$description = gettext('Media Server Name.');
		$placeholder = gettext('Name');
		$property = parent::init_friendlyname();
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(128)->
			filter_use_default();
		return $property;
	}
	public function init_loglevel(): myp\property_list {
		$description = gettext('Verbosity of information that is logged.');
		$options = [
			'off' => gettext('Off'),
			'fatal' => gettext('Fatal'),
			'error' => gettext('Error'),
			'warn' => gettext('Warning'),
			'info' => gettext('Info'),
			'debug' => gettext('Debug')
		];
		$property = parent::init_loglevel();
		$property->
			set_id('loglevel')->
			set_description($description)->
			set_defaultvalue('info')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_inotify(): myp\property_bool {
		$caption = gettext('Enable inotify.');
		$description = gettext('Use inotify monitoring to automatically discover new media files.');
		$property = parent::init_inotify();
		$property->
			set_id('inotify')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_notifyinterval(): myp\property_int {
		$description = gettext('Broadcasts its availability every configured seconds on the network, the default is 300 seconds.');
		$placeholder = gettext('300');
		$property = parent::init_notifyinterval();
		$property->
			set_id('notify_int')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_maxlength(5)->
			set_size(7)->
			set_defaultvalue('300')->
			set_min(5)->
			set_max(86400)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_interface(): myp\property_list {
		$description = gettext('Select which interface to use.');
		$property = parent::init_interface();
		$property->
			set_id('if')->
			set_description($description)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_port(): myp\property_int {
		$description = gettext('Port to listen on. Port number must be between 1024 and 65535. Default port is 8200.');
		$placeholder = gettext('8200');
		$property = parent::init_port();
		$property->
			set_id('port')->
			set_description($description)->
			set_defaultvalue('8200')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_size(10)->
			set_maxlength(5)->
			set_min(1024)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_strict(): myp\property_bool {
		$caption = gettext('Enable to strictly adhere to DLNA standards.');
		$description = gettext('This will allow server-side downscaling of very large JPEG images, it can impact JPEG serving performance on some DLNA devices.');
		$property = parent::init_strict();
		$property->
			set_id('strict')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_enabletivo(): myp\property_bool {
		$caption = gettext('Enable TiVo support.');
		$description = gettext('This will support streaming .jpg and .mp3 files to a TiVo supporting HMO.');
		$property = parent::init_enabletivo();
		$property->
			set_id('tivo')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_home(): myp\property_text {
		global $g;

		$description = gettext('Location of the media content database.');
		$placeholder = gettext('Path');
		$property = parent::init_home();
		$property->
			set_id('home')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue($g['media_path'])->
			filter_use_default();
		return $property;
	}
	public function init_widelinks(): myp\property_bool {
		$caption = gettext('Allow symlinks that point outside defined media folders.');
		$property = parent::init_widelinks();
		$property->
			set_id('wide_links')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to minidlna.conf');
		$property = parent::init_auxparam();
		$property->
			set_description($description);
		return $property;
	}
	public function init_rootcontainer(): myp\property_list {
		$description = gettext('Select a container as the root of the tree exposed to clients.');
		$options = [
			'.' => gettext('Use the standard container (this is the default)'),
			'B' => gettext('Use the "Browse Directory" container'),
			'M' => gettext('Use the "Music" container'),
			'V' => gettext('Use the "Video" container'),
			'P' => gettext('Use the "Pictures" container')
		];
		$property = parent::init_rootcontainer();
		$property->
			set_id('container')->
			set_description($description)->
			set_defaultvalue('.')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_forcesortcriteria(): myp\property_text {
		$description = gettext('Overwrite the sort criteria passed by the client.');
		$placeholder = gettext('+upnp:class,+upnp:originalTrackNumber,+dc:title');
		 $property = parent::init_forcesortcriteria();
		$property->
			set_id('force_sort_criteria')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_maxlength(256)->
			set_size(60)->
			filter_use_default_or_empty();
	 return $property;
	}
	public function init_disablesubtitles(): myp\property_bool {
		$caption = gettext('Disable subtitle support on unknown clients.');
		$property = parent::init_disablesubtitles();
		$property->
			set_id('disablesubtitles')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
