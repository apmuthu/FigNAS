<?php
/*
	unzip.php

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
require_once './_include/permissions.php';
require_once './_include/debug.php';
//------------------------------------------------------------------------------
// File Clone of copy_move.php
//------------------------------------------------------------------------------
function dir_list($dir) { // make list of directories
	// this list is used to copy/move items to a specific location
	$dir_list = [];
	$handle = @opendir(get_abs_dir($dir));
	if($handle === false):
		return; // unable to open dir
	endif;
	while(($new_item = readdir($handle)) !== false):
		//if(!@file_exists(get_abs_item($dir, $new_item))) continue;
		if(!get_show_item($dir,$new_item)):
			continue;
		endif;
		if(!get_is_dir($dir,$new_item)):
			continue;
		endif;
		$dir_list[$new_item] = $new_item;
	endwhile;
	// sort
	if(is_array($dir_list)):
		ksort($dir_list);
	endif;
	return $dir_list;
}
//------------------------------------------------------------------------------
function dir_print($dir_list,$new_dir) { // print list of directories
	// this list is used to copy/move items to a specific location
	// Link to Parent Directory
	$dir_up = dirname($new_dir);
	if($dir_up == '.'):
		$dir_up = '';
	endif;
	$js_string = addslashes($dir_up);
	echo '<tr>',"\n",
			'<td class="lcelc">',
				'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
					'<img style="border:0px;vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['up'],'" alt="">',
				'</div></a>',
			'</td>',"\n",
			'<td class="lcebl">',
				'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
					htmlspecialchars('..'),
				'</div></a>',
			'</td>',"\n",
		'</tr>',"\n";
	// Print List Of Target Directories
	if(!is_array($dir_list)):
		return;
	endif;
	foreach($dir_list as $new_item => $value):
		$s_item = $new_item;
		if(strlen($s_item) > 40):
			$s_item = substr($s_item,0,37) . '...';
		endif;
		$js_string = addslashes(get_rel_item($new_dir,$new_item));
		echo '<tr>',"\n",
				'<td class="lcelc">',
					'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
						'<img style="border:0px;vertical-align:middle" width="16" height="16" src="_img/dir.gif" alt="">',
					'</div></a>',
				'</td>',"\n",
				'<td class="lcebl">',
					'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
						htmlspecialchars($s_item),
					'</div></a>',
				'</td>',"\n",
			'</tr>',"\n";
	endforeach;
}
//------------------------------------------------------------------------------
	// copy/move file/dir
function unzip_item($dir) {
	global $home_dir;

	// copy and move are only allowed if the user may read and change files
	if(!permissions_grant_all($dir,NULL,['read','create'])):
		show_error($GLOBALS['error_msg']['accessfunc']);
	endif;
	// Vars
	$new_dir = $GLOBALS['__POST']['new_dir'] ?? $dir;
	$_img = $GLOBALS['baricons']['unzip'];
	// Get Selected Item
	if(!isset($GLOBALS['__POST']['item']) && isset($GLOBALS['__GET']['item'])):
		$s_item = $GLOBALS['__GET']['item'];
	elseif(isset($GLOBALS['__POST']['item'])):
		$s_item = $GLOBALS['__POST']['item'];
	endif;
	$dir_extract = sprintf('%s/%s',$home_dir,$new_dir);
	if($new_dir != ''):
		$dir_extract .= '/';
	endif;
	$zip_name = sprintf('%s/%s/%s',$home_dir,$dir,$s_item);
	// Get New Location & Names
	if(!isset($GLOBALS['__POST']['confirm']) || $GLOBALS['__POST']['confirm'] != 'true'):
		show_header($GLOBALS['messages']['actunzipitem']);

		// JavaScript for Form:
		// Select new target directory / execute action
?>
<script type="text/javascript">
//<![CDATA[
function NewDir(newdir) {
	document.selform.new_dir.value = newdir;
	document.selform.submit();
}
function Execute() {
	document.selform.confirm.value = "true";
}
//]]>
</script>
<?php
		// "Copy / Move from .. to .."
		$s_dir = $dir;
		if(strlen($s_dir) > 40):
			$s_dir = '...' . substr($s_dir,-37);
		endif;
		$s_ndir = $new_dir;
		if(strlen($s_ndir) > 40):
			$s_ndir = '...' . substr($s_ndir,-37);
		endif;
		echo '<form name="selform" method="post" action="',make_link('post',$dir,NULL),'">';
		echo	'<table class="area_data_selection">',"\n",
					'<colgroup>',"\n",
						'<col style="width:5%">',"\n",
						'<col style="width:95%">',"\n",
					'</colgroup>',
					'<thead>',"\n",
						'<tr>',"\n",
							'<th class="lhelc">',
//								'<img style="border:0px;vertical-align:middle" src="',$_img,'" alt="">',
//								htmlspecialchars(' > '),
								'<img style="border:0px;vertical-align:middle" src="',$GLOBALS['baricons']['unzipto'],'" alt="">',"\n",
							'</th>',
							'<th class="lhebl">',"\n",
								'&nbsp;',htmlspecialchars(sprintf('%s: /%s',$GLOBALS['messages']['actdir'],$s_ndir)),
							'</th>',"\n",
						'</tr>',"\n",
					'</thead>',"\n",
					'<tbody>',"\n",
						dir_print(dir_list($new_dir),$new_dir),
					'</tbody>',"\n",
				'</table>',"\n";
		echo	'<table class="area_data_selection">',"\n",
					'<colgroup>',"\n",
						'<col style="width:5%">',"\n",
						'<col style="width:95%">',"\n",
					'</colgroup>',"\n",
					'<thead>',"\n",
						'<tr>',"\n",
							'<th class="gap" colspan="2">','</th>',"\n",
						'</tr>',"\n",
						'<tr>',"\n",
							'<th class="lhetop" colspan="3">',htmlspecialchars('Archive'),'</th>',"\n",
						'</tr>',"\n",
						'<tr>',"\n",
							'<th class="lhelc">','</th>',
							'<th class="lhebl">',htmlspecialchars('Archive Name'),'</th>',"\n",
						'</tr>',"\n",
					'</thead>',
					'<tbody>',"\n",
						'<tr>',
							'<td class="lcelc">',
								'<img style="border:0px;vertical-align:middle" src="',$GLOBALS['baricons']['zip'],'" alt="">',
							'</td>',
							'<td class="lcebl">',
								'<input type="hidden" name="item" value="',htmlspecialchars($s_item),'">',htmlspecialchars($s_item),
							'</td>',
						'</tr>',
					'</tbody>',
				'</table>';
		// Submit & Cancel
		echo	'<div id="submit">',
					'<input type="submit" class="formbtn" value="',$GLOBALS['messages']['btnunzip'],'" onclick="javascript:Execute();">',
					'<input type="button" class="formbtn" value="',$GLOBALS['messages']['btncancel'],'" onClick="javascript:location=\'',make_link('list',$dir,NULL),'\';">',
					'<input type="hidden" name="do_action" value="',$GLOBALS['action'],'">',"\n",
					'<input type="hidden" name="confirm" value="false">',"\n",
					'<input type="hidden" name="new_dir" value="',htmlspecialchars($new_dir),'">',"\n",
				'</div>';
		echo '</form>',"\n";
		return;
	endif;
	// DO COPY/MOVE
	// ALL OK?
	if(!@file_exists(get_abs_dir($new_dir))):
		show_error(htmlspecialchars($new_dir) . ': ' . $GLOBALS['error_msg']['targetexist']);
	endif;
	if(!get_show_item($new_dir,'')):
		show_error(htmlspecialchars($new_dir) . ': ' . $GLOBALS['error_msg']['accesstarget']);
	endif;
	if(!down_home(get_abs_dir($new_dir))):
		show_error(htmlspecialchars($new_dir) . ': ' . $GLOBALS['error_msg']['targetabovehome']);
	endif;
	// copy / move files
	$err = false;
	$res = true;
	$exx = pathinfo($zip_name,PATHINFO_EXTENSION);
	if($exx == 'zip'):
		$zip = new ZipArchive;
		$res = $zip->open($zip_name);
		if($res === true):
			$zip->extractTo($dir_extract);
			$zip->close();
		endif;
	else: // gz, tar, bz2, ....
		include_once './_lib/archive.php';
		extArchive::extract($zip_name,$dir_extract);
	endif;
	if($res == false):
		$error = $GLOBALS['error_msg']['unzip'];
		$err = true;
	else:
		$error = NULL;
	endif;
	if($err): // there were errors
		$err_msg = '';
		if(isset($error)):
			$err_msg .= $zip_name . ' : ' . $error . '<br>' . "\n";
		endif;
		show_error($err_msg);
	endif;
	header('Location: ' . make_link('list',$dir,NULL));
}
?>
