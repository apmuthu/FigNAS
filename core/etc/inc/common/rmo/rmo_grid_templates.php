<?php
/*
	rmo_grid_templates.php

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
use common\properties as myp;
use common\sphere as mys;
/**
 *	Request Method Object Templates
 */
final class rmo_grid_templates {
/**
 *	create and return RMO object with<br/>
 *	<b>SESSION</b>: basename of the script file<br/>
 *	<b>POST</b>: apply, delete, toggle or enable/disable<br/>
 *	<b>GET</b>: view is the default.
 *	@param \common\properties\container $cop the property object
 *	@param \common\sphere\grid $sphere the sphere object
 *	@return \common\rmo\rmo
 */
	public static function rmo_base(myp\container $cop,mys\grid $sphere) {
		$rmo = new rmo();
		$rmo->
			set_default('GET','view',PAGE_MODE_VIEW)->
			add('SESSION',$sphere->get_script()->get_basename(),PAGE_MODE_VIEW)->
			add('POST','apply',PAGE_MODE_POST)->
			add('POST',$sphere->get_cbm_button_val_delete(),PAGE_MODE_POST);
		if($sphere->is_enadis_enabled() && method_exists($cop,'get_enable')):
			if($sphere->toggle()):
				$rmo->add('POST',$sphere->get_cbm_button_val_toggle(),PAGE_MODE_POST);
			else:
				$rmo->add('POST',$sphere->get_cbm_button_val_enable(),PAGE_MODE_POST);
				$rmo->add('POST',$sphere->get_cbm_button_val_disable(),PAGE_MODE_POST);
			endif;
		endif;
		return $rmo;
	}
}
