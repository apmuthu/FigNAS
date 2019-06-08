<?php
/*
	properties.php

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
require_once 'util.inc';

/**
 *	Adds additional characteristics to a variable
 */
abstract class property {
	protected $x_owner = NULL;
	protected $x_id = NULL;
	protected $x_name = NULL;
	protected $x_title = NULL;
	protected $x_description = NULL;
	protected $x_caption = NULL;
	protected $x_defaultvalue = NULL;
	protected $x_editableonadd = NULL;
	protected $x_editableonmodify = NULL;
	protected $x_filter = [];
	protected $x_filter_group = [];
	protected $x_message_error = NULL;
	protected $x_message_info = NULL;
	protected $x_message_warning = NULL;

	public function __construct($owner = NULL) {
		$this->set_owner($owner);
		return $this;
	}
	abstract public function filter_use_default();
	public function set_owner($owner = NULL) {
		if(is_object($owner)):
			$this->x_owner = $owner;
		endif;
		return $this;
	}
	public function get_owner() {
		return $this->x_owner;
	}
	public function set_id(string $id = NULL) {
		$this->x_id = $id;
		return $this;
	}
	public function get_id() {
		return $this->x_id;
	}
	public function set_name(string $name = NULL) {
		$this->x_name = $name;
		return $this;
	}
	public function get_name() {
		return $this->x_name;
	}
	public function set_title(string $title = NULL) {
		$this->x_title = $title;
		return $this;
	}
	public function get_title() {
		return $this->x_title;
	}
	public function set_description($description = NULL) {
		$this->x_description = $description;
		return $this;
	}
	public function get_description() {
		return $this->x_description;
	}
	public function set_caption(string $caption = NULL) {
		$this->x_caption = $caption;
		return $this;
	}
	public function get_caption() {
		return $this->x_caption;
	}
	public function set_defaultvalue($defaultvalue = NULL) {
		$this->x_defaultvalue = $defaultvalue;
		return $this;
	}
	public function get_defaultvalue() {
		return $this->x_defaultvalue;
	}
	public function set_editableonadd(bool $editableonadd = true) {
		$this->x_editableonadd = $editableonadd;
		return $this;
	}
	public function get_editableonadd() {
		return $this->x_editableonadd;
	}
	public function set_editableonmodify(bool $editableonmodify = true) {
		$this->x_editableonmodify = $editableonmodify;
		return $this;
	}
	public function get_editableonmodify() {
		return $this->x_editableonmodify;
	}
	public function set_message_error(string $message_error = NULL) {
		$this->x_message_error = $message_error;
		return $this;
	}
	public function get_message_error() {
		if(is_null($this->x_message_error)):
			return sprintf('%s: %s',$this->get_title() ?? gettext('Undefined'),gettext('The value is invalid.'));
		endif;
		return $this->x_message_error;
	}
	public function set_message_info(string $message_info = NULL) {
		$this->x_message_info = $message_info;
		return $this;
	}
	public function get_message_info() {
		return $this->x_message_info;
	}
	public function set_message_warning(string $message_warning = NULL) {
		$this->x_message_warning = $message_warning;
		return $this;
	}
	public function get_message_warning() {
		return $this->x_message_warning;
	}
/**
 *	Method to set filter type.
 *	@param int $value Filter type.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter($value = NULL,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['filter'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => $value,'flags' => NULL,'options' => NULL];
		endif;
		return $this;
	}
/**
 *	Method to set filter flags.
 *	@param type $value Flags for filter.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter_flags($value = NULL,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['flags'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => NULL,'flags' => $value,'options' => NULL];
		endif;
		return $this;
	}
/**
 *	Method to set filter options.
 *	@param array $value Filter options.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter_options($value = NULL,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['options'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => NULL,'flags' => NULL,'options' => $value];
		endif;
		return $this;
	}
/**
 *	Method returns the filter settings of $filter_name:
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return array If $filter_name exists the filter configuration is returned, otherwise NULL is returned.
 */
	public function get_filter(string $filter_name = 'ui') {
		if(array_key_exists($filter_name,$this->x_filter)):
			return $this->x_filter[$filter_name];
		endif;
		return NULL;
	}
/**
 *	Method to set a filter group
 *	@param string $root_filter_name
 *	@param array $filter_names
 *	@return object Returns $this.
 */
	public function set_filter_group(string $root_filter_name = 'ui', array $filter_names) {
		$this->x_filter_group[$root_filter_name] = $filter_names;
		return $this;
	}
/**
 *	Method to get a previously defined filter group
 *	@param string $root_filter_name Name of the filter group
 *	@return array Array with group members
 */
	public function get_filter_group(string $root_filter_name = 'ui') {
		return array_key_exists($root_filter_name,$this->x_filter_group) ? $this->x_filter_group[$root_filter_name] : [$root_filter_name];
	}
/**
 *	Get a list of filter names og a group
 *	@param type $filter
 *	@return array Array of filter names
 */
	public function get_filter_names($filter = 'ui') {
		$filter_names = [];
		$filter_groups = is_array($filter) ? $filter : (is_string($filter) ? [$filter] : []);
		foreach($filter_groups as $filter_group):
			if(is_string($filter_group)):
				$filter_group_members = $this->get_filter_group($filter_group);
				foreach($filter_group_members as $filter_group_member):
					$filter_names[] = $filter_group_member;
				endforeach;
			endif;
		endforeach;
		return $filter_names;
	}
/**
 *	Method to apply filter to an input element.
 *	A list of filters will be processed until a filter does not return NULL.
 *	@param int $input_type Input type. Check the PHP manual for supported input types.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_input(int $input_type = INPUT_POST,$filter = 'ui') {
		$result = NULL;
		$filter_names = $this->get_filter_names($filter);
		foreach($filter_names as $filter_name):
			if(is_string($filter_name)):
				$filter_parameter = $this->get_filter($filter_name);
				if(isset($filter_parameter)):
					$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
					switch($action):
						case 3:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
							break;
						case 2:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],['options' => $filter_parameter['options']]);
							break;
						case 1:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],$filter_parameter['flags']);
							break;
						case 0:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter']);
							break;
					endswitch;
				endif;
				if(isset($result)):
					break; // foreach
				endif;
			endif;
		endforeach;
		return $result;
	}
/**
 *	Method to validate a value.
 *	A list of filters will be processed until a filter does not return NULL.
 *	If an array of values is provided all values must pass validation.
 *	@param mixed $value The value to be tested.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_value($content,$filter = 'ui') {
		$result = NULL;
		$filter_names = $this->get_filter_names($filter);
		switch(true):
			case is_scalar($content):
				$value = $content;
				$row_results = NULL;
				foreach($filter_names as $filter_name):
					$filter_result = NULL;
					if(is_string($filter_name)):
						$filter_parameter = $this->get_filter($filter_name);
						if(isset($filter_parameter)):
							$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
							switch($action):
								case 3:
									$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
									break;
								case 2:
									$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
									break;
								case 1:
									$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
									break;
								case 0:
									$filter_result = filter_var($value,$filter_parameter['filter']);
									break;
							endswitch;
						endif;
					endif;
					if(isset($filter_result)):
						$row_results = $filter_result;
						break;
					endif;
				endforeach;
				if(isset($row_results)):
					$result = $row_results;
				endif;
				break;
			case is_array($content):
				$row_results = [];
				foreach($content as $value):
					$row_results[] = NULL;
					end($row_results);
					$row_results_key = key($row_results);
					if(is_scalar($value)):
						foreach($filter_names as $filter_name):
							$filter_result = NULL;
							if(is_string($filter_name)):
								$filter_parameter = $this->get_filter($filter_name);
								if(isset($filter_parameter)):
									$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
									switch($action):
										case 3:
											$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
											break;
										case 2:
											$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
											break;
										case 1:
											$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
											break;
										case 0:
											$filter_result = filter_var($value,$filter_parameter['filter']);
											break;
									endswitch;
								endif;
							endif;
							if(isset($filter_result)):
								$row_results[$row_results_key] = $filter_result;
								break;
							endif;
						endforeach;
					endif;
					if(is_null($row_results[$row_results_key])):
						$row_results = [];
						break;
					endif;
				endforeach;
				if(count($row_results) > 0):
					$result = $row_results;
				endif;
				break;
		endswitch;
		return $result;
	}
/**
 *	Method to validate an array value. Key/Index is the name property of $this.
 *	A list of filters will be processed until a filter does not return NULL.
 *	If an array of values is provided all values must pass validation.
 *	@param array $variable The variable to be tested.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_array_element(array $variable,$filter = 'ui') {
		$result = NULL;
		$filter_names = $this->get_filter_names($filter);
		$key = $this->get_name();
		if(array_key_exists($key,$variable)):
			$content = $variable[$key];
			switch(true):
				case is_scalar($content):
					$value = $content;
					$row_results = NULL;
					foreach($filter_names as $filter_name): //	loop through filters
						$filter_result = NULL;
						if(is_string($filter_name)):
							$filter_parameter = $this->get_filter($filter_name);
							if(isset($filter_parameter)):
								$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
								switch($action):
									case 3:
										$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
										break;
									case 2:
										$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
										break;
									case 1:
										$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
										break;
									case 0:
										$filter_result = filter_var($value,$filter_parameter['filter']);
										break;
								endswitch;
							endif;
						endif;
						if(isset($filter_result)):
							$row_results = $filter_result;
							break;
						endif;
					endforeach;
					if(isset($row_results)):
						$result = $row_results;
					endif;
					break;
				case is_array($content):
					$row_results = [];
					foreach($content as $value): // loop through array elements
						$row_results[] = NULL;
						end($row_results);
						$row_results_key = key($row_results);
						if(is_scalar($value)):
							foreach($filter_names as $filter_name): //	loop through filters
								$filter_result = NULL;
								if(is_string($filter_name)):
									$filter_parameter = $this->get_filter($filter_name);
									if(isset($filter_parameter)):
										$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
										switch($action):
											case 3:
												$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
												break;
											case 2:
												$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
												break;
											case 1:
												$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
												break;
											case 0:
												$filter_result = filter_var($value,$filter_parameter['filter']);
												break;
										endswitch;
									endif;
								endif;
								if(isset($filter_result)):
									$row_results[$row_results_key] = $filter_result;
									break;
								endif;
							endforeach;
						endif;
						if(is_null($row_results[$row_results_key])):
							$row_results = [];
							break;
						endif;
					endforeach;
					if(count($row_results) > 0):
						$result = $row_results;
					endif;
					break;
			endswitch;
		endif;
		return $result;
	}
/**
 *	Returns the value if key exists and is not null, otherwise the default value is returned.
 *	@param array $source
 *	@return string
 */
	public function validate_config(array $source) {
		$name = $this->get_name();
		if(array_key_exists($name,$source)):
			$return_data = $source[$name];
		else:
			$return_data = $this->get_defaultvalue();
		endif;
		return $return_data;
	}
}
class property_text extends property {
	public $x_maxlength = 0;
	public $x_placeholder = NULL;
	public $x_placeholderv = NULL;
	public $x_size = 40;

	public function set_maxlength(int $maxlength = 0) {
		$this->x_maxlength = $maxlength;
		return $this;
	}
	public function get_maxlength() {
		return $this->x_maxlength;
	}
	public function set_placeholder(string $placeholder = NULL) {
		$this->x_placeholder = $placeholder;
		return $this;
	}
	public function get_placeholder() {
		return $this->x_placeholder;
	}
	public function set_placeholderv(string $placeholderv = NULL) {
		$this->x_placeholderv = $placeholderv;
		return $this;
	}
	public function get_placeholderv() {
		return $this->x_placeholderv;
	}
	public function set_size(int $size = 40) {
		$this->x_size = $size;
		return $this;
	}
	public function get_size() {
		return $this->x_size;
	}
	public function filter_use_default() {
		//	not empty, must contain at least one printable character
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => '/\S/'],$filter_name);
		return $this;
	}
	public function filter_use_empty() {
		$filter_name = 'empty';
		$regexp = '/^$/';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => $regexp],$filter_name);
		return $this;
	}
	public function filter_use_default_or_empty() {
		$this->filter_use_default();
		$this->filter_use_empty();
		$this->set_filter_group('ui',['empty','ui']);
		return $this;
	}
}
abstract class property_text_callback extends property_text {
	abstract public function validate($test);
	public function validate_or_default($test) {
		if(is_null($this->validate($test))):
			return $this->get_defaultvalue();
		else:
			return $test;
		endif;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_CALLBACK,$filter_name)->
			set_filter_options([$this,'validate'],$filter_name);
		return $this;
	}
}
class property_textarea extends property {
	public $x_cols = 65;
	public $x_maxlength = 0;
	public $x_placeholder = NULL;
	public $x_placeholderv = NULL;
	public $x_rows = 5;
	public $x_wrap = false;

	public function set_cols(int $cols = 65) {
		$this->x_cols = $cols;
		return $this;
	}
	public function get_cols() {
		return $this->x_cols;
	}
	public function set_maxlength(int $maxlength = 0) {
		$this->x_maxlength = $maxlength;
		return $this;
	}
	public function get_maxlength() {
		return $this->x_maxlength;
	}
	public function set_placeholder(string $placeholder = NULL) {
		$this->x_placeholder = $placeholder;
		return $this;
	}
	public function get_placeholder() {
		return $this->x_placeholder;
	}
	public function set_placeholderv(string $placeholderv = NULL) {
		$this->x_placeholderv = $placeholderv;
		return $this;
	}
	public function get_placeholderv() {
		return $this->x_placeholderv;
	}
	public function set_rows(int $rows = 5) {
		$this->x_rows = $rows;
		return $this;
	}
	public function get_rows() {
		return $this->x_rows;
	}
	public function set_wrap(bool $wrap = false) {
		$this->x_wrap = $wrap;
		return $this;
	}
	public function get_wrap() {
		return $this->x_wrap;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->set_filter(FILTER_DEFAULT,$filter_name);
		return $this;
	}
}
class property_auxparam extends property_textarea {
	public function __construct($owner = NULL) {
		$placeholder = gettext('Enter additional parameters');
		$placeholderv = gettext('No additional parameters');
		$title = gettext('Additional Parameters');
		parent::__construct($owner);
		$this->
			set_defaultvalue('')->
			set_description('')->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_id('auxparam')->
			set_name('auxparam')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_title($title)->
			filter_use_default();
		return $this;
	}
}
class property_ipaddress extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(45)->
			set_placeholder(gettext('IP Address'))->
			set_size(60);
		return $this;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_IP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL],$filter_name);
		return $this;
	}
}
class property_ipv4 extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(15)->
			set_placeholder(gettext('IPv4 Address'))->
			set_size(20);
		return $this;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_IP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR | FILTER_FLAG_IPV4,$filter_name)->
			set_filter_options(['default' => NULL],$filter_name);
		return $this;
	}
}
class property_ipv6 extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(45)->
			set_placeholder(gettext('IPv6 Address'))->
			set_size(60);
		return $this;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_IP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR | FILTER_FLAG_IPV6,$filter_name)->
			set_filter_options(['default' => NULL],$filter_name);
		return $this;
	}
}
class property_cidr extends property_text_callback {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(49)->
			set_placeholder(gettext('Network Address'))->
			set_size(60);
		return $this;
	}
	public function validate($cidr) {
		if(is_string($cidr)):
			list($ipaddress,$subnet) = explode('/',$cidr,2);
			if(!is_null(filter_var($ipaddress,FILTER_VALIDATE_IP,['flags' => FILTER_FLAG_IPV4,'options' => ['default' => NULL]]))):
				if(!is_null(filter_var($subnet,FILTER_VALIDATE_INT,['options' => ['default' => NULL,'min_range' => 0,'max_range' => 32]]))):
					return $cidr;
				endif;
			elseif(!is_null(filter_var($ipaddress,FILTER_VALIDATE_IP,['flags' => FILTER_FLAG_IPV6,'options' => ['default' => NULL]]))):
				if(!is_null(filter_var($subnet,FILTER_VALIDATE_INT,['options' => ['default' => NULL,'min_range' => 0,'max_range' => 128]]))):
					return $cidr;
				endif;
			endif;
		endif;
		return NULL;
	}
}
class property_cidr_ipv4 extends property_text_callback {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(18)->
			set_placeholder(gettext('Network Address'))->
			set_size(60);
		return $this;
	}
	public function validate($cidr) {
		if(is_string($cidr)):
			list($ipaddress,$subnet) = explode('/',$cidr,2);
			if(!is_null(filter_var($ipaddress,FILTER_VALIDATE_IP,['flags' => FILTER_FLAG_IPV4,'options' => ['default' => NULL]]))):
				if(!is_null(filter_var($subnet,FILTER_VALIDATE_INT,['options' => ['default' => NULL,'min_range' => 0,'max_range' => 32]]))):
					return $cidr;
				endif;
			endif;
		endif;
		return NULL;
	}
}
class property_cidr_ipv6 extends property_text_callback {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_maxlength(49)->
			set_placeholder(gettext('Network Address'))->
			set_size(60);
		return $this;
	}
	public function validate($cidr) {
		if(is_string($cidr)):
			list($ipaddress,$subnet) = explode('/',$cidr,2);
			if(!is_null(filter_var($ipaddress,FILTER_VALIDATE_IP,['flags' => FILTER_FLAG_IPV6,'options' => ['default' => NULL]]))):
				if(!is_null(filter_var($subnet,FILTER_VALIDATE_INT,['options' => ['default' => NULL,'min_range' => 0,'max_range' => 128]]))):
					return $cidr;
				endif;
			endif;
		endif;
		return NULL;
	}
}
class property_int extends property_text {
	public $x_min = NULL;
	public $x_max = NULL;

	public function set_min(int $min = NULL) {
		$this->x_min = $min;
		return $this;
	}
	public function get_min() {
		return $this->x_min;
	}
	public function set_max(int $max = NULL) {
		$this->x_max = $max;
		return $this;
	}
	public function get_max() {
		return $this->x_max;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$options = [];
		$options['default'] = NULL;
		$min = $this->get_min();
		if(isset($min)):
			$options['min_range'] = $min;
		endif;
		$max = $this->get_max();
		if(isset($max)):
			$options['max_range'] = $max;
		endif;
		$this->
			set_filter(FILTER_VALIDATE_INT,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options($options,$filter_name);
		return $this;
	}
}
class property_toolbox extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->set_title(gettext('Toolbox'));
	}
}
class property_uuid extends property_text {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_name('uuid')->
			set_title(gettext('Universally Unique Identifier'));
		$this->
			set_id('uuid')->
			set_description(gettext('The UUID of the record.'))->
			set_size(45)->
			set_maxlength(36)->
			set_placeholder(gettext('Enter Universally Unique Identifier'))->
			filter_use_default()->
			set_editableonadd(false)->
			set_editableonmodify(false)->
			set_message_error(sprintf('%s: %s',$this->get_title(),gettext('The value is invalid.')));
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_REGEXP,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_SCALAR,$filter_name)->
			set_filter_options(['default' => NULL,'regexp' => '/^[\da-f]{4}([\da-f]{4}-){2}4[\da-f]{3}-[89ab][\da-f]{3}-[\da-f]{12}$/i'],$filter_name);
		return $this;
	}
	public function get_defaultvalue() {
		return uuid();
	}
}
class property_list extends property {
	public $x_options = NULL;

	public function set_options(array $options = NULL) {
		$this->x_options = $options;
		return $this;
	}
	public function upsert_option(string $key = '',string $value = '') {
		$this->x_options[$key] = isset($this->x_options) ? $value : [$key => $value];
	}
	public function get_options() {
		return $this->x_options;
	}
	public function validate($option) {
		if(array_key_exists($option,$this->get_options())):
			return $option;
		else:
			return NULL;
		endif;
	}
	public function validate_config(array $source) {
		$return_data = '';
		$key = $this->get_name();
		if(array_key_exists($key,$source)):
			$option = $source[$key];
			if(array_key_exists($option,$this->get_options())):
				$return_data = $option;
			endif;
		else:
			$return_data = $this->get_defaultvalue();
		endif;
		return $return_data;
	}
/**
 * Method to apply the default class filter to a filter name.
 * The filter is a regex to match any of the option array keys.
 * @return object Returns $this.
 */
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_CALLBACK,$filter_name)->
			set_filter_options([$this,'validate'],$filter_name);
		return $this;
	}
}
class property_list_multi extends property_list {
	public function validate_config(array $source) {
		$return_data = [];
		$key = $this->get_name();
		if(array_key_exists($key,$source)):
			$a_option = $source[$key];
			foreach($a_option as $option):
				if(is_scalar($option)):
					$return_data[] = $option;
				endif;
			endforeach;
		else:
			$return_data = $this->get_defaultvalue();
		endif;
		return $return_data;
	}
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_DEFAULT,$filter_name)->
			set_filter_flags(FILTER_REQUIRE_ARRAY)->
			set_filter_options(['default' => []],$filter_name);
		return $this;
	}
}
class property_bool extends property {
	public function filter_use_default() {
		$filter_name = 'ui';
		$this->
			set_filter(FILTER_VALIDATE_BOOLEAN,$filter_name)->
			set_filter_flags(FILTER_NULL_ON_FAILURE,$filter_name)->
			set_filter_options(['default' => false],$filter_name);
		return $this;
	}
	public function validate_config(array $source) {
		$key = $this->get_name();
		if(array_key_exists($key,$source)):
			$value = $source[$key];
			$return_data = is_bool($value) ? $value : true;
		else:
			$return_data = false;
		endif;
		return $return_data;
	}
}
class property_enable extends property_bool {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_name('enable')->
			set_title(gettext('Enable Setting'));
		$this->
			set_id('enable')->
			set_caption(gettext('Enable'))->
			set_description('')->
			set_defaultvalue(true)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$this->get_title(),gettext('The value is invalid.')));
		return $this;
	}
}
class property_protected extends property_bool {
	public function __construct($owner = NULL) {
		parent::__construct($owner);
		$this->
			set_name('protected')->
			set_title(gettext('Protect Setting'));
		$this->
			set_id('protected')->
			set_caption(gettext('Protect'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$this->get_title(),gettext('The value is invalid.')));
		return $this;
	}
}
abstract class co_property_container {
/**
 *	protected $x_propertyname;
 */
	public function __construct() {
		$this->reset();
	}
	public function __destruct() {
		$this->reset();
	}
/**
 *	Sets all 'x_*' properties to NULL
 *	@return $this
 */
	public function reset() {
		foreach($this as $key => $value):
			if(strncmp($key,'x_',2) === 0):
				$this->$key = NULL;
			endif;
		endforeach;
		return $this;
	}
/**
 *	Initializes all 'x_*' properties by calling their 'get_*' method.
 *	The 'get_*' method itself calls the related 'init_*' method if necessary.
 *	@return $this
 */
	public function init_all() {
		foreach($this as $key => $value):
			unset($matches);
			if(1 === preg_match('/^x_(.+)/',$key,$matches)):
				$method_name = 'get_' . $matches[1];
				$this->$method_name();
			endif;
		endforeach;
		return $this;
 	}
/**
 *	Lazy call via magic get
 *	@param string $name the name of the property
 *	@return property Returns a property object
 *	@throws BadMethodCallException
 */
	public function &__get(string $name) {
		$method_name = 'get_' . $name;
		if(method_exists($this,$method_name)):
			$return_data = $this->$method_name();
		else:
			$message = htmlspecialchars(sprintf("Method '%s' for '%s' not found",$method_name,$name));
			throw new BadMethodCallException($message);
		endif;
		return $return_data;
	}
/*
 *	public function get_propertyname() {
 *		return $this->x_propertyname ?? $this->init_propertyname();
 *	}
 *	public function init_propertyname() {
 *		$this->x_propertyname = new property_nnn($this);
 *		...
 *		return $this->x_propertyname;
 *	}
 */
}
abstract class co_property_container_param extends co_property_container {
	protected $x_uuid;
	public function init_uuid() {
		$property = $this->x_uuid = new property_uuid($this);
		return $property;
	}
	public function get_uuid() {
		return $this->x_uuid ?? $this->init_uuid();
	}
	protected $x_description;
	public function init_description() {
		$property = new property_text($this);
		$property->
			set_name('description')->
			set_title(gettext('Description'));
		$description = gettext('Enter a description for your reference.');
		$placeholder = gettext('Enter a description');
		$property->
			set_id('description')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(256)->
			set_filter(FILTER_UNSAFE_RAW)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => ''])->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	public function get_description() {
		return $this->x_description ?? $this->init_description();
	}
	protected $x_enable;
	public function init_enable() {
		$property = $this->x_enable = new property_enable($this);
		return $property;
	}
	public function get_enable() {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_protected;
	public function init_protected() {
		$property = $this->x_protected = new property_protected($this);
		return $property;
	}
	public function get_protected() {
		return $this->x_protected ?? $this->init_protected();
	}
	protected $x_toolbox;
	public function init_toolbox() {
		$property = $this->x_toolbox = new property_toolbox($this);
		return $property;
	}
	public function get_toolbox() {
		return $this->x_toolbox ?? $this->init_toolbox();
	}
	public function get_row_identifier() {
		return $this->get_uuid();
	}
}
