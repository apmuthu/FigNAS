<?php
/*
	properties_disks_zfs_volume.php

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

class properties_disks_zfs_volume extends co_property_container {
	protected $x_uuid;
	protected $x_enabled;
	protected $x_protected;
	protected $x_description;
	protected $x_toolbox;

	protected $x_checksum;
	protected $x_compression;
	protected $x_dedup;
	protected $x_logbias;
	protected $x_name;
	protected $x_pool;
	protected $x_primarycache;
	protected $x_secondarycache;
	protected $x_sparse;
	protected $x_sync;
	protected $x_volblocksize;
	protected $x_volmode;
	protected $x_volsize;

	public function get_uuid() {
		return $this->x_uuid ?? $this->init_uuid();
	}
	public function init_uuid() {
		$property = $this->x_uuid = new property_uuid($this);
		return $property;
	}
	public function get_enabled() {
		return $this->x_enabled ?? $this->init_enabled();
	}
	public function init_enabled() {
		$property = $this->x_enabled = new property_bool($this);
		$property->
			set_title(gettext('Enabled'))->
			set_name('enabled');
		return $property;
	}
	public function get_protected() {
		return $this->x_protected ?? $this->init_protected();
	}
	public function init_protected() {
		$property = $this->x_protected = new property_bool($this);
		$property->
			set_title(gettext('Protected'))->
			set_name('protected');
		return $property;
	}
	public function get_description() {
		return $this->x_description ?? $this->init_description();
	}
	public function init_description() {
		$property = $this->x_description = new property_text($this);
		$property->
			set_title(gettext('Description'))->
			set_name('desc');
		return $property;
	}
	public function get_toolbox() {
		return $this->x_toolbox ?? $this->init_toolbox();
	}
	public function init_toolbox() {
		$property = $this->x_toolbox = new property_toolbox($this);
		return $property;
	}
	public function get_checksum() {
		return $this->x_checksum ?? $this->init_checksum();
	}
	public function init_checksum() {
		$property = $this->x_checksum = new property_list($this);
		$property->
			set_title(gettext('Checksum'))->
			set_name('checksum');
		return $property;
	}
	public function get_compression() {
		return $this->x_compression ?? $this->init_compression();
	}
	public function init_compression() {
		$property = $this->x_compression = new property_list($this);
		$property->
			set_title(gettext('Compression'))->
			set_name('compression');
		return $property;
	}
	public function get_dedup() {
		return $this->x_dedup ?? $this->init_dedup();
	}
	public function init_dedup() {
		$property = $this->x_dedup = new property_list($this);
		$property->
			set_title(gettext('Dedup Method'))->
			set_name('dedup');
		return $property;
	}
	public function get_logbias() {
		return $this->x_logbias ?? $this->init_logbias();
	}
	public function init_logbias() {
		$property = $this->x_logbias = new property_list($this);
		$property->
			set_title(gettext('Logbias'))->
			set_name('logbias');
		return $property;
	}
	public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	public function init_name() {
		$property = $this->x_name = new property_text($this);
		$property->
			set_title(gettext('Name'))->
			set_name('name');
		return $property;
	}
	public function get_pool() {
		return $this->x_pool ?? $this->init_pool();
	}
	public function init_pool() {
		$property = $this->x_pool = new property_list($this);
		$property->
			set_title(gettext('Pool'))->
			set_name('pool');
		return $property;
	}
	public function get_primarycache() {
		return $this->x_primarycache ?? $this->init_primarycache();
	}
	public function init_primarycache() {
		$property = $this->x_primarycache = new property_list($this);
		$property->
			set_title(gettext('Primary Cache'))->
			set_name('primarycache');
		return $property;
	}
	public function get_secondarycache() {
		return $this->x_secondarycache ?? $this->init_secondarycache();
	}
	public function init_secondarycache() {
		$property = $this->x_secondarycache = new property_list($this);
		$property->
			set_title(gettext('Secondary Cache'))->
			set_name('secondarycache');
		return $property;
	}
	public function get_sparse() {
		return $this->x_sparse ?? $this->init_sparse();
	}
	public function init_sparse() {
		$property = $this->x_sparse = new property_bool();
		$property->
			set_title(gettext('Sparse'))->
			set_name('sparse');
		return $property;
	}
	public function get_sync() {
		return $this->x_sync ?? $this->init_sync();
	}
	public function init_sync() {
		$property = $this->x_sync = new property_list($this);
		$property->
			set_title(gettext('Sync'))->
			set_name('sync');
		return $property;
	}
	public function get_volblocksize() {
		return $this->x_volblocksize ?? $this->init_volblocksize();
	}
	public function init_volblocksize() {
		$property = $this->x_volblocksize = new property_list($this);
		$property->
			set_title(gettext('Block Size'))->
			set_name('volblocksize');
		return $property;
	}
	public function get_volmode() {
		return $this->x_volmode ?? $this->init_volmode($this);
	}
	public function init_volmode() {
		$property = $this->x_volmode = new property_list($this);
		$property->
			set_title(gettext('Volume Mode'))->
			set_name('volmode');
		return $property;
	}
	public function get_volsize() {
		return $this->x_volsize ?? $this->init_volsize();
	}
	public function init_volsize() {
		$property = $this->x_volsize = new property_text($this);
		$property->
			set_title(gettext('Size'))->
			set_name('volsize');
		return $property;
	}
}
