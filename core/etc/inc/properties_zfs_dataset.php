<?php
/*
	properties_zfs_dataset.php

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
/*
 * To activate a property:
 * - Enable property variable.
 * - Enable init call in method load.
 * - Enable property method.
 */
class properties_zfs_dataset extends co_property_container {
	public $x_aclinherit;
	public $x_aclmode;
	public $x_atime;
	public $x_canmount;
	public $x_casesensitivity;
	public $x_checksum;
	public $x_compression;
	public $x_copies;
	public $x_dedup;
//	public $x_devices;
	public $x_exec;
	public $x_jailed;
	public $x_logbias;
//	public $x_name;
//	public $x_nbmand;
	public $x_normalization;
	public $x_primarycache;
	public $x_quota;
	public $x_readonly;
	public $x_redundant_metadata;
	public $x_refquota;
	public $x_refreservation;
	public $x_reservation;
	public $x_secondarycache;
	public $x_setuid;
//	public $x_sharesmb;
//	public $x_sharenfs;
	public $x_snapdir;
	public $x_sync;
	public $x_type;
	public $x_utf8only;
	public $x_volmode;
	public $x_volblocksize;
	public $x_volsize;
//	public $x_vscan;
//	public $x_xattr;

	const REGEXP_SIZE = '/^(0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?[b]?$/i';
	const REGEXP_SIZEORNONE = '/^((0*[1-9]\d*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none)$/i';
	const REGEXP_SIZEORNONEORNOTHING = '/^((0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none|^$)$/i';	

	public function get_aclinherit() {
		return $this->x_aclinherit ?? $this->init_aclinherit();
	}
	public function init_aclinherit() {
		$property = $this->x_aclinherit = new property_list($this);
		$property->
			set_name('aclinherit')->
			set_title(gettext('ACL Inherit'));
		$options = [
			'discard' => gettext('Discard - Do not inherit entries'),
			'noallow' => gettext('Noallow - Only inherit deny entries'),
			'restricted' => gettext('Restricted - Inherit all but "write ACL" and "change owner"'),
			'passthrough' => gettext('Passthrough - Inherit all entries'),
//			'secure' => gettext('Same as "Restricted" - kept for compatibility.'),
			'passthrough-x' => gettext('Passthrough-X - Inherit all but "execute" when not specified')
		];
		$property->
			set_id('aclinherit')->
			set_description(gettext('This attribute determines the behavior of Access Control List inheritance.'))->
			set_defaultvalue('restricted')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_aclmode() {
		return $this->x_aclmode ?? $this->init_aclmode();
	}
	public function init_aclmode() {
		$property = $this->x_aclmode = new property_list($this);
		$property->
			set_name('aclmode')->
			set_title(gettext('ACL Mode'));
		$options = [
			'discard' => gettext('Discard - Discard ACL'),
			'groupmask' => gettext('Groupmask - Mask ACL with mode'),
			'passthrough' => gettext('Passthrough - Do not change ACL'),
			'restricted' => gettext('Restricted')
		];
		$property->
			set_id('aclmode')->
			set_description(gettext('This attribute controls the ACL behavior when a file is created or whenever the mode of a file or a directory is modified.'))->
			set_defaultvalue('discard')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_atime() {
		return $this->x_atime ?? $this->init_atime();
	}
	public function init_atime() {
		$property = $this->x_atime = new property_list($this);
		$property->
			set_name('atime')->
			set_title(gettext('Access Time'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('atime')->
			set_description(gettext('Controls whether the access time for files is updated when they are read.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_canmount() {
		return $this->x_canmount ?? $this->init_canmount();
	}
	public function init_canmount() {
		$property = $this->x_canmount = new property_list($this);
		$property->
			set_name('canmount')->
			set_title(gettext('Can Mount'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
			'noauto' => gettext('Noauto'),
		];
		$property->
			set_id('canmount')->
			set_description(gettext('If this property is set to off, the file system cannot be mounted.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_casesensitivity() {
		return $this->x_casesensitivity ?? $this->init_casesensitivity();
	}
	public function init_casesensitivity() {
		$property = $this->x_casesensitivity = new property_list($this);
		$property->
			set_name('casesensitivity')->
			set_title(gettext('Case Sensitivity'));
		$options = [
			'sensitive' => gettext('Sensitive'),
			'insensitive' => gettext('Insensitive'),
			'mixed' => gettext('Mixed'),
		];
		$property->
			set_id('casesensitivity')->
			set_description(gettext('Indicates whether the file name matching algorithm used by the filesystem should be case-sensitive, case-insensitive, or allow a combination of both styles of matching.'))->
			set_defaultvalue('sensitive')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_checksum() {
		return $this->x_checksum ?? $this->init_checksum();
	}
	public function init_checksum() {
		$property = $this->x_checksum = new property_list($this);
		$property->
			set_name('checksum')->
			set_title(gettext('Checksum'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
			'fletcher2' => 'Fletcher 2',
			'fletcher4' => 'Fletcher 4',
			'sha256' => 'SHA-256',
			'noparity' => gettext('No Parity'),
			'sha512' => 'SHA-512',
			'skein' => 'Skein',
//			'edonr' => 'Edon-R',
		];
		$property->
			set_id('checksum')->
			set_description(gettext('Defines the checksum algorithm.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_compression() {
		return $this->x_compression ?? $this->init_compression();
	}
	public function init_compression() {
		$property = $this->x_compression = new property_list($this);
		$property->
			set_name('compression')->
			set_title(gettext('Compression'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
			'lz4' => 'lz4',
			'lzjb' => 'lzjb',
			'gzip' => 'gzip',
			'gzip-1' => 'gzip-1',
			'gzip-2' => 'gzip-2',
			'gzip-3' => 'gzip-3',
			'gzip-4' => 'gzip-4',
			'gzip-5' => 'gzip-5',
			'gzip-6' => 'gzip-6',
			'gzip-7' => 'gzip-7',
			'gzip-8' => 'gzip-8',
			'gzip-9' => 'gzip-9',
			'zle' => 'zle'
		];
		$property->
			set_id('compression')->
			set_description(gettext("Controls the compression algorithm. 'LZ4' is now the recommended compression algorithm. Setting compression to 'On' uses the LZ4 compression algorithm if the feature flag lz4_compress is active, otherwise LZJB is used. You can specify the 'GZIP' level by using the value 'GZIP-N', where N is an integer from 1 (fastest) to 9 (best compression ratio). Currently, 'GZIP' is equivalent to 'GZIP-6'."))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_copies() {
		return $this->x_copies ?? $this->init_copies();
	}
	public function init_copies() {
		$property = $this->x_copies = new property_list($this);
		$property->
			set_name('copies')->
			set_title(gettext('Copies'));
		$options = [
			'1' => gettext('1'),
			'2' => gettext('2'),
			'3' => gettext('3')
		];
		$property->
			set_id('copies')->
			set_description(gettext('Controls the number of copies of data stored for this dataset.'))->
			set_defaultvalue('1')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
/*
	public function get_devices() {
		return $this->x_devices ?? $this->init_devices();
	}
	public function init_devices() {
		$property = $this->x_devices = new property_list($this);
		$property->
			set_name('devices')->
			set_title(gettext('Devices'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('devices')->
			set_description(gettext('The devices property is currently not supported on FreeBSD.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
	public function get_dedup() {
		return $this->x_dedup ?? $this->init_dedup();
	}
	public function init_dedup() {
		$property = $this->x_dedup = new property_list($this);
		$property->
			set_name('dedup')->
			set_title(gettext('Dedup Method'));
		$description = '<div>' . gettext('Controls the dedup method.') . '</div>'
			. '<div><b>'
			. '<font color="red">' . gettext('WARNING') . '</font>' . ': '
			. '<a href="https://www.xigmanas.com/wiki/doku.php?id=documentation:setup_and_user_guide:disks_zfs_datasets_dataset" target="_blank">'
			. gettext('See ZFS datasets & deduplication wiki article BEFORE using this feature.')
			. '</a>'
			. '</b></div>';
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
			'verify' => gettext('Verify'),
			'sha256' => 'SHA-256',
			'sha256,verify' => gettext('SHA-256, Verify'),
			'sha512' => 'SHA-512',
			'sha512,verify' => gettext('SHA-512, Verify'),
			'skein' => 'Skein',
			'skein,verify' => gettext('Skein, Verify'),
//			'edonr,verify' => gettext('Edon-R, Verify')
		];
		$property->
			set_id('dedup')->
			set_description($description)->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_exec() {
		return $this->x_exec ?? $this->init_exec();
	}
	public function init_exec() {
		$property = $this->x_exec = new property_list($this);
		$property->
			set_name('exec')->
			set_title(gettext('Exec'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('exec')->
			set_description(gettext('Controls whether processes can be executed from within this file system.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_jailed() {
		return $this->x_jailed ?? $this->init_jailed();
	}
	public function init_jailed() {
		$property = $this->x_jailed = new property_list($this);
		$property->
			set_name('jailed')->
			set_title(gettext('Jailed'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('jailed')->
			set_description(gettext('Controls whether the dataset is managed from a jail.'))->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_logbias() {
		return $this->x_logbias ?? $this->init_logbias();
	}
	public function init_logbias() {
		$property = $this->x_logbias = new property_list($this);
		$property->
			set_name('logbias')->
			set_title(gettext('Logbias'));
		$options = [
			'latency' => gettext('Latency'),
			'throughput' => gettext('Throughput')
		];
		$property->
			set_id('logbias')->
			set_description(gettext('Provide a hint to ZFS about handling of synchronous requests in this dataset.'))->
			set_defaultvalue('latency')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
/*
	public function get_name() {
		return $this->x_name ?? $this->init_name();
	}
	public function init_name() {
		$property = $this->x_name = new property_text($this);
		$property->
			name('name')->
			title(gettext('Name'));
		$regexp = sprintf('/^[a-z\d][a-z\d%1$s]*(?:\/[a-z\d][a-z\d%1$s]*)*$/i',preg_quote('.:-_','/'));
		$property->
			id('name')->
			description(gettext('The name of the dataset.'))->
			defaultvalue('')->
			editableonadd(true)->
			editableonmodify(false)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $regexp])->
			set_message_error(sprintf('%s: %s',$property->title(),gettext('The value is invalid.')));
		return $property;
	}
 */
/*
	public function get_nbmand() {
		return $this->x_nbmand ?? $this->init_nbmand();
	}
	public function init_nbmand() {
		$property = $this->x_nbmand = new property_list($this);
		$property->
			set_name('nbmand')->
			set_title(gettext('NBMAND'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('nbmand')->
			set_description(gettext('The nbmand property is currently not supported on FreeBSD.'))->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
	public function get_normalization() {
		return $this->x_normalization ?? $this->init_normalization();
	}
	public function init_normalization() {
		$property = $this->x_normalization = new property_list($this);
		$property->
			set_name('normalization')->
			set_title(gettext('Normalization'));
		$options = [
			'none' => gettext('None'),
			'formC' => 'formC',
			'formD' => 'formD',
			'formKC' => 'formKC',
			'formKD' => 'formKD',
		];
		$property->
			set_id('normalization')->
			set_description(gettext('Indicates whether the file system should perform a unicode normalization of file names whenever two file names are compared, and which normalization algorithm should be used.'))->
			set_defaultvalue('none')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_primarycache() {
		return $this->x_primarycache ?? $this->init_primarycache();
	}
	public function init_primarycache() {
		$property = $this->x_primarycache = new property_list($this);
		$property->
			set_name('primarycache')->
			set_title(gettext('Primary Cache'));
		$options = [
			'all' => gettext('Both user data and metadata will be cached in ARC.'),
			'metadata' => gettext('Only metadata will be cached in ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in ARC.')
		];
		$property->
			set_id('primarycache')->
			set_description(gettext('Controls what is cached in the primary cache (ARC).'))->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_quota() {
		return $this->x_quota ?? $this->init_quota();
	}
	public function init_quota() {
		$property = $this->x_quota = new property_text($this);
		$property->
			set_name('quota')->
			set_title(gettext('Quota'));
		$property->
			set_id('quota')->
			set_description(gettext('Limits the amount of space a dataset and its descendents can consume.'))->
			set_defaultvalue('')->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_readonly() {
		return $this->x_readonly ?? $this->init_readonly();
	}
	public function init_readonly() {
		$property = $this->x_readonly = new property_list($this);
		$property->
			set_name('readonly')->
			set_title(gettext('Read Only'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('readonly')->
			set_description(gettext('Controls whether this dataset can be modified.'))->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_redundant_metadata() {
		return $this->x_redundant_metadata ?? $this->init_redundant_metadata();
	}
	public function init_redundant_metadata() {
		$property = $this->x_redundant_metadata = new property_list($this);
		$property->
			set_name('redundant_metadata')->
			set_title(gettext('Redundant Metadata'));
		$options = [
			'all' => gettext('All'),
			'most' => gettext('Most')
		];
		$property->
			set_id('redundant_metadata')->
			set_description(gettext('Controls what types of metadata are stored redundantly.'))->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_refquota() {
		return $this->x_refquota ?? $this->init_refquota();
	}
	public function init_refquota() {
		$property = $this->x_refquota = new property_text($this);
		$property->
			set_name('refquota')->
			set_title(gettext('Refquota'));
		$property->
			set_id('refquota')->
			set_description(gettext('Limits the amount of space a dataset can consume.'))->
			set_defaultvalue('')->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_refreservation() {
		return $this->x_refreservation ?? $this->init_refreservation();
	}
	public function init_refreservation() {
		$property = $this->x_refreservation = new property_text($this);
		$property->
			set_name('refreservation')->
			set_title(gettext('Refreservation'));
		$property->set_id('refreservation')->
			set_description(gettext('The minimum amount of space guaranteed to a dataset, not including its descendents.'))->
			set_defaultvalue('')->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_reservation() {
		return $this->x_reservation ?? $this->init_reservation();
	}
	public function init_reservation() {
		$property = $this->x_reservation = new property_text($this);
		$property->
			set_name('reservation')->
			set_title(gettext('Reservation'));
		$property->
			set_id('reservation')->
			set_description(gettext('The minimum amount of space guaranteed to a dataset and its descendents.'))->
			set_defaultvalue('')->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_secondarycache() {
		return $this->x_secondarycache ?? $this->init_secondarycache();
	}
	public function init_secondarycache() {
		$property = $this->x_secondarycache = new property_list($this);
		$property->set_name('secondarycache')->
			set_title(gettext('Secondary Cache'));
		$options = [
			'all' => gettext('Both user data and metadata will be cached in L2ARC.'),
			'metadata' => gettext('Only metadata will be cached in L2ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in L2ARC.')
		];
		$property->
			set_id('secondarycache')->
			set_description(gettext('Controls what is cached in the secondary cache (L2ARC).'))->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_setuid() {
		return $this->x_setuid ?? $this->init_setuid();
	}
	public function init_setuid() {
		$property = $this->x_setuid = new property_list($this);
		$property->
			set_name('setuid')->
			set_title(gettext('Set UID'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('setuid')->
			set_description(gettext('Controls whether the set-UID bit is respected for the file system.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
/*
	public function get_sharesmb() {
		return $this->x_sharesmb ?? $this->init_sharesmb();
	}
	public function init_sharesmb() {
		$property = $this->x_sharesmb = new property_list($this);
		$property->
			set_name('sharesmb')->
			set_title(gettext('Share SMB'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('sharesmb')->
			set_description(gettext('The sharesmb property currently has no effect on FreeBSD.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
/*
	public function get_sharenfs() {
		return $this->x_sharenfs ?? $this->init_sharenfs();
	}
	public function init_sharenfs() {
		$property = $this->x_sharenfs = new property_list($this);
		$property->
			set_name('sharenfs')->
			set_title(gettext('Share NFS'));
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('sharenfs')->
			set_description(gettext('Controls whether the file system is shared via NFS.'))->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd($editableonadd)->
			set_editableonmodify($editableonmodify)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
	public function get_snapdir() {
		return $this->x_snapdir ?? $this->init_snapdir();
	}
	public function init_snapdir() {
		$property = $this->x_snapdir = new property_list($this);
		$property->
			set_name('snapdir')->
			set_title(gettext('Snapdir'));
		$options = [
			'hidden' => gettext('Hidden'),
			'visible' => gettext('Visible'),
		];
		$property->
			set_id('snapdir')->
			set_description(gettext('Controls whether the .zfs directory is hidden or visible in the root of the file system.'))->
			set_defaultvalue('hidden')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_sync() {
		return $this->x_sync ?? $this->init_sync();
	}
	public function init_sync() {
		$property = $this->x_sync = new property_list($this);
		$property->
			set_name('sync')->
			set_title(gettext('Sync'));
		$options = [
			'standard' => gettext('Standard'),
			'always' => gettext('Always'),
			'disabled' => gettext('Disabled')
		];
		$property->
			set_id('sync')->
			set_description(gettext('Controls the behavior of synchronous requests.'))->
			set_defaultvalue('standard')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_type() {
		return $this->x_type ?? $this->init_type();
	}
	public function init_type() {
		$property = $this->x_type = new property_list($this);
		$property->
			set_name('type')->
			set_title(gettext('Dataset Type'));
		$options = [
			'filesystem' => gettext('File System - can be mounted within the standard system namespace and behaves like other file systems.'),
			'volume' => gettext('Volume - A logical volume. Can be exported as a raw or block device.')
//			'snapshot' => gettext('Snapshot - A read-only version of a file system or volume at a given point in time.')
//			'bookmark' => gettext('Bookmark - Creates a bookmark of a given snapshot.')
		];
		$property->
			set_id('type')->
			set_description(gettext('Controls the type of the ZFS dataset.'))->
			set_defaultvalue('filesystem')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_utf8only() {
		return $this->x_utf8only ?? $this->init_utf8only();
	}
	public function init_utf8only() {
		$property = $this->x_utf8only = new property_list($this);
		$property->
			set_name('utf8only')->
			set_title(gettext('UTF-8 Only'));
		$description = gettext('Indicates whether the file system should reject file names that include characters that are not present in the UTF-8 character code set.');
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('utf8only')->
			set_description($description)->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_volblocksize() {
		return $this->x_volblocksize ?? $this->init_volblocksize();
	}
	public function init_volblocksize() {
		$property = $this->x_volblocksize = new property_list($this);
		$property->
			set_name('volblocksize')->
			set_title(gettext('Block Size'));
		$options = [
			'512B' => '512B',
			'1K' => '1K',
			'2K' => '2K',
			'4K' => '4K',
			'8K' => '8K',
			'16K' => '16K',
			'32K' => '32K',
			'64K' => '64K',
			'128K' => '128K'
		];
		$property->
			set_id('volblocksize')->
			set_description(gettext('ZFS volume block size. This value can not be changed after creation.'))->
			set_defaultvalue('8K')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_volmode() {
		return $this->x_volmode ?? $this->init_volmode();
	}
	public function init_volmode() {
		$property = $this->x_volmode = new property_list($this);
		$property->
			set_name('volmode')->
			set_title(gettext('Volume Mode'));
		$options = [
			'default' => gettext('Default'),
			'geom' => 'geom',
			'dev' => 'dev',
			'none' => 'none'
		];
		$property->
			set_id('volmode')->
			set_description(gettext('Specifies how the volume should be exposed to the OS.'))->
			set_defaultvalue('default')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_volsize() {
		return $this->x_volsize ?? $this->init_volsize();
	}
	public function init_volsize() {
		$property = $this->x_volsize = new property_text($this);
		$property->
			set_name('volsize')->
			set_title(gettext('Volume Size'));
		$property->
			set_id('volsize')->
			set_description(gettext('ZFS volume size. You can use human-readable suffixes like K, KB, M, GB.'))->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(20)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => NULL,'regexp' => $this::REGEXP_SIZE])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
/*
	public function get_vscan() {
		return $this->x_vscan ?? $this->init_vscan();
	}
	public function init_vscan() {
		$property = $this->x_vscan = new property_list($this);
		$property->
			set_name('vscan')->
			set_title(gettext('Vscan'));
		$description = gettext('The vscan property is currently not supported on FreeBSD.');
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('vscan')->
			set_description($description)->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
/*
	public function get_xattr() {
		return $this->x_xattr ?? $this->init_xattr();
	}
	public function init_xattr() {
		$property = $this->x_attr = new property_list($this);
		$property->
			set_name('xattr')->
			set_title(gettext('Xattr'));
		$description = gettext('The xattr property is currently not supported on FreeBSD.');
		$options = [
			'on' => gettext('On'),
			'off' => gettext('Off'),
		];
		$property->
			set_id('xattr')->
			set_description($description)->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
 */
}
class properties_zfs_dataset_enhanced extends properties_zfs_dataset {
	public $supported_properties = [];
}
class properties_zfs_filesystem extends properties_zfs_dataset_enhanced {
	public function load() {
		parent::load();
		$this->supported_properties = [
			'aclinherit',
			'aclmode',
			'atime',
			'canmount',
			'casesensitivity',
			'checksum',
			'compression',
			'copies',
			'dedup',
			'logbias',
			'normalization',
			'primarycache',
			'quota',
			'readonly',
			'redundant_metadata',
			'refquota',
			'refreservation',
			'reservation',
			'secondarycache',
			'setuid',
			'snapdir',
			'sync',
			'type',
			'utf8only'
		];
	}
}
class properties_zfs_volume extends properties_zfs_dataset_enhanced {
	public function load() {
		parent::load();
		$this->supported_properties = [
			'checksum',
			'compression',
			'dedup',
			'logbias',
			'primarycache',
			'secondarycache',
			'sparse',
			'sync',
			'volblocksize',
			'volmode',
			'volsize'
		];
	}
}
