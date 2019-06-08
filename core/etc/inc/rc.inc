<?php
/*
	rc.inc

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
require_once 'util.inc';

/**
 *	Updates /etc/rc.conf file.
 *	Check if KEYWORD 'RCVAR' is defined (e.g. '# RCVAR: xxxx').
 *	@param string $name The name of the service.
 *	@param string $state The state of the service (enable / disable).
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_update_rcconf($name,$state) {
	$retval = 0;
	$fullpathname = sprintf('/etc/rc.d/%s',$name);
	if(file_exists($fullpathname)):
		$data = @file_get_contents($fullpathname);
		$regexp = '/RCVAR: (.*)/';
		if(preg_match($regexp,$data,$result)):
			//	Update /etc/rc.conf
			$cmd = sprintf('/usr/local/sbin/rconf service %s %s',$state,$result[1]);
			$retval = mwexec($cmd);
		endif;
	endif;
	return $retval;
}
/**
 *	Checks if rcconf variable exists and is set to YES.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if service is enabled (default), otherwise 1.
 */
function rc_is_rcconf_enabled(string $name) {
	$cmd = sprintf('/usr/local/sbin/rconf service rcvar %s',escapeshellarg($name));
	return mwexec($cmd);
}
/**
 *	Checks if service is enabled.
 *	The KEYWORD 'XQUERY' is used to determine if service is enabled or not.
 *	@global array $g Global settings.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if service is enabled (default), otherwise 1.
 */
function rc_is_service_enabled($name) {
	global $g;
	
	$retval = 0;
	$fullpathname = sprintf('/etc/rc.d/%s',$name);
	if(file_exists($fullpathname)):
		$data = @file_get_contents($fullpathname);
		$regexp = '/XQUERY: (.*)/';
		if(preg_match($regexp,$data,$result)):
			//	Execute query
			$fullpathconfig = sprintf('%s/config.xml',$g['conf_path']);
			$cmd = sprintf('/usr/local/bin/xml sel -t %s %s',$result[1],$fullpathconfig);
			mwexec2($cmd,$output);
			if(!empty($output) && '0' === $output[0]):
			else:
				$retval = 1;
			endif;
		endif;
	endif;
	return $retval;
}
/**
 *	Execute rc script.
 *	@param string $scriptname The name of the script.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_exec_script($scriptname) {
	$cmd = sprintf('%s >/dev/null 2>&1 </dev/null',$scriptname);
	mwexec2($cmd,$output,$retval);
	return $retval;
}
/**
 *	Execute rc script asynchronuously.
 *	@param string $scriptname The name of the script.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_exec_script_async($scriptname) {
	$cmd = sprintf('nohup %s >/dev/null 2>&1 &',$scriptname);
	mwexec2($cmd,$output,$retval);
	return $retval;
}
/**
 *	Check if service is running.
 *	@param string $name The name of the service.
 *	@return int Returns 0 ifservice is running, otherwise 1.
 */
function rc_is_service_running($name) {
	$cmd = sprintf('/etc/rc.d/%s onestatus',$name);
	$retval = rc_exec_script($cmd);
	return $retval;
}
/**
 *	Execute a service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_exec_service($name) {
	$cmd = sprintf('/etc/rc.d/%s',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s service executed',$name));
	else:
		write_log(sprintf('Failed to execute service %s',$name));
	endif;
	return $retval;
}
/**
 *	Send reload command to a service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_reload_service($name) {
	$cmd = sprintf('/etc/rc.d/%s reload',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s service reloaded',$name));
	else:
		write_log(sprintf('Failed to reload service %s',$name));
	endif;
	return $retval;
}
/**
 *	Send rescan command to service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_rescan_service($name) {
	$cmd = sprintf('/etc/rc.d/%s rescan',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s: rescan command issued',$name));
	else:
		write_log(sprintf('%s: rescan command failed',$name));
	endif;
	return $retval;
}
/**
 *	Send restart command to a service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_restart_service($name) {
	$cmd = sprintf('/etc/rc.d/%s restart',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s service restarted',$name));
	else:
		write_log(sprintf('Failed to restart service %s',$name));
	endif;
	return $retval;
}
/**
 *	Send start command to a service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_start_service($name) {
	$cmd = sprintf('/etc/rc.d/%s start',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s service started',$name));
	else:
		write_log(sprintf('Failed to start service %s',$name));
	endif;
	return $retval;
}
/**
 *	Send stop command to service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_stop_service($name) {
	$cmd = sprintf('/etc/rc.d/%s stop',$name);
	$retval = rc_exec_script($cmd);
	if(0 == $retval):
		write_log(sprintf('%s service stopped',$name));
	else:
		write_log(sprintf('Failed to stop service %s',$name));
	endif;
	return $retval;
}
/**
 *	Update service - modify rc.conf and execute rc script.
 *	@param string $name The name of the service.
 *	@param bool $reload Flag to reload (true) the service instead of restarting (false) it. 
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_update_service_ex($name,bool $reload = false) {
	$retval = 0;
	//	Check if service is running
	$running = rc_is_service_running($name);
	//	Check if service is enabled
	$enabled = rc_is_service_enabled($name);
	//	Update rc.conf and execute rc script
	if(0 == $enabled):
		rc_update_rcconf($name,'enable');
		switch ($running):
			case 0:
				if($reload):
					$retval = rc_reload_service($name);
				else:
					$retval = rc_restart_service($name);
				endif;
				break;
			case 1:
				$retval = rc_start_service($name);
				break;
		endswitch;
	else:
		//	Stop service if necessary
		if(0 == $running):
			$retval = rc_stop_service($name);
		endif;
		rc_update_rcconf($name,'disable');
	endif;
	return $retval;
}
/**
 *	Update rc.conf and restart service.
 *	@param string $name The name of the service.
 *	@return int Returns 0 if successful, otherwise 1.
 */
function rc_update_service($name) {
	 return rc_update_service_ex($name,false);
}
/**
 *	Update rc.conf and reload service.
 *	@param	string $name The name of the service.
 *	@return	int Returns 0 if successful, otherwise 1.
 */
function rc_update_reload_service($name) {
	 return rc_update_service_ex($name,true);
}
/**
 *	Gets the value of an /etc/rc.conf environment variable.
 *	@param $varname The variable name.
 *	@return string Returns the value of the environment variable varname, or false on error.
 */
function rc_getenv($varname) {
	$data = @file_get_contents('/etc/rc.conf');
	$regexp = sprintf('/%s="(.*)"/',$varname);
	if(preg_match($regexp,$data,$result)):
		return $result[1];
	endif;
	return false;
}
/**
 *	Gets the value of an /etc/rc.conf environment variable. If the variable
 *	is not defined in rc.conf the default value will be used instead.
 *	@param[in] $varname	The variable name.
 *	@param string $default The default variable value.
 *	@return string Returns the value of the environment variable varname in rc.conf or the default value.
 */
function rc_getenv_ex($varname,$default) {
	if(false === ($retval = rc_getenv($varname))):
		return $default;
	endif;
	return $retval;
}
/**
 *	Restarts a service when it is enabled and running.
 *	@param string $name The name ofd the service to be restarted.
 *	@return int Returns the exit code of the restart command .
 */
function rc_restart_service_if_running_and_enabled(string $name) {
	$retval = 0;
	$regexp = '/^\S+$/';
	if(preg_match($regexp,$name)):
		//	process if service is enabled and running
		if(0 == rc_is_service_running($name) && 0 == rc_is_service_enabled($name)):
			$retval = rc_restart_service($name);
		endif;
	endif;
	return $retval;
}
/**
 *	Reloads a service when it is enabled and running.
 *	@param string $name The name ofd the service to be reloaded.
 *	@return int Returns the exit code of the reload command.
 */
function rc_reload_service_if_running_and_enabled(string $name) {
	$retval = 0;
	$regexp = '/^\S+$/';
	if(preg_match($regexp,$name)):
		//	process if service is enabled and running
		if(0 == rc_is_service_running($name) && 0 == rc_is_service_enabled($name)):
			$retval = rc_reload_service($name);
		endif;
	endif;
	return $retval;
}
