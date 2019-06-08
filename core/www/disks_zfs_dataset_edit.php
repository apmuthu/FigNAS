<?php
/*
	disks_zfs_dataset_edit.php

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
require_once 'auth.inc';
require_once 'guiconfig.inc';
require_once 'zfs.inc';
require_once 'co_sphere.php';

function disks_zfs_dataset_edit_get_sphere() {
	global $config;
	$sphere = new co_sphere_row('disks_zfs_dataset_edit','php');
	$sphere->set_row_identifier('uuid');
	$sphere->get_parent()->set_basename('disks_zfs_dataset','php');
	$sphere->set_notifier('zfsdataset');
	$sphere->row_default = [
		'name' => '',
		'pool' => '',
		'compression' => 'off',
		'dedup' => 'off',
		'sync' => 'standard',
		'atime' => 'off',
		'aclinherit' => 'restricted',
		'aclmode' => 'discard',
		'casesensitivity' => 'sensitive',
		'canmount' => true,
		'readonly' => false,
		'xattr' => true,
		'snapdir' => false,
		'quota' => '',
		'reservation' => '',
		'desc' => '',
		'accessrestrictions' => [
			'owner' => 'root',
			'group' => 'wheel',
			'mode' => '0777'
		]
	];
	$sphere->grid = &array_make_branch($config,'zfs','datasets','dataset');
	if(!empty($sphere->grid)):
		array_sort_key($sphere->grid,'name');
	endif;
	return $sphere;
}
$sphere = &disks_zfs_dataset_edit_get_sphere();
$input_errors = [];
$prerequisites_ok = true;
//	determine page mode
if(false !== ($action = filter_input(INPUT_POST,'submit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]))):
	switch($action):
		case 'cancel':
			header($sphere->get_parent()->get_location());
			exit;
			break;
		case 'clone':
			$id = uuid();
			$sphere->row[$sphere->get_row_identifier()] = $id;
			$mode_page = PAGE_MODE_CLONE;
			break;
		case 'save':
			$id = filter_input(INPUT_POST,$sphere->get_row_identifier(),FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
			if(false === $id):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			if(!is_uuid_v4($id)):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			$sphere->row[$sphere->get_row_identifier()] = $id;
			$mode_page = PAGE_MODE_POST;
			break;
		default:
			header($sphere->get_parent()->get_location());
			exit;
			break;
	endswitch;
elseif(false !== ($action = filter_input(INPUT_GET,'submit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]))):
	switch($action):
		case 'add':
			$sphere->row[$sphere->get_row_identifier()] = uuid();
			$mode_page = PAGE_MODE_ADD;
			break;
		case 'edit':
			$id = filter_input(INPUT_GET,$sphere->get_row_identifier(),FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
			if(false === $id):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			if(!is_uuid_v4($id)):
				header($sphere->get_parent()->get_location());
				exit;
			endif;
			$sphere->row[$sphere->get_row_identifier()] = $id;
			$mode_page = PAGE_MODE_EDIT;
			break;
		default:
			header($sphere->get_parent()->get_location());
			exit;
			break;
	endswitch;
else:
	header($sphere->get_parent()->get_location());
	exit;
endif;
$a_volume = &array_make_branch($config,'zfs','volumes','volume');
if(empty($a_volume)):
else:
	array_sort_key($a_volume,'name');
endif;
$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)):
	$errormsg = gtext('No configured pools.') . ' ' . '<a href="' . 'disks_zfs_zpool.php' . '">' . gtext('Please add a pool first.') . '</a>';
	$prerequisites_ok = false;
else:
	array_sort_key($a_pool,'name');
endif;
$sphere->row_id = array_search_ex($sphere->row[$sphere->get_row_identifier()],$sphere->grid,$sphere->get_row_identifier());
//	determine record update mode
$mode_updatenotify = updatenotify_get_mode($sphere->get_notifier(),$sphere->row[$sphere->get_row_identifier()]); // get updatenotify mode
$mode_record = RECORD_ERROR;
if(false === $sphere->row_id): // record does not exist in config
	if((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page || (PAGE_MODE_CLONE == $mode_page))): // POST, ADD or CLONE
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		endswitch;
	endif;
else: // record found in config
	if((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))): // POST or EDIT
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		endswitch;
	endif;
endif;
if(RECORD_ERROR == $mode_record): // oops, someone tries to cheat, over and out
	header($sphere->get_parent()->get_location());
	exit;
endif;
$isrecordnew = (RECORD_NEW === $mode_record);
$isrecordnewmodify = (RECORD_NEW_MODIFY === $mode_record);
$isrecordmodify = (RECORD_MODIFY === $mode_record);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
switch($mode_page):
	case PAGE_MODE_ADD:
		$sphere->row['name'] = $sphere->row_default['name'];
		$sphere->row['pool'] = $sphere->row_default['pool'];
		$sphere->row['compression'] = $sphere->row_default['compression'];
		$sphere->row['dedup'] = $sphere->row_default['dedup'];
		$sphere->row['sync'] = $sphere->row_default['sync'];
		$sphere->row['atime'] = $sphere->row_default['atime'];
		$sphere->row['aclinherit'] = $sphere->row_default['aclinherit'];
		$sphere->row['aclmode'] = $sphere->row_default['aclmode'];
		$sphere->row['casesensitivity'] = $sphere->row_default['casesensitivity'];
		$sphere->row['canmount'] = $sphere->row_default['canmount'];
		$sphere->row['readonly'] = $sphere->row_default['readonly'];
		$sphere->row['xattr'] = $sphere->row_default['xattr'];
		$sphere->row['snapdir'] = $sphere->row_default['snapdir'];
		$sphere->row['quota'] = $sphere->row_default['quota'];
		$sphere->row['reservation'] = $sphere->row_default['reservation'];
		$sphere->row['desc'] = $sphere->row_default['desc'];
		$sphere->row['accessrestrictions'] = $sphere->row_default['accessrestrictions'];
		break;
	case PAGE_MODE_CLONE:
		$sphere->row['name'] = filter_input(INPUT_POST,'name',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['name']]]);
		$sphere->row['pool'] = filter_input(INPUT_POST,'pool',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['pool']]]);
		$sphere->row['compression'] = filter_input(INPUT_POST,'compression',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['compression']]]);
		$sphere->row['dedup'] = filter_input(INPUT_POST,'dedup',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['dedup']]]);
		$sphere->row['sync'] = filter_input(INPUT_POST,'sync',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['sync']]]);
		$sphere->row['atime'] = filter_input(INPUT_POST,'atime',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['atime']]]);
		$sphere->row['aclinherit'] = filter_input(INPUT_POST,'aclinherit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['aclinherit']]]);
		$sphere->row['aclmode'] = filter_input(INPUT_POST,'aclmode',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['aclmode']]]);
		$sphere->row['casesensitivity'] = filter_input(INPUT_POST,'casesensitivity',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		//	get casesensitivity from physical dataset when no POST information was found
		if(false === $sphere->row['casesensitivity']):
			$cmd = sprintf('zfs get -Hp -o value casesensitivity %s',escapeshellarg(sprintf('%s/%s',$sphere->row['pool'],$sphere->row['name'])));
			unset($retdat);
			unset($retval);
			mwexec2($cmd,$retdat,$retval);
			switch($retval):
//				case 1: // An error occured.
//					$helpinghand = 'sensitive';
//					break;
				case 0: // Successful completion.
					$helpinghand = $retdat[0];
					break;
//				case 2: // Invalid command line options were specified.
//					$helpinghand = 'sensitive';
//					break;
				default:
					$helpinghand = 'sensitive';
					break;
			endswitch;
			$sphere->row['casesensitivity'] = $helpinghand;
		endif;
		$sphere->row['canmount'] = filter_input(INPUT_POST,'canmount',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['readonly'] = filter_input(INPUT_POST,'readonly',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['xattr'] = filter_input(INPUT_POST,'xattr',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['snapdir'] = filter_input(INPUT_POST,'snapdir',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['quota'] = filter_input(INPUT_POST,'quota',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['quota']]]);
		$sphere->row['reservation'] = filter_input(INPUT_POST,'reservation',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['reservation']]]);
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['desc']]]);
		$sphere->row['accessrestrictions']['owner'] = filter_input(INPUT_POST,'owner',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['accessrestrictions']['owner']]]);
		$sphere->row['accessrestrictions']['group'] = filter_input(INPUT_POST,'group',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => $sphere->row_default['accessrestrictions']['group']]]);
		//	calculate access mode from POST
		$helpinghand = 0;
		if(isset($_POST['mode_access']) && is_array($_POST['mode_access']) && (count($_POST['mode_access']) < 10)):
			foreach($_POST['mode_access'] as $r_mode_access):
				$helpinghand |= (257 > $r_mode_access) ? $r_mode_access : 0;
			endforeach;
		endif;
		$sphere->row['accessrestrictions']['mode'] = sprintf( "%04o",$helpinghand);
		//	adjust page mode
		$mode_page = PAGE_MODE_ADD;
		break;
	case PAGE_MODE_EDIT:
		$sphere->row['name'] = $sphere->grid[$sphere->row_id]['name'];
		$sphere->row['pool'] = $sphere->grid[$sphere->row_id]['pool'][0];
		$sphere->row['compression'] = $sphere->grid[$sphere->row_id]['compression'];
		$sphere->row['dedup'] = $sphere->grid[$sphere->row_id]['dedup'];
		$sphere->row['sync'] = $sphere->grid[$sphere->row_id]['sync'];
		$sphere->row['atime'] = $sphere->grid[$sphere->row_id]['atime'];	
		$sphere->row['aclinherit'] = $sphere->grid[$sphere->row_id]['aclinherit'];
		$sphere->row['aclmode'] = $sphere->grid[$sphere->row_id]['aclmode'];
		$sphere->row['canmount'] = isset($sphere->grid[$sphere->row_id]['canmount']);
		$sphere->row['readonly'] = isset($sphere->grid[$sphere->row_id]['readonly']);
		$sphere->row['xattr'] = isset($sphere->grid[$sphere->row_id]['xattr']);
		$sphere->row['snapdir'] = isset($sphere->grid[$sphere->row_id]['snapdir']);
		$sphere->row['quota'] = $sphere->grid[$sphere->row_id]['quota'];
		$sphere->row['reservation'] = $sphere->grid[$sphere->row_id]['reservation'];
		$sphere->row['desc'] = $sphere->grid[$sphere->row_id]['desc'];
		$sphere->row['accessrestrictions']['owner'] = $sphere->grid[$sphere->row_id]['accessrestrictions']['owner'];
		$sphere->row['accessrestrictions']['group'] = $sphere->grid[$sphere->row_id]['accessrestrictions']['group'][0];
		$sphere->row['accessrestrictions']['mode'] = $sphere->grid[$sphere->row_id]['accessrestrictions']['mode'];
		switch($mode_record):
			case RECORD_NEW_MODIFY:
				$sphere->row['casesensitivity'] = $sphere->grid[$sphere->row_id]['casesensitivity'];
				break;
		endswitch;
		break;
	case PAGE_MODE_POST:
		$sphere->row['compression'] = filter_input(INPUT_POST,'compression',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['dedup'] = filter_input(INPUT_POST,'dedup',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['sync'] = filter_input(INPUT_POST,'sync',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['atime'] = filter_input(INPUT_POST,'atime',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['aclinherit'] = filter_input(INPUT_POST,'aclinherit',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['aclmode'] = filter_input(INPUT_POST,'aclmode',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['canmount'] = filter_input(INPUT_POST,'canmount',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['readonly'] = filter_input(INPUT_POST,'readonly',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['xattr'] = filter_input(INPUT_POST,'xattr',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['snapdir'] = filter_input(INPUT_POST,'snapdir',FILTER_VALIDATE_BOOLEAN,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => false]]);
		$sphere->row['quota'] = filter_input(INPUT_POST,'quota',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['reservation'] = filter_input(INPUT_POST,'reservation',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['desc'] = filter_input(INPUT_POST,'desc',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['accessrestrictions']['owner'] = filter_input(INPUT_POST,'owner',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$sphere->row['accessrestrictions']['group'] = filter_input(INPUT_POST,'group',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
		$helpinghand = 0;
		if(isset($_POST['mode_access']) && is_array($_POST['mode_access']) && (count($_POST['mode_access']) < 10)):
			foreach($_POST['mode_access'] as $r_mode_access):
				$helpinghand |= (257 > $r_mode_access) ? $r_mode_access : 0;
			endforeach;
		endif;
		$sphere->row['accessrestrictions']['mode'] = sprintf( "%04o",$helpinghand);
		switch($mode_record):
			case RECORD_NEW:
			case RECORD_NEW_MODIFY:
				$sphere->row['name'] = filter_input(INPUT_POST,'name',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
				$sphere->row['pool'] = filter_input(INPUT_POST,'pool',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
				$sphere->row['casesensitivity'] = filter_input(INPUT_POST,'casesensitivity',FILTER_UNSAFE_RAW,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '']]);
				break;
			case RECORD_MODIFY:
				$sphere->row['name'] = $sphere->grid[$sphere->row_id]['name'];
				$sphere->row['pool'] = $sphere->grid[$sphere->row_id]['pool'][0];
				break;
		endswitch;
		//	Input validation
		$reqdfields = ['pool','name'];
		$reqdfieldsn = [gtext('Pool'),gtext('Name')];
		$reqdfieldst = ['string','string'];
		do_input_validation($sphere->row,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($sphere->row,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if($prerequisites_ok && empty($input_errors)): // check for a valid name with format name[/name], blanks are excluded.
			if(false === zfs_is_valid_dataset_name($sphere->row['name'])):
				$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."),gtext('Name'));
			endif;
		endif;
		//	1. RECORD_MODIFY: throw error if posted pool is different from configured pool.
		//	2. RECORD_NEW: posted pool/name must not exist in configuration or live.
		//	3. RECORD_NEW_MODIFY: if posted pool/name is different from configured pool/name: posted pool/name must not exist in configuration or live.
		//	4. RECORD_MODIFY: if posted name is different from configured name: pool/posted name must not exist in configuration or live.
		// 
		//	1.
		if($prerequisites_ok && empty($input_errors)):
			if($isrecordmodify && (0 !== strcmp($sphere->grid[$sphere->row_id]['pool'][0],$sphere->row['pool']))):
				$input_errors[] = gtext('Pool name cannot be modified.');
			endif;
		endif;
		//	2., 3., 4.
		if ($prerequisites_ok && empty($input_errors)):
			$poolslashname = escapeshellarg(sprintf('%s/%s',$sphere->row['pool'],$sphere->row['name'])); // create quoted full dataset name
			if($isrecordnew || (!$isrecordnew && (0 !== strcmp(escapeshellarg(sprintf('%s/%s',$sphere->grid[$sphere->row_id]['pool'][0],$sphere->grid[$sphere->row_id]['name'])),$poolslashname)))):
				//	throw error when pool/name already exists in live
				if(empty($input_errors)):
					$cmd = sprintf("zfs get -H -o value type %s 2>&1",$poolslashname);
					unset($retdat);
					unset($retval);
					mwexec2($cmd,$retdat,$retval);
					switch($retval):
						case 1: // An error occured. => zfs dataset doesn't exist
							break;
						case 0: // Successful completion. => zfs dataset found
							$input_errors[] = sprintf(gtext('%s already exists as a %s.'),$poolslashname,$retdat[0]);
							break;
						case 2: // Invalid command line options were specified.
							$input_errors[] = gtext('Failed to execute command zfs.');
							break;
					endswitch;
				endif;
				//	throw error when pool/name exists in configuration file, zfs->volumes->volume[]
				if(empty($input_errors)):
					foreach($a_volume as $r_volume):
						if (0 === strcmp(escapeshellarg(sprintf('%s/%s',$r_volume['pool'][0],$r_volume['name'])),$poolslashname)):
							$input_errors[] = sprintf(gtext('%s is already configured as a volume.'),$poolslashname);
							break;
						endif;
					endforeach;
				endif;
				//	throw error when  pool/name exists in configuration file, zfs->datasets->dataset[] 
				if(empty($input_errors)):
					foreach($sphere->grid as $r_dataset):
						if(0 === strcmp(escapeshellarg(sprintf('%s/%s',$r_dataset['pool'][0],$r_dataset['name'])),$poolslashname)):
							$input_errors[] = sprintf(gtext('%s is already configured as a filesystem.'),$poolslashname);
							break;
						endif;
					endforeach;
				endif;
			endif;
		endif;
		if($prerequisites_ok && empty($input_errors)):
			//	convert listtags to arrays
			$helpinghand = $sphere->row['pool'];
			$sphere->row['pool'] = [$helpinghand]; 
			$helpinghand = $sphere->row['accessrestrictions']['group'];
			$sphere->row['accessrestrictions']['group'] = [$helpinghand];
			if($isrecordnew):
				$sphere->grid[] = $sphere->row;
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_NEW,$sphere->row[$sphere->get_row_identifier()]);
			else:
				$sphere->grid[$sphere->row_id] = $sphere->row;
				//	avoid unnecessary notifications, avoid mode modify if mode new already exists
				if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
					updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->row[$sphere->get_row_identifier()]);
				endif;
			endif;
			write_config();
			header($sphere->get_parent()->get_location());
			exit;		
		endif;
		break;
endswitch;
$a_poollist = zfs_get_pool_list();
$l_poollist = [];
$use_si = is_sidisksizevalues();
foreach($a_pool as $r_pool):
	$r_poollist = $a_poollist[$r_pool['name']];
	$helpinghand = sprintf('%s: %s',$r_pool['name'],format_bytes($r_poollist['size'],2,false,$use_si));
	if(!empty($r_pool['desc'])):
		$helpinghand .= ' ' . $r_pool['desc'];
	endif;
	$l_poollist[$r_pool['name']] = $helpinghand;
endforeach;
$l_compressionmode = [
	'on' => gettext('On'),
	'off' => gettext('Off'),
	'lz4' => 'LZ4',
	'lzjb' => 'LZJB',
	'gzip' => 'GZIP',
	'gzip-1' => 'GZIP-1',
	'gzip-2' => 'GZIP-2',
	'gzip-3' => 'GZIP-3',
	'gzip-4' => 'GZIP-4',
	'gzip-5' => 'GZIP-5',
	'gzip-6' => 'GZIP-6',
	'gzip-7' => 'GZIP-7',
	'gzip-8' => 'GZIP-8',
	'gzip-9' => 'GZIP-9',
	'zle' => 'ZLE'
];
$l_dedup = [
	'on' => gettext('On'),
	'off' => gettext('Off'),
	'verify' => gettext('Verify'),
	'sha256' => 'SHA256',
	'sha256,verify' => gettext('SHA256, Verify')
];		
$l_sync = [
	'standard' => gettext('Standard'),
	'always' => gettext('Always'),
	'disabled' => gettext('Disabled')
];
$l_atime = [
	'on' => gettext('On'),
	'off' => gettext('Off')
];
$l_aclinherit = [
	'discard' => gettext('Discard - Do not inherit entries'),
	'noallow' => gettext('Noallow - Only inherit deny entries'),
	'restricted' => gettext('Restricted - Inherit all but "write ACL" and "change owner"'),
	'passthrough' => gettext('Passthrough - Inherit all entries'),
	'passthrough-x' => gettext('Passthrough-X - Inherit all but "execute" when not specified')
];
$l_aclmode = [
	'discard' => gettext('Discard - Discard ACL'),
	'groupmask' => gettext('Groupmask - Mask ACL with mode'),
	'passthrough' => gettext('Passthrough - Do not change ACL'),
	'restricted' => gettext('Restricted')
];
$l_casesensitivity = [
	'sensitive' => gettext('Sensitive'),
	'insensitive' => gettext('Insensitive'),
	'mixed' => gettext('Mixed')
];
$l_users = [];
foreach(system_get_user_list() as $r_key => $r_value):
	$l_users[$r_key] = $r_key;
endforeach;
$l_groups = [];
foreach(system_get_group_list() as $r_key => $r_value):
	$l_groups[$r_key] = $r_key;
endforeach;
// Calculate value of access right checkboxes, contains a) 0 for not checked or b) the required bit mask value
$mode_access = [];
$helpinghand = octdec($sphere->row['accessrestrictions']['mode']);
for($i = 0; $i < 9; $i++):
	$mode_access[$i] = $helpinghand & (1 << $i);
endfor;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Datasets'),gtext('Dataset'),$isrecordnew ? gtext('Add') : gtext('Edit')];
include 'fbegin.inc';
$sphere->doj();
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Dataset'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_dataset_info.php',gettext('Information'));
$document->render();
?>
<form action="<?=$sphere->get_scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('name',gettext('Name'),$sphere->row['name'],'',true,60,$isrecordmodify,false,128,gettext('Enter a name for this dataset'));
			if($isrecordmodify):
				html_inputbox2('pool',gettext('Pool'),$sphere->row['pool'],'',true,60,true);
			else:
				html_combobox2('pool',gettext('Pool'),$sphere->row['pool'],$l_poollist,'',true,$isrecordmodify);
			endif;
			html_combobox2('compression',gettext('Compression'),$sphere->row['compression'],$l_compressionmode,gettext("Controls the compression algorithm used for this dataset. 'LZ4' is now the recommended compression algorithm. Setting compression to 'On' uses the LZ4 compression algorithm if the feature flag lz4_compress is active, otherwise LZJB is used. You can specify the 'GZIP' level by using the value 'GZIP-N', where N is an integer from 1 (fastest) to 9 (best compression ratio). Currently, 'GZIP' is equivalent to 'GZIP-6'."),true);
			$helpinghand = '<div>' . gettext('Controls the dedup method.') . '</div>'
				. '<div><b>'
				. '<font color="red">' . gettext('WARNING') . '</font>' . ': '
				. '<a href="https://www.xigmanas.com/wiki/doku.php?id=documentation:setup_and_user_guide:disks_zfs_datasets_dataset" target="_blank">'
				. gettext('See ZFS datasets & deduplication wiki article BEFORE using this feature.')
				. '</a>'
				. '</b></div>';
			html_combobox2('dedup',gettext('Dedup'),$sphere->row['dedup'],$l_dedup,$helpinghand,true);
			html_radiobox2('sync',gettext('Sync'),$sphere->row['sync'],$l_sync,gettext('Controls the behavior of synchronous requests.'),true);
			html_combobox2('atime',gettext('Access Time (atime)'),$sphere->row['atime'],$l_atime,gettext('Controls whether the access time for files is updated when they are read. Turning this Off avoids producing write traffic when reading files and can result in significant performance gains.'),true);
			html_radiobox2('aclinherit',gettext('ACL inherit'),$sphere->row['aclinherit'],$l_aclinherit,gettext('This attribute determines the behavior of Access Control List inheritance.'),true);
			html_radiobox2('aclmode',gettext('ACL mode'),$sphere->row['aclmode'],$l_aclmode,gettext('This attribute controls the ACL behavior when a file is created or whenever the mode of a file or a directory is modified.'),true);
			if($isrecordnewornewmodify):
				html_radiobox2('casesensitivity',gettext('Case Sensitivity'),$sphere->row['casesensitivity'],$l_casesensitivity,gettext('This property indicates whether the file name matching algorithm used by the file system should be casesensitive, caseinsensitive, or allow a combination of both styles of matching'),false);
			endif;
			html_checkbox2('canmount',gettext('Canmount'),!empty($sphere->row['canmount']) ? true : false,gettext('If this property is disabled, the file system cannot be mounted.'),'',false);
			html_checkbox2('readonly',gettext('Readonly'),!empty($sphere->row['readonly']) ? true : false,gettext('Controls whether this dataset can be modified.'),'',false);
			html_checkbox2('xattr',gettext('Extended attributes'),!empty($sphere->row['xattr']) ? true : false,gettext('Enable extended attributes for this file system.'),'',false);
			html_checkbox2('snapdir',gettext('Snapshot Visibility'),!empty($sphere->row['snapdir']) ? true : false,gettext('If this property is enabled, the snapshots are displayed into .zfs directory.'),'',false);
			html_inputbox2('reservation',gettext('Reservation'),$sphere->row['reservation'],gettext("The minimum amount of space guaranteed to a dataset (usually empty). To specify the size use the following human-readable suffixes (for example, 'k', 'KB', 'M', 'Gb', etc.)."),false,10);
			html_inputbox2('quota',gettext('Quota'),$sphere->row['quota'],gettext("Limits the amount of space a dataset and its descendants can consume. This property enforces a hard limit on the amount of space used. This includes all space consumed by descendants, including file systems and snapshots. To specify the size use the following human-readable suffixes (for example, 'k', 'KB', 'M', 'Gb', etc.)."),false,10);
			html_inputbox2('desc',gettext('Description'),$sphere->row['desc'],gettext('You may enter a description here for your reference.'),false,40,false,false,40,gettext('Enter a description'));
			html_separator2();
			html_titleline2(gettext('Access Restrictions'));
			html_combobox2('owner',gettext('Owner'),$sphere->row['accessrestrictions']['owner'],$l_users,'',false);
			html_combobox2('group',gettext('Group'),$sphere->row['accessrestrictions']['group'],$l_groups,'',false);
?>
			<tr>
				<td class="celltag"><?=gtext('Mode');?></td>
				<td class="celldata">
					<table class="area_data_selection">
						<colgroup>
							<col style="width:25%">
							<col style="width:25%">
							<col style="width:25%">
							<col style="width:25%">
						</colgroup>
						<thead>
							<tr>
								<td class="lhell"><?=gtext('Who');?></td>
								<td class="lhelc"><?=gtext('Read');?></td>
								<td class="lhelc"><?=gtext('Write');?></td>
								<td class="lhebc"><?=gtext('Execute');?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="lcell"><?=gtext('Owner');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="owner_r" value="256" <?php if ($mode_access[8] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="owner_w" value="128" <?php if ($mode_access[7] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="owner_x" value= "64" <?php if ($mode_access[6] > 0) echo 'checked="checked"';?>/></td>
							</tr>
							<tr>
								<td class="lcell"><?=gtext('Group');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="group_r" value= "32" <?php if ($mode_access[5] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="group_w" value= "16" <?php if ($mode_access[4] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="group_x" value=  "8" <?php if ($mode_access[3] > 0) echo 'checked="checked"';?>/></td>
							</tr>
							<tr>
								<td class="lcell"><?=gtext('Others');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="other_r" value=  "4" <?php if ($mode_access[2] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="other_w" value=  "2" <?php if ($mode_access[1] > 0) echo 'checked="checked"';?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="other_x" value=  "1" <?php if ($mode_access[0] > 0) echo 'checked="checked"';?>/></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<div id="submit">
<?php
		echo $sphere->html_button('save',$isrecordnew ? gettext('Add') : gettext('Save'));
		if($prerequisites_ok && empty($input_errors) && !$isrecordnew):
			echo $sphere->html_button('clone',gettext('Clone Configuration'));
		endif;
		echo $sphere->html_button('cancel',gettext('Cancel'));
?>
		<input name="<?=$sphere->get_row_identifier();?>" type="hidden" value="<?=$sphere->row[$sphere->get_row_identifier()];?>"/>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
