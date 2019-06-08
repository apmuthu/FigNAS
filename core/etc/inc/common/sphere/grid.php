<?php
/*
	common\sphere\grid.php

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
/**
 *	sphere object for grid pages
 */
final class grid extends hub {
	protected $x_cbm_suffix = '';
//	checkbox member array
	protected $x_cbm_name = 'cbm_grid';
	public $cbm_grid = [];
	public $cbm_row = [];
//	gettext
	protected $x_cbm_delete = NULL;
	protected $x_cbm_disable = NULL;
	protected $x_cbm_enable = NULL;
	protected $x_cbm_lock = NULL;
	protected $x_cbm_toggle = NULL;
	protected $x_cbm_unlock = NULL;
	protected $x_cbm_delete_confirm = NULL;
	protected $x_cbm_disable_confirm = NULL;
	protected $x_cbm_enable_confirm = NULL;
	protected $x_cbm_lock_confirm = NULL;
	protected $x_cbm_toggle_confirm = NULL;
	protected $x_cbm_unlock_confirm = NULL;
	protected $x_sym_add = NULL;
	protected $x_sym_mod = NULL;
	protected $x_sym_del = NULL;
	protected $x_sym_loc = NULL;
	protected $x_sym_unl = NULL;
	protected $x_sym_mai = NULL;
	protected $x_sym_inf = NULL;
	protected $x_sym_mup = NULL;
	protected $x_sym_mdn = NULL;
//	html id tags
	protected $x_cbm_button_id_delete = 'delete_selected_rows';
	protected $x_cbm_button_id_disable = 'disable_selected_rows';
	protected $x_cbm_button_id_enable = 'enable_selected_rows';
	protected $x_cbm_button_id_toggle = 'toggle_selected_rows';
	protected $x_cbm_checkbox_id_toggle = 'togglemembers';
//	html value tags
	protected $x_cbm_button_val_delete = 'rows.delete';
	protected $x_cbm_button_val_disable = 'rows.disable';
	protected $x_cbm_button_val_enable = 'rows.enable';
	protected $x_cbm_button_val_toggle = 'rows.toggle';
//	methods
	public function get_cbm_suffix() {
		return $this->x_cbm_suffix;
	}
	public function set_cbm_suffix(string $value) {
		if(preg_match('/^[a-z\d_]+$/i',$value)):
			$this->x_cbm_suffix = $value;
		endif;
		return $this;
	}
	public function get_cbm_name() {
		return $this->x_cbm_name . $this->get_cbm_suffix();
	}
	public function set_cbm_name(string $value) {
		if(preg_match('/^\S+$/i',$value)):
			$this->x_cbm_name = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_id_delete() {
		return $this->x_cbm_button_id_delete . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_delete(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_delete = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_disable() {
		return $this->x_cbm_button_id_disable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_disable(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_disable = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_enable() {
		return $this->x_cbm_button_id_enable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_enable(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_enable = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_toggle() {
		return $this->x_cbm_button_id_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_toggle(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_toggle = $id;
		endif;
		return $this;
	}
	public function get_cbm_checkbox_id_toggle() {
		return $this->x_cbm_checkbox_id_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_checkbox_id_toggle(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_checkbox_id_toggle = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_val_delete() {
		return $this->x_cbm_button_val_delete . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_delete(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_delete = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_disable() {
		return $this->x_cbm_button_val_disable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_disable(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_disable = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_enable() {
		return $this->x_cbm_button_val_enable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_enable(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_enable = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_toggle() {
		return $this->x_cbm_button_val_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_toggle(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_toggle = $value;
		endif;
		return $this;
	}
	public function cbm_delete(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_delete = $message;
		endif;
		return $this->x_cbm_delete ?? gettext('Delete Selected Records');
	}
	public function cbm_delete_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_delete_confirm = $message;
		endif;
		return $this->x_cbm_delete_confirm ?? gettext('Do you want to delete selected records?');
	}
	public function cbm_disable(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_disable = $message;
		endif;
		return $this->x_cbm_disable ?? gettext('Disable Selected Records');
	}
	public function cbm_disable_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_disable_confirm = $message;
		endif;
		return $this->x_cbm_disable_confirm ?? gettext('Do you want to disable selected records?');
	}
	public function cbm_enable(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_enable = $message;
		endif;
		return $this->x_cbm_enable ?? gettext('Enable Selected Records');
	}
	public function cbm_enable_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_enable_confirm = $message;
		endif;
		return $this->x_cbm_enable_confirm ?? gettext('Do you want to enable selected records?');
	}
	public function cbm_lock(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_lock = $message;
		endif;
		return $this->x_cbm_lock ?? gettext('Lock Selected Records');
	}
	public function cbm_lock_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_lock_confirm = $message;
		endif;
		return $this->x_cbm_lock_confirm ?? gettext('Do you want to lock selected records?');
	}
	public function cbm_toggle(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_toggle = $message;
		endif;
		return $this->x_cbm_toggle ?? gettext('Toggle Selected Records');
	}
	public function cbm_toggle_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_toggle_confirm = $message;
		endif;
		return $this->x_cbm_toggle_confirm ?? gettext('Do you want to toggle selected records?');
	}
	public function cbm_unlock(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_unlock = $message;
		endif;
		return $this->x_cbm_unlock ?? gettext('Unlock Selected Records');
	}
	public function cbm_unlock_confirm(string $message = NULL) {
		if(isset($message)):
			$this->x_cbm_unlock_confirm = $message;
		endif;
		return $this->x_cbm_unlock_confirm ?? gettext('Do you want to unlock selected records?');
	}
	public function getmsg_cbm_delete() {
		return $this->x_cbm_delete ?? gettext('Delete Selected Records');
	}
	public function getmsg_cbm_delete_confirm() {
		return $this->x_cbm_delete_confirm ?? gettext('Do you want to delete selected records?');
	}
	public function getmsg_cbm_disable() {
		return $this->x_cbm_disable ?? gettext('Disable Selected Records');
	}
	public function getmsg_cbm_disable_confirm() {
		return $this->x_cbm_disable_confirm ?? gettext('Do you want to disable selected records?');
	}
	public function getmsg_cbm_enable() {
		return $this->x_cbm_enable ?? gettext('Enable Selected Records');
	}
	public function getmsg_cbm_enable_confirm() {
		return $this->x_cbm_enable_confirm ?? gettext('Do you want to enable selected records?');
	}
	public function getmsg_cbm_lock() {
		return $this->x_cbm_lock ?? gettext('Lock Selected Records');
	}
	public function getmsg_cbm_lock_confirm() {
		return $this->x_cbm_lock_confirm ?? gettext('Do you want to lock selected records?');
	}
	public function getmsg_cbm_toggle() {
		return $this->x_cbm_toggle ?? gettext('Toggle Selected Records');
	}
	public function getmsg_cbm_toggle_confirm() {
		return $this->x_cbm_toggle_confirm ?? gettext('Do you want to toggle selected records?');
	}
	public function getmsg_cbm_unlock() {
		return $this->x_cbm_unlock ?? gettext('Unlock Selected Records');
	}
	public function getmsg_cbm_unlock_confirm() {
		return $this->x_cbm_unlock_confirm ?? gettext('Do you want to unlock selected records?');
	}
	public function getmsg_sym_add() {
		return $this->x_sym_add ?? gettext('Add Record');
	}
	public function getmsg_sym_del(string $message = NULL) {
		return $this->x_sym_del ?? gettext('Record is marked for deletion');
	}
	public function getmsg_sym_inf() {
		return $this->x_sym_inf ?? gettext('Record Information');
	}
	public function getmsg_sym_loc() {
		return $this->x_sym_loc ?? gettext('Record is protected');
	}
	public function getmsg_sym_mai() {
		return $this->x_sym_mai ?? gettext('Record Maintenance');
	}
	public function getmsg_sym_mdn() {
		return $this->x_sym_mdn ?? gettext('Move down');
	}
	public function getmsg_sym_mod() {
		return $this->x_sym_mod ?? gettext('Edit Record');
	}
	public function getmsg_sym_mup() {
		return $this->x_sym_mup ?? gettext('Move up');
	}
	public function getmsg_sym_unl() {
		return $this->x_sym_unl ?? gettext('Record is unlocked');
	}
	public function setmsg_cbm_delete(string $message = NULL) {
		$this->x_cbm_delete = $message;
		return $this;
	}
	public function setmsg_cbm_delete_confirm(string $message = NULL) {
		$this->x_cbm_delete_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_disable(string $message = NULL) {
		$this->x_cbm_disable = $message;
		return $this;
	}
	public function setmsg_cbm_disable_confirm(string $message = NULL) {
		$this->x_cbm_disable_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_enable(string $message = NULL) {
		$this->x_cbm_enable = $message;
		return $this;
	}
	public function setmsg_cbm_enable_confirm(string $message = NULL) {
		$this->x_cbm_enable_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_lock(string $message = NULL) {
		$this->x_cbm_lock = $message;
		return $this;
	}
	public function setmsg_cbm_lock_confirm(string $message = NULL) {
		$this->x_cbm_lock_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_toggle(string $message = NULL) {
		$this->x_cbm_toggle = $message;
		return $this;
	}
	public function setmsg_cbm_toggle_confirm(string $message = NULL) {
		$this->x_cbm_toggle_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_unlock(string $message = NULL) {
		$this->x_cbm_unlock = $message;
		return $this;
	}
	public function setmsg_cbm_unlock_confirm(string $message = NULL) {
		$this->x_cbm_unlock_confirm = $message;
		return $this;
	}
	public function setmsg_sym_add(string $message = NULL) {
		$this->x_sym_add = $message;
		return $this;
	}
	public function setmsg_sym_del(string $message = NULL) {
		$this->x_sym_del = $message;
		return $this;
	}
	public function setmsg_sym_inf(string $message = NULL) {
		$this->x_sym_inf = $message;
		return $this;
	}
	public function setmsg_sym_loc(string $message = NULL) {
		$this->x_sym_loc = $message;
		return $this;
	}
	public function setmsg_sym_mai(string $message = NULL) {
		$this->x_sym_mai = $message;
		return $this;
	}
	public function setmsg_sym_mdn(string $message = NULL) {
		$this->x_sym_mdn = $message;
		return $this;
	}
	public function setmsg_sym_mod(string $message = NULL) {
		$this->x_sym_mod = $message;
		return $this;
	}
	public function setmsg_sym_mup(string $message = NULL) {
		$this->x_sym_mup = $message;
		return $this;
	}
	public function setmsg_sym_unl(string $message = NULL) {
		$this->x_sym_unl = $message;
		return $this;
	}
	public function get_js_on_load(): string {
		$output = [];
		//	Init action buttons.
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm("' . $this->escape_javascript($this->getmsg_cbm_toggle_confirm()) . '");';
				$output[] = "\t" . '});';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm("' . $this->escape_javascript($this->getmsg_cbm_enable_confirm()) . '");';
				$output[] = "\t" . '});';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm("' . $this->escape_javascript($this->getmsg_cbm_disable_confirm()) . '");';
				$output[] = "\t" . '});';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").click(function () {';
		$output[] = "\t\t" . 'return confirm("' . $this->escape_javascript($this->getmsg_cbm_delete_confirm()) . '");';
		$output[] = "\t" . '});';
		//	Disable action buttons.
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(true);';
		//	Init toggle checkbox.
		$output[] = "\t" . '$("#' . $this->get_cbm_checkbox_id_toggle() . '").click(function() {';
		$output[] = "\t\t" . 'cb_tbn' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		//	Init member checkboxes.
		$output[] = "\t" . '$("input[name=\'' . $this->get_cbm_name() . '[]\']").click(function() {';
		$output[] = "\t\t" . 'ab_control' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		return implode(PHP_EOL,$output);
	}
	public function get_js(): string {
		$output = [];
		$output[] = 'function ab_disable' . $this->get_cbm_suffix() . '(flag) {';
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").prop("disabled",flag);';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").prop("disabled",flag);';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").prop("disabled",flag);';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").prop("disabled",flag);';
		$output[] = '}';
		$output[] = 'function cb_tbn' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'cba.prop("checked", function(_, checked) { return !checked; });';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = "\t" . 'ego.checked = false;';
		$output[] = '}';
		$output[] = 'function ab_control' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = '}';
		return implode(PHP_EOL,$output);
	}
}
