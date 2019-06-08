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
namespace services\tftpd;
use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_allowfilecreation;
	public function init_allowfilecreation() {
		$property = $this->x_allowfilecreation = new myp\property_bool($this);
		$property->
			set_name('allowfilecreation')->
			set_title(gettext('Allow New Files'));
		return $property;
	}
	final public function get_allowfilecreation() {
		return $this->x_allowfilecreation ?? $this->init_allowfilecreation();
	}
	protected $x_dir;
	public function init_dir() {
		$property = $this->x_dir = new myp\property_text($this);
		$property->
			set_name('dir')->
			set_title(gettext('Directory'));
		return $property;
	}
	final public function get_dir() {
		return $this->x_dir ?? $this->init_dir();
	}
	protected $x_enable;
	public function init_enable() {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable() {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_extraoptions;
	public function init_extraoptions() {
		$property = $this->x_extraoptions = new myp\property_text($this);
		$property->
			set_name('extraoptions')->
			set_title(gettext('Extra Options'));
		return $property;
	}
	final public function get_extraoptions() {
		return $this->x_extraoptions ?? $this->init_extraoptions();
	}
	protected $x_maxblocksize;
	public function init_maxblocksize() {
		$property = $this->x_maxblocksize = new myp\property_int($this);
		$property->
			set_name('maxblocksize')->
			set_title(gettext('Max. Block Size'));
		return $property;
	}
	final public function get_maxblocksize() {
		return $this->x_maxblocksize ?? $this->init_maxblocksize();
	}
	protected $x_port;
	public function init_port() {
		$property = $this->x_port = new myp\property_int($this);
		$property->
			set_name('port')->
			set_title(gettext('Port'));
		return $property;
	}
	final public function get_port() {
		return $this->x_port ?? $this->init_port();
	}
	protected $x_timeout;
	public function init_timeout() {
		$property = $this->x_timeout = new myp\property_int($this);
		$property->
			set_name('timeout')->
			set_title(gettext('Timeout'));
		return $property;
	}
	final public function get_timeout() {
		return $this->x_timeout ?? $this->init_timeout();
	}
	protected $x_umask;
	public function init_umask() {
		$property = $this->x_umask = new myp\property_text($this);
		$property->
			set_name('umask')->
			set_title(gettext('Umask'));
		return $property;
	}
	final public function get_umask() {
		return $this->x_umask ?? $this->init_umask();
	}
	protected $x_username;
	public function init_username() {
		$property = $this->x_username = new myp\property_list($this);
		$property->
			set_name('username')->
			set_title(gettext('Username'));
		return $property;
	}
	final public function get_username() {
		return $this->x_username ?? $this->init_username();
	}
}
