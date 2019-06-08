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
namespace services\nfsd\export;
use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_filesystem;
	public function init_filesystem(): myp\property_text {
		$property = $this->x_filesystem = new myp\property_text($this);
		$property->
			set_name('path')->
			set_title(gettext('Path'));
		return $property;
	}
	final public function get_filesystem(): myp\property_text {
		return $this->x_filesystem ?? $this->init_filesystem();
	}
	protected $x_mapall;
	public function init_mapall(): myp\property_list {
		$property = $this->x_mapall = new myp\property_list($this);
		$property->
			set_name('mapall')->
			set_title(gettext('Mapping'));
		return $property;
	}
	final public function get_mapall(): myp\property_list {
		return $this->x_mapall ?? $this->init_mapall();
	}
	protected $x_mapalltouser;
	public function init_mapalltouser() {
		$property = $this->x_mapalltouser = new myp\property_list($this);
		$property->
			set_name('mapalltouser')->
			set_title(gettext('Map All To User'));
		return $property;
	}
	final public function get_mapalltouser() {
		return $this->x_mapalltouser ?? $this->init_mapalltouser();
	}
	protected $x_maproottouser;
	public function init_maproottouser() {
		$property = $this->x_maproottouser = new myp\property_list($this);
		$property->
			set_name('maproottouser')->
			set_title(gettext('Map Root To User'));
		return $property;
	}
	final public function get_maproottouser() {
		return $this->x_maproottouser ?? $this->init_maproottouser();
	}
	protected $x_client;
	public function init_client(): myp\property_cidr {
//		$property = $this->x_client = new myp\property_text($this);
		$property = $this->x_client = new myp\property_cidr($this);
		$property->
			set_name('network')->
			set_title(gettext('Network'));
		return $property;
	}
	final public function get_client(): myp\property_cidr {
		return $this->x_client ?? $this->init_client();
	}
	protected $x_opt_alldirs;
	public function init_opt_alldirs(): myp\property_bool {
		$property = $this->x_opt_alldirs = new myp\property_bool($this);
		$property->
			set_name('opt_alldirs')->
			set_title(gettext('Export Subfolders'));
		return $property;
	}
	final public function get_opt_alldirs(): myp\property_bool {
		return $this->x_opt_alldirs ?? $this->init_opt_alldirs();
	}
	protected $x_opt_public;
	public function init_opt_public(): myp\property_bool {
		$property = $this->x_opt_public = new myp\property_bool($this);
		$property->
			set_name('opt_public')->
			set_title(gettext('WebNFS'));
		return $property;
	}
	final public function get_opt_public(): myp\property_bool {
		return $this->x_opt_public ?? $this->init_opt_public();
	}
	protected $x_opt_quiet;
	public function init_opt_quiet(): myp\property_bool {
		$property = $this->x_opt_quiet = new myp\property_bool($this);
		$property->
			set_name('opt_quiet')->
			set_title(gettext('Be Quiet'));
		return $property;
	}
	final public function get_opt_quiet(): myp\property_bool {
		return $this->x_opt_quiet ?? $this->init_opt_quiet();
	}
	protected $x_opt_readonly;
	public function init_opt_readonly(): myp\property_bool {
		$property = $this->x_opt_readonly = new myp\property_bool($this);
		$property->
			set_name('opt_readonly')->
			set_title(gettext('Read Only'));
		return $property;
	}
	final public function get_opt_readonly(): myp\property_bool {
		return $this->x_opt_readonly ?? $this->init_opt_readonly();
	}
	protected $x_opt_sec;
	public function init_opt_sec(): myp\property_list_multi {
		$property = $this->x_opt_sec = new myp\property_list_multi($this);
		$property->
			set_name('param')->
			set_title(gettext('Security Flavor'));
		return $property;
	}
	final public function get_opt_sec(): myp\property_list_multi {
		return $this->x_opt_sec ?? $this->init_opt_sec();
	}
}
