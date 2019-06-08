<?php
/*
	rmo_row_templates.php

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
namespace common\rmo;
/**
 *	Request Method Object Templates
 */
final class rmo_row_templates {
/**
 *	create and return RMO object with<br/>
 *	<b>GET</b>: add & edit<br/>
 *	<b>POST</b>: add, edit, save & cancel<br/>
 *	<b>POST</b>: cancel is the default
 *	@return \common\rmo\rmo
 */
	public static function rmo_base() {
		$rmo = new rmo();
		$rmo->
			set_default('POST','cancel',PAGE_MODE_POST)->
			add('GET' ,'add',PAGE_MODE_ADD)->
			add('POST','add',PAGE_MODE_ADD)->
			add('POST','cancel',PAGE_MODE_POST)->
			add('GET' ,'edit',PAGE_MODE_EDIT)->
			add('POST','edit',PAGE_MODE_EDIT)->
			add('POST','save',PAGE_MODE_POST);
		return $rmo;
	}
/**
 *	create and return RMO object with
 *	GET: add & edit
 *	POST: add, edit, clone, save & cancel
 *	POST>cancel is the default
 *	@return \common\rmo
 */
	public static function rmo_with_clone() {
		$rmo = self::rmo_base();
		$rmo->add('POST','clone',PAGE_MODE_CLONE);
		return $rmo;
	}
}
