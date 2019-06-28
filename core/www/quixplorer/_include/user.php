<?php
/*
	user.php

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
function _idx($what) {
	$idx = [
		'username' => 0,
		'password' => 1,
		'permissions' => 6,
		'useractive' => 7
	];
	return $idx[$what];
}
/**
 *	Loads the user database for authenticating the users
 *	@param string $file The name of the file containing the user database. Default is ./_config/.htusers.php
*/
function user_load($file = NULL) {
	if(!isset($file)):
		$file = './_config/.htusers.php';
	endif;
	if(!is_readable($file)):
		show_error("user database $file does not exist or is not readable.<p>See the installation manual for details");
	endif;
	require $file;
}
/**
 *	Write user configuration to .htusers.php
 *	@return boolean Return true if successful, otherwise false
 */
function _saveUsers() {
	$cnt = count($GLOBALS['users']);
	if($cnt > 1):
		sort($GLOBALS['users']);
	endif;
//	prepare file
	$content = ['<?php' . PHP_EOL];
	$content[] = '//	created by saveusers' . PHP_EOL;
	$content[] = '$GLOBALS["users"] = [];' . PHP_EOL;
	for($i = 0;$i < $cnt;++$i):
		$content[] = sprintf('$GLOBALS["%s"][] = [\'%s\',\'%s\',\'%s\',\'%s\',%u,\'%s\',%u,%u];' . PHP_EOL,
			'users',
			$GLOBALS['users'][$i][0],
			$GLOBALS['users'][$i][1],
			$GLOBALS['users'][$i][2],
			$GLOBALS['users'][$i][3],
			$GLOBALS['users'][$i][4],
			$GLOBALS['users'][$i][5],
			$GLOBALS['users'][$i][6],
			$GLOBALS['users'][$i][7]
		);
	endfor;
//	write to file
	if(false === file_put_contents('./_config/.htusers.php',$content)):
		return false;
	endif;
	return true;
}
/**
 *	Returns the index of the user in the user configuration
 *	@param string $user
 *	@return int Return the index of the user when found, otherwise -1
 */
function user_get_index($user) {
	if(!isset($GLOBALS['users'])):
		return -1;
	endif;
//	determine the number of registered users
	$cnt = count($GLOBALS['users']);
//	search for the user with the given user name in the user table
	$idx_username = _idx('username');
	for($ii = 0;$ii < $cnt;++$ii):
//		look for the next entry if the current user dont match the one we're looking for
		if($user != $GLOBALS['users'][$ii][$idx_username]):
			continue;
		endif;
//		return the index of the user
		return $ii;
	endfor;
//	return -1 if the user could not been found
	return -1;
}
/**
 *	Try to find the user with the username $user and the password $pass
 *	in the user table.
 *	If you provide NULL as password, no password and user active check
 *	is done. otherwise, this function returns the user, if $pass matches
 *	the user password and the user is active.
 *	If the user is inactive or the password mismatches, NULL is returned.
 *	@param string $user
 *	@param string $pass
 *	@return array
 */
function user_find($user,$pass = NULL) {
	$idx = user_get_index($user);
	if($idx < 0):
		return;
	endif;
//	if no password check should be done, return the user
	if(!isset($pass)):
		return $GLOBALS['users'][$idx];
	endif;
//	check if the password matches
	$userpw = $GLOBALS['users'][$idx][_idx('password')];
	if(!password_verify($pass,$userpw)):
		return;
	endif;
//	check if the user is active
	if(!$GLOBALS['users'][$idx][_idx('useractive')]):
		return;
	endif;
//	return the user if all checks are passed
	return $GLOBALS['users'][$idx];
}
/**
 *	Activate the user with the given user name and password.
 *	this function tries to find the user with the given user name and
 *	password in the user database and tries to activate this user.
 *	If username and password matches to the content of the
 *	user database, the user is activated, it's home directory,
 *	home url and permissions are set in the global variable and the
 *	function returns true.
 *	If the user cannot be authenticated, the function returns false.
 *	@param string $user User name of the user to be authenticated
 *	@param string $pass Password of the user to authenticate
 *	@return boolean
 */
function user_activate($user,$pass) {
//	try to find and authenticate the user.
	$data = user_find($user,$pass);
//	if the user could not be authenticated, return false.
	if(!isset($data)):
		return false;
	endif;
//	store the user data in the globals variable
	$_SESSION['s_user']	= $data[0];
//	$_SESSION['s_pass']	= $data[1];
	$_SESSION['s_pass']	= base64_encode($pass);
	$GLOBALS['home_dir']	= $data[2];
	$GLOBALS['home_url']	= $data[3];
	$GLOBALS['show_hidden']	= $data[4];
	$GLOBALS['no_access']	= $data[5];
//	return true on success.
	return true;
}
/**
 *	Updates the user data for the given user.
 *	@param string $user
 *	@param array $new_data
 *	@return type
 */
function user_update($user,$new_data) {
	$idx = user_get_index($user);
	if($idx < 0):
		return;
	endif;
	$data = $new_data;
	$GLOBALS['users'][$idx] = $new_data;
	return _saveUsers();
}
/**
 *	Adds a new user to the user database.
 *	@param array $data
 *	@return boolean
 */
function user_add($data) {
	if(user_find($data[0],NULL)):
		return false;
	endif;
	$GLOBALS['users'][] = $data;
	return _saveUsers();
}
/**
 *	Removes the user with the given user name from the user database.
 *	@param string $user
 *	@return boolean
 */
function user_remove($user) {
//	copy valid users
	$cnt = count($GLOBALS['users']);
	for($i = 0;$i < $cnt;++$i):
		if($GLOBALS['users'][$i][0] != $user):
			$save_users[] = $GLOBALS['users'][$i];
		endif;
	endfor;
	$GLOBALS['users'] = $save_users;
	return _saveUsers();
}
/**
 *	This function returns the permission values of the user with the given user name.
 *	if the user is not found in the user database, this function returns
 *	NULL, otherwise, it returns the permissions of the user.
 *	@param string $username
 *	@return int
 */
function user_get_permissions($username) {
//	try to find the user in the user database
	$data = user_find($username,NULL);
//	return NULL if the user does not exist
	if(!isset($data)):
		return;
	endif;
//	return the user permissions
	return $data[_idx('permissions')];
}
