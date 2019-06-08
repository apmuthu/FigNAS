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
namespace services\minidlnad;
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
	protected $x_friendlyname;
	public function init_friendlyname(): myp\property_text {
		$property = $this->x_friendlyname = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('Name'));
		return $property;
	}
	final public function get_friendlyname(): myp\property_text {
		return $this->x_friendlyname ?? $this->init_friendlyname();
	}
	protected $x_loglevel;
	public function init_loglevel(): myp\property_list {
		$property = $this->x_loglevel = new myp\property_list($this);
		$property->
			set_name('loglevel')->
			set_title(gettext('Log Level'));
		return $property;
	}
	final public function get_loglevel(): myp\property_list {
		return $this->x_loglevel ?? $this->init_loglevel();
	}
	protected $x_inotify;
	public function init_inotify(): myp\property_bool {
		$property = $this->x_inotify = new myp\property_bool($this);
		$property->
			set_name('inotify')->
			set_title(gettext('Inotify'));
		return $property;
	}
	final public function get_inotify(): myp\property_bool {
		return $this->x_inotify ?? $this->init_inotify();
	}
	protected $x_notifyinterval;
	public function init_notifyinterval(): myp\property_int {
		$property = $this->x_notifyinterval = new myp\property_int($this);
		$property->
			set_name('notify_int')->
			set_title(gettext('Broadcast Interval'));
		return $property;
	}
	final public function get_notifyinterval(): myp\property_int {
		return $this->x_notifyinterval ?? $this->init_notifyinterval();
	}
	protected $x_interface;
	public function init_interface(): myp\property_list {
		$property = $this->x_interface = new myp\property_list($this);
		$property->
			set_name('if')->
			set_title(gettext('Interface'));
		return $property;
	}
	final function get_interface(): myp\property_list {
		return $this->x_interface ?? $this->init_interface();
	}
	protected $x_port;
	public function init_port(): myp\property_int {
		$property = $this->x_port = new myp\property_int($this);
		$property->
			set_name('port')->
			set_title(gettext('Port'));
		return $property;
	}
	final public function get_port(): myp\property_int {
		return $this->x_port ?? $this->init_port();
	}
	protected $x_strict;
	public function init_strict(): myp\property_bool {
		$property = $this->x_strict = new myp\property_bool($this);
		$property->
			set_name('strict')->
			set_title(gettext('Strict DLNA'));
		return $property;
	}
	final public function get_strict(): myp\property_bool {
		return $this->x_strict ?? $this->init_strict();
	}
	protected $x_enabletivo;
	public function init_enabletivo(): myp\property_bool {
		$property = $this->x_enabletivo = new myp\property_bool($this);
		$property->
			set_name('tivo')->
			set_title(gettext('TiVo Support'));
		return $property;
	}
	final public function get_enabletivo(): myp\property_bool {
		return $this->x_enabletivo ?? $this->init_enabletivo();
	}
	protected $x_home;
	public function init_home(): myp\property_text {
		$property = $this->x_home = new myp\property_text($this);
		$property->
			set_name('home')->
			set_title(gettext('Database Directory'));
		return $property;
	}
	final public function get_home(): myp\property_text {
		return $this->x_home ?? $this->init_home();
	}
	protected $x_widelinks;
	public function init_widelinks(): myp\property_bool {
		$property = $this->x_widelinks = new myp\property_bool($this);
		$property->
			set_name('wide_links')->
			set_title(gettext('Wide Links'));
		return $property;
	}
	final public function get_widelinks(): myp\property_bool {
		return $this->x_widelinks ?? $this->init_widelinks();
	}
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	protected $x_auxparam;
	public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
	protected $x_rootcontainer;
	public function init_rootcontainer(): myp\property_list {
		$property = $this->x_rootcontainer = new myp\property_list($this);
		$property->
			set_name('container')->
			set_title(gettext('Container'));
		return $property;
	}
	final function get_rootcontainer(): myp\property_list {
		return $this->x_rootcontainer ?? $this->init_rootcontainer();
	}
	protected $x_forcesortcriteria;
	public function init_forcesortcriteria(): myp\property_text {
		$property = $this->x_forcesortcriteria = new myp\property_text($this);
		$property->
			set_name('force_sort_criteria')->
			set_title('Sort Criteria');
		return $property;
	}
	final public function get_forcesortcriteria(): myp\property_text {
		return $this->x_forcesortcriteria ?? $this->init_forcesortcriteria();
	}
	protected $x_disablesubtitles;
	public function init_disablesubtitles(): myp\property_bool {
		$property = $this->x_disablesubtitles = new myp\property_bool();
		$property->
			set_name('disablesubtitles')->
			set_title(gtext('Disable Subtitles'));
		return $property;
	}
	final public function get_disablesubtitles(): myp\property_bool {
		return $this->x_disablesubtitles ?? $this->init_disablesubtitles();
	}
}
