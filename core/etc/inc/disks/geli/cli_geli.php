<?php
/*
	cli_geli.php

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
namespace disks\geli;
/**
 *	Class to compose and execute geli commands
 */
final class cli_geli {
	static $cli2cfg_aalgo = [
		'hmac/md5' => 'hmac/md5',
		'hmac/sha1' => 'hmac/sha1',
		'hmac/ripemd160' => 'hmac/ripemd160',
		'hmac/sha256' => 'hmac/sha256',
		'hmac/sha384' => 'hmac/sha384',
		'hmac/sha512' => 'hmac/sha512'
	];
	static $cli2cfg_ealgo = [
		'null' => 'null-cbc',
		'null-cbc' => 'null-cbc',
		'aes' => 'aes-xts',
		'aes-xts' => 'aes-xts',
		'aes-cbc' => 'aes-cbc',
		'blowfish' => 'blowfish-cbc',
		'blowfish-cbc' => 'blowfish-cbc',
		'camellia' => 'camellia-cbc',
		'camellia-cbc' => 'camellia-cbc',
		'3des' => '3des-cbc',
		'3des-cbc' => '3des-cbc'
	];
	static $cli2cfg_keylen = [
		'128' => '128',
		'160' => '160',
		'192' => '192',
		'224' => '224',
		'256' => '256',
		'288' => '288',
		'320' => '320',
		'352' => '352',
		'384' => '384',
		'416' => '416',
		'448' => '448'
	];
	static $valid_ealgo_keylen = [
		'null-cbc' => [],
		'aes-xts' => ['128','256'],
		'aes-cbc' => ['128','192','256'],
		'blowfish-cbc' => ['128','160','192','224','256','288','320','352','384','416','448'],
		'camellia-cbc' => ['128','192','256'],
		'3des-cbc' => ['192']
	];
	static $cli2cfg_sectorsize = [
		'512' => '512',
		'1024' => '1024',
		'2048' => '2048',
		'4096' => '4096',
		'8192' => '8192'
	];
	private $action;
	private $command_composer = [];
	private $command = '';
	private $provider = [];
	private $passphrase = null;
	private $key = null;
	private $passphrase_new = null;
	private $key_new = null;
	private $aalgo = null;
	private $ealgo = null;
	private $keylen = null;
	private $sectorsize = null;
	private $decrypt_before_loading_rootfs = null;
	private $enable_boot_rootfs = null;
	private $passthru_trim = null;
	private $tmp_filename_passphrase = false;
	private $tmp_filename_key = false;
	private $tmp_filename_passphrase_new = false;
	private $tmp_filename_key_new = false;
	public function __destruct() {
//		Ensure to delete all temporary files.
		$this->delete_temporary_files();
	}
/**
 *	Add a provider to the list of providers.
 *	@param string $provider Provider, i.e. da5
 *	@return $this
 */
	public function add_provider(string $provider) {
		$this->provider[] = $provider;
		return $this;
	}
/**
 *	Add a filename to the list of passphrase filenames.<br/>
 *	The passphrase can be split across multiple files.
 *	@param string $passphrase_filename Name of the passphrase file
 *	@return $this
 */
	public function add_passphrase_file(string $passphrase_filename) {
		if(is_array($this->passphrase)):
			$this->passphrase[] = $passphrase_filename;
		else:
			$this->passphrase = [$passphrase_filename];
		endif;
		return $this;
	}
/**
 *	Add a filename to the list of new passphrase filenames.<br/>
 *	The new passphrase can be split across multiple files.
 *	@param string $new_passphrase_filename Name of the new passphrase file
 *	@return $this
 */
	public function add_new_passphrase_file(string $new_passphrase_filename) {
		if(is_array($this->passphrase_new)):
			$this->passphrase_new[] = $new_passphrase_filename;
		else:
			$this->passphrase_new = [$new_passphrase_filename];
		endif;
		return $this;
	}
/**
 *	Set passphrase from a string.
 *	@param string $passphrase Passphrase
 *	@return $this
 */
	public function set_passphrase(string $passphrase) {
		$this->passphrase = $passphrase;
		return $this;
	}
/**
 *	Set new passphrase from a string.
 *	@param string $new_passphrase Passphrase
 *	@return $this
 */
	public function set_new_passphrase(string $new_passphrase) {
		$this->passphrase_new = $new_passphrase;
		return $this;
	}
/**
 *	Add a filename to the list of key filenames.<br/>
 *	The key can be split across multiple files.
 *	@param string $key_filename Name of the key file
 *	@return $this
 */
	public function add_key_file(string $key_filename) {
		if(is_array($this->key)):
			$this->key[] = $key_filename;
		else:
			$this->key = [$key_filename];
		endif;
		return $this;
	}
/**
 *	Add a filename to the list of key filenames.<br/>
 *	The key can be split across multiple files.
 *	@param string $new_key_filename Name of the new key file
 *	@return $this
 */
	public function add_new_key_file(string $new_key_filename) {
		if(is_array($this->key_new)):
			$this->key_new[] = $new_key_filename;
		else:
			$this->key_new = [$new_key_filename];
		endif;
		return $this;
	}
	public function set_key(string $key) {
		$this->key = $key;
		return $this;
	}
	public function set_new_key(string $new_key) {
		$this->key_new = $new_key;
		return $this;
	}
/**
 *	Sets the data integrity verification algorithm, If no parameter<br/>
 *	is given or the parameter is invalid, no algorithm will be set.
 *	@param string $aalgo Data integrity verification algorithm
 *	@return $this
 */
	public function set_aalgo(string $aalgo = null) {
		if(is_null($aalgo)):
			$this->aalgo = $aalgo;
		else:
			$test_aalgo = strtolower($aalgo);
			if(array_key_exists($test_aalgo,self::$cli2cfg_aalgo)):
				$this->aalgo = self::$cli2cfg_aalgo[$test_aalgo];
			else:
				$this->aalgo = null;
			endif;
		endif;
		return $this;
	}
/**
 *  Sets the encryption algorithm and the key length of the<br/>
 *	encryption.
 *	If no encryption is given or the encryption is wrong, default<br/>
 *	encryption will be used. If no key length is given or if the<br/>
 *	key length is invalid, the default key length will be used.
 *	@param string $ealgo Encryption algorithm
 *	@param string $keylen Encryption key length
 *	@return $this
 */
	public function set_ealgo(string $ealgo = null,string $keylen = null) {
		if(is_null($ealgo)):
			$this->ealgo = null;
		else:
			$test_ealgo = strtolower($ealgo);
			if(array_key_exists($test_ealgo,self::$cli2cfg_ealgo)):
				$this->ealgo = self::$cli2cfg_ealgo[$test_ealgo];
			else:
				$this->ealgo = null;
			endif;
		endif;
		if(is_null($keylen)):
			$this->keylen = null;
		else:
			$test_keylen = strtolower($keylen);
			if(array_key_exists($test_keylen,self::$cli2cfg_keylen)):
				$this->keylen = self::$cli2cfg_keylen[$test_keylen];
			else:
				$this->keylen = null;
			endif;
		endif;
		if(is_null($this->keylen) || is_null($this->ealgo) || !in_array($this->keylen,self::$valid_ealgo_keylen[$this->ealgo])):
			$this->keylen = null;
		endif;
		return $this;
	}
/**
 *	The sector size of the decrypted provider
 *	@param string $sectorsize Sector size
 *	@return $this
 */
	public function set_sectorsize(string $sectorsize = null) {
		if(is_null($sectorsize)):
			$this->sectorsize = null;
		else:
			$test_sectorsize = strtolower($sectorsize);
			if(array_key_exists($test_sectorsize,self::$cli2cfg_sectorsize)):
				$this->sectorsize = self::$cli2cfg_sectorsize[$test_sectorsize];
			else:
				$this->sectorsize = null;
			endif;
		endif;
		return $this;
	}
/**
 *	Try to decrypt the partition during boot, before the root<br/>
 *	partition is mounted. This makes it possible to use an encrypted</br/>
 *	root partition.
 *	@param bool $decrypt_before_loading_rootfs Set to true to decrypt
 *	@return $this
 */
	public function set_decrypt_before_loading_rootfs(bool $decrypt_before_loading_rootfs = null) {
		$this->decrypt_before_loading_rootfs = $decrypt_before_loading_rootfs;
		return $this;
	}
/**
 *	Enable booting from this encrypted root filesystem. The boot<br/>
 *	loader prompts for the passphrase and loads loader(8) from the<br/>
 *	encrypted partition.
 *	@param bool $enable_boot_rootfs Set to true to enable boot
 *	@return $this
 */
	public function set_enable_boot_rootfs(bool $enable_boot_rootfs = null) {
		$this->enable_boot_rootfs = $enable_boot_rootfs;
		return $this;
	}
/**
 *  Pass through BIO_DELETE calls (i.e. TRIM/UNMAP).
 *	@param bool $passthru_trim Set to true to enable passthru
 *	@return $this
 */
	public function set_passthru_trim(bool $passthru_trim = null) {
		$this->passthru_trim = $passthru_trim;
		return $this;
	}
/**
 *	Initialize command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_new(string $action) {
		$this->action = $action;
		$this->command_composer = ['/sbin/geli',$this->action];
		return 0;
	}
/**
 *	Helper function to add all providers to the command composer.
 *	@param bool $single_provider_mode Some commands expect one provider only.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_providers(bool $single_provider_mode = false): int {
		$found_a_provider = false;
		foreach($this->provider as $provider):
			if(1 === preg_match('/\S/',$provider)):
				if($single_provider_mode && $found_a_provider):
					write_log(sprintf('geli %s: too many providers',$this->action));
					return 1;
				endif;
				$this->command_composer[] = escapeshellarg($provider);
				$found_a_provider = true;
			else:
				write_log(sprintf('geli %s: provider name is empty',$this->action));
				return 1;
			endif;
		endforeach;
		if(!$found_a_provider):
			write_log(sprintf('geli %s: missing provider',$this->action));
			return 1;
		endif;
		return 0;
	}
/**
 *	Helper function to add passphrase and key information to the<br/>
 *	command composer.<br/>
 *	A string passphrase/key is written to a temporary file.<br/>
 *	Temporary files should be deleted after the command has been<br/>
 *	executed.<br/>
 *	The passphrase information, the key information or both must be<br/>
 *	provided, otherwise the function will return an error.<br/>
 *	If only the key information was provided, the "no passphrase"<br/>
 *	flag will be set to prevent geli asking for the passphrase on the<br/>
 *	command line.
 *	@global array $g The global array
 *	@param bool $setkey_new_key Set to true to create 2nd parameter
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_userkeys(bool $setkey_new_key = false): int {
		global $g;

		$found_a_passphrase_file = false;
		$found_a_key_file = false;
		$tmp_filename_passphrase = false;
		$tmp_filename_key = false;
		switch($this->action):
			case 'init':
				$opt_passphrase_file = '-J';
				$opt_key_file = '-K';
				$opt_passphrase_skip = '-P';
				$setkey_new_key = false;
				break;
			case 'attach':
			case 'resume':
				$opt_passphrase_file = '-j';
				$opt_key_file = '-k';
				$opt_passphrase_skip = '-p';
				$setkey_new_key = false;
				break;
			case 'setkey':
				if($setkey_new_key):
					$opt_passphrase_file = '-J';
					$opt_key_file = '-K';
					$opt_passphrase_skip = '-P';
				else:
					$opt_passphrase_file = '-j';
					$opt_key_file = '-k';
					$opt_passphrase_skip = '-p';
				endif;
				break;
			default:
				return 1;
		endswitch;
//		user key passphrase
		if(is_string($this->passphrase)):
//			write string to a temporary file
			$tmp_filename_passphrase = tempnam($g['varrun_path'],'ukp');
			if($setkey_new_key):
				$this->tmp_filename_passphrase_new = $tmp_filename_passphrase;
			else:
				$this->tmp_filename_passphrase = $tmp_filename_passphrase;
			endif;
			if(false === $tmp_filename_passphrase):
				write_log(sprintf('geli %s: failed to create passphrase file',$this->action));
				return 1;
			elseif(false === file_put_contents($tmp_filename_passphrase,$this->passphrase)):
				write_log(sprintf('geli %s: failed to write passphrase',$this->action));
				return 1;
			endif;
			$passphrase_filenames = [$tmp_filename_passphrase];
		elseif(is_array($this->passphrase)):
			$passphrase_filenames = $this->passphrase;
		else:
			$passphrase_filenames = [];
		endif;
		foreach($passphrase_filenames as $passphrase_filename):
			if(is_string($passphrase_filename)):
				if(file_exists($passphrase_filename)):
					$this->command_composer[] = sprintf('%s %s',$opt_passphrase_file,escapeshellarg($passphrase_filename));
					$found_a_passphrase_file = true;
				else:
					write_log(sprintf('geli %s: passphrase file not found',$this->action));
					return 1;
				endif;
			else:
				write_log(sprintf('geli %s: passphrase filename type mismatch',$this->action));
				return 1;
			endif;
		endforeach;
//		user key keyfile
		if(is_string($this->key)):
//			write string to a temporary file
			$tmp_filename_key = tempnam($g['varrun_path'],'key');
			if($setkey_new_key):
				$this->tmp_filename_key_new = $tmp_filename_key;
			else:
				$this->tmp_filename_key = $tmp_filename_key;
			endif;
			if(false === $tmp_filename_key):
				write_log(sprintf('geli %s: failed to create key file',$this->action));
				return 1;
			elseif(false === file_put_contents($tmp_filename_key,$this->key)):
				write_log(sprintf('geli %s: failed to write key',$this->action));
				return 1;
			endif;
			$uk_key_filenames = [$tmp_filename_key];
		elseif(is_array($this->key)):
			$uk_key_filenames = $this->key;
		else:
			$uk_key_filenames = [];
		endif;
		foreach($uk_key_filenames as $uk_key_filename):
			if(is_string($uk_key_filename)):
				if(file_exists($uk_key_filename)):
					$this->command_composer[] = sprintf('%s %s',$opt_key_file,escapeshellarg($uk_key_filename));
					$found_a_key_file = true;
				else:
					write_log(sprintf('geli %s: key file not found',$this->action));
					return 1;
				endif;
			else:
				write_log(sprintf('geli %s: key filename type mismatch',$this->action));
				return 1;
			endif;
		endforeach;
		if(!$found_a_passphrase_file):
			if($found_a_key_file):
				$this->command_composer[] = $opt_passphrase_skip;
			else:
				write_log(sprintf('geli %s: user key component missing',$this->action));
				return 1;
			endif;
		endif;
		return 0;
	}
/**
 *	Helper function to add the aalgo parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_aalgo(): int {
		switch($this->action):
			case 'init':
			case 'onetime':
				if(is_string($this->aalgo)):
					$this->command_composer[] = sprintf('-a %s',$this->aalgo);
				endif;
				break;
		endswitch;
		return 0;
	}
/**
 *	Helper function to add the ealgo parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_ealgo(): int {
		switch($this->action):
			case 'init':
			case 'onetime':
				if(is_string($this->ealgo)):
					$this->command_composer[] = sprintf('-e %s',$this->ealgo);
					if(is_string($this->keylen)):
						$this->command_composer[] = sprintf('-l %s',$this->keylen);
					endif;
				endif;
				break;
		endswitch;
		return 0;
	}
/**
 *	Helper function to add the sectorsize parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_sectorsize(): int {
		switch($this->action):
			case 'init':
			case 'onetime':
				if(is_string($this->sectorsize)):
					$this->command_composer[] = sprintf('-s %s',$this->sectorsize);
				endif;
				break;
		endswitch;
		return 0;
	}
/**
 *	Helper function to add the decrypt before loading rootfs parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_decrypt_before_loading_rootfs(): int {
		if(is_bool($this->decrypt_before_loading_rootfs)):
			switch($this->action):
				case 'init':
					if($this->decrypt_before_loading_rootfs):
						$this->command_composer[] =  '-b';
					endif;
					break;
				case 'configure':
					$this->command_composer[] = $this->decrypt_before_loading_rootfs ? '-b' : '-B';
					break;
			endswitch;
		endif;
		return 0;
	}
/**
 *	Helper function to add the boot rootfs parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_enable_boot_rootfs(): int {
		if(is_bool($this->enable_boot_rootfs)):
			switch($this->action):
				case 'init':
					if($this->enable_boot_rootfs):
						$this->command_composer[] =  '-g';
					endif;
					break;
				case 'configure':
					$this->command_composer[] = $this->enable_boot_rootfs ? '-g' : '-G';
					break;
			endswitch;
		endif;
		return 0;
	}
/**
 *	Helper function to add the trim passthru parameter to the command composer.
 *	@return int Return 0 if successful, 1 if error
 */
	private function cmd_add_passthru_trim(): int {
		if(is_bool($this->passthru_trim)):
			switch($this->action):
				case 'init':
				case 'onetime':
					if(!$this->passthru_trim):
						$this->command_composer[] = '-T';
					endif;
					break;
				case 'configure':
					$this->command_composer[] = $this->passthru_trim ? '-t' : '-T';
					break;
			endswitch;
		endif;
		return 0;
	}
/**
 *	Helper function, delete all temporary files.
 */
	private function delete_temporary_files() {
		if(is_string($this->tmp_filename_passphrase)):
			@unlink($this->tmp_filename_passphrase);
			$this->tmp_filename_passphrase = false;
		endif;
		if(is_string($this->tmp_filename_key)):
			@unlink($this->tmp_filename_key);
			$this->tmp_filename_key = false;
		endif;
		if(is_string($this->tmp_filename_passphrase_new)):
			@unlink($this->tmp_filename_passphrase_new);
			$this->tmp_filename_passphrase_new = false;
		endif;
		if(is_string($this->tmp_filename_key_new)):
			@unlink($this->tmp_filename_key_new);
			$this->tmp_filename_key_new = false;
		endif;
		return 0;
	}
/**
 *	Attach the given provider. The master key will be decrypted using<br/>
 *	the given passphrase/keyfile(s) and a new GEOM provider will be<br/>
 *  created using the given provider names with an ".eli" suffix.
 *	@param bool $be_verbose Be verbose, default is false
 *	@return int Return 0 if successful, 1 if error
 */
	public function attach(bool $be_verbose = false) {
		$result = 1;
		$this->cmd_new('attach');
		if($be_verbose):
			$this->command_composer[] = '-v';
		endif;
		if((0 === $this->cmd_add_userkeys()) && (0 === $this->cmd_add_providers())):
			$this->command_composer[] = '2>&1';
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		$this->delete_temporary_files();
		return $result;
	}
/**
 *	Set/clear flags of the given providers.
 *	@return int Return 0 if successful, 1 if error
 */
	public function configure() {
		$result = 1;
		$this->cmd_new('configure');
		$this->cmd_add_decrypt_before_loading_rootfs();
		$this->cmd_add_enable_boot_rootfs();
		$this->cmd_add_passthru_trim();
		if(0 === $this->cmd_add_providers()):
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		return $result;
	}
/**
 *	Detach the given providers, which means remove the devfs entry<br/>
 *	and clear the keys from memory.
 *	@param bool $be_forceful Set force flag
 *	@param bool $be_verbose Be verbose, default is false
 *	@return int Return 0 if successful, 1 if error
 */
	public function detach(bool $be_forceful = false,bool $be_verbose = false) {
		$result = 1;
		$this->cmd_new('detach');
		if($be_forceful):
			$this->command_composer[] = '-f';
		endif;
		if(0 === $this->cmd_add_providers()):
			if($be_verbose):
				$this->command_composer[] = '2>&1';
			endif;
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		return $result;
	}
/**
 *	Initialize providers which need to be encrypted. If multiple<br/>
 *	providers are listed as arguments, they will all be initialized<br/>
 *	with the same passphrase and/or User Key. A unique salt will be<br/>
 *	randomly generated for each provider to ensure the Master Key for<br/>
 *	each is unique. Here you can set up the cryptographic algorithm<br/>
 *	to use, Data Key length, etc. The last sector of the providers is<br/>
 *	used to store metadata.
 *	@param bool $be_verbose Be verbose, default is false
 *	@return int Return 0 if successful, 1 if error
 */
	public function init(bool $be_verbose = false) {
		$result = 1;
		$this->cmd_new('init');
		if($be_verbose):
			$this->command_composer[] = '-v';
		endif;
//		inhibit metadata backup
		$this->command_composer[] = '-B none';
		$this->cmd_add_aalgo();
		$this->cmd_add_ealgo();
		$this->cmd_add_sectorsize();
		$this->cmd_add_decrypt_before_loading_rootfs();
		$this->cmd_add_enable_boot_rootfs();
		if((0 === $this->cmd_add_userkeys()) && (0 === $this->cmd_add_providers())):
			$this->command_composer[] = '2>&1';
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		$this->delete_temporary_files();
		return $result;
	}
/**
 *	Kill (destroy) a geli encrypted volume.<br/>
 *	Removes passphrase on boot flag.<br/>
 *	Deactivates boot from geli encrypted device flag.
 *	@param bool $be_verbose Be verbose, default is false
 *	@return int Returns 0 if successful, 1 if error.
 */
	public function kill(bool $be_verbose = false) {
		$result = 1;
		$this->cmd_new('kill');
		if($be_verbose):
			$this->command_composer[] = '-v';
		endif;
		if((0 === $this->configure(false,false)) && (0 === $this->cmd_add_providers())):
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		return $result;
	}
/**
 *	Attach the given providers with	a random, one-time (ephemeral)<br/>
 *	Master Key. The command can be used to encrypt swap partitions<br/>
 *	or temporary filesystems.
 */
	public function onetime() {
		$result = 1;
		$this->cmd_new('onetime');
		$this->cmd_add_aalgo();
		$this->cmd_add_ealgo();
		$this->cmd_add_sectorsize();
		$this->cmd_add_passthru_trim();
		if(0 === $this->cmd_add_providers()):
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		return $result;
	}
/**
 *	Install a copy of the Master Key into the selected slot,<br/>
 *	encrypted with a new User Key. If the selected slot is populated,<br/>
 *	replace the existing copy. A provider has one Master Key, which<br/>
 *	can be stored in one or both slots, each encrypted with an<br/>
 *	independent User Key. With the init subcommand, only key number 0<br/>
 *	is initialized. The User Key can be changed at any time: for an<br/>
 *	attached provider, for a detached provider, or on the backup<br/>
 *	file.
 *	@param int $slot The key index, 0, 1 or null
 *	@param bool $be_verbose Be verbose, default is false
 *	@return int Return 0 if successful, 1 if error
 */
	public function setkey(int $slot = null,bool $be_verbose = false) {
		$result = 1;
		$this->cmd_new('setkey');
		if($be_verbose):
			$this->command_composer[] = '-v';
		endif;
		$test_slot = filter_var($slot,FILTER_VALIDATE_INT,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => null,'min_range' => 0,'max_range' => 1]]);
		if(!is_null($test_slot)):
			$this->command_composer[] = sprintf('-n %d',$test_slot);
		endif;
		if(0 === $this->cmd_add_userkeys(true) && (0 === $this->cmd_add_userkeys()) && (0 === $this->cmd_add_providers(true))):
			$this->command_composer[] = '2>&1';
			$this->command = implode(' ',$this->command_composer);
			system($this->command,$result);
		endif;
		$this->delete_temporary_files();
		return $result;
	}
}
