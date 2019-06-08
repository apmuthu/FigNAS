<?php
/*
	init.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Authors: quix@free.fr, ck@realtime-projects.com.
	The Initial Developer of the Original Code is The QuiX project.

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
require_once "_include/error.php";

_debug("Initializing ---------------------------------------------------");
$GLOBALS['__GET'] =&$_GET;
$GLOBALS['__POST'] =&$_POST;
$GLOBALS['__SERVER'] =&$_SERVER;
$GLOBALS['__FILES'] =&$_FILES;
/*
_debug('xxx3 action: ' . $GLOBALS['__GET']['action'] ?? '' . '/' . $GLOBALS["__GET"]['do_action'] ?? '' . '/' . (isset($GLOBALS['__GET']['action']) ? 'true' : 'false'));
 */
$GLOBALS['action'] = $GLOBALS['__GET']['action'] ?? 'list';
if($GLOBALS['action'] == 'post' && isset($GLOBALS['__POST']['do_action'])):
	$GLOBALS['action'] = $GLOBALS['__POST']['do_action'];
endif;
if($GLOBALS['action'] == ''):
	$GLOBALS['action'] = 'list';
endif;
/*
_debug('xxx3 action: ' . $GLOBALS['__GET']['action'] ?? '' . '/' . $GLOBALS['__GET']['do_action'] ?? '' . '/' . (isset($GLOBALS['__GET']['action']) ? 'true' : 'false'));
 */
// Get Item
$GLOBALS['item'] = $GLOBALS['__GET']['item'] ?? '';
// Get Sort
$GLOBALS['order'] = filter_input(INPUT_GET,'order',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'name','regexp' => '/\S/']]);
// Get Sortorder (yes==up)
$GLOBALS['srt'] = filter_input(INPUT_GET,'srt',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'yes','regexp' => '/^(yes|no)$/']]);
// Necessary files
ob_start(); // prevent unwanted output
/* XIGMANAS CODE*/
if(function_exists('date_default_timezone_set')):
	if(function_exists('date_default_timezone_get')):
		@date_default_timezone_set(@date_default_timezone_get());
	else:
		@date_default_timezone_set('UTC');
	endif;
endif;
/* END XIGMANAS CODE*/
/* ORIGINAL CODE
date_default_timezone_set ( "UTC" );
 */
if(!is_readable('./_config/conf.php')):
	show_error('./_config/conf.php not found.. please see installation instructions');
endif;

require './_config/conf.php';
require './_config/configs.php';

_load_language($GLOBALS['language']);
/* XIGMANAS CODE*/
if(isset($GLOBALS['lang'])):
	$GLOBALS['language'] = $GLOBALS['lang'];
endif;
if(file_exists('./_lang/' . $GLOBALS['language'] . '.php')):
	require './_lang/' . $GLOBALS['language'] . '.php';
else:
	require './_lang/en_US.php';
endif;
/* END XIGMANAS CODE*/

require './_config/mimes.php';
require './_include/extra.php';
require_once './_include/header.php';
require './_include/footer.php';
ob_start(); // prevent unwanted output
require_once './_include/login.php';

login_check();

// after login, language may have changed..
if(isset($_SESSION['language'])):
	_load_language($_SESSION['language']);
endif;

ob_end_clean(); // get rid of cached unwanted output
$prompt = $GLOBALS['login_prompt'][$GLOBALS['language']] ?? $GLOBALS['login_prompt']['en'];
if(isset($prompt)):
	$GLOBALS['messages']['actloginheader'] = $prompt;
endif;
ob_end_clean(); // get rid of cached unwanted output

function _load_language($lang) {
    if(!isset($lang)):
        $lang = 'en_US';
	endif;
	$f1 = sprintf('./_lang/%s.php',$lang);
	$f2 = sprintf('./_lang/%s_mimes.php',$lang);
	if(!(file_exists($f1) and file_exists($f2))):
        $lang = 'en_US';
		$f1 = sprintf('./_lang/%s.php',$lang);
		$f2 = sprintf('./_lang/%s_mimes.php',$lang);
	endif;
	require $f1;
	require $f2;
}
?>
