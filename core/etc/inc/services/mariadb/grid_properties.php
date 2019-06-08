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
namespace services\mariadb;
use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable() {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable() {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_homedir;
	public function init_homedir() {
		$property = $this->x_homedir = new myp\property_text($this);
		$property->
			set_name('homedir')->
			set_title(gettext('Home Directory'));
		return $property;
	}
	final public function get_homedir() {
		return $this->x_homedir ?? $this->init_homedir();
	}
	protected $x_auxparam;
	public function init_auxparam() {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
	protected $x_phrasecookieauth;
	public function init_phrasecookieauth() {
		$property = $this->x_phrasecookieauth = new myp\property_text($this);
		$property->
			set_name('phrasecookieauth')->
			set_title(gettext('Passphrase'));
		return $property;
	}
	final public function get_phrasecookieauth() {
		return $this->x_phrasecookieauth ?? $this->init_phrasecookieauth();
	}
}
