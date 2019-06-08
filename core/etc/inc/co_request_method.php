<?php
/*
	co_request_method.php

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
class co_request_method {
	protected $x_activities = [];
	protected $x_default = [NULL,NULL,NULL];
	protected $x_method;
	protected $x_action;

	/**
	 *	Add request method
	 *	@param string $method The name of the method ('GET', 'POST' or 'SESSIOn').
	 *	@param string $submit The value of the submit parameter.
	 *	@param mixed $value An additional value on return, usually a PAGE_MODE constant.
	 *	@return $this
	 */
	public function add(string $method,string $submit,$value = NULL) {
		if(array_key_exists($method,$this->x_activities)):
			$this->x_activities[$method][$submit] = $value;
		else:
			$this->x_activities[$method] = [$submit => $value];
		endif;
		return $this;
	}
	/**
	 *	Set the default method when validation failed
	 *	@param string $method The name of the method ('GET', 'POST' or 'SESSIOn').
	 *	@param string $submit The value of the submit parameter.
	 *	@param mixed $value An additional value on return, usually a PAGE_MODE constant.
	 *	@return $this
	 */
	public function set_default(string $method = NULL,string $submit = NULL,$value = NULL) {
		$this->x_default = [$method,$submit,$value];
		return $this;
	}
	/**
	 *	Returns the default method
	 *	@return array
	 */
	public function get_default() : array {
		return $this->x_default;
	}
	/**
	 *	Validate request method and submit parameter
	 *	@return array
	 */
	public function validate() : array {
		//	check $_SESSION settings first
		$this->x_method = 'SESSION';
		if(array_key_exists($this->x_method,$this->x_activities) && array_key_exists('submit',$_SESSION)):
//			switch($this->x_method):
//				case 'SESSION': // Validate $_SESSION['submit']
					$this->x_action = filter_var($_SESSION['submit'],FILTER_CALLBACK,['options' =>
						function(string $value) { return array_key_exists($value,$this->x_activities[$this->x_method]) ? $value : NULL; }
					]);
					if(isset($this->x_action)):
						return [$this->x_method,$this->x_action,$this->x_activities[$this->x_method][$this->x_action]];
					endif;
//					break;
//			endswitch;
		endif;
		//	check inputs
		$rm_name = 'REQUEST_METHOD';
		if(array_key_exists($rm_name,$_SERVER)):
			$this->x_method = filter_var($_SERVER[$rm_name],FILTER_CALLBACK,['options' =>
				function(string $value) { return array_key_exists($value,$this->x_activities) ? $value : NULL; }
			]);
		else:
			$this->x_method = NULL;
		endif;
		if(isset($this->x_method)):
			switch($this->x_method):
				case 'POST': // Validate $_POST['submit']
					$this->x_action = filter_input(INPUT_POST,'submit',FILTER_CALLBACK,['options' =>
						function(string $value) { return array_key_exists($value,$this->x_activities[$this->x_method]) ? $value : NULL; }
					]);
					if(isset($this->x_action)):
						return [$this->x_method,$this->x_action,$this->x_activities[$this->x_method][$this->x_action]];
					endif;
					break;
				case 'GET': // Validate $_GET['submit']
					$this->x_action = filter_input(INPUT_GET,'submit',FILTER_CALLBACK,['options' =>
						function(string $value) { return array_key_exists($value,$this->x_activities[$this->x_method]) ? $value : NULL; }
					]);
					if(isset($this->x_action)):
						return [$this->x_method,$this->x_action,$this->x_activities[$this->x_method][$this->x_action]];
					endif;
					break;
/*
				case 'HEAD':
					break;
				case 'PUT':
					break;
				case 'DELETE':
					break;
				case 'CONNECT':
					break;
				case 'OPTIONS':
					break;
				case 'TRACE':
					break;
				case 'PATCH':
					break;
 */
				default:
					break;
			endswitch;
		endif;
		return $this->get_default();
	}
}