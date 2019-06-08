<?php
/*
	super_fun.inc

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
/*
 * super_fun contains replacements for system functions
 */
/*
 *	Replacement for gettext, converts html characters <>"' into their character codes 
 */
function gtext(string $data = '') {
	return htmlspecialchars(gettext($data),ENT_QUOTES|ENT_HTML5,'UTF-8',false);
}
function gxmltext(string $data = '') {
	return xmlspecialchars(gettext($data));
}
function unicode_escape_javascript(string $data = '',int $flags = JSON_HEX_QUOT | JSON_HEX_APOS): string {
	$source = [];
	$destination = [];
	if($flags & JSON_HEX_QUOT):
		$source[] = '"';
		$destination[] = '\u0022';
	endif;
	if($flags & JSON_HEX_AMP):
		$source[] = '&';
		$destination[] = '\u0026';
	endif;
	if($flags & JSON_HEX_APOS):
		$source[] = "'";
		$destination[] = '\u0027';
	endif;
	if($flags & JSON_HEX_TAG):
		$source[] = '<';
		$destination[] = '\u003c';
		$source[] = '>';
		$destination[] = '\u003e';
	endif;
	return str_replace($source,$destination,$data);
}
function xmlspecialchars(string $data = '') {
/*
 *	Convert or strip illegal XML characters from UTF-8 string
 */
	return preg_replace('/[^'
		. '\x{0009}'
		. '\x{000A}'
		. '\x{000D}'
		. '\x{0020}-\x{007E}'
		. '\x{0085}'
		. '\x{00A0}-\x{D7FF}'
		. '\x{E000}-\x{FFFD}'
		. '\x{10000}-\x{1FFFD}'
		. '\x{20000}-\x{2FFFD}'
		. '\x{30000}-\x{3FFFD}'
		. '\x{40000}-\x{4FFFD}'
		. '\x{50000}-\x{5FFFD}'
		. '\x{60000}-\x{6FFFD}'
		. '\x{70000}-\x{7FFFD}'
		. '\x{80000}-\x{8FFFD}'
		. '\x{90000}-\x{9FFFD}'
		. '\x{A0000}-\x{AFFFD}'
		. '\x{B0000}-\x{BFFFD}'
		. '\x{C0000}-\x{CFFFD}'
		. '\x{D0000}-\x{DFFFD}'
		. '\x{E0000}-\x{EFFFD}'
		. '\x{F0000}-\x{FFFFD}'
		. '\x{100000}-\x{10FFFD}'
		. ']+/u','�',htmlspecialchars($data,ENT_QUOTES|ENT_XML1,'UTF-8',false));
}
