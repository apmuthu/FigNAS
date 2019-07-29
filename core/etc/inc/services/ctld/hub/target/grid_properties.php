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
namespace services\ctld\hub\target;
use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name(): myp\property_text {
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('Target Name'));
		return $property;
	}
	final public function get_name(): myp\property_text {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_alias;
	public function init_alias(): myp\property_text {
		$property = $this->x_alias = new myp\property_text($this);
		$property->
			set_name('alias')->
			set_title(gettext('Alias'));
		return $property;
	}
	final public function get_alias(): myp\property_text {
		return $this->x_alias ?? $this->init_alias();
	}
	protected $x_auth_group;
	public function init_auth_group(): myp\property_list {
		$property = $this->x_auth_group = new myp\property_list($this);
		$property->
			set_name('auth_group')->
			set_title(gettext('Auth Group'));
		return $property;
	}
	final public function get_auth_group(): myp\property_list {
		return $this->x_auth_group ?? $this->init_auth_group();
	}
	protected $x_portal_group;
	public function init_portal_group(): myp\property_list {
		$property = $this->x_portal_group = new myp\property_list($this);
		$property->
			set_name('portal_group')->
			set_title(gettext('Portal Group'));
		return $property;
	}
	final public function get_portal_group(): myp\property_list {
		return $this->x_portal_group ?? $this->init_portal_group();
	}
	protected $x_redirect;
	public function init_redirect(): myp\property_ipaddress {
		$property = $this->x_redirect = new myp\property_ipaddress($this);
		$property->
			set_name('redirect')->
			set_title(gettext('Redirect'));
		return $property;
	}
	final public function get_redirect(): myp\property_ipaddress {
		return $this->x_redirect ?? $this->init_redirect();
	}
	protected $x_auxparam;
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
}
