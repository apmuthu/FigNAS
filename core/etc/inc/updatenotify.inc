<?php
/*
	updatenotify.inc

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
require_once 'globals.inc';

// Update notification constants
define('UPDATENOTIFY_MODE_NEW',0);
define('UPDATENOTIFY_MODE_MODIFIED',1);
define('UPDATENOTIFY_MODE_DIRTY',2);
define('UPDATENOTIFY_MODE_DIRTY_CONFIG',22);
define('UPDATENOTIFY_MODE_UNKNOWN',3);

define('UPDATENOTIFY_DB',sprintf('%s/updatenotify.db',$g['vardb_path']));

// Config parameter constants to determine the mode of a parameter / array of parameters
define('RECORD_ERROR',0);
define('RECORD_NEW',1);
define('RECORD_NEW_MODIFY',12);
define('RECORD_NEW_DELETE',13);
define('RECORD_MODIFY',2);
define('RECORD_MODIFY_MODIFY',22);
define('RECORD_MODIFY_DELETE',23);
define('RECORD_DELETE',3);
define('RECORD_DELETE_MODIFY',32);
define('RECORD_DELETE_DELETE',33);

// Parameter to define the mode of a page
define('PAGE_MODE_VIEW',0);
define('PAGE_MODE_ADD',1);
define('PAGE_MODE_EDIT',2);
define('PAGE_MODE_POST',3);
define('PAGE_MODE_CLONE',4);

// Create database object and create tables if necessary.
try {
	$createdb = !file_exists(UPDATENOTIFY_DB);
	$dbh = new PDO('sqlite:'.UPDATENOTIFY_DB);
	if($createdb):
		updatenotify_initdb();
	endif;
} catch (PDOException $e) {
	print $e->getMessage();
}
/**
 *	Initialize the database by creating the tables.
 *	@global PDO $dbh
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_initdb() {
	global $dbh;

	try {
		$sql = "CREATE TABLE updatenotify (id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT, ts INTEGER, mode INTEGER, data TEXT, processor TEXT)";
		$dbh->exec($sql);
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return true;
}
/**
 *	Set a notification.
 *	@global PDO $dbh
 *	@param string $key Key to be used
 *	@param int $mode Mode to be set
 *	@param string $data Data to be set
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_set($key,$mode,$data,string $processor = '') {
	global $dbh;

	try {
		$sql = "INSERT INTO updatenotify (key, ts, mode, data, processor) VALUES (:key, :ts, :mode, :data, :processor)";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key,':ts' => time(),':mode' => $mode,':data' => $data,':processor' => $processor]);
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return true;
}
/**
 *	Get all notifications for the given key.
 *	@global PDO $dbh
 *	@param string $key Key to be processed
 *	@return mixed Return array of notifications, otherwise false.
 */
function updatenotify_get($key) {
	global $dbh;

	try {
		$sql = "SELECT mode, data, processor FROM updatenotify WHERE key = :key ORDER BY ts DESC";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key]);
		$result = [];
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
			$result[] = $row;
		endwhile;
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return $result;
}
/**
 *	Get all notifications.
 *	@global PDO $dbh
 *	@return array Return array of notifications.
 */
function updatenotify_get_all() {
	global $dbh;

	try {
		$sql = "SELECT id, key, ts, mode, data, processor FROM updatenotify";
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		unset($stmt);
	} catch (PDOException $e) {
		return [];
	}
	return $result;
}
/**
 *	Remove notifications with given key.
 *	@global PDO $dbh
 *	@param string $key Key to be removed.
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_delete($key) {
	global $dbh;

	try {
		$sql = "DELETE FROM updatenotify WHERE key = :key";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key]);
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return true;
}
/**
 *	Remove notification with given id.
 *	@global PDO $dbh
 *	@param string $id Id to be removed
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_delete_id($id) {
	global $dbh;

	try {
		$sql = "DELETE FROM updatenotify WHERE id = :id";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':id' => $id]);
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return true;
}
/**
 *	Clear the notification.
 *	@global PDO $dbh
 *	@param string $key File to be used.
 *	@param string $data Data to clear.
 *	@param boolean $matchwholeword
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_clear($key,$data,$matchwholeword = true) {
	global $dbh;

	try {
		if(true === $matchwholeword):
			$sql = "DELETE FROM updatenotify WHERE key = :key AND data = :data";
		else:
			$sql = "DELETE FROM updatenotify WHERE key = :key AND data LIKE '%:data%'";
		endif;
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key,':data' => $data]);
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return true;
}
/**
 *	Get the notification mode.
 *	@global PDO $dbh
 *	@param string $key File to be used.
 *	@param string $data Data to search for.
 *	@param boolean $matchwholeword
 *	@return int Returns the notification mode, otherwise UPDATENOTIFY_MODE_UNKNOWN
 */
function updatenotify_get_mode($key,$data,$matchwholeword = true) {
	global $dbh;

	try {
		if(true === $matchwholeword):
			$sql = "SELECT mode FROM updatenotify WHERE key = :key AND data = :data AND id IN (SELECT id FROM (SELECT id, MAX(ts) AS ts FROM updatenotify GROUP BY data))";
		else:
			$sql = "SELECT mode FROM updatenotify WHERE key = :key AND data LIKE '%:data%' AND id IN (SELECT id FROM (SELECT id, MAX(ts) AS ts FROM updatenotify GROUP BY data))";
		endif;
		// Remove duplicate entries. Last inserted notification wins (last in, first out).
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key,':data' => $data]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if(false !== $result):
			$result = $result['mode'];
		else:
			$result = UPDATENOTIFY_MODE_UNKNOWN;
		endif;
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return UPDATENOTIFY_MODE_UNKNOWN;
	}
	return $result;
}
/**
 *	Check if any notification is set for the given key.
 *	@global PDO $dbh
 *	@param string $key Key to be checked.
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_exists($key) {
	global $dbh;

	try {
		$sql = "SELECT COUNT(*) FROM updatenotify WHERE key = :key";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key]);
		$result = $stmt->fetchColumn();
		$result = (0 < $result) ? true : false;
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return $result;
}
/**
 *	Check if specified notification mode is set.
 *	@global PDO $dbh
 *	@param string $key Key to be checked.
 *	@param int $mode Mode to search for.
 *	@return boolean Return true if successful, otherwise false.
 */
function updatenotify_exists_mode($key,$mode) {
	global $dbh;

	try {
		// Remove duplicate entries. Last inserted notification wins (last in, first out).
		$sql = "SELECT COUNT(*) FROM updatenotify WHERE key = :key AND mode = :mode AND id IN (SELECT id FROM (SELECT id, MAX(ts) AS ts FROM updatenotify GROUP BY mode))";
		$stmt = $dbh->prepare($sql);
		$stmt->execute([':key' => $key,':mode' => $mode]);
		$result = $stmt->fetchColumn();
		$result = (0 < $result) ? true : false;
		$stmt = null;
	} catch (PDOException $e) {
		print $e->getMessage();
		return false;
	}
	return $result;
}
/**
 *	Process notifications.
 *	@param string $key Key to be processed.
 *	@param string $function Name of callback function. Function syntax: xyz($mode, $data).
 *	@return int Return 0 if successful, otherwise 1.
 */
function updatenotify_process($key,string $processor = '') {
	$a_notification = updatenotify_get($key);
	if(is_array($a_notification)):
		foreach($a_notification as $r_notification):
//			highest priority for processor set via updatenotify_set
			if(preg_match('/\S/',$r_notification['processor'])):
				$processor_function = $r_notification['processor'];
			elseif(preg_match('/\S/',$processor)):
				$processor_function = $processor;
			else:
				$processor_function = NULL;
			endif;
			if(isset($processor_function) && is_callable($processor_function)):
				if(0 != call_user_func($processor_function,$r_notification['mode'],$r_notification['data'])):
					return 1;
				endif;
			endif;
		endforeach;
	endif;
	return 0;
}
/**
 *	Send a delete message to updatenotify for a list of ID's sent via POST
 *	@param co_sphere $sphere
 *	@param co_property_container $cop
 *	@return boolean true indicates that the configuration has changed.
 */
function updatenotify_cbm_delete($sphere,$cop) {
	$update_config = false;
	$id_name = $sphere->get_row_identifier();
	$notifier_name = $sphere->get_notifier();
	if(method_exists($sphere,'get_notifier_processor')):
		$notifier_processor = $sphere->get_notifier_processor();
	else:
		$notifier_processor = '';
	endif;
	$sphere->cbm_grid = filter_input(INPUT_POST,$sphere->get_cbm_name(),FILTER_DEFAULT,['flags' => FILTER_REQUIRE_ARRAY,'options' => ['default' => []]]);
	foreach($sphere->cbm_grid as $sphere->cbm_row):
		$sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$id_name);
		if(false !== $sphere->row_id):
			if($sphere->is_lock_enabled() && method_exists($cop,'get_protected')):
				$test = $sphere->grid[$sphere->row_id][$cop->get_protected()->get_name()] ?? false;
				$is_protected = is_bool($test) ? $test : true;
			else:
				$is_protected = false;
			endif;
			if($is_protected):
			else:
				$mode_updatenotify = updatenotify_get_mode($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
				switch($mode_updatenotify):
					case UPDATENOTIFY_MODE_NEW:
						updatenotify_clear($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
						updatenotify_set($notifier_name,UPDATENOTIFY_MODE_DIRTY_CONFIG,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
						$update_config = true;
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						updatenotify_clear($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
						updatenotify_set($notifier_name,UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
						$update_config = true;
						break;
					case UPDATENOTIFY_MODE_UNKNOWN:
						updatenotify_set($notifier_name,UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
						$update_config = true;
						break;
				endswitch;
			endif;
		endif;
	endforeach;
	return $update_config;
}
/**
 *	Toggles the enable flag of a setting for a list of ID's sent via POST
 *	and sends a modified message to updatenotify.
 *	@param co_sphere $sphere
 *	@param co_property_container $cop
 *	@return boolean true indicates that the configuration has changed.
 */
function updatenotify_cbm_toggle($sphere,$cop) {
	$update_config = false;
	if(method_exists($cop,'get_enable')):
		$enable_name = $cop->get_enable()->get_name();
		$id_name = $sphere->get_row_identifier();
		$notifier_name = $sphere->get_notifier();
		if(method_exists($sphere,'get_notifier_processor')):
			$notifier_processor = $sphere->get_notifier_processor();
		else:
			$notifier_processor = '';
		endif;
		$sphere->cbm_grid = filter_input(INPUT_POST,$sphere->get_cbm_name(),FILTER_DEFAULT,['flags' => FILTER_REQUIRE_ARRAY,'options' => ['default' => []]]);
		foreach($sphere->cbm_grid as $sphere->cbm_row):
			$sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$id_name);
			if(false !== $sphere->row_id):
				$test = $sphere->grid[$sphere->row_id][$enable_name] ?? false;
				if(is_bool($test) ? $test : true):
					$sphere->grid[$sphere->row_id][$enable_name] = false;
					unset($sphere->grid[$sphere->row_id][$enable_name]);
				else:
					$sphere->grid[$sphere->row_id][$enable_name] = true;
				endif;
				$update_config = true;
				$mode_updatenotify = updatenotify_get_mode($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
				if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
					updatenotify_set($notifier_name,UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
				endif;
			endif;
		endforeach;
	endif;
	return $update_config;
}
/**
 *	Sets the enable flag of a setting to true for a list of ID's sent via POST
 *	and sends a modify message to updatenotify.
 *	@param co_sphere $sphere
 *	@param co_property_container $cop
 *	@return boolean true indicates that the configuration array was updated
 */
function updatenotify_cbm_enable($sphere,$cop) {
	$update_config = false;
	if(method_exists($cop,'get_enable')):
		$enable_name = $cop->get_enable()->get_name();
		$id_name = $sphere->get_row_identifier();
		$notifier_name = $sphere->get_notifier();
		if(method_exists($sphere,'get_notifier_processor')):
			$notifier_processor = $sphere->get_notifier_processor();
		else:
			$notifier_processor = '';
		endif;
		$sphere->cbm_grid = filter_input(INPUT_POST,$sphere->get_cbm_name(),FILTER_DEFAULT,['flags' => FILTER_REQUIRE_ARRAY,'options' => ['default' => []]]);
		foreach($sphere->cbm_grid as $sphere->cbm_row):
			$sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$id_name);
			if(false !== $sphere->row_id):
				$test = $sphere->grid[$sphere->row_id][$enable_name] ?? false;
				if(is_bool($test) ? $test : true):
				else:
					$sphere->grid[$sphere->row_id][$enable_name] = true;
					$update_config = true;
					$mode_updatenotify = updatenotify_get_mode($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
					if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
						updatenotify_set($notifier_name,UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
					endif;
				endif;
			endif;
		endforeach;
	endif;
	return $update_config;
}
/**
 *	Sets the enable flag of a setting to false for a list of ID's sent via POST
 *	and sends a modify message to updatenotify.
 *	@param co_sphere $sphere
 *	@param co_property_container $cop
 *	@return boolean true indicates that the configuration array was updated
 */
function updatenotify_cbm_disable($sphere,$cop) {
	$update_config = false;
	if(method_exists($cop,'get_enable')):
		$enable_name = $cop->get_enable()->get_name();
		$id_name = $sphere->get_row_identifier();
		$notifier_name = $sphere->get_notifier();
		if(method_exists($sphere,'get_notifier_processor')):
			$notifier_processor = $sphere->get_notifier_processor();
		else:
			$notifier_processor = '';
		endif;
		$notifier_processor = method_exists($sphere,'get_notifier_processor') ? $sphere->get_notifier_processor() : '';
		$sphere->cbm_grid = filter_input(INPUT_POST,$sphere->get_cbm_name(),FILTER_DEFAULT,['flags' => FILTER_REQUIRE_ARRAY,'options' => ['default' => []]]);
		foreach($sphere->cbm_grid as $sphere->cbm_row):
			$sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$id_name);
			if(false !== $sphere->row_id):
				$test = $sphere->grid[$sphere->row_id][$enable_name] ?? false;
				if(is_bool($test) ? $test : true):
					$sphere->grid[$sphere->row_id][$enable_name] = false;
					$update_config = true;
					$mode_updatenotify = updatenotify_get_mode($notifier_name,$sphere->grid[$sphere->row_id][$id_name]);
					if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
						updatenotify_set($notifier_name,UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$id_name],$notifier_processor);
					endif;
				endif;
			endif;
		endforeach;
	endif;
	return $update_config;
}
