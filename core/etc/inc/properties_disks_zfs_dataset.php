<?php
/*
	properties_disks_zfs_dataset.php

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

class properties_disks_zfs_dataset extends co_property_container {
	protected $x_uuid;
	protected $x_enabled;
	protected $x_protected;
	protected $x_toolbox;

	protected $x_aclinherit;
	protected $x_aclmode;
	protected $x_atime;
	protected $x_available;
	protected $x_canmount;
	protected $x_casesensitivity;
	protected $x_checksum;
	protected $x_compression;
	protected $x_copies;
	protected $x_dedup;
	protected $x_description;
	protected $x_logbias;
	protected $x_name;
	protected $x_normalization;
	protected $x_pool;
	protected $x_primarycache;
	protected $x_quota;
	protected $x_readonly;
	protected $x_redundant_metadata;
	protected $x_refquota;
	protected $x_refreservation;
	protected $x_reservation;
	protected $x_secondarycache;
	protected $x_setuid;
	protected $x_snapdir;
	protected $x_sync;
	protected $x_type;
	protected $x_utf8only;

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
	public function get_toolbox() {
		return $this->x_toolbox ?? $this->init_toolbox();
	}
	public function init_toolbox() {
		$property = $this->x_toolbox = new property_toolbox($this);
		return $property;
	}
	public function get_aclinherit() {
		return $this->x_aclinherit ?? $this->init_aclinherit();
	}
	public function init_aclinherit() {
		$property = $this->x_aclinherit = new property_list($this);
		$property->
			set_title(gettext('ACL Inherit'))->
			set_name('aclinherit');
		return $property;
	}
	public function get_aclmode() {
		return $this->x_aclmode ?? $this->init_aclmode();
	}
	public function init_aclmode() {
		$property = $this->x_aclmode = new property_list($this);
		$property->
			set_title(gettext('ACL Mode'))->
			set_name('aclmode');
		return $property;
	}
	public function get_atime() {
		return $this->x_atime ?? $this->init_atime();
	}
	public function init_atime() {
		$property = $this->x_atime = new property_list($this);
		$property->
			set_title(gettext('Access Time'))->
			set_name('atime');
		return $property;
	}
	public function get_available() {
		return $this->x_available ?? $this->init_available();
	}
	public function init_available() {
		$property = $this->x_available = new property_text($this);
		$property->
			set_title(gettext('Available'))->
			set_name('avail');
		return $property;
	}
	public function get_canmount() {
		return $this->x_canmount ?? $this->init_canmount();
	}
	public function init_canmount() {
		$property = $this->x_canmount = new property_list($this);
		$property->
			set_title(gettext('Can Mount'))->
			set_name('canmount');
		return $property;
	}
	public function get_casesensitivity() {
		return $this->x_casesensitivity ?? $this->init_casesensitivity();
	}
	public function init_casesensitivity() {
		$property = $this->x_casesensitivity = new property_list($this);
		$property->
			set_title(gettext('Case Sensitivity'))->
			set_name('casesensitivity');
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
	public function get_copies() {
		return $this->x_copies ?? $this->init_copies();
	}
	public function init_copies() {
		$property = $this->x_copies = new property_list($this);
		$property->
			set_title(gettext('Copies'))->
			set_name('copies');
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
	public function get_normalization() {
		return $this->x_normalization ?? $this->init_normalization();
	}
	public function init_normalization() {
		$property = $this->x_normalization = new property_list($this);
		$property->
			set_title(gettext('Normalization'))->
			set_name('normalization');
		return $property;
	}
	public function get_pool() {
		return $this->x_pool ?? $this->init_pool();
	}
	public function init_pool() {
		$property = $this->x_pool = new property_text($this);
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
	public function get_quota() {
		return $this->x_quota ?? $this->init_quota();
	}
	public function init_quota() {
		$property = $this->x_quota = new property_text($this);
		$property->
			set_title(gettext('Quota'))->
			set_name('quota');
		return $property;
	}
	public function get_readonly() {
		return $this->x_readonly ?? $this->init_readonly();
	}
	public function init_readonly() {
		$property = $this->x_readonly = new property_list($this);
		$property->
			set_title(gettext('Read Only'))->
			set_name('readonly');
		return $property;
	}
	public function get_redundant_metadata() {
		return $this->x_redundant_metadata ?? $this->init_redundant_metadata();
	}
	public function init_redundant_metadata() {
		$property = $this->x_redundant_metadata = new property_list($this);
		$property->
			set_title(gettext('Redundant Metadata'))->
			set_name('redundant_metadata');
		return $property;
	}
	public function get_refquota() {
		return $this->x_refquota ?? $this->init_refquota();
	}
	public function init_refquota() {
		$property = $this->x_refquota = new property_text($this);
		$property->
			set_title(gettext('Refquota'))->
			set_name('refquota');
		return $property;
	}
	public function get_refreservation() {
		return $this->x_refreservation ?? $this->init_refreservation();
	}
	public function init_refreservation() {
		$property = $this->x_refreservation = new property_text($this);
		$property->
			set_title(gettext('Refreservation'))->
			set_name('refreservation');
		return $property;
	}
	public function get_reservation() {
		return $this->x_reservation ?? $this->init_reservation();
	}
	public function init_reservation() {
		$property = $this->x_reservation = new property_text($this);
		$property->
			set_title(gettext('Reservation'))->
			set_name('reservation');
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
	public function get_setuid() {
		return $this->x_setuid ?? $this->init_setuid();
	}
	public function init_setuid() {
		$property = $this->x_setuid = new property_list($this);
		$property->
			set_title(gettext('Set UID'))->
			set_name('setuid');
		return $property;
	}
	public function get_snapdir() {
		return $this->x_snapdir ?? $this->init_snapdir();
	}
	public function init_snapdir() {
		$property = $this->x_snapdir = new property_list($this);
		$property->
			set_title(gettext('Snapdir'))->
			set_name('snapdir');
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
	public function get_type() {
		return $this->x_type ?? $this->init_type();
	}
	public function init_type() {
		$property = $this->x_type = new property_list($this);
		$property->
			set_title(gettext('Dataset Type'))->
			set_name('type');
		return $property;
	}
	public function get_utf8only() {
		return $this->x_utf8only ?? $this->init_utf8only();
	}
	public function init_utf8only() {
		$property = $this->x_utf8only = new property_list($this);
		$property->
			set_title(gettext('UTF-8 Only'))->
			set_name('utf8only');
		return $property;
	}
}
