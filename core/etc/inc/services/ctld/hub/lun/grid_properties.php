<?php
/*
	basic_properties.php

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
namespace services\ctld\hub\lun;
use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name() {
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('LUN Name'));
		return $property;
	}
	final public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_backend;
	public function init_backend() {
		$property = $this->x_backend = new myp\property_list($this);
		$property->
			set_name('backend')->
			set_title(gettext('Backend'));
		return $property;
	}
	final public function get_backend() {
		return $this->x_backend ?? $this->init_backend();
	}
	protected $x_blocksize;
	public function init_blocksize() {
		$property = $this->x_blocksize = new myp\property_list($this);
		$property->
			set_name('blocksize')->
			set_title(gettext('Block Size'));
		return $property;
	}
	final public function get_blocksize() {
		return $this->x_blocksize ?? $this->init_blocksize();
	}
	protected $x_ctl_lun;
	public function init_ctl_lun() {
		$property = $this->x_ctl_lun = new myp\property_text($this);
		$property->
			set_name('ctl_lun')->
			set_title(gettext('CTL LUN'));
		return $property;
	}
	final public function get_ctl_lun() {
		return $this->x_ctl_lun ?? $this->init_ctl_lun();
	}
	protected $x_device_id;
	public function init_device_id() {
		$property = $this->x_device_id = new myp\property_text($this);
		$property->
			set_name('device_id')->
			set_title(gettext('Device ID'));
		return $property;
	}
	final public function get_device_id() {
		return $this->x_device_id ?? $this->init_device_id();
	}
	protected $x_device_type;
	public function init_device_type() {
		$property = $this->x_device_type = new myp\property_list($this);
		$property->
			set_name('device_type')->
			set_title(gettext('Device Type'));
		return $property;
	}
	final public function get_device_type() {
		return $this->x_device_type ?? $this->init_device_type();
	}
	protected $x_passthrough_address;
	public function init_passthrough_address() {
		$property = $this->x_passthrough_address = new myp\property_text($this);
		$property->
			set_name('passthrough_address')->
			set_title(gettext('Passthrough Address'));
		return $property;
	}
	final public function get_passthrough_address() {
		return $this->x_passthrough_address ?? $this->init_passthrough_address();
	}
	protected $x_path;
	public function init_path() {
		$property = $this->x_path = new myp\property_text($this);
		$property->
			set_name('path')->
			set_title(gettext('Path'));
		return $property;
	}
	final public function get_path() {
		return $this->x_path ?? $this->init_path();
	}
	protected $x_serial;
	public function init_serial() {
		$property = $this->x_ = new myp\property_text($this);
		$property->
			set_name('serial')->
			set_title(gettext('Serial Number'));
		return $property;
	}
	final public function get_serial() {
		return $this->x_serial ?? $this->init_serial();
	}
	protected $x_size;
	public function init_size() {
		$property = $this->x_size = new myp\property_text($this);
		$property->
			set_name('size')->
			set_title(gettext('Size'));
		return $property;
	}
	final public function get_size() {
		return $this->x_size ?? $this->init_size();
	}
	protected $x_opt_vendor;
	public function init_opt_vendor() {
		$property = $this->x_opt_vendor = new myp\property_text($this);
		$property->
			set_name('opt_vendor')->
			set_title(gettext('LUN Vendor'));
		return $property;
	}
	final public function get_opt_vendor() {
		return $this->x_opt_vendor ?? $this->init_opt_vendor();
	}
	protected $x_opt_product;
	public function init_opt_product() {
		$property = $this->x_opt_product = new myp\property_text($this);
		$property->
			set_name('opt_product')->
			set_title(gettext('LUN Product'));
		return $property;
	}
	final public function get_opt_product() {
		return $this->x_opt_product ?? $this->init_opt_product();
	}
	protected $x_opt_revision;
	public function init_opt_revision() {
		$property = $this->x_opt_revision = new myp\property_text($this);
		$property->
			set_name('opt_revision')->
			set_title(gettext('LUN Revision'));
		return $property;
	}
	final public function get_opt_revision() {
		return $this->x_opt_revision ?? $this->init_opt_revision();
	}
	protected $x_opt_scsiname;
	public function init_opt_scsiname() {
		$property = $this->x_opt_scsiname = new myp\property_text($this);
		$property->
			set_name('opt_scsiname')->
			set_title(gettext('LUN SCSI Name'));
		return $property;
	}
	final public function get_opt_scsiname() {
		return $this->x_opt_scsiname ?? $this->init_opt_scsiname();
	}
	protected $x_opt_eui;
	public function init_opt_eui() {
		$property = $this->x_opt_eui = new myp\property_text($this);
		$property->
			set_name('opt_eui')->
			set_title(gettext('LUN EUI-64	identifier'));
		return $property;
	}
	final public function get_opt_eui() {
		return $this->x_opt_eui ?? $this->init_opt_eui();
	}
	protected $x_opt_naa;
	public function init_opt_naa() {
		$property = $this->x_opt_naa = new myp\property_text($this);
		$property->
			set_name('opt_naa')->
			set_title(gettext('LUN NAA Identifier'));
		return $property;
	}
	final public function get_opt_naa() {
		return $this->x_opt_naa ?? $this->init_opt_naa();
	}
	protected $x_opt_uuid;
	public function init_opt_uuid() {
		$property = $this->x_opt_uuid = new myp\property_text($this);
		$property->
			set_name('opt_uuid')->
			set_title(gettext('LUN UUID'));
		return $property;
	}
	final public function get_opt_uuid() {
		return $this->x_opt_uuid ?? $this->init_opt_uuid();
	}
	protected $x_opt_ha_role;
	public function init_opt_ha_role() {
		$property = $this->x_opt_ha_role = new myp\property_list($this);
		$property->
			set_name('opt_ha_role')->
			set_title(gettext('HA Role'));
		return $property;
	}
	final public function get_opt_ha_role() {
		return $this->x_opt_ha_role ?? $this->init_opt_ha_role();
	}
	protected $x_opt_insecure_tpc;
	public function init_opt_insecure_tpc() {
		$property = $this->x_opt_insecure_tpc = new myp\property_list($this);
		$property->
			set_name('opt_insecure_tpc')->
			set_title(gettext('Insecure TPC'));
		return $property;
	}
	final public function get_opt_insecure_tpc() {
		return $this->x_opt_insecure_tpc ?? $this->init_opt_insecure_tpc();
	}
	protected $x_opt_readcache;
	public function init_opt_readcache() {
		$property = $this->x_opt_readcache = new myp\property_list($this);
		$property->
			set_name('opt_readcache')->
			set_title(gettext('Read Cache'));
		return $property;
	}
	final public function get_opt_readcache() {
		return $this->x_opt_readcache ?? $this->init_opt_readcache();
	}
	protected $x_opt_readonly;
	public function init_opt_readonly() {
		$property = $this->x_opt_readonly = new myp\property_list($this);
		$property->
			set_name('opt_readonly')->
			set_title(gettext('Read Only'));
		return $property;
	}
	final public function get_opt_readonly() {
		return $this->x_opt_readonly ?? $this->init_opt_readonly();
	}
	protected $x_opt_removable;
	public function init_opt_removable() {
		$property = $this->x_opt_removable = new myp\property_list($this);
		$property->
			set_name('opt_removable')->
			set_title(gettext('Removable'));
		return $property;
	}
	final public function get_opt_removable() {
		return $this->x_opt_removable ?? $this->init_opt_removable();
	}
	protected $x_opt_reordering;
	public function init_opt_reordering() {
		$property = $this->x_opt_reordering = new myp\property_list($this);
		$property->
			set_name('opt_reordering')->
			set_title(gettext('Reordering'));
		return $property;
	}
	final public function get_opt_reordering() {
		return $this->x_opt_reordering ?? $this->init_opt_reordering();
	}
	protected $x_opt_serseq;
	public function init_opt_serseq() {
		$property = $this->x_opt_serseq = new myp\property_list($this);
		$property->
			set_name('opt_serseq')->
			set_title(gettext('Serialize Sequence'));
		return $property;
	}
	final public function get_opt_serseq() {
		return $this->x_opt_serseq ?? $this->init_opt_serseq();
	}
	protected $x_opt_pblocksize;
	public function init_opt_pblocksize() {
		$property = $this->x_opt_pblocksize = new myp\property_text($this);
		$property->
			set_name('opt_pblocksize')->
			set_title(gettext('Physical Block Size'));
		return $property;
	}
	final public function get_opt_pblocksize() {
		return $this->x_opt_pblocksize ?? $this->init_opt_pblocksize();
	}
	protected $x_opt_pblockoffset;
	public function init_opt_pblockoffset() {
		$property = $this->x_opt_pblockoffset = new myp\property_text($this);
		$property->
			set_name('opt_pblockoffset')->
			set_title(gettext('Physical Block Offset'));
		return $property;
	}
	final public function get_opt_pblockoffset() {
		return $this->x_opt_pblockoffset ?? $this->init_opt_pblockoffset();
	}
	protected $x_opt_ublocksize;
	public function init_opt_ublocksize() {
		$property = $this->x_opt_ublocksize = new myp\property_text($this);
		$property->
			set_name('opt_ublocksize')->
			set_title(gettext('UNMAP Block Size'));
		return $property;
	}
	final public function get_opt_ublocksize() {
		return $this->x_opt_ublocksize ?? $this->init_opt_ublocksize();
	}
	protected $x_opt_ublockoffset;
	public function init_opt_ublockoffset() {
		$property = $this->x_opt_ublockoffset = new myp\property_text($this);
		$property->
			set_name('opt_ublockoffset')->
			set_title(gettext('UNMAP Block Offset'));
		return $property;
	}
	final public function get_opt_ublockoffset() {
		return $this->x_opt_ublockoffset ?? $this->init_opt_ublockoffset();
	}
	protected $x_opt_rpm;
	public function init_opt_rpm() {
		$property = $this->x_opt_rpm = new myp\property_text($this);
		$property->
			set_name('opt_rpm')->
			set_title(gettext('RPM'));
		return $property;
	}
	final public function get_opt_rpm() {
		return $this->x_opt_rpm ?? $this->init_opt_rpm();
	}
	protected $x_opt_formfactor;
	public function init_opt_formfactor() {
		$property = $this->x_opt_formfactor = new myp\property_list($this);
		$property->
			set_name('opt_formfactor')->
			set_title(gettext('Form Factor'));
		return $property;
	}
	final public function get_opt_formfactor() {
		return $this->x_opt_formfactor ?? $this->init_opt_formfactor();
	}
	protected $x_opt_provisioning_type;
	public function init_opt_provisioning_type() {
		$property = $this->x_opt_provisioning_type = new myp\property_list($this);
		$property->
			set_name('opt_provisioning_type')->
			set_title(gettext('Provisioning Type'));
		return $property;
	}
	final public function get_opt_provisioning_type() {
		return $this->x_opt_provisioning_type ?? $this->init_opt_provisioning_type();
	}
	protected $x_opt_unmap;
	public function init_opt_unmap() {
		$property = $this->x_opt_unmap = new myp\property_list($this);
		$property->
			set_name('opt_unmap')->
			set_title(gettext('UNMAP'));
		return $property;
	}
	final public function get_opt_unmap() {
		return $this->x_opt_unmap ?? $this->init_opt_unmap();
	}
	protected $x_opt_unmap_max_lba;
	public function init_opt_unmap_max_lba() {
		$property = $this->x_opt_unmap_max_lba = new myp\property_text($this);
		$property->
			set_name('opt_unmap_max_lba')->
			set_title(gettext('UNMAP Maximum LBA'));
		return $property;
	}
	final public function get_opt_unmap_max_lba() {
		return $this->x_opt_unmap_max_lba ?? $this->init_opt_unmap_max_lba();
	}
	protected $x_opt_unmap_max_descr;
	public function init_opt_unmap_max_descr() {
		$property = $this->x_opt_unmap_max_descr = new myp\property_text($this);
		$property->
			set_name('opt_unmap_max_descr')->
			set_title(gettext('UNMAP Maximum Descriptors'));
		return $property;
	}
	final public function get_opt_unmap_max_descr() {
		return $this->x_opt_unmap_max_descr ?? $this->init_opt_unmap_max_descr();
	}
	protected $x_opt_write_same_max_lba;
	public function init_opt_write_same_max_lba() {
		$property = $this->x_opt_write_same_max_lba = new myp\property_text($this);
		$property->
			set_name('opt_write_same_max_lba')->
			set_title(gettext('Write Same Maximum LBA'));
		return $property;
	}
	final public function get_opt_write_same_max_lba() {
		return $this->x_opt_write_same_max_lba ?? $this->init_opt_write_same_max_lba();
	}
	protected $x_opt_avail_threshold;
	public function init_opt_avail_threshold() {
		$property = $this->x_opt_avail_threshold = new myp\property_text($this);
		$property->
			set_name('opt_avail_threshold')->
			set_title(gettext('Avail Threshold'));
		return $property;
	}
	final public function get_opt_avail_threshold() {
		return $this->x_opt_avail_threshold ?? $this->init_opt_avail_threshold();
	}
	protected $x_opt_used_threshold;
	public function init_opt_used_threshold() {
		$property = $this->x_opt_used_threshold = new myp\property_text($this);
		$property->
			set_name('opt_used_threshold')->
			set_title(gettext('Used Threshold'));
		return $property;
	}
	final public function get_opt_used_threshold() {
		return $this->x_opt_used_threshold ?? $this->init_opt_used_threshold();
	}
	protected $x_opt_pool_avail_threshold;
	public function init_opt_pool_avail_threshold() {
		$property = $this->x_opt_pool_avail_threshold = new myp\property_text($this);
		$property->
			set_name('opt_pool_avail_threshold')->
			set_title(gettext('Pool Avail Threshold'));
		return $property;
	}
	final public function get_opt_pool_avail_threshold() {
		return $this->x_opt_pool_avail_threshold ?? $this->init_opt_pool_avail_threshold();
	}
	protected $x_opt_pool_used_threshold;
	public function init_opt_pool_used_threshold() {
		$property = $this->x_opt_pool_used_threshold = new myp\property_text($this);
		$property->
			set_name('opt_pool_used_threshold')->
			set_title(gettext('Pool Used Threshold'));
		return $property;
	}
	final public function get_opt_pool_used_threshold() {
		return $this->x_opt_pool_used_threshold ?? $this->init_opt_pool_used_threshold();
	}
	protected $x_opt_writecache;
	public function init_opt_writecache() {
		$property = $this->x_opt_writecache = new myp\property_list($this);
		$property->
			set_name('opt_writecache')->
			set_title(gettext('Write Cache'));
		return $property;
	}
	final public function get_opt_writecache() {
		return $this->x_opt_writecache ?? $this->init_opt_writecache();
	}
	protected $x_opt_file;
	public function init_opt_file() {
		$property = $this->x_opt_file = new myp\property_text($this);
		$property->
			set_name('opt_file')->
			set_title(gettext('File'));
		return $property;
	}
	final public function get_opt_file() {
		return $this->x_opt_file ?? $this->init_opt_file();
	}
	protected $x_opt_num_threads;
	public function init_opt_num_threads() {
		$property = $this->x_opt_num_threads = new myp\property_text($this);
		$property->
			set_name('opt_num_threads')->
			set_title(gettext('Threads'));
		return $property;
	}
	final public function get_opt_num_threads() {
		return $this->x_opt_num_threads ?? $this->init_opt_num_threads();
	}
	protected $x_opt_capacity;
	public function init_opt_capacity() {
		$property = $this->x_opt_capacity = new myp\property_text($this);
		$property->
			set_name('opt_capacity')->
			set_title(gettext('Capacity'));
		return $property;
	}
	final public function get_opt_capacity() {
		return $this->x_opt_capacity ?? $this->init_opt_capacity();
	}
	protected $x_auxparam;
	public function init_auxparam() {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam() {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
}
