<?php
/*
	navbar.css.php

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
require_once 'config.inc';

header('Content-type: text/css');
$config_subkey = 'cssnavbarfile';
$filemode_subkey = 'cssnavbarfilemode';
$css_default_filename = sprintf('%1$s/css/%2$s',$g['www_path'],'navbar.css');
$webgui_settings = &array_make_branch($config,'system','webgui');
$css_filemode = filter_var($webgui_settings[$filemode_subkey],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '','regexp' => '/^(|append|replace)$/']]);
$css_custom_filename = filter_var($webgui_settings[$config_subkey],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => NULL,'regexp' => '/\S/']]);
switch($css_filemode):
	default:
		if(file_exists($css_default_filename)):
			$css_content = file_get_contents($css_default_filename);
			if(false !== $css_content):
				echo $css_content;
			endif;
		endif;
		break;
	case 'append':
		if(file_exists($css_default_filename)):
			$css_content = file_get_contents($css_default_filename);
			if(false !== $css_content):
				echo $css_content;
			endif;
		endif;
//		append customizations
		if(isset($css_custom_filename) && file_exists($css_custom_filename)):
			$css_content = file_get_contents($css_custom_filename);
			if(false !== $css_content):
				echo "/*\n\tCustomizations\n*/\n",$css_content;
			endif;
		endif;
		break;
	case 'replace':
		$css_content = null;
		if(isset($css_custom_filename) && file_exists($css_custom_filename)):
			$css_content = file_get_contents($css_custom_filename);
			if(false === $css_content):
				$css_content = NULL;
			else:
				echo "/*\n\tCustom CSS\n*/\n",$css_content;
			endif;
		endif;
//		fallback
		if(is_null($css_content)):
			if(file_exists($css_default_filename)):
				$css_content = file_get_contents($css_default_filename);
				if(false !== $css_content):
					echo $css_content;
				endif;
			endif;
		endif;
		break;
endswitch;
