<?php
/*
	session.inc

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
class Session {
	/**
	 * Initialize default session variables.
	 */
	private static function _init() {
		session_regenerate_id(true);
		$_SESSION['login'] = true;
		$_SESSION['authtoken'] = password_hash(session_id(),PASSWORD_DEFAULT);
		$_SESSION['ts'] = time();
		$_SESSION['g'] = [
			'shrinkpageheader' => false,
			'headermenu' => []
		];
	}
	/**
	 * Start session.
	 */
	public static function start() {
		session_start();
		$_SESSION['at'] = time();
	}
	/**
	 * Destroy session.
	 */
	public static function destroy() {
		session_start();
		session_destroy();
	}
	/**
	 * Initialize administrator.
	 */
	public static function initAdmin() {
		Session::_init();
		$_SESSION['admin'] = true;
	}
	/**
	 * Initialize user.
	 * @param uid The user ID.
	 * @param uname The user name.
	 */
	public static function initUser($uid, $uname, $admin = false) {
		Session::_init();
		$_SESSION['uid'] = $uid;
		$_SESSION['uname'] = $uname;
		$_SESSION['admin'] = $admin;
	}
	/**
	 * Has the current user administration permissions?
	 * @return TRUE if the current user has administration permissions,
	 * otherwise false.
	 */
	public static function isAdmin() {
		if(!isset($_SESSION['admin']) || !$_SESSION['admin']):
			return false;
		endif;
		return true;
	}
	/**
	 * Is the login flag set?
	 */
	public static function isLogin() {
		if(!isset($_SESSION['login']) || !$_SESSION['login']):
			return false;
		endif;
	 	return $_SESSION['login'];
	}
	/**
	 * Validate the given token.
	 * @param authtoken The token to be validated.
	 * @return TRUE if the token is valid, otherwise FALSE.
	 */
	public static function isValidAuthToken($authtoken) {
		if(!isset($_SESSION['authtoken']) || !$_SESSION['authtoken']):
			return false;
		endif;
		return ($authtoken === $_SESSION['authtoken']);
	}
	/**
	 * Get the current authentication token.
	 * @return The current authentication token.
	 */
	public static function getAuthToken() {
		return $_SESSION['authtoken'];
	}
	/**
	 * Get the current user name.
	 * @return Returns the current user name, otherwise FALSE.
	 */
	public static function getUserName() {
		if(!isset($_SESSION['uname']) || !$_SESSION['uname']):
			return false;
		endif;
	 	return $_SESSION['uname'];
	}
	/**
	 * Get the current user ID.
	 * @return Returns the current user ID, otherwise FALSE.
	 */
	public static function getUserId() {
		if(!isset($_SESSION['uid']) || !$_SESSION['uid']):
			return false;
		endif;
	 	return $_SESSION['uid'];
	}
}
?>
