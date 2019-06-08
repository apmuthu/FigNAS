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
namespace services\iscsid;
use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name() {
		$title = gettext('Name');
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title($title);
		return $property;
	}
	final public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_targetname;
	public function init_targetname() {
		$title = gettext('Target Name');
		$property = $this->x_targetname = new myp\property_text($this);
		$property->
			set_name('targetname')->
			set_title($title);
		return $property;
	}
	final public function get_targetname() {
		return $this->x_targetname ?? $this->init_targetname();
	}
	protected $x_targetaddress;
	public function init_targetaddress() {
		$title = gettext('Target Address');
		$property = $this->x_targetaddress = new myp\property_text($this);
		$property->
			set_name('targetaddress')->
			set_title($title);
		return $property;
	}
	final public function get_targetaddress() {
		return $this->x_targetaddress ?? $this->init_targetaddress();
	}
	protected $x_initiatorname;
	public function init_initiatorname() {
		$title = gettext('Initiator Name');
		$property = $this->x_initiatorname = new myp\property_text($this);
		$property->
			set_name('initiatorname')->
			set_title($title);
		return $property;
	}
	final public function get_initiatorname() {
		return $this->x_initiatorname ?? $this->init_initiatorname();
	}
	protected $x_auxparam;
	public function init_auxparam() {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
	protected $x_authmethod;
	public function init_authmethod() {
		$property = $this->x_authmethod = new myp\property_list($this);
		$title = gettext('Authentication Method');
		$property->
			set_name('authmethod')->
			set_title($title);
		return $property;
	}
	final public function get_authmethod() {
		return $this->x_authmethod ?? $this->init_authmethod();
	}
	protected $x_chapiname;
	public function init_chapiname() {
		$title = gettext('CHAP Name');
		$property = $this->x_chapiname = new myp\property_text($this);
		$property->
			set_name('chapiname')->
			set_title($title);
		return $property;
	}
	final public function get_chapiname() {
		return $this->x_chapiname ?? $this->init_chapiname();
	}
	protected $x_chapsecret;
	public function init_chapsecret() {
		$title = gettext('CHAP Secret');
		$property = $this->x_chapsecret = new myp\property_text($this);
		$property->
			set_name('chapsecret')->
			set_title($title);
		return $property;
	}
	final public function get_chapsecret() {
		return $this->x_chapsecret ?? $this->init_chapsecret();
	}
	protected $x_tgtchapname;
	public function init_tgtchapname() {
		$title = gettext('Mutual CHAP Name');
		$property = $this->x_tgtchapname = new myp\property_text($this);
		$property->
			set_name('tgtchapname')->
			set_title($title);
		return $property;
	}
	final public function get_tgtchapname() {
		return $this->x_tgtchapname ?? $this->init_tgtchapname();
	}
	protected $x_tgtchapsecret;
	public function init_tgtchapsecret() {
		$title = gettext('Mutual CHAP Secret');
		$property = $this->x_tgtchapsecret = new myp\property_text($this);
		$property->
			set_name('tgtchapsecret')->
			set_title($title);
		return $property;
	}
	final public function get_tgtchapsecret() {
		return $this->x_tgtchapsecret ?? $this->init_tgtchapsecret();
	}
	protected $x_headerdigest;
	public function init_headerdigest() {
		$title = gettext('Header Digest');
		$property = $this->x_headerdigest = new myp\property_list($this);
		$property->
			set_name('headerdigest')->
			set_title($title);
		return $property;
	}
	final public function get_headerdigest() {
		return $this->x_headerdigest ?? $this->init_headerdigest();
	}
	protected $x_datadigest;
	public function init_datadigest() {
		$title = gettext('Data Digest');
		$property = $this->x_datadigest = new myp\property_list($this);
		$property->
			set_name('datadigest')->
			set_title($title);
		return $property;
	}
	final public function get_datadigest() {
		return $this->x_datadigest ?? $this->init_datadigest();
	}
	protected $x_protocol;
	public function init_protocol() {
		$title = gettext('Protocol');
		$property = $this->x_protocol = new myp\property_list($this);
		$property->
			set_name('protocol')->
			set_title($title);
		return $property;
	}
	final public function get_protocol() {
		return $this->x_protocol ?? $this->init_protocol();
	}
	protected $x_offload;
	public function init_offload() {
		$title = gettext('Offload Driver');
		$property = $this->x_offload = new myp\property_text($this);
		$property->
			set_name('offload')->
			set_title($title);
		return $property;
	}
	final public function get_offload() {
		return $this->x_offload ?? $this->init_offload();
	}
	protected $x_sessionstate;
	public function init_sessionstate() {
		$title = gettext('Session State');
		$property = $this->x_sessionstate = new myp\property_list($this);
		$property->
			set_name('sessionstate')->
			set_title($title);
		return $property;
	}
	final public function get_sessionstate() {
		return $this->x_sessionstate ?? $this->init_sessionstate();
	}
	protected $x_sessiontype;
	public function init_sessiontype() {
		$title = gettext('Session Type');
		$property = $this->x_sessiontype = new myp\property_list($this);
		$property->
			set_name('sessiontype')->
			set_title($title);
		return $property;
	}
	final public function get_sessiontype() {
		return $this->x_sessiontype ?? $this->init_sessiontype();
	}
}
