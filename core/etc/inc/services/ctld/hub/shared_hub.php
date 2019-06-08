<?php
/*
	shared_hub.php

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
namespace services\ctld\hub;
use common\properties as myp;
use common\rmo as myr;
use common\sphere as mys;
/**
 *	Wrapper class for autoloading functions
 */
final class shared_hub {
/**
 *	Helper function to process row update notifications
 *	@param int $mode
 *	@param string $data
 *	@param object $sphere
 *	@return int
 */
	final public static function process_notification(int $mode,string $data,mys\grid $sphere) {
		$retval = 0;
		$sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->get_row_identifier());
		if(false !== $sphere->row_id):
			switch($mode):
				case UPDATENOTIFY_MODE_NEW:
					break;
				case UPDATENOTIFY_MODE_MODIFIED:
					break;
				case UPDATENOTIFY_MODE_DIRTY_CONFIG:
					unset($sphere->grid[$sphere->row_id]);
					write_config();
					break;
				case UPDATENOTIFY_MODE_DIRTY:
					unset($sphere->grid[$sphere->row_id]);
					write_config();
					break;
			endswitch;
		endif;
		updatenotify_clear($sphere->get_notifier(),$data);
		return $retval;
	}
}
