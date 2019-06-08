<?php
/*
	row_properties.php

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

final class row_properties extends grid_properties {
	public function init_name() {
		$description = gettext('This is a nickname and is for information only.');
		$placeholder = gettext('Enter Name');
		$property = parent::init_name();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('name')->
			set_size(20)->
			filter_use_default();
		return $property;
	}
	public function init_targetname() {
		$description = gettext('The name of the target.');
		$property = parent::init_targetname();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('targetname')->
			set_size(60)->
			filter_use_default();
		return $property;
	}
	public function init_targetaddress() {
		$description = gettext('The IP address or the DNS name of the iSCSI target.');
		$property = parent::init_targetaddress();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('targetaddress')->
			set_size(60)->
			filter_use_default();
		return $property;
	}
	public function init_initiatorname() {
		$description = gettext('The name of the initiator. By default, the name is concatenation of "iqn.1994-09.org.freebsd:" with the hostname.');
		$placeholder = 'iqn.1994-09.org.freebsd:hostname';
		$property = parent::init_initiatorname();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('initiatorname')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_auxparam() {
		$description = gettext('These parameters will be added to the configuration of this initiator.');
		$property = parent::init_auxparam();
		$property->
			set_description($description);
		return $property;
	}
	public function init_authmethod() {
		$description = gettext('Sets the authentication type. Type can be either "None", or "CHAP". Default is "None". When set to CHAP, both chapIName and chapSecret must be defined.');
		$options = [
			'' => gettext('Default'),
			'None' => gettext('None'),
			'CHAP' => gettext('CHAP')
		];
		$property = parent::init_authmethod();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('authmethod')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_chapiname() {
		$description = gettext('Login for CHAP authentication.');
		$placeholder = gettext('Username');
		$regexp = '/^\S{0,32}$/';
		$property = parent::init_chapiname();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('chapiname')->
			set_maxlength(32)->
			set_placeholder($placeholder)->
			set_size(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_chapsecret() {
		$description = gettext('Secret for CHAP authentication.');
		$placeholder = gettext('Secret');
		$regexp = '/^[^"]{0,32}$/';
		$property = parent::init_chapsecret();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('chapsecret')->
			set_maxlength(32)->
			set_placeholder($placeholder)->
			set_size(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
	public function init_tgtchapname() {
		$description = gettext('Target login for Mutual CHAP authentication.');
		$placeholder = gettext('Username');
		$regexp = '/^\S{0,32}$/';
		$property = parent::init_tgtchapname();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('tgtchapname')->
			set_maxlength(32)->
			set_placeholder($placeholder)->
			set_size(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_tgtchapsecret() {
		$description = gettext('Target secret for Mutual CHAP authentication.');
		$placeholder = gettext('Secret');
		$regexp = '/^[^"]{0,32}$/';
		$property = parent::init_tgtchapsecret();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('tgtchapsecret')->
			set_maxlength(32)->
			set_placeholder($placeholder)->
			set_size(40)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			filter_use_empty()->
			set_filter_group('ui',['empty','ui']);
		return $property;
	}
	public function init_headerdigest() {
		$description = gettext('Sets the header digest; a checksum calculated over the header of iSCSI PDUs, and verified on receive. Digest can be either "None", or "CRC32C". Default is "None".');
		$options = [
			'' => gettext('Default'),
			'None' => gettext('None'),
			'CRC32C' => gettext('CRC32C')
		];
		$property = parent::init_headerdigest();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('headerdigest')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_datadigest() {
		$description = gettext('Sets the data digest; a checksum calculated over the Data Section of iSCSI PDUs, and verified on receive. Digest can be either "None", or "CRC32C". Default is "None".');
		$options = [
			'' => gettext('Default'),
			'None' => gettext('None'),
			'CRC32C' => gettext('CRC32C')
		];
		$property = parent::init_datadigest();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('datadigest')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_protocol() {
		$description = gettext('Name of selected protocol. It can be either "iSER", for iSCSI over RDMA, or "iSCSI". Default is "iSCSI".');
		$options = [
			'' => gettext('Default'),
			'iSCSI' => gettext('iSCSI'),
			'iSER' => gettext('iSER - iSCSI over RDMA')
		];
		$property = parent::init_protocol();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('protocol')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_offload() {
		$description = gettext('Define iSCSI hardware offload driver. The default is "None".');
		$placeholder = gettext('Driver Name');
		$regexp = '/^\S{0,60}$/';
		$property = parent::init_offload();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('offload')->
			set_maxlength(60)->
			set_placeholder($placeholder)->
			set_size(60)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp]);
		return $property;
	}
	public function init_sessionstate() {
		$description = gettext('Enable or disable the session. State can be either "On" or "Off". Default is "On".');
		$options = [
			'' => gettext('Default'),
			'on' => gettext('On'),
			'off' => gettext('Off')
		];
		$property = parent::init_sessionstate();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('sessionstate')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_sessiontype() {
		$description = gettext('Sets the session type. Type can be either "Discovery", or "Normal". Default is "Normal". For normal sessions, the TargetName must be defined. Discovery sessions ignore the TargetName and will result in the initiator connecting to all the targets returned by SendTargets iSCSI discovery with the defined TargetAddress.');
		$options = [
			'' => gettext('Default'),
			'normal' => gettext('Normal'),
			'discovery' => gettext('Discovery')
		];
		$property = parent::init_sessiontype();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('sessiontype')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
}
