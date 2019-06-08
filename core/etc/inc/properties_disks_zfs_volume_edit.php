<?php
/*
	properties_disks_zfs_volume_edit.php

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
require_once 'properties_disks_zfs_volume.php';

class properties_disks_zfs_volume_edit extends properties_disks_zfs_volume {
	const REGEXP_SIZE = '/^(0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?$/i';
	const REGEXP_SIZEORNONE = '/^((0*[1-9]\d*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none)$/i';
	const REGEXP_SIZEORNONEORNOTHING = '/^((0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none|^$)$/i';	

	public function init_description() {
		$property = parent::init_description();
		$description = gettext('You may enter a description for your reference.');
		$placeholder = gettext('Enter a description');
		$property->
			set_id('desc')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(1024)->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => ''])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_checksum() {
		$property = parent::init_checksum();
		$description = gettext('Defines the checksum algorithm.');
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
			set_description($description)->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_compression() {
		$property = parent::init_compression();
		$description = gettext("Controls the compression algorithm. 'LZ4' is now the recommended compression algorithm. Setting compression to 'On' uses the LZ4 compression algorithm if the feature flag lz4_compress is active, otherwise LZJB is used. You can specify the 'GZIP' level by using the value 'GZIP-N', where N is an integer from 1 (fastest) to 9 (best compression ratio). Currently, 'GZIP' is equivalent to 'GZIP-6'.");
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
			set_description($description)->
			set_defaultvalue('on')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_dedup() {
		$property = parent::init_dedup();
		$description = 
			sprintf('<div>%s</div>',gettext('Controls the dedup method.')) .
			'<div style="font-weight: bold;">' .
			sprintf('<span style="color: red;">%s</span>: ',gettext('WARNING')) .
			'<a href="https://wiki.xigmanas.com/doku.php?id=documentation:setup_and_user_guide:disks_zfs_datasets_dataset" target="_blank">' .
			gettext('See ZFS datasets & deduplication wiki article BEFORE using this feature.') .
			'</a>' .
			'</div>';
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
	public function init_logbias() {
		$property = parent::init_logbias();
		$description = gettext('Provide a hint to ZFS about handling of synchronous requests in this dataset.');
		$options = [
			'latency' => gettext('Latency'),
			'throughput' => gettext('Throughput')
		];
		$property->
			set_id('logbias')->
			set_description($description)->
			set_defaultvalue('latency')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function test_name(string $value = '') {
		//	must not be empty
		if(1 === preg_match('/^$/',$value)):
			return NULL;
		endif;
		//	must not be greater than 256 characters
		if(strlen($value) > 256):
			return NULL;
		endif;
		$a_component = explode('/',$value);
		foreach($a_component as $component):
			if(1 !== preg_match(sprintf('/^[a-z\d][a-z\d%1$s]+$/i',preg_quote('.:-_','/')),$component)):
				return NULL;
			endif;
		endforeach;
		return $value;
	}
	public function init_name() {
		$property = parent::init_name();
		$description = gettext('Enter a name for this volume.');
		$placeholder = gettext('Enter Volume Name');
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(256)->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_filter(FILTER_CALLBACK)->set_filter_options([$this,'test_name'])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_pool() {
		$property = parent::init_pool();
		$description = gettext('The name of the ZFS pool.');
		$property->
			set_id('pool')->
			set_description($description)->
			set_defaultvalue('')->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_primarycache() {
		$property = parent::init_primarycache();
		$description = gettext('Controls what is cached in the primary cache (ARC).');
		$options = [
			'all' => gettext('Both user data and metadata will be cached in ARC.'),
			'metadata' => gettext('Only metadata will be cached in ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in ARC.')
		];
		$property->
			set_id('primarycache')->
			set_description($description)->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_secondarycache() {
		$property = parent::init_secondarycache();
		$description = gettext('Controls what is cached in the secondary cache (L2ARC).');
		$options = [
			'all' => gettext('Both user data and metadata will be cached in L2ARC.'),
			'metadata' => gettext('Only metadata will be cached in L2ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in L2ARC.')
		];
		$property->
			set_id('secondarycache')->
			set_description($description)->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_sparse() {
		$property = parent::init_sparse();
		$caption = gettext('Use as sparse volume (thin provisioning).');
		$property->
			set_caption($caption);
		return $property;
	}
	public function init_sync() {
		$property = parent::init_sync();
		$description = gettext('Controls the behavior of synchronous requests.');
		$options = [
			'standard' => gettext('Standard'),
			'always' => gettext('Always'),
			'disabled' => gettext('Disabled')
		];
		$property->
			set_id('sync')->
			set_description($description)->
			set_defaultvalue('standard')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_volblocksize() {
		$property = parent::init_volblocksize();
		$description = gettext('ZFS volume block size. This value can not be changed after creation.');
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
			set_description($description)->
			set_defaultvalue('8K')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_volmode() {
		$property = parent::init_volmode();
		$description = gettext('Specifies how the volume should be exposed to the OS.');
		$options = [
			'default' => gettext('Default'),
			'geom' => 'geom',
			'dev' => 'dev',
			'none' => 'none'
		];
		$property->
			set_id('volmode')->
			set_description($description)->
			set_defaultvalue('default')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function init_volsize() {
		$property = parent::init_volsize();
		$description = gettext('ZFS volume size. You can use human-readable suffixes like K, KB, M, GB.');
		$property->
			set_id('volsize')->
			set_description($description)->
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
}
