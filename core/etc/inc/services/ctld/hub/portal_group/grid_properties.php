<?php
/*
	grid_properties.php

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
namespace services\ctld\hub\portal_group;
use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name() {
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('Portal Group Name'));
		return $property;
	}
	final public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_discovery_auth_group;
	public function init_discovery_auth_group() {
		$property = $this->x_discovery_auth_group = new myp\property_list($this);
		$property->
			set_name('discovery_auth_group')->
			set_title(gettext('Discovery Auth Group'));
		return $property;
	}
	final public function get_discovery_auth_group() {
		return $this->x_discovery_auth_group ?? $this->init_discovery_auth_group();
	}
	protected $x_discovery_filter;
	public function init_discovery_filter() {
		$property = $this->x_discovery_filter = new myp\property_list($this);
		$property->
			set_name('discovery_filter')->
			set_title(gettext('Discovery Filter'));
		return $property;
	}
	final public function get_discovery_filter() {
		return $this->x_discovery_filter ?? $this->init_discovery_filter();
	}
	protected $x_offload;
	public function init_offload() {
		$property = $this->x_offload = new myp\property_text($this);
		$property->
			set_name('offload')->
			set_title(gettext('Offload'));
		return $property;
	}
	final public function get_offload() {
		return $this->x_offload ?? $this->init_offload();
	}
	protected $x_redirect;
	public function init_redirect() {
		$property = $this->x_redirect = new myp\property_ipaddress($this);
		$property->
			set_name('redirect')->
			set_title(gettext('Redirect'));
		return $property;
	}
	final public function get_redirect() {
		return $this->x_redirect ?? $this->init_redirect();
	}
	protected $x_tag;
	public function init_tag() {
		$property = $this->x_tag = new myp\property_int($this);
		$property->
			set_name('tag')->
			set_title(gettext('Tag'));
		return $property;
	}
	final public function get_tag() {
		return $this->x_tag ?? $this->init_tag();
	}
	protected $x_foreign;
	public function init_foreign() {
		$property = $this->x_foreign = new myp\property_bool($this);
		$property->
			set_name('foreign')->
			set_title(gettext('Foreign'));
		return $property;
	}
	final public function get_foreign() {
		return $this->x_foreign ?? $this->init_foreign();
	}
	protected $x_auxparam;
	public function init_auxparam() {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
}
