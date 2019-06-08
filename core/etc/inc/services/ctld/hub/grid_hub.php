<?php
/*
	grid_hub.php

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
final class grid_hub {
/**
 *	process request method
 *	@global string $d_sysrebootreqd_path
 *	@global array $input_errors
 *	@global string $errormsg
 *	@global string $savemsg
 *	@param \common\properties\container $cop
 *	@param \common\sphere\root $sphere
 *	@param \common\rmo\rmo $rmo
 */
	final public static function looper(myp\container $cop,mys\root $sphere,myr\rmo $rmo) {
		global $d_sysrebootreqd_path;
		global $input_errors;
		global $errormsg;
		global $savemsg;

//		preset $savemsg in case a reboot is pending
		if(file_exists($d_sysrebootreqd_path)):
			$savemsg = get_std_save_message(0);
		endif;
		list($page_method,$page_action,$page_mode) = $rmo->validate();
		switch($page_method):
			case 'SESSION':
				switch($page_action):
					case $sphere->get_script()->get_basename():
						//	catch error code
						$retval = filter_var($_SESSION[$sphere->get_script()->get_basename()],FILTER_VALIDATE_INT,['options' => ['default' => 0]]);
						unset($_SESSION['submit'],$_SESSION[$sphere->get_script()->get_basename()]);
						$savemsg = get_std_save_message($retval);
						break;
				endswitch;
				break;
/*
			case 'GET':
				switch($page_action):
					case 'view':
						break;
				endswitch;
				break;
 */
			case 'POST':
				switch($page_action):
					case 'apply':
						$retval = 0;
						$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
						config_lock();
						$retval |= rc_update_reload_service('ctld');
						config_unlock();
						$_SESSION['submit'] = $sphere->get_script()->get_basename();
						$_SESSION[$sphere->get_script()->get_basename()] = $retval;
						header($sphere->get_script()->get_location());
						exit;
						break;
					case $sphere->get_cbm_button_val_delete():
						updatenotify_cbm_delete($sphere,$cop);
						header($sphere->get_script()->get_location());
						exit;
						break;
					case $sphere->get_cbm_button_val_toggle():
						if(updatenotify_cbm_toggle($sphere,$cop)):
							write_config();
						endif;
						header($sphere->get_script()->get_location());
						exit;
						break;
					case $sphere->get_cbm_button_val_enable():
						if(updatenotify_cbm_enable($sphere,$cop)):
							write_config();
						endif;
						header($sphere->get_script()->get_location());
						exit;
						break;
					case $sphere->get_cbm_button_val_disable():
						if(updatenotify_cbm_disable($sphere,$cop)):
							write_config();
						endif;
						header($sphere->get_script()->get_location());
						exit;
						break;
				endswitch;
				break;
		endswitch;
	}
}
