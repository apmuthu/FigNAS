<?php
/*
	row_properties.php

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

final class row_properties extends grid_properties {
	public function init_name(): myp\property_text {
		$description = gettext('Name of the LUN.');
		$placeholder = gettext('LUN Name');
		$regexp = '/^\S{1,223}$/';
		$property = parent::init_name();
		$property->
			set_id('name')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(223)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_backend(): myp\property_list {
		$description = gettext('The CTL backend to use for a given LUN.');
		$options = [
			'' => gettext('Default'),
			'block' => gettext('Block'),
//			'passthrough' => gettext('Passthrough'),
			'ramdisk' => gettext('RAM Disk')
		];
		$property = parent::init_backend();
		$property->
			set_id('backend')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_blocksize(): myp\property_list {
		$description = gettext('The blocksize visible to the initiator.');
		$options = [
			'' => gettext('Default'),
			'512' => gettext('512 Bytes'),
			'2048' => gettext('2 KiB'),
			'4096' => gettext('4 KiB'),
			'8192' => gettext('8 KiB'),
			'16384' => gettext('16 KiB'),
			'32738' => gettext('32 KiB'),
			'65536' => gettext('64 KiB')
		];
		$property = $property = parent::init_blocksize();
		$property->
			set_id('blocksize')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_ctl_lun(): myp\property_text {
		$description = gettext('Global numeric identifier to use for a given LUN inside CTL.');
		$regexp = '/^(?:|[0-9]|[1-9][0-9]{1,2}|10[01][0-9]|102[0-3])$/';
		$property = parent::init_ctl_lun();
		$property->
			set_id('ctl_lun')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(5)->
			set_maxlength(4)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_device_id(): myp\property_text {
//		The device-id shall be 48 characters width,
//		Compatibility istgt: 16 bytes "iSCSI Disk     " + 32 bytes aligned serial number
		$description = gettext('The SCSI Device Identification string presented to the initiator.');
		$placeholder = gettext('Device ID');
		$regexp = '/^\S{0,48}$/';
		$property = parent::init_device_id();
		$property->
			set_id('device_id')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(48)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_device_type(): myp\property_list {
		$description = gettext('Specify the SCSI device type to use when creating the LUN.');
		$options = [
			'' => gettext('Undefined'),
			'disk' => gettext('Disk'),
			'direct' => gettext('Direct'),
			'processor' => gettext('Processor'),
			'cd' => gettext('CD'),
			'cdrom' => gettext('CD-ROM'),
			'dvd' => gettext('DVD'),
			'dvdrom' => gettext('DVD-ROM'),
/*
			'0' => gettext('0'),
			'1' => gettext('1'),
			'2' => gettext('2'),
			'3' => gettext('3'),
			'4' => gettext('4'),
			'5' => gettext('5'),
			'6' => gettext('6'),
			'7' => gettext('7'),
			'8' => gettext('8'),
			'9' => gettext('9'),
			'10' => gettext('10'),
			'11' => gettext('11'),
			'12' => gettext('12'),
			'13' => gettext('13'),
			'14' => gettext('14'),
			'15' => gettext('15'),
 */
		];
		$property = parent::init_device_type();
		$property->
			set_id('device_type')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_passthrough_address(): myp\property_text {
		$description = gettext('Enter passthrough device address bus:path:lun');
		$regexp = '/^(?:|[0-9]+:[0-9]+:[0-9]+)$/';
		$property = parent::init_passthrough_address();
		$property->
			set_id('passthrough_address')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(18)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_path(): myp\property_text {
		$description = gettext('The path to the file, device node, or zfs volume used to back the LUN.');
		$regexp = '/^(?:|.{1,223})$/';
		$property = parent::init_path();
		$property->
			set_id('path')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(223)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_serial(): myp\property_text {
		$description = gettext('The SCSI serial number presented to the initiator.');
		$regexp = '/^(?:|.{1,40})$/';
		$property = parent::init_serial();
		$property->
			set_id('serial')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_size(): myp\property_text {
		$description = gettext('The size of the LUN.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,16}[kmgtpezy]b?)$/i';
		$property = parent::init_size();
		$property->
			set_id('size')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_vendor(): myp\property_text {
		$description = gettext('Specifies LUN vendor string up to 8 chars.');
		$placeholder = 'FreeBSD';
		$regexp = '/^.{0,8}$/';
		$property = parent::init_opt_vendor();
		$property->
			set_id('opt_vendor')->
			set_description($description)->
			set_placeholder($placeholder)->
//			set_defaultvalue('FreeBSD')->
			set_defaultvalue('')->
			set_size(10)->
			set_maxlength(8)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_product(): myp\property_text {
		$description = gettext('Specifies LUN product string up to 16 chars.');
		$placeholder = 'iSCSI Disk';
		$regexp = '/^.{0,16}$/';
		$property = parent::init_opt_product();
		$property->
			set_id('opt_product')->
			set_description($description)->
			set_placeholder($placeholder)->
//			set_defaultvalue('iSCSI Disk')->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_revision(): myp\property_text {
		$description = gettext('Specifies LUN revision string up to 4 chars.');
		$placeholder = '0123';
		$regexp = '/^.{0,4}$/';
		$property = parent::init_opt_revision();
		$property->
			set_id('opt_revision')->
			set_description($description)->
			set_placeholder($placeholder)->
//			set_defaultvalue('0123')->
			set_defaultvalue('')->
			set_size(6)->
			set_maxlength(4)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_scsiname(): myp\property_text {
		$description = gettext('Specifies LUN SCSI name string.');
		$placeholder = gettext('SCSI Name');
		$regexp = '/^\S{0,223}$/';
		$property = parent::init_opt_scsiname();
		$property->
			set_id('opt_scsiname')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(223)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_eui(): myp\property_text {
		$description = gettext('Specifies LUN EUI-64 identifier.');
		$placeholder = gettext('EUI-64');
		$regexp = '/^(?:|[0-9a-f]{16})$/i';
		$property = parent::init_opt_eui();
		$property->
			set_id('opt_eui')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_naa(): myp\property_text {
		$description = gettext('Specifies LUN NAA identifier.');
		$placeholder = gettext('NAA Identifier');
		$regexp = '/^(?:|(?:[0-9a-f]{16}){1,2})$/i';
		$property = parent::init_opt_naa();
		$property->
			set_id('opt_naa')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(34)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_uuid(): myp\property_text {
		$description = gettext('Specifies LUN locally assigned RFC 4122 UUID.');
		$placeholder = gettext('UUID');
		$regexp = '/^(?:|[0-9a-f]{4}(?:[0-9a-f]{4}-){4}[0-9a-f]{12})$/i';
		$property = parent::init_opt_uuid();
		$property->
			set_id('opt_uuid')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(45)->
			set_maxlength(36)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_ha_role(): myp\property_list {
		$description = gettext('Setting to "primary" or "secondary" overrides default role of the node in HA cluster.');
		$options = [
			'' => gettext('Default'),
			'primary' => gettext('Primary'),
			'secondary' => gettext('Secondary')
		];
		$property = parent::init_opt_ha_role();
		$property->
			set_id('opt_ha_role')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_insecure_tpc(): myp\property_list {
		$description = gettext('Setting to "on" allows EXTENDED COPY command sent to this LUN access other LUNs on this host, not accessible otherwise. This allows to offload copying between different iSCSI targets residing on the same host in trusted environments.');
		$options = [
			'' => gettext('Off'),
			'on' => gettext('On')
		];
		$property = parent::init_opt_insecure_tpc();
		$property->
			set_id('opt_insecure_tpc')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_readcache(): myp\property_list {
		$description = gettext('Set to "off", disables read caching for the LUN, if supported by the backend.');
		$options = [
			'' => gettext('On'),
			'off' => gettext('Off')
		];
		$property = parent::init_opt_readcache();
		$property->
			set_id('opt_readcache')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_readonly(): myp\property_list {
		$description = gettext('Set to "on", blocks all media write operations to the LUN reporting it as write protected.');
		$options = [
			'' => gettext('Off'),
			'on' => gettext('On')
		];
		$property = parent::init_opt_readonly();
		$property->
			set_id('opt_readonly')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_removable(): myp\property_list {
		$description = gettext('Set to "on" makes LUN removable.');
		$options = [
			'' => gettext('Off'),
			'on' => gettext('On')
		];
		$property = parent::init_opt_removable();
		$property->
			set_id('opt_removable')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_reordering(): myp\property_list {
		$description = gettext('Set to "unrestricted", allows target to process commands with SIMPLE task attribute in arbitrary order.');
		$options = [
			'' => gettext('Restricted'),
			'unrestricted' => gettext('Unrestricted')
		];
		$property = parent::init_opt_reordering();
		$property->
			set_id('opt_reordering')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_serseq(): myp\property_list {
		$description = '';
		$options = [
			'' => gettext('Default'),
			'off' => gettext('Allow consecutive read/writes to be issued in parallel'),
			'on' => gettext('Serialize consecutive reads/writes'),
			'read' => gettext('Serialize consecutive reads')
		];
		$property = parent::init_opt_serseq();
		$property->
			set_id('opt_serseq')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_pblocksize(): myp\property_text {
		$description = gettext('Specify physical block size of the device.');
		$regexp = '/^(?:|[1-9][0-9]{0,8}[kmgtpezy]b?)$/i';
		$property = parent::init_opt_pblocksize();
		$property->
			set_id('opt_pblocksize')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_pblockoffset(): myp\property_text {
		$description = gettext('Specify physical block offset of the device.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,8}[kmgtpezy]b?)$/i';
		$property = parent::init_opt_pblockoffset();
		$property->
			set_id('opt_pblockoffset')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_ublocksize(): myp\property_text {
		$description = gettext('Specify UNMAP block size of the device.');
		$regexp = '/^(?:|[1-9][0-9]{0,8}[kmgtpezy]b?)$/i';
		$property = parent::init_opt_ublocksize();
		$property->
			set_id('opt_ublocksize')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_ublockoffset(): myp\property_text {
		$description = gettext('Specify UNMAP block offset of the device.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,8}[kmgtpezy]b?)$/i';
		$property = parent::init_opt_ublockoffset();
		$property->
			set_id('opt_ublockoffset')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_rpm(): myp\property_text {
		$description = gettext('Specifies medium rotation rate of the device.');
//		0: not reported
//		1: non-rotating (SSD)
//		>1024: value in	revolutions per minute
//		99999: max rpm
		$regexp = '/^(?:|[01]|102[5-9]|10[3-9][0-9]|1[1-9][0-9]{2}|[2-9][0-9]{3}|[1-9][0-9]{4})$/';
		$property = parent::init_opt_rpm();
		$property->
			set_id('opt_rpm')->
			set_defaultvalue('')->
			set_description($description)->
			set_size(7)->
			set_maxlength(5)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_formfactor(): myp\property_list {
		$description = gettext('Specifies nominal form factor of the device.');
		$options = [
			'' => gettext('Undefined'),
			'0' => gettext('Not Reported'),
			'1' => gettext('5.25"'),
			'2' => gettext('3.5"'),
			'3' => gettext('2.5"'),
			'4' => gettext('1.8"'),
			'5' => gettext('less then 1.8".')
		];
		$property = parent::init_opt_formfactor();
		$property->
			set_id('opt_formfactor')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_provisioning_type(): myp\property_list {
		$description = gettext('When UNMAP support is enabled, this option specifies provisioning type.');
		$options = [
			'' => gettext('Default'),
			'resource' => gettext('Resource Provisioning'),
			'thin' => gettext('Thin Provisioning'),
			'unknown' => gettext('Unknown')
		];
		$property = parent::init_opt_provisioning_type();
		$property->
			set_id('opt_provisioning_type')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_unmap(): myp\property_list {
		$description = gettext('Setting to "on" or "off" controls UNMAP support for the logical unit. Default value is "on" if supported by the backend.');
		$options = [
			'' => gettext('Default'),
			'on' => gettext('On'),
			'off' => gettext('Off')
		];
		$property = parent::init_opt_unmap();
		$property->
			set_id('opt_unmap')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_unmap_max_lba(): myp\property_text {
		$description = gettext('Specify maximum allowed number of LBAs per UNMAP command to report in Block Limits VPD page.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_unmap_max_lba();
		$property->
			set_id('opt_unmap_max_lba')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_unmap_max_descr(): myp\property_text {
		$description = gettext('Specify maximum allowed number of block descriptors per UNMAP command to report in Block Limits VPD page.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_unmap_max_descr();
		$property->
			set_id('opt_unmap_max_descr')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_write_same_max_lba(): myp\property_text {
		$description = gettext('Specify maximum allowed number of LBAs per WRITE SAME command to report in Block Limits VPD page.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_write_same_max_lba();
		$property->
			set_id('opt_write_same_max_lba')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_avail_threshold(): myp\property_text {
		$description = gettext('Set per-LUN/per-pool thin provisioning soft threshold.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_avail_threshold();
		$property->
			set_id('opt_avail_threshold')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_used_threshold(): myp\property_text {
		$description = gettext('Set per-LUN/per-pool thin provisioning soft threshold.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_used_threshold();
		$property->
			set_id('opt_used_threshold')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_pool_avail_threshold(): myp\property_text {
		$description = gettext('Set per-LUN/per-pool thin provisioning soft threshold.');
		$regexp = '/^(?:|0|[1-9][0-9]*)$/';
		$property = parent::init_opt_pool_avail_threshold();
		$property->
			set_id('opt_pool_avail_threshold')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(18)->
			set_maxlength(16)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_pool_used_threshold(): myp\property_text {
		$description = gettext('Set per-LUN/per-pool thin provisioning soft threshold.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,8})$/';
		$property = parent::init_opt_pool_used_threshold();
		$property->
			set_id('opt_pool_used_threshold')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(12)->
			set_maxlength(10)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_writecache(): myp\property_list {
		$description = gettext('Set to "off", disables write caching for the LUN, if supported by the backend.');
		$options = [
			'' => gettext('On'),
			'off' => gettext('Off')
		];
		$property = parent::init_opt_writecache();
		$property->
			set_id('opt_writecache')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_opt_file(): myp\property_text {
		$description = gettext('Specifies file or device name to use for backing store.');
		$regexp = '/^\S{0,223}$/';
		$property = parent::init_opt_file();
		$property->
			set_id('opt_file')->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_num_threads(): myp\property_text {
		$description = gettext('Specifies number of backend threads to use for this LUN.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,8})$/';
		$property = parent::init_opt_num_threads();
		$property->
			set_id('opt_num_threads')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(12)->
			set_maxlength(10)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_opt_capacity(): myp\property_text {
		$description = gettext('Specifies capacity of backing store (maximum RAM for data). The default value is zero, that disables backing store completely, making all writes go to nowhere, while all reads return zeroes.');
		$regexp = '/^(?:|0|[1-9][0-9]{0,8}[kmgtpezy]b?)$/i';
		$property = parent::init_opt_capacity();
		$property->
			set_id('opt_capacity')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(14)->
			set_maxlength(12)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to this lun.');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
}
