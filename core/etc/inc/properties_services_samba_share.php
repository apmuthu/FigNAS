<?php
/*
	properties_services_samba_share.php

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

class properties_services_samba_share extends co_property_container_param {
	protected $x_afpcompat;
	protected $x_auxparam;
	protected $x_inheritacls;
	protected $x_shadowformat;
	protected $x_storealternatedatastreams;
	protected $x_storentfsacls;
	protected $x_vfs_fruit_encoding;
	protected $x_vfs_fruit_locking;
	protected $x_vfs_fruit_metadata;
	protected $x_vfs_fruit_resource;
	protected $x_vfs_fruit_time_machine;
	protected $x_zfsacl;
	protected $x_hostsallow;
	protected $x_hostsdeny;
	protected $x_shadowcopy;
	protected $x_readonly;
	protected $x_browseable;
	protected $x_guest;
	protected $x_inheritpermissions;
	protected $x_recyclebin;
	protected $x_hidedotfiles;
	protected $x_name;
	protected $x_comment;
	protected $x_path;

	public function get_afpcompat() {
		return $this->x_afpcompat ?? $this->init_afpcompat();
	}
	public function init_afpcompat() {
		$property = $this->x_afpcompat = new property_bool($this);
		$property->
			set_title(gettext('Enable AFP'))->
			set_name('afpcompat');
		return $property;
	}
	public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
	public function init_auxparam() {
		$property = $this->x_auxparam = new property_auxparam($this);
		return $property;
	}
	public function get_shadowformat() {
		return $this->x_shadowformat ?? $this->init_shadowformat();
	}
	public function init_shadowformat() {
		$property = $this->x_shadowformat = new property_text($this);
		$property->
			set_title(gettext('Shadow Copy Format'))->
			set_name('shadowformat');
		return $property;
	}
	public function get_vfs_fruit_encoding() {
		return $this->x_vfs_fruit_encoding ?? $this->init_vfs_fruit_encoding();
	}
	public function init_vfs_fruit_encoding() {
		$property = $this->x_vfs_fruit_encoding = new property_list($this);
		$property->
			set_title(gettext('VFS fruit:encoding'))->
			set_name('vfs_fruit_encoding');
		return $property;
	}
	public function get_vfs_fruit_locking() {
		return $this->x_vfs_fruit_locking ?? $this->init_vfs_fruit_locking();
	}
	public function init_vfs_fruit_locking() {
		$property = $this->x_vfs_fruit_locking = new property_list($this);
		$property->
			set_title(gettext('VFS fruit:locking'))->
			set_name('vfs_fruit_locking');
		return $property;
	}
	public function get_vfs_fruit_metadata() {
		return $this->x_vfs_fruit_metadata ?? $this->init_vfs_fruit_metadata();
	}
	public function init_vfs_fruit_metadata() {
		$property = $this->x_vfs_fruit_metadata = new property_list($this);
		$property->
			set_title(gettext('VFS fruit:metadata'))->
			set_name('vfs_fruit_metadata');
		return $property;
	}
	public function get_vfs_fruit_resource() {
		return $this->x_vfs_fruit_resource ?? $this->init_vfs_fruit_resource();
	}
	public function init_vfs_fruit_resource() {
		$property = $this->x_vfs_fruit_resource = new property_list($this);
		$property->
			set_title(gettext('VFS fruit:resource'))->
			set_name('vfs_fruit_resource');
		return $property;
	}
	public function get_vfs_fruit_time_machine() {
		return $this->x_vfs_fruit_time_machine ?? $this->init_vfs_fruit_time_machine();
	}
	public function init_vfs_fruit_time_machine() {
		$property = $this->x_vfs_fruit_time_machine = new property_list($this);
		$property->
			set_title(gettext('VFS fruit:time machine'))->
			set_name('vfs_fruit_time_machine');
		return $property;
	}
	public function get_zfsacl() {
		return $this->x_zfsacl ?? $this->init_zfsacl();
	}
	public function init_zfsacl() {
		$property = $this->x_zfsacl = new property_bool($this);
		$property->
			set_title(gettext('ZFS ACL'))->
			set_name('zfsacl');
		return $property;
	}
	public function get_inheritacls() {
		return $this->x_inheritacls ?? $this->init_inheritacls();
	}
	public function init_inheritacls() {
		$property = $this->x_inheritacls = new property_bool($this);
		$property->
			set_title(gettext('Inherit ACL'))->
			set_name('inheritacls');
		return $property;
	}
	public function get_storealternatedatastreams() {
		return $this->x_storealternatedatastreams ?? $this->init_storealternatedatastreams();
	}
	public function init_storealternatedatastreams() {
		$property = $this->x_storealternatedatastreams = new property_bool($this);
		$property->
			set_title(gettext('Alternate Data Streams'))->
			set_name('storealternatedatastreams');
		return $property;
	}
	public function get_storentfsacls() {
		return $this->x_storentfsacls ?? $this->init_storentfsacls();
	}
	public function init_storentfsacls() {
		$property = $this->x_storentfsacls = new property_bool($this);
		$property->
			set_title(gettext('NTFS ACLs'))->
			set_name('storentfsacls');
		return $property;
	}
	public function get_hostsallow() {
		return $this->x_hostsallow ?? $this->init_hostsallow();
	}
	public function init_hostsallow() {
		$property = $this->x_hostsallow = new property_text($this);
		$property->
			set_title(gettext('Hosts Allow'))->
			set_name('hostsallow');
		return $property;
	}
	public function get_hostsdeny() {
		return $this->x_hostsdeny ?? $this->init_hostsdeny();
	}
	public function init_hostsdeny() {
		$property = $this->x_hostsdeny = new property_text($this);
		$property->
			set_title(gettext('Hosts Deny'))->
			set_name('hostsdeny');
		return $property;
	}
	public function get_shadowcopy() {
		return $this->x_shadowcopy ?? $this->init_shadowcopy();
	}
	public function init_shadowcopy() {
		$property = $this->x_shadowcopy = new property_bool($this);
		$property->
			set_title(gettext('Shadow Copy'))->
			set_name('shadowcopy');
		return $property;
	}
	public function get_readonly() {
		return $this->x_readonly ?? $this->init_readonly();
	}
	public function init_readonly() {
		$property = $this->x_readonly = new property_bool($this);
		$property->
			set_title(gettext('Read Only'))->
			set_name('readonly');
		return $property;
	}
	public function get_browseable() {
		return $this->x_browseable ?? $this->init_browseable();
	}
	public function init_browseable() {
		$property = $this->x_browseable = new property_bool($this);
		$property->
			set_title(gettext('Browseable'))->
			set_name('browseable');
		return $property;
	}
	public function get_guest() {
		return $this->x_guest ?? $this->init_guest();
	}
	public function init_guest() {
		$property = $this->x_guest = new property_bool($this);
		$property->
			set_title(gettext('Guest'))->
			set_name('guest');
		return $property;
	}
	public function get_inheritpermissions() {
		return $this->x_inheritpermissions ?? $this->init_inheritpermissions();
	}
	public function init_inheritpermissions() {
		$property = $this->x_inheritpermissions = new property_bool($this);
		$property->
			set_title(gettext('Inherit Permissions'))->
			set_name('inheritpermissions');
		return $property;
	}
	public function get_recyclebin() {
		return $this->x_recyclebin ?? $this->init_recyclebin();
	}
	public function init_recyclebin() {
		$property = $this->x_recyclebin = new property_bool($this);
		$property->
			set_title(gettext('Recycle Bin'))->
			set_name('recyclebin');
		return $property;
	}
	public function get_hidedotfiles() {
		return $this->x_hidedotfiles ?? $this->init_hidedotfiles();
	}
	public function init_hidedotfiles() {
		$property = $this->x_hidedotfiles = new property_bool($this);
		$property->
			set_title(gettext('Hide Dot Files'))->
			set_name('hidedotfiles');
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
	public function get_comment() {
		return $this->x_comment ?? $this->init_comment();
	}
	public function init_comment() {
		$property = $this->x_comment = new property_text($this);
		$property->
			set_title(gettext('Comment'))->
			set_name('comment');
		return $property;
	}
	public function get_path() {
		return $this->x_path ?? $this->init_path();
	}
	public function init_path() {
		$property = $this->x_path = new property_text($this);
		$property->
			set_title(gettext('Path'))->
			set_name('path');
		return $property;
	}
}
