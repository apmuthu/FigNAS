<?php
/*
	properties_services_websrv_webdav.php

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
require_once 'properties.php';

class websrv_webdav_properties extends co_property_container_param {
	protected $x_name;
	public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	public function init_name() {
		$property = $this->x_name = new property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('WebDAV Name'));
		return $property;
	}
	protected $x_folderpattern;
	public function get_folderpattern() {
		return $this->x_folderpattern ?? $this->init_folderpattern();
	}
	public function init_folderpattern() {
		$property = $this->x_folderpattern = new property_text($this);
		$property->
			set_name('folderpattern')->
			set_title(gettext('Folder Pattern'));
		return $property;
	}
	protected $x_isreadonly;
	public function get_isreadonly() {
		return $this->x_isreadonly ?? $this->init_isreadonly();
	}
	public function init_isreadonly() {
		$property = $this->x_isreadonly = new property_bool($this);
		$property->
			set_name('isreadonly')->
			set_title(gettext('Read-Only'));
		return $property;
	}
	protected $x_usesqlite;
	public function get_usesqlite() {
		return $this->x_usesqlite ?? $this->init_usesqlite();
	}
	public function init_usesqlite() {
		$property = $this->x_usesqlite = new property_bool($this);
		$property->
			set_name('usesqlite')->
			set_title(gettext('Use Sqlite'));
		return $property;
	}
	protected $x_auxparam;
	public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
	public function init_auxparam() {
		$property = $this->x_auxparam = new property_textarea($this);
		$property->
			set_name('auxparam')->
			set_title(gettext('Additional Parameters'));
		return $property;
	}
}
class websrv_webdav_edit_properties extends websrv_webdav_properties {
	public function init_name() {
		$property = parent::init_name();
		$description = gettext('Name of the WebDAV Record.');
		$placeholder = gettext('WebDAV Name');
		$regexp = '/^\S.{0,222}$/';
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(223)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_folderpattern() {
		$property = parent::init_folderpattern();
		$description = gettext('The WebDAV configuration is applied to all folders matching this pattern.');
		$placeholder = gettext('Folder Pattern');
		$regexp = '~^[^"\s]{1,223}$~';
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(223)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_isreadonly() {
		$property = parent::init_isreadonly();
		$caption = gettext('Read-Only Access.');
		$property->
			set_id('isreadonly')->
			set_caption($caption)->
			set_description('')->
			set_defaultvalue(true)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_usesqlite() {
		$property = parent::init_usesqlite();
		$caption = gettext('Use Sqlite for lockings and properties.');
		$property->
			set_id('usesqlite')->
			set_caption($caption)->
			set_description('')->
			set_defaultvalue(false)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_auxparam() {
		$property = parent::init_auxparam();
		$description = gettext('These parameters will be added to this WebDAV configuration.');
		$placeholder = gettext('Enter additional parameters');
		$property->
			set_id('auxparam')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_defaultvalue('')->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
}
