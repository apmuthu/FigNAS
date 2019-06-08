<?php
/*
	copy_move.php

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
function copy_move_items($dir) {
	// copy and move are only allowed if the user may read and change files
	if($GLOBALS['action'] == 'copy' && !permissions_grant_all($dir,NULL,['read','create'])):
		show_error($GLOBALS['error_msg']['accessfunc']);
	endif;
	if($GLOBALS['action'] == 'move' && !permissions_grant($dir,NULL,'change')):
		show_error($GLOBALS['error_msg']['accessfunc']);
	endif;
	// Vars
	$first = $GLOBALS['__POST']['first'];
	if($first == 'y'):
		$new_dir = $dir;
	else:
		$new_dir = $GLOBALS['__POST']['new_dir'];
	endif;
	if($new_dir == '.'):
		$new_dir = '';
	endif;
	$cnt = count($GLOBALS['__POST']['selitems']);
	// Copy or Move?
	if($GLOBALS['action'] != 'move'):
		$_img = '_img/__copy.gif';
	else:
		$_img = '_img/__cut.gif';
	endif;
	// Get New Location & Names
	if(!isset($GLOBALS['__POST']['confirm']) || $GLOBALS['__POST']['confirm'] != 'true'):
		$msg = $GLOBALS['action'] != 'move' ? $GLOBALS['messages']['actcopyitems'] : $GLOBALS['messages']['actmoveitems'];
		show_header($msg);

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
		// Form for Target Directory & New Names
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
								'<img style="border:0px;vertical-align:middle" src="_img/__paste.gif" alt="">',"\n",
							'</th>',
							'<th class="lhebl">',"\n",
								'&nbsp;',htmlspecialchars(sprintf(($GLOBALS['action'] != 'move' ? $GLOBALS['messages']['actcopyfrom'] : $GLOBALS['messages']['actmovefrom']),$s_dir,$s_ndir)),
							'</th>',"\n",
						'</tr>',"\n",
					'</thead>',"\n",
					'<tbody>',"\n",
						dir_print(dir_list($new_dir),$new_dir),
					'</tbody>',"\n",
				'</table>',"\n";
//		echo	'<br>',"\n";
//		Print Text Inputs to change Names
		echo	'<table class="area_data_selection">',"\n",
					'<colgroup>',"\n",
						'<col style="width:5%">',"\n",
						'<col style="width:45%">',"\n",
						'<col style="width:50%">',"\n",
					'</colgroup>',"\n",
					'<thead>',"\n",
						'<tr>',"\n",
							'<th class="gap" colspan="3">','</th>',"\n",
						'</tr>',"\n",
						'<tr>',"\n",
							'<th class="lhetop" colspan="3">',htmlspecialchars('Rename Item(s)'),'</th>',"\n",
						'</tr>',"\n",
						'<tr>',"\n",
							'<th class="lhelc">','</th>',"\n",
							'<th class="lhell">',htmlspecialchars('New Name'),'</th>',"\n",
							'<th class="lhebl">',htmlspecialchars('Current Name'),'</th>',"\n",
						'</tr>',"\n",
					'</thead>',"\n",
					'<tbody>',"\n";
		for($i = 0;$i < $cnt;++$i):
			$selitem = $GLOBALS['__POST']['selitems'][$i];
			if(isset($GLOBALS['__POST']['newitems'][$i])):
				$newitem = $GLOBALS['__POST']['newitems'][$i];
				if($first == 'y'):
					$newitem = $selitem;
				endif;
			else:
				$newitem = $selitem;
			endif;
			echo		'<tr>',"\n",
							'<td class="lcelc">',
								'<img style="border:0px;vertical-align:middle" src="_img/_info.gif" alt="">',
							'</td>',"\n",
							'<td class="lcell">',
								'<input type="text" size="40" name="newitems[]" value="',htmlspecialchars($newitem),'">',
							'</td>',"\n",
							'<td class="lcebl">',
								'<input type="hidden" name="selitems[]" value="',htmlspecialchars($selitem),'">',htmlspecialchars($selitem),
							'</td>',"\n",
						'</tr>',"\n";
		endfor;
		echo		'</tbody>',
				'</table>';
		// Submit & Cancel
		echo	'<div id="submit">',
					'<input type="submit" class="formbtn" value="',($GLOBALS['action'] != 'move' ? $GLOBALS['messages']['btncopy'] : $GLOBALS['messages']['btnmove']),'" onclick="javascript:Execute();">',
					'<input type="button" class="formbtn" value="',$GLOBALS['messages']['btncancel'],'" onClick="javascript:location=\'',make_link('list',$dir,NULL),'\';">',
					'<input type="hidden" name="do_action" value="',$GLOBALS['action'],'">',"\n",
					'<input type="hidden" name="confirm" value="false">',"\n",
					'<input type="hidden" name="first" value="n">',"\n",
					'<input type="hidden" name="new_dir" value="',htmlspecialchars($new_dir),'">',"\n",
				'</div>';
		echo '</form>',"\n";
		return;
	endif;
	// DO COPY/MOVE
	// ALL OK?
	if(!@file_exists(get_abs_dir($new_dir))):
		show_error($new_dir . ': ' . $GLOBALS['error_msg']['targetexist']);
	endif;
	if(!get_show_item($new_dir,'')):
		show_error($new_dir . ': ' . $GLOBALS['error_msg']['accesstarget']);
	endif;
	if(!down_home(get_abs_dir($new_dir))):
		show_error($new_dir . ': ' . $GLOBALS['error_msg']['targetabovehome']);
	endif;
	// copy / move files
	$err = false;
	$items = [];
	for($i = 0;$i < $cnt;++$i):
		$tmp = $GLOBALS['__POST']['selitems'][$i];
		$new = basename($GLOBALS['__POST']['newitems'][$i]);
		$abs_item = get_abs_item($dir,$tmp);
		$abs_new_item = get_abs_item($new_dir,$new);
		$items[$i] = $tmp;
		// Check
		if($new == ''):
			$error[$i] = $GLOBALS['error_msg']['miscnoname'];
			$err = true;
			continue;
		endif;
		if(!@file_exists($abs_item)):
			$error[$i] = $GLOBALS['error_msg']['itemexist'];
			$err = true;
			continue;
		endif;
		if(!get_show_item($dir,$tmp)):
			$error[$i] = $GLOBALS['error_msg']['accessitem'];
			$err = true;
			continue;
		endif;
		if(@file_exists($abs_new_item)):
			$error[$i] = $GLOBALS['error_msg']['targetdoesexist'];
			$err = true;
			continue;
		endif;
		// Copy / Move
		if($GLOBALS['action'] == 'copy'):
			if(@is_link($abs_item) || @is_file($abs_item)):
				// check file-exists to avoid error with 0-size files (PHP 4.3.0)
				$ok = @copy($abs_item,$abs_new_item);    //||@file_exists($abs_new_item);
			elseif(@is_dir($abs_item)):
				$ok = copy_dir($abs_item,$abs_new_item);
			endif;
		else:
			$ok = @rename($abs_item,$abs_new_item);
		endif;
		if($ok === false):
			$error[$i] = ($GLOBALS['action'] == 'copy' ? $GLOBALS['error_msg']['copyitem'] : $GLOBALS['error_msg']['moveitem']);
			$err = true;
			continue;
		endif;
		$error[$i] = NULL;
	endfor;
	if($err): // there were errors
		$err_msg = '';
		for($i = 0;$i < $cnt;++$i):
			if($error[$i] == NULL):
				continue;
			endif;
			$err_msg .= $items[$i] . ' : ' . $error[$i] . '<br>' . "\n";
		endfor;
		show_error($err_msg);
	endif;
	header('Location: ' . make_link('list',$dir,NULL));
}
?>
