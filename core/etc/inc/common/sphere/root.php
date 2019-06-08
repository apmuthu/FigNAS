<?php
/*
	root.php

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
namespace common\sphere;
/*
 *	sphere level 1 object for settings, services, row and grid
 */
class root {
	public $grid = [];
	public $row = [];
	public $row_default = [];

	protected $x_script_inform = NULL;
	protected $x_script_maintain = NULL;
	protected $x_script_modify = NULL;
	protected $x_script_parent = NULL;
	protected $x_script_this = NULL;
	protected $x_enadis = false;
	protected $x_lock = false;
	protected $x_notifier = NULL;
	protected $x_notifier_processor = NULL;
	protected $x_row_identifier = NULL;

	public function __destruct() {
		unset(
			$this->x_script_inform,
			$this->x_script_maintain,
			$this->x_script_modify,
			$this->x_script_parent,
			$this->x_script_this
		);
	}
	public function set_script(string $basename,string $extension = 'php'): self {
		if(is_null($this->x_script_this)):
			$this->x_script_this = new scriptname($basename,$extension);
		endif;
		return $this;
	}
	public function get_script(): ?scriptname {
		return $this->x_script_this;
	}
	public function set_inform(string $basename,string $extension = 'php'): self {
		if(is_null($this->x_script_inform)):
			$this->x_script_inform = new scriptname($basename,$extension);
		endif;
		return $this;
	}
	public function get_inform(): ?scriptname{
		return $this->x_script_inform;
	}
	public function set_maintain(string $basename,string $extension = 'php'): self {
		if(is_null($this->x_script_maintain)):
			$this->x_script_maintain = new scriptname($basename,$extension);
		endif;
		return $this;
	}
	public function get_maintain(): ?scriptname {
		return $this->x_script_maintain;
	}
	public function set_modify(string $basename,string $extension = 'php'): self {
		if(is_null($this->x_script_modify)):
			$this->x_script_modify = new scriptname($basename,$extension);
		endif;
		return $this;
	}
	public function get_modify(): ?scriptname {
		return $this->x_script_modify;
	}
	public function set_parent(string $basename,string $extension = 'php'): self {
		if(is_null($this->x_script_parent)):
			$this->x_script_parent = new scriptname($basename,$extension);
		endif;
		return $this;
	}
	public function get_parent(): ?scriptname {
		return $this->x_script_parent;
	}
/**
 *	Enable/disable enable/disable option
 *	@param bool $flag
 *	@return $this
 */
	public function set_enadis(bool $flag = false): self {
		$this->x_enadis = $flag;
		return $this;
	}
/**
 *	Returns the status of the enable/disable option.
 *	@return boolean
 */
	public function is_enadis_enabled(): bool {
		return $this->x_enadis;
	}
	public function toggle(): bool {
		global $config;

		$test = $config['system']['enabletogglemode'] ?? false;
		return $this->is_enadis_enabled() && (is_bool($test) ? $test : true);
	}
/**
 *	Enable/disable record lock support
 *	@param bool $flag
 *	@return $this
 */
	public function set_lock(bool $flag = false): self {
		$this->x_lock = $flag;
		return $this;
	}
/**
 *	Returns true when record lock support is enabled
 *	@return bool
 */
	public function is_lock_enabled(): bool {
		return $this->x_lock;
	}
	public function set_notifier(string $notifier): self {
		$this->x_notifier = $notifier;
		return $this;
	}
	public function get_notifier(): ?string {
		return $this->x_notifier;
	}
	public function set_row_identifier(string $row_identifier): self {
		$this->x_row_identifier = $row_identifier;
		return $this;
	}
	public function get_row_identifier(): ?string {
		return $this->x_row_identifier;
	}
	public function set_notifier_processor(string $notifier_processor): self {
		$this->x_notifier_processor = $notifier_processor;
		return $this;
	}
	public function get_notifier_processor(): ?string {
		return $this->x_notifier_processor;
	}
	public function get_row_identifier_value(): ?string {
		return $this->row[$this->x_row_identifier] ?? NULL;
	}
	public function escape_javascript(string $data = ''): string {
		return str_replace(['"',"'"],['\u0022','\u0027'],$data);
	}
	public function get_js_on_load(): string {
		return '';
	}
	public function get_js_document_ready(): string {
		return '';
	}
	public function get_js() {
		return '';
	}
}
