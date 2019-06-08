<?php
/*
	grid_properties.php

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
namespace system\access\user;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name(): myp\property_text {
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('login')->
			set_title(gettext('Login Name'));
		return $property;
	}
	final public function get_name(): myp\property_text {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_fullname;
	public function init_fullname(): myp\property_text {
		$property = $this->x_fullname = new myp\property_text($this);
		$property->
			set_name('fullname')->
			set_title(gettext('Full Name'));
		return $property;
	}
	final public function get_fullname(): myp\property_text {
		return $this->x_fullname ?? $this->init_fullname();
	}
	protected $x_uid;
	public function init_uid(): myp\property_int {
		$property = $this->x_uid = new myp\property_int($this);
		$property->
			set_name('id')->
			set_title(gettext('User ID'));
		return $property;
	}
	final public function get_uid(): myp\property_int {
		return $this->x_uid ?? $this->init_uid();
	}
	protected $x_password;
	public function init_password(): myp\property_text {
		$property = $this->x_password = new myp\property_text($this);
		$property->
			set_name('password')->
			set_title(gettext('Password'));
		return $property;
	}
	final public function get_password(): myp\property_text {
		return $this->x_password ?? $this->init_password();
	}
	protected $x_passwordsha;
	public function init_passwordsha(): myp\property_text {
		$property = $this->x_passwordsha = new myp\property_text($this);
		$property->
			set_name('passwordsha')->
			set_title(gettext('SHA Password'));
		return $property;
	}
	final public function get_passwordsha(): myp\property_text {
		return $this->x_passwordsha ?? $this->init_passwordsha();
	}
	protected $x_passwordmd4;
	public function init_passwordmd4(): myp\property_text {
		$property = $this->x_passwordmd4 = new myp\property_text($this);
		$property->
			set_name('passwordmd4')->
			set_title(gettext('MD4 Password'));
		return $property;
	}
	final public function get_passwordmd4(): myp\property_text {
		return $this->x_passwordmd4 ?? $this->init_passwordmd4();
	}
	protected $x_usershell;
	public function init_usershell(): myp\property_list {
		$property = $this->x_usershell = new myp\property_list($this);
		$property->
			set_name('shell')->
			set_title(gettext('Shell'));
		return $property;
	}
	final public function get_usershell(): myp\property_list {
		return $this->x_usershell ?? $this->init_usershell();
	}
	protected $x_primary_group;
	public function init_primary_group(): myp\property_list {
		$property = $this->x_primary_group = new myp\property_list($this);
		$property->
			set_name('primarygroup')->
			set_title(gettext('Primary Group'));
		return $property;
	}
	final public function get_primary_group(): myp\property_list {
		return $this->x_primary_group ?? $this->init_primary_group();
	}
	protected $x_additional_groups;
	public function init_additional_groups(): myp\property_list_multi {
		$property = $this->x_additional_groups = new myp\property_list_multi($this);
		$property->
			set_name('group')->
			set_title(gettext('Groups'));
		return $property;
	}
	final public function get_additional_groups(): myp\property_list_multi {
		return $this->x_additional_groups ?? $this->init_additional_groups();
	}
	protected $x_homedir;
	public function init_homedir(): myp\property_text {
		$property = $this->x_homedir = new myp\property_text($this);
		$property->
			set_name('homedir')->
			set_title(gettext('Home Directory'));
		return $property;
	}
	final public function get_homedir(): myp\property_text {
		return $this->x_homedir ?? $this->init_homedir();
	}
	protected $x_user_portal_access;
	public function init_user_portal_access(): myp\property_bool {
		$property = $this->x_user_portal_access = new myp\property_bool();
		$property->
			set_name('user_portal_access')->
			set_title(gettext('User Portal'));
		return $property;
	}
	final public function get_user_portal_access(): myp\property_bool {
		return $this->x_user_portal_access ?? $this->init_user_portal_access();
	}
}
