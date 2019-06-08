<?php
/*
	list.php

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
require_once './_include/login.php';
require_once './_include/qxpath.php';

function make_list($_list1,$_list2) { // make list of files
	$list = [];
	if($GLOBALS['srt'] == 'yes'):
		$list1 = $_list1;
		$list2 = $_list2;
	else:
		$list1 = $_list2;
		$list2 = $_list1;
	endif;
	if(is_array($list1)):
		foreach($list1 as $key => $val):
			$list[$key] = $val;
		endforeach;
	endif;
	if(is_array($list2)):
		foreach($list2 as $key => $val):
			$list[$key] = $val;
		endforeach;
	endif;
	return $list;
}
/**
 make table of files in dir
 make tables & place results in reference-variables passed to function
 also 'return' total filesize & total number of items
*/
function make_tables($dir,&$dir_list,&$file_list,&$tot_file_size,&$num_items) {
	$tot_file_size = $num_items = 0;

	// Open directory
	$handle = @opendir(get_abs_dir($dir));
	if($handle === false):
		show_error($dir . ': ' . $GLOBALS['error_msg']['opendir']);
	endif;
	// Read directory
	while(($new_item = readdir($handle)) !== false):
		$abs_new_item = get_abs_item($dir, $new_item);
		if(!get_show_item($dir,$new_item)):
			continue;
		endif;
		$new_file_size = is_link($abs_new_item) ? 0 : @filesize($abs_new_item);
		$tot_file_size += $new_file_size;
		$num_items++;
		if(is_dir($dir.DIRECTORY_SEPARATOR.$new_item)):
			if($GLOBALS['order'] == 'mod'):
				$dir_list[$new_item] = @filemtime($abs_new_item);
			else:
				// order == "size", "type" or "name"
				$dir_list[$new_item] = $new_item;
			endif;
		else:
			if($GLOBALS['order'] == 'size'):
				$file_list[$new_item] = $new_file_size;
			elseif ($GLOBALS['order'] == 'mod'):
				$file_list[$new_item] = @filemtime($abs_new_item);
			elseif ($GLOBALS['order'] == 'type'):
				$file_list[$new_item] = get_mime_type($dir,$new_item,'type');
			else:
				// order == "name"
				$file_list[$new_item] = $new_item;
			endif;
		endif;
	endwhile;
	closedir($handle);
	//	sort directories
	if(is_array($dir_list)):
		if($GLOBALS['order'] == 'mod'):
			if ($GLOBALS['srt'] == 'yes'):
				arsort($dir_list);
			else:
				asort($dir_list);
			endif;
		else:
			// order == "size", "type" or "name"
			if ($GLOBALS['srt'] == 'yes'):
				ksort($dir_list);
			else:
				krsort($dir_list);
			endif;
		endif;
	endif;
	//	sort files
	if(is_array($file_list)):
		if($GLOBALS['order'] == 'mod'):
			if($GLOBALS["srt"] == 'yes'):
				arsort($file_list);
			else:
				asort($file_list);
			endif;
		elseif($GLOBALS['order'] == 'size' || $GLOBALS['order'] == 'type'):
			if($GLOBALS['srt'] == 'yes'):
				asort($file_list);
			else:
				arsort($file_list);
			endif;
		else:
			// order == "name"
			if($GLOBALS['srt'] == 'yes'):
				ksort($file_list);
			else:
				krsort($file_list);
			endif;
		endif;
	endif;
}
/**
  print table of files
 */
function print_table($dir,$list) {
	if(!is_array($list)):
		return;
	endif;
	foreach($list as $item => $value):
		// link to dir / file
		$abs_item = get_abs_item($dir,$item);
		$target='';
		if(is_dir($abs_item)):
			$link = make_link('list',get_rel_item($dir,$item),NULL);
		else:
			$link = make_link('download',$dir,$item);
			$target = '_blank';
		endif;
		echo '<tr class="rowdata">';
		echo '<td class="lcelc"><input type="checkbox" name="selitems[]" value="',htmlspecialchars($item),'" onclick="javascript:Toggle(this);"></td>',"\n";
		// Icon + Link
		echo '<td class="lcell" style="white-space: nowrap">';
		if(permissions_grant($dir,$item,'read')):
			echo '<a href="',$link,'"><div>';
		endif;
		echo '<img style="vertical-align:middle" width="16" height="16" src="_img/',get_mime_type($dir,$item,'img'),'" alt="">&nbsp;';
		$s_item = $item;
		if(strlen($s_item)>50):
			$s_item = substr($s_item,0,47) . '...';
		endif;
		echo htmlspecialchars($s_item);
		if(permissions_grant($dir,$item,'read')):
			echo '</div></a>';
		endif;
		echo '</td>',"\n";
		// Size
		echo '<td class="lcell">',format_bytes(get_file_size($dir,$item),2,false,false),sprintf('%10s','&nbsp;'),'</td>',"\n";
		// Type
		echo '<td class="lcell">',_get_link_info($dir,$item,'type'),'</td>',"\n";
		// Modified
		echo '<td class="lcell">',parse_file_date(get_file_date($dir,$item)),'</td>',"\n";
		// Permissions
		echo '<td class="lcell">';
		if(permissions_grant($dir,NULL,'change')):
			echo '<a href="',make_link('chmod',$dir,$item),'" title="',$GLOBALS['messages']['permlink'],'">';
		endif;
		echo parse_file_type($dir,$item).parse_file_perms(get_file_perms($dir,$item));
		if(permissions_grant($dir,NULL,'change')):
			echo '</a>';
		endif;
		echo '</td>',"\n";
		// Actions
		echo '<td class="lcebl">';
		echo '<table><tbody><tr>';
		// Edit
		if(get_is_editable($dir, $item)):
			_print_link('edit',permissions_grant($dir,$item,'change'),$dir,$item);
		else:
			// Unzip
			if(get_is_unzipable($dir,$item)):
				_print_link('unzip',permissions_grant($dir,$item,'create'),$dir,$item);
			else:
				echo '<td><img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['none'],'" alt=""></td>',"\n";
			endif;
		endif;
		// Download
		if(get_is_file($dir,$item)):
			_print_link('download',permissions_grant($dir,$item,'read'),$dir,$item);
		else:
			echo '<td><img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['none'],'" alt=""></td>',"\n";
		endif;
		echo '</tr></tbody></table>';
		echo '</td>';
		echo '</tr>',"\n";
	endforeach;
}
/**
 MAIN FUNCTION
 */
function list_dir($dir) {
	if(!get_show_item($dir,NULL)):
		show_error($GLOBALS['error_msg']['accessdir'] . " : '$dir'");
	endif;
	// make file & dir tables, & get total filesize & number of items
	make_tables($dir,$dir_list,$file_list,$tot_file_size,$num_items);
	$s_dir = $dir;
	if (strlen($s_dir) > 50 ):
		$s_dir = '...' . substr($s_dir,-47);
	endif;
	show_header($GLOBALS['messages']['actdir'] . ': ' . _breadcrumbs($dir));
	// Javascript functions:
	include './_include/javascript.php';
	// Sorting of items
	$_img = '&nbsp;<img style="vertical-align:middle" width="10" height="10" src="_img/';
	if($GLOBALS['srt'] == 'yes'):
		$_srt = 'no';
		$_img .= '_arrowup.gif" alt="^">';
	else:
		$_srt = 'yes';
		$_img .= '_arrowdown.gif" alt="v">';
	endif;
	// Toolbar
	echo '<table class="area_data_settings"><tbody><tr>';
	echo '<td><table><tbody><tr>',"\n";
	// PARENT DIR
	echo '<td style="padding-right:4px"><a href="',make_link('list',path_up($dir),NULL),'">',
			'<img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['up'],'" alt="',$GLOBALS['messages']['uplink'],'" title="',$GLOBALS['messages']['uplink'],'">',
		'</a></td>',"\n";
	// HOME DIR
	echo '<td style="padding-right:4px"><a href="',make_link('list',NULL,NULL),'">',
			'<img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['home'],'" alt="',$GLOBALS['messages']['homelink'],'" title="',$GLOBALS['messages']['homelink'],'">',
		'</a></td>',"\n";
	// RELOAD
	echo '<td style="padding-right:4px"><a href="javascript:location.reload();">',
			'<img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['reload'],'" alt="',$GLOBALS['messages']['reloadlink'],'" title="',$GLOBALS['messages']['reloadlink'],'">',
		'</a></td>',"\n";
	// SEARCH
	echo '<td style="padding-right:4px"><a href="',make_link('search',$dir,NULL),'">',
			'<img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['search'],'" alt="',$GLOBALS['messages']['searchlink'],'" title="',$GLOBALS['messages']['searchlink'],'">',
		'</a></td>',"\n";
	echo '<td style="padding-right:8px"></td>';
	_print_link('download_selected',permissions_grant($dir, NULL,'read'),$dir,NULL); // print the download button
	_print_edit_buttons($dir); // print the edit buttons
	// ADMIN & LOGOUT
	if(login_is_user_logged_in()):
		echo '<td style="padding-right:8px"></td>';
		_print_link('admin',permissions_grant(NULL,NULL,'admin') || permissions_grant(NULL,NULL,'password'),$dir,NULL); // ADMIN
		_print_link('logout',true,$dir,NULL); // LOGOUT
	endif;
	echo '</tr></tbody></table></td>',"\n";
	// Create File / Dir
	if(permissions_grant($dir,NULL,'create')):
		echo '<td style="text-align:right">',"\n";
		echo '<form action="',make_link('mkitem',$dir,NULL),'" method="post">',"\n";
		echo '<table style="width:100%"><tbody><tr><td>',"\n";
		echo '<img style="vertical-align:middle" width="16" height="16" src="',$GLOBALS['baricons']['add'],'" alt="">',"\n";
		echo '<select name="mktype">',"\n";
		echo '<option value="file">',$GLOBALS['mimes']['file'],'</option>',"\n";
		echo '<option value="dir">',$GLOBALS['mimes']['dir'],'</option>',"\n";
		echo '</select>',"\n";
		echo '<input name="mkname" type="text" size="15">',"\n";
		echo '<input type="submit" value="',$GLOBALS['messages']['btncreate'],'">',"\n";
		echo '</td></tr></tbody></table>',"\n";
		echo '</form>',"\n";
		echo '</td>',"\n";
	endif;
	echo "</tr></tbody></table>\n";
	// End Toolbar
	// Begin Table + Form for checkboxes
	echo '<form name="selform" method="post" action="',make_link('post',$dir,NULL),'">',"\n";
	echo '<table class="area_data_selection">';
	echo	'<colgroup>',
				'<col style="width:5%">',
				'<col style="width:35%">',
				'<col style="width:10%">',
				'<col style="width:15%">',
				'<col style="width:15%">',
				'<col style="width:10%">',
				'<col style="width:10%">',
			'</colgroup>',"\n";
	// Table Header
//	echo '<tr><td colspan="7"><HR></td></tr>';
	echo	'<thead>',
				'<tr>',
		  			'<th class="lhelc">',"\n",
		  				'<input type="checkbox" name="toggleAllC" onclick="javascript:ToggleAll(this);">',
					'</th>',"\n";
	$new_srt = ($GLOBALS['order'] == 'name') ? $_srt : 'yes'; 
	echo			'<th class="lhell">',
						'<a href="',make_link('list',$dir,NULL,'name',$new_srt),'">',$GLOBALS['messages']['nameheader'];
	if($GLOBALS['order'] == 'name'):
							echo $_img;
	endif;
	echo				'</a>',
					'</th>',"\n";
	$new_srt = ($GLOBALS['order'] == 'size') ? $_srt : 'yes'; 
	echo			'<th class="lhell">',
						'<a href="',make_link('list',$dir,NULL,'size',$new_srt),'">',$GLOBALS['messages']['sizeheader'];
	if($GLOBALS['order'] == 'size'):
							echo $_img;
	endif;
	echo				'</a>',
					'</th>',"\n";
	$new_srt = ($GLOBALS['order'] == 'type') ? $_srt : 'yes'; 
	echo			'<th class="lhell">',
						'<a href="',make_link('list',$dir,NULL,'type',$new_srt),'">',$GLOBALS['messages']['typeheader'];
	if($GLOBALS['order'] == 'type'):
							echo $_img;
	endif;
	echo				'</a>',
					'</th>',"\n";
	$new_srt = ($GLOBALS['order'] == 'mod') ? $_srt : 'yes'; 
	echo			'<th class="lhell">',
						'<a href="',make_link('list',$dir,NULL,'mod',$new_srt),'">',$GLOBALS["messages"]["modifheader"];
	if($GLOBALS['order'] == 'mod'):
							echo $_img;
	endif;
	echo				'</a>',
					'</th>',"\n";
	echo			'<th class="lhell">',
						$GLOBALS['messages']['permheader'],
					'</th>',"\n";
	echo			'<th class="lhebl">',
						$GLOBALS['messages']['actionheader'],
					'</th>',"\n";
	echo		'</tr>',"\n";
	echo	'</thead>',"\n";
	// make & print Table using lists
	echo	'<tbody>',"\n";
				print_table($dir, make_list($dir_list, $file_list));
	echo	'</tbody>',"\n";
	// print number of items & total filesize
	$free = format_bytes(diskfreespace('/'),2,false,false);
	echo	'<tfoot>',"\n",
				'<tr>',"\n",
					'<th class="lcell"></th>',"\n",
					'<th class="lcell">',$num_items,' ',$GLOBALS['messages']['miscitems'],' (',$GLOBALS['messages']['miscfree'],': ',$free,')</th>',"\n",
					'<th class="lcell">',format_bytes($tot_file_size,2,false,false),'</th>',"\n",
					'<th class="lcebl" colspan="4"></th>',"\n",
				'</tr>',"\n",
			'</tfoot>',"\n";
	echo '</table>',"\n";
	echo '<div id="submit">',"\n",
			'<input type="hidden" name="do_action">',"\n",
			'<input type="hidden" name="first" value="y">',"\n",
		'</div>',"\n";
	echo '</form>';
?>
<script type="text/javascript">
//<![CDATA[
	// Uncheck all items (to avoid problems with new items)
	var ml = document.selform;
	var len = ml.elements.length;
	for(var i=0; i<len; ++i) {
		var e = ml.elements[i];
		if(e.name == "selitems[]" && e.checked == true) {
			e.checked=false;
		}
	}
//]]>
</script><?php
}

// *** HELPER FUNCTIONS

function _print_edit_buttons ($dir) {
	// for the copy button the user must have create and read rights
	_print_link('copy',permissions_grant_all($dir,NULL,['create','read']),$dir,NULL);
	_print_link('move',permissions_grant($dir,NULL,'change'),$dir,NULL);
	_print_link('delete',permissions_grant($dir,NULL,'delete'),$dir,NULL);
//	XigmaNAS info: We disable upload function for security and limited space var/temp
//	_print_link('upload',permissions_grant($dir,NULL,'create') && get_cfg_var('file_uploads'),$dir,NULL);
//	_print_link('archive',permissions_grant_all($dir,NULL,['create','read']) && ($GLOBALS['zip'] || $GLOBALS['tar'] || $GLOBALS['tgz']),$dir,NULL);
}

/**
  print out an button link in the toolbar.

  if $allow is set, make this button active and work, otherwise print
  an inactive button.
*/
function _print_link ($function,$allow,$dir,$item) {
	// the list of all available button and the coresponding data
	switch($function):
		case 'copy': $v = ['jf' => 'javascript:Copy();','img' => $GLOBALS['baricons']['copy'],'imgdis' => $GLOBALS['baricons']['notcopy'],'msg' => $GLOBALS['messages']['copylink']];break;
		case 'move': $v = ['jf' => 'javascript:Move();','img' => $GLOBALS['baricons']['move'],'imgdis' => $GLOBALS['baricons']['notmove'],'msg' => $GLOBALS['messages']['movelink']];break;
		case 'delete': $v = ['jf' => 'javascript:Delete();','img' => $GLOBALS['baricons']['delete'],'imgdis' => $GLOBALS['baricons']['notdelete'],'msg' => $GLOBALS['messages']['dellink']];break;
		case 'upload': $v = ['jf' => make_link('upload',$dir,NULL),'img' => $GLOBALS['baricons']['upload'],'imgdis' => $GLOBALS['baricons']['notupload'],'msg' => $GLOBALS['messages']['uploadlink']];break;
		case 'archive': $v = ['jf' => 'javascript:Archive();','img' => $GLOBALS['baricons']['archive'],'msg' => $GLOBALS['messages']['comprlink']];break;
		case 'admin': $v = ['jf' => make_link('admin',$dir,NULL),'img' => $GLOBALS['baricons']['admin'],'msg' => $GLOBALS['messages']['adminlink']];break;
		case 'logout': $v = ['jf' => make_link('logout',NULL,NULL),'img' => $GLOBALS['baricons']['logout'],'imgdis' => '_img/_logout_.gif','msg' => $GLOBALS['messages']['logoutlink']];break;
		case 'edit': $v = ['jf' => make_link('edit',$dir,$item),'img' => $GLOBALS['baricons']['edit'],'imgdis' => $GLOBALS['baricons']['notedit'],'msg' => $GLOBALS['messages']['editlink']];break;
		case 'unzip': $v = ['jf' => make_link('unzip',$dir,$item),'img' => $GLOBALS['baricons']['unzip'],'imgdis' => $GLOBALS['baricons']['notunzip'],'msg' => $GLOBALS['messages']['unziplink']];break;
		case 'download': $v = ['jf' => make_link('download',$dir,$item),'img' => $GLOBALS['baricons']['download'],'imgdis' => $GLOBALS['baricons']['notdownload'],'msg' => $GLOBALS['messages']['downlink']];break;
		case 'download_selected': $v = ['jf' => 'javascript:DownloadSelected();','img' => $GLOBALS['baricons']['download'],'imgdis' => $GLOBALS['baricons']['notdownload'],'msg' => $GLOBALS['messages']['download_selected']];break;
	endswitch;
	if($allow): // make an active link if access is allowed
		echo '<td style="padding-right:4px"><a href="',$v['jf'],'">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$v['img'],'" alt="',$v['msg'],'" title="',$v['msg'],'">',
			'</a></td>',"\n";
	elseif(isset($v['imgdis'])): // make an inactive link if access is forbidden
		echo '<td style="padding-right:4px">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$v['imgdis'],'" alt="',$v['msg'],'" title="',$v['msg'],'">',
			'</td>',"\n";
	endif;
	return;
}

function _get_link_info($dir, $item) {
	$type = get_mime_type($dir, $item, "type");
	if(is_array($type)):
		$type = $type[0];
	endif;
	if(!file_exists(get_abs_item($dir, $item))):
		return '<span style="background:red;">'.$type.'</span>';
	endif;
	return $type;
}

/*
 * The breadcrumbs function will take the user's current path and build a breadcrumb.
 * 
 * 	A breadcrums is a list of links for each directory in the current path.
 * 
 * 	@param
 * $curdir is a string containing what will usually be the users
 * current directory.  %displayseparator is optional and contains a
 * string that will be displayed betweenach crumb.
 * 
 *  Typical syntax:
 * 
 * echo breadcrumbs($dir, ">>");
 * show_header($GLOBALS["messages"]["actdir"].":".breadcrumbs($dir));
 */
function _breadcrumbs($curdir, $displayseparator = ' &raquo; ') {
	//Get localized name for the Home directory
	$homedir = $GLOBALS["messages"]["homelink"];
	// Initialize first crumb and set it to the home directory.
	$breadcrumbs[] = "<a href=\"".make_link("list", "", NULL)."\">$homedir</a>";
	// Take the current directory and split the string into an array at each '/'.
	$patharray = explode('/', $curdir);
	// Find out the index for the last value in our path array
	$lastx = array_keys($patharray);
	$last = end($lastx);
	// Build the rest of the breadcrumbs
	$crumbdir = "";
	foreach($patharray AS $x => $crumb):
		// Add a new directory to the directory list so the link has the
		// correct path to the current crumb.
		$crumbdir = $crumbdir . $crumb;
		if($x != $last):
			// If we are not on the last index, then create a link using $crumb
			// as the text.
			$breadcrumbs[] = "<a href=\"".make_link("list", $crumbdir, NULL)."\">".htmlspecialchars($crumb)."</a>";
			// Add a separator between our crumbs.
			$crumbdir = $crumbdir . DIRECTORY_SEPARATOR;
		else:
			// Don't create a link for the final crumb.  Just display the crumb name.
			$breadcrumbs[] = htmlspecialchars($crumb);
		endif;
	endforeach;
	// Build temporary array into one string.
	return implode($displayseparator, $breadcrumbs);
}
?>
