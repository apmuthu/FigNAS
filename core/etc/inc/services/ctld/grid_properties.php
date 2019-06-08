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
namespace services\ctld;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_debug;
	public function init_debug(): myp\property_int {
		$property = $this->x_debug = new myp\property_int($this);
		$property->
			set_name('debug')->
			set_title(gettext('Debug Level'));
		return $property;
	}
	final public function get_debug(): myp\property_int {
		return $this->x_debug ?? $this->init_debug();
	}
	protected $x_maxproc;
	public function init_maxproc(): myp\property_int {
		$property = $this->x_maxproc = new myp\property_int($this);
		$property->
			set_name('maxproc')->
			set_title(gettext('Max Processes'));
		return $property;
	}
	final public function get_maxproc(): myp\property_int {
		return $this->x_maxproc ?? $this->init_maxproc();
	}
	protected $x_timeout;
	public function init_timeout(): myp\property_int {
		$property = $this->x_timeout = new myp\property_int($this);
		$property->
			set_name('timeout')->
			set_title(gettext('Timeout'));
		return $property;
	}
	final public function get_timeout(): myp\property_int {
		return $this->x_timeout ?? $this->init_timeout();
	}
	protected $x_isns_period;
	public function init_isns_period(): myp\property_int {
		$property = $this->x_isns_period = new myp\property_int($this);
		$property->
			set_name('isns_period')->
			set_title(gettext('iSNS Period'));
		return $property;
	}
	final public function get_isns_period(): myp\property_int {
		return $this->x_isns_period ?? $this->init_isns_period();
	}
	protected $x_isns_timeout;
	public function init_isns_timeout(): myp\property_int {
		$property = $this->x_isns_timeout = new myp\property_int($this);
		$property->
			set_name('isns_timeout')->
			set_title(gettext('iSNS Timeout'));
		return $property;
	}
	final public function get_isns_timeout(): myp\property_int {
		return $this->x_isns_timeout ?? $this->init_isns_timeout();
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
