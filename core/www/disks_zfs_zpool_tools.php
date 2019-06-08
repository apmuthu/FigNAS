<?php
/*
	disks_zfs_zpool_tools.php

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
require_once 'co_zpool_info.inc';
require_once 'disks_zfs_zpool_tools_render.inc';
require_once 'co_geom_info.inc';

$b_test = false; // flag to force all options to show - remove after testing
$b_exec = true; // flag to indicate to execute a command or not - remove after testing

$sphere_scriptname = basename(__FILE__);
$sphere_array = [];
$prerequisites_ok = true;

$o_zpool = new co_zpool_info();
if(!$o_zpool->configuration_loaded()):
	header('Location: index.php');
	exit;
endif;
$a_pool = $o_zpool->get_all_pools();
$a_pool_for_attach_data = $o_zpool->get_pools_for_attach_data();
$a_pool_for_attach_log = $o_zpool->get_pools_for_attach_log();
$a_pool_for_detach_data = $o_zpool->get_pools_with_mirrored_data_devices();
$a_pool_for_detach_log = $o_zpool->get_pools_with_mirrored_log_devices();
$a_pool_for_offline_data = $o_zpool->get_pools_for_offline_data();
$a_pool_for_online_data = $o_zpool->get_pools_for_online_data();
$a_pool_for_remove_log = $o_zpool->get_pools_with_single_log_devices();
$a_pool_for_remove_cache = $o_zpool->get_pools_with_single_cache_devices();
$a_pool_for_remove_spare = $o_zpool->get_pools_with_single_spare_devices();
$a_pool_for_replace_data = $o_zpool->get_pools_for_replace_data();
// pools from config.xml, needed for add data
$a_cfg_pool = &array_make_branch($config,'zfs','pools','pool');
// vdevices from config.xml, needed for add data
$a_cfg_vdev = &array_make_branch($config,'zfs','vdevices','vdevice');
$b_vdev = !empty($a_cfg_vdev);
array_sort_key($a_cfg_vdev,'name');
$a_cfg_newvdev_data = [];
foreach($a_cfg_vdev as $r_cfg_vdev):
	if(false === array_search_ex($r_cfg_vdev['name'],$a_cfg_pool,'vdevice')): // vdevice not found in config pools
		switch($r_cfg_vdev['type']):
			case 'disk':
			case 'stripe':
			case 'mirror':
			case 'raidz1':
			case 'raidz2':
			case 'raidz3':
				$a_cfg_newvdev_data[] = $r_cfg_vdev;
			break;
		endswitch;
	endif;
endforeach;
$o_geom = new co_geom_info();
$a_geom_available_provider = $o_geom->get_available_provider();
$a_reserved_devices = [
	'ufs/embboot','gpt/gptboot','gpt/gptroot'
];
/*
 *	Eliminate devices
 */
$a_newdev = [];
foreach($a_geom_available_provider as $potential_device):
	if(false !== array_search($potential_device['name'],$a_reserved_devices)):
		//	skip reserved devices
		//	search provider id with name of reserved device
		//	get geom id from geom ref
		//	get consumer id -> provider ref from geom id	
	elseif(0 === ($potential_device['mediasize'] ?? 0)):
		//	skip read-only devices
	else:
		$a_newdev[] = $potential_device;
	endif;
endforeach;
array_sort_key($a_newdev,'name');
$b_pool = $b_test || (0 < count($a_pool));
$b_add_data	 = $b_test || ($b_pool && (0 < count($a_newdev)) && (0 < count($a_cfg_newvdev_data))); // a bit weak
$b_add_cache = $b_test || ($b_pool && (0 < count($a_newdev)));
$b_add_log = $b_test || ($b_pool && (0 < count($a_newdev)));
$b_add_spare = $b_test || ($b_pool && (0 < count($a_newdev)));
$b_attach_data = $b_test || ($b_pool && (0 < count($a_newdev)) && (0 < count($a_pool_for_attach_data)));
$b_attach_log = $b_test || ($b_pool && (0 < count($a_newdev)) && (0 < count($a_pool_for_attach_log)));
$b_detach_data = $b_test || ($b_pool && (0 < count($a_pool_for_detach_data)));
$b_detach_log = $b_test || ($b_pool && (0 < count($a_pool_for_detach_log)));
$b_offline_data = $b_test || ($b_pool && (0 < count($a_pool_for_offline_data)));
$b_online_data = $b_test || ($b_pool && (0 < count($a_pool_for_online_data)));
$b_remove_cache = $b_test || ($b_pool && (0 < count($a_pool_for_remove_cache)));
$b_remove_log = $b_test || ($b_pool && (0 < count($a_pool_for_remove_log)));
$b_remove_spare = $b_test || ($b_pool && (0 < count($a_pool_for_remove_spare)));
$b_replace_data = $b_test || ($b_pool && (0 < count($a_pool_for_replace_data))); // (0 < count($a_newdev)) && 
$l_command = [
	'add.data' => ['name' => 'activity','value' => 'add.data','show' => $b_add_data,'default' => false,'longname' => gettext('Add a virtual device to a pool')],
	'add.cache' => ['name' => 'activity','value' => 'add.cache','show' => $b_add_cache,'default' => false,'longname' => gettext('Add a cache device to a pool')],
	'add.log' => ['name' => 'activity','value' => 'add.log','show' => $b_add_log,'default' => false,'longname' => gettext('Add a log device to a pool')],
	'add.spare' => ['name' => 'activity','value' => 'add.spare','show' => $b_add_spare,'default' => false,'longname' => gettext('Add a spare device to a pool')],
	'attach.data' => ['name' => 'activity','value' => 'attach.data','show' => $b_attach_data,'default' => false,'longname' => gettext('Attach a data device')],
	'attach.log' => ['name' => 'activity','value' => 'attach.log','show' => $b_attach_log,'default' => false,'longname' => gettext('Attach a log device')],
	'clear' => ['name' => 'activity','value' => 'clear','show' => $b_pool,'default' => false,'longname' => gettext('Clear device errors')],
//	'create' => ['name' => 'activity','value' => 'create','show' => $b_pool && false,'default' => false,'longname' => gettext('Create a new storage pool')],
	'destroy' => ['name' => 'activity','value' => 'destroy','show' => $b_pool,'default' => false,'longname' => gettext('Destroy a pool')],
	'detach.data' => ['name' => 'activity','value' => 'detach.data','show' => $b_detach_data,'default' => false,'longname' => gettext('Detach a data device from a mirror')],
	'detach.log' => ['name' => 'activity','value' => 'detach.log','show' => $b_detach_log,'default' => false,'longname' => gettext('Detach a log device from a mirrored log')],
	'export' => ['name' => 'activity','value' => 'export','show' => $b_pool,'default' => false,'longname' => gettext('Export a pool from the system')],
//	'get' => ['name' => 'activity','value' => 'get','show' => $b_pool && false,'default' => false,'longname' => gettext('Get properties of a pool')],
	'history' => ['name' => 'activity','value' => 'history','show' => $b_pool,'default' => true,'longname' => gettext('Display ZFS command history')],
	'import' => ['name' => 'activity','value' => 'import','show' => true,'default' => false,'longname' => gettext('List or import pools')],
//	'iostat' => ['name' => 'activity','value' => 'iostat','show' => $b_pool && false,'default' => false,'longname' => gettext('Display I/O statistics')],
	'labelclear' => ['name' => 'activity','value' => 'labelclear','show' => true,'default' => false,'longname' => gettext('Remove ZFS label information from a device')],
//	'list' => ['name' => 'activity','value' => 'list','show' => $b_pool && false,'default' => false,'longname' => gettext('List the status of pools')],
	'offline' => ['name' => 'activity','value' => 'offline','show' => $b_offline_data,'default' => false,'longname' => gettext('Take a device offline')],
	'online' => ['name' => 'activity','value' => 'online','show' => $b_online_data,'default' => false,'longname' => gettext('Bring a device online')],
	'reguid' => ['name' => 'activity','value' => 'reguid','show' => $b_pool,'default' => false,'longname' => gettext('Generate a new unique identifier for a pool')],
	'remove.cache' => ['name' => 'activity','value' => 'remove.cache','show' => $b_remove_cache,'default' => false,'longname' => gettext('Remove a cache device from a pool')],
	'remove.log' => ['name' => 'activity','value' => 'remove.log','show' => $b_remove_log,'default' => false,'longname' => gettext('Remove a log device from a pool')],
	'remove.spare' => ['name' => 'activity','value' => 'remove.spare','show' => $b_remove_spare,'default' => false,'longname' => gettext('Remove a spare device from a pool')],
//	'reopen' => ['name' => 'activity','value' => 'reopen','show' => $b_pool && false,'default' => false,'longname' => gettext('Reopen all virtual devices of a pool')],
	'replace' => ['name' => 'activity','value' => 'replace','show' => $b_replace_data,'default' => false,'longname' => gettext('Replace a device')],
	'scrub' => ['name' => 'activity','value' => 'scrub','show' => $b_pool,'default' => false,'longname' => gettext('Scrub a pool')],
//	'set' => ['name' => 'activity','value' => 'set','show' => $b_pool && false,'default' => false,'longname' => gettext('Set property of a pool')],
//	'split' => ['name' => 'activity','value' => 'split','show' => $b_pool && false,'default' => false,'longname' => gettext('Split off a device from mirrored virtual devices')],
//	'status' => ['name' => 'activity','value' => 'status','show' => true && false,'default' => false,'longname' => gettext('Displays the health status of a pool')],
	'upgrade' => ['name' => 'activity','value' => 'upgrade','show' => $b_pool,'default' => false,'longname' => gettext('Upgrade ZFS and add all supported feature flags on a pool')]
];
$lcommand = array_sort_key($l_command,'longname');
$l_option = [
	'all' => ['name' => 'option','value' => 'all','show' => true,'default' => false,'longname' => gettext('All')],
	'd' => ['name' => 'option','value' => 'd','show' => true,'default' => false,'longname' => gettext('Device')],
	'pool' => ['name' => 'option','value' => 'pool','show' => true,'default' => false,'longname' => gettext('Pool')],
	't' => ['name' => 'option','value' => 't','show' => true,'default' => false,'longname' => gettext('Temporary Device')],
	'start' => ['name' => 'option','value' => 'start','show' => true,'default' => false,'longname' => gettext('Start')],
	'stop' => ['name' => 'option','value' => 'stop','show' => true,'default' => false,'longname' => gettext('Stop')],
	'view' => ['name' => 'option','value' => 'view','show' => true,'default' => false,'longname' => gettext('Display')],
	'force' => ['name' => 'flag','value' => 'force','show' => true,'default' => false,'longname' => gettext('Force Operation')],
	'test' => ['name' => 'flag','value' => 'test','show' => true,'default' => false,'longname' => gettext('Test Mode')]
];
$sphere_array['submit'] = false;
$sphere_array['pageindex'] = 1;
$sphere_array['activity'] = $l_command['history']['value'];
$sphere_array['option'] = '';
$sphere_array['flag'] = [];
$sphere_array['pool'] = [];
$sphere_array['pooldev'] = [];
$sphere_array['poolvdev'] = [];
$sphere_array['newdev'] = [];
$sphere_array['newvdev'] = [];

if(isset($_POST['submit']) && is_string($_POST['submit'])):
	$sphere_array['submit'] = true;
endif;
if(isset($_POST['pageindex']) && is_string($_POST['pageindex'])):
	$sphere_array['pageindex'] = $_POST['pageindex'];
endif;
if(isset($_POST['activity']) && is_array($_POST['activity'])):
	$sphere_array['activity'] = $_POST['activity'][0];
endif;
if(isset($_POST['option']) && is_array($_POST['option'])):
	$sphere_array['option'] = $_POST['option'][0];
endif;
if(isset($_POST['flag']) && is_array($_POST['flag'])):
	$sphere_array['flag'] = $_POST['flag'];
endif;
if(isset($_POST['pool']) && is_array($_POST['pool'])):
	$sphere_array['pool'] = $_POST['pool'];
endif;
if(isset($_POST['poolvdev']) && is_array($_POST['poolvdev'])):
	$sphere_array['poolvdev'] = $_POST['poolvdev'];
endif;
if(isset($_POST['pooldev']) && is_array($_POST['pooldev'])):
	$sphere_array['pooldev'] = $_POST['pooldev'];
endif;
if(isset($_POST['newdev']) && is_array($_POST['newdev'])):
	$sphere_array['newdev'] = $_POST['newdev'];
endif;
if(isset($_POST['newvdev']) && is_array($_POST['newvdev'])):
	$sphere_array['newvdev'] = $_POST['newvdev'];
endif;
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Pools'),gettext('Tools'),sprintf('%1$s %2$d',gettext('Step'),$sphere_array['pageindex'])];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
	// Init toggle checkbox
	$("#togglepool").click(function() { togglecheckboxesbyname(this, "pool[]"); });
	$("#togglepooldev").click(function() { togglecheckboxesbyname(this, "pooldev[]"); });
	$("#togglenewdev").click(function() { togglecheckboxesbyname(this, "newdev[]"); });
	$("#togglenewvdev").click(function() { togglecheckboxesbyname(this, "newvdev[]"); });
});
function togglecheckboxesbyname(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
			}
		}
	}
	if (ego.type == 'checkbox') { ego.checked = false; }
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_zpool_vdevice.php',gettext('Virtual Device'))->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Management'))->
			ins_tabnav_record('disks_zfs_zpool_tools.php',gettext('Tools'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_zpool_info.php',gettext('Information'))->
			ins_tabnav_record('disks_zfs_zpool_io.php',gettext('I/O Statistics'));
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform">
<?php
	if(1 < $sphere_array['pageindex']):
		if($sphere_array['submit']):
			if(isset($l_command[$sphere_array['activity']])):
				$c_activity = $l_command[$sphere_array['activity']]['longname'];
			else:
				$c_activity = gettext('Unknown Activity');
			endif;
			switch($sphere_array['activity']):
				default:
					$sphere_array['pageindex'] = 1;
					break;
				case 'add.cache': // add a device to a pool as a cache device
					$subcommand = 'add';
					$o_flags = new co_zpool_flags(['force','test'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // add cache: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1',$sphere_array['pool']); // 1 pool required
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // add cache: select geom device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							html_separator2(2);
							html_titleline2(gettext('Select Cache Device'),2);
							render_newdev_edit($a_newdev,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // add cache: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
										case 'test':
											$a_param[] = '-n';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = 'cache';
								foreach($sphere_array['newdev'] as $tmp_device):
									$a_param[] = escapeshellarg($tmp_device);
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'add.data': // enhance an existing pool with a predefined vdev (data)
					$subcommand = 'add';
					$o_flags = new co_zpool_flags(['force','test'],$sphere_array['flag']);
 					switch($sphere_array['pageindex']):
						case 2: // add data: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1',$sphere_array['pool']); // 1 pool required
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // add data: select new virtual device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							html_separator2(2);
							html_titleline2(gettext('Select Data Device'),2);
							render_newvdev_edit($a_cfg_newvdev_data,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // add data: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newvdev_view($sphere_array['newvdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							if($prerequisites_ok):
								if(false === ($index = array_search_ex($sphere_array['newvdev'][0],$a_cfg_newvdev_data,'name'))):
									$prerequisites_ok = false;
								endif;
							endif;
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
										case 'test':
											$a_param[] = '-n';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$tmp_virtual = $a_cfg_newvdev_data[$index];
								$tmp_devices = $tmp_virtual['device'];
								switch($tmp_virtual['type']):
									case 'disk':
									case 'stripe':
										break;
									default:
										$a_param[] = $tmp_virtual['type'];
										break;
								endswitch;
								foreach($tmp_devices as $tmp_device):
									$a_param[] = $tmp_device;
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'add.log': // add a device to a pool as a log device
					$subcommand = 'add';
					$o_flags = new co_zpool_flags(['force','test'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // add log: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1',$sphere_array['pool']); // 1 pool required
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // add log: select geom device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							html_separator2(2);
							html_titleline2(gettext('Select Log Device'),2);
							render_newdev_edit($a_newdev,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // add log: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
										case 'test':
											$a_param[] = '-n';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = 'log';
								foreach($sphere_array['newdev'] as $tmp_device):
									$a_param[] = escapeshellarg($tmp_device);
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'add.spare': // add a device to a pool as a spare device
					$subcommand = 'add';
					$o_flags = new co_zpool_flags(['force','test'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // add spare: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1',$sphere_array['pool']); // 1 pool required
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // add spare: select geom device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							html_separator2(2);
							html_titleline2(gettext('Select Spare Device'),2);
							render_newdev_edit($a_newdev,'1N');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // add spare: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
										case 'test':
											$a_param[] = '-n';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = 'spare';
								foreach($sphere_array['newdev'] as $tmp_device):
									$a_param[] = escapeshellarg($tmp_device);
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'attach.data': // parameter: force flag, non-raidz vdev, new device
					$subcommand = 'attach';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // attach data: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_attach_data,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // attach data: select pool device and new device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]); // limit next query to selected pool
							$a_device_for_attach_data = $o_zpool->get_pool_devices_for_attach_data();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_device_for_attach_data,'1',[],true);
							html_separator2(2);
							html_titleline2(gettext('Select Data Device'),2);
							render_newdev_edit($a_newdev,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // attach data: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]); // limit next query to selected pool
							$a_device_for_attach_data = $o_zpool->get_pool_devices_for_attach_data();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_device_for_attach_data);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$a_param[] = escapeshellarg($sphere_array['newdev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['keys']);
							break;
					endswitch;
					break;
				case 'attach.log': // force flag, non-raidz vdev, vdev, new device
					$subcommand = 'attach';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // attach log: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_attach_log,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // attach log: select device and new device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_device_for_attach_log = $o_zpool->get_pool_devices_for_attach_log();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_device_for_attach_log,'1');
							html_separator2(2);
							html_titleline2(gettext('Select Log Device'),2);
							render_newdev_edit($a_newdev,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // attach log: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]); // limit next query to selected pool
							$a_device_for_attach_log = $o_zpool->get_pool_devices_for_attach_log();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_device_for_attach_log);
							html_separator2(2);
							html_titleline2(gettext('Source'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$a_param[] = escapeshellarg($sphere_array['newdev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'clear':
					$subcommand = 'clear';
					switch($sphere_array['pageindex']):
						case 2: // clear: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1',$sphere_array['pool']); // 1 pool only
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // clear: select pool device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool Device'),2);
							render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_clear = $o_zpool->get_all_data_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_clear,'0');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4:
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_clear = $o_zpool->get_all_data_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_clear); // 0-N devices can be selected, no check for success
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								if(0 < count($sphere_array['pooldev'])):
									foreach($sphere_array['pooldev'] as $tmp_device):
										$a_param[] = escapeshellarg($tmp_device);
									endforeach;
								endif;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'destroy':
					$subcommand = 'destroy';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // destroy: select flags & pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1N',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // destroy: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								foreach($sphere_array['pool'] as $tmp_pool):
									$result = 0;
									$a_param = [];
									foreach($sphere_array['flag'] as $tmp_flag):
										switch($tmp_flag):
											case 'force':
												$a_param[] = '-f';
												break;
										endswitch;
									endforeach;
									$a_param[] = escapeshellarg($tmp_pool);
									$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
									render_command_result($result);
								endforeach;
							else:
								render_command_result($result);
							endif;
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'detach.data':
					$subcommand = 'detach';
					switch($sphere_array['pageindex']):
						case 2: // detach data: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_detach_data,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // detach data: select pool data device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Data Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_detach_data = $o_zpool->get_mirrored_data_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_detach_data,'1',[],true);
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // detach data page 4: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_detach_data = $o_zpool->get_mirrored_data_devices();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_detach_data);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'detach.log':
					$subcommand = 'detach';
					switch($sphere_array['pageindex']):
						case 2: // detach log: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_detach_log,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // detach log: select pool device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Log Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_detach_log = $o_zpool->get_mirrored_log_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_detach_log,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // detach log: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_detach_log = $o_zpool->get_mirrored_log_devices();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_detach_log);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'export': // parameter: force flag, pool
					$subcommand = 'export';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // export: select flags & pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pools'),2);
							render_pool_edit($a_pool,'1N',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // export: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								foreach($sphere_array['pool'] as $r_pool):
									$result = 0;
									$a_param = [];
									foreach($sphere_array['flag'] as $tmp_flag):
										switch($tmp_flag):
											case 'force':
												$a_param[] = '-f';
												break;
										endswitch;
									endforeach;
									$a_param[] = escapeshellarg($r_pool);
									$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
									render_command_result($result);
								endforeach;
							else:
								render_command_result($result);
							endif;
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'history':
					$subcommand = 'history';
					switch($sphere_array['pageindex']):
						case 2: // history: select pool(s)
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pools'),2);
							render_pool_edit($a_pool,'0N',$sphere_array['pool']); // 0..N = checkboxes
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // history: process
							if(0 === count($sphere_array['pool']) || (count($a_pool) === count($sphere_array['pool']))):
								render_set_start();
								render_activity_view($c_activity);
								$prerequisites_ok = true;
								html_separator2(2);
								html_titleline2(gettext('Output'),2);
								$result = $prerequisites_ok ? 0 : 15;
								if($prerequisites_ok):
									$a_param = [];
									$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
								endif;
								render_command_result($result);
								render_set_end();
								render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							else:
								$result = 0;
								render_set_start();
								render_activity_view($c_activity);
								$prerequisites_ok = true;
								html_separator2(2);
								html_titleline2(gettext('Output'),2);
								$result = $prerequisites_ok ? 0 : 15;
								if($prerequisites_ok):
									foreach($sphere_array['pool'] as $tmp_pool):
										$result = 0;
										$a_param = [];
										render_pool_view($tmp_pool);
										$a_param[] = escapeshellarg($tmp_pool);
										$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
										render_command_result($result);
									endforeach;
								else:
									render_command_result($result);
								endif;
								render_set_end();
								render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							endif;
							break;
					endswitch;
					break;
				case 'import':
					$subcommand = 'import';
					$o_flags = new co_zpool_flags(['sfaiapf','force','gptlabel','import.autoexpand','import.readonly','gptid'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // import page: get flags
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 3: // import page: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							$prerequisites_ok = true;
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
										case 'gptlabel':
											if(is_dir('/dev/gpt')):
												$a_param[] = '-d /dev/gpt';
											endif;
											break;
										case 'gptid':
											if(is_dir('/dev/gptid')):
												$a_param[] = '-d /dev/gptid';
											endif;
											break;
										case 'import.readonly':
											$a_param[]= '-o readonly=on';
											break;
										case 'import.autoexpand':
											$a_param[]= '-o autoexpand=on';
											break;
										case 'sfaiapf':
											$a_param[] = '-a';
											break;
									endswitch;
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'labelclear': // labelclear wipes zfs information from a disk
					$subcommand = 'labelclear';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // labelclear: select flags and device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Device'),2);
							render_newdev_edit($a_newdev,'1');
//							render_newdev_edit($o_zpool->get_all_devices(),'1');
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],$a_sphere['pool'],[]);
							break;
						case 3: // labelclear: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
									endswitch;
								endforeach;
								foreach($sphere_array['newdev'] as $tmp_device): // labelclear expects a full path
									if(preg_match('/^\//',$tmp_device)): // verify full path
										$a_param[] = escapeshellarg($tmp_device);
									else:
										$a_param[] = escapeshellarg(sprintf('/dev/%s',$tmp_device));
									endif;
								endforeach;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'offline': // mirror and raidz allowed
					$subcommand = 'offline';
					switch($sphere_array['pageindex']):
						case 2: // offline data: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_offline_data,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flags']);
							break;
						case 3: // offline data: select data device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_offline_data = $o_zpool->get_pool_devices_for_offline_data();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_offline_data,'1',[],true);
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // offline data: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_offline_data = $o_zpool->get_pool_devices_for_offline_data();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_offline_data);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'online':
					$subcommand = 'online';
					$o_flags = new co_zpool_flags(['expand'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // online data: select pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_online_data,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // online data: select data device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_online_data = $o_zpool->get_pool_devices_for_online_data();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_online_data,'1',[],true);
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // online data: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_online_data = $o_zpool->get_pool_devices_for_online_data();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_online_data);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'expand':
											$a_param[] = '-e';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'reguid':
					$subcommand = 'reguid';
					switch($sphere_array['pageindex']):
						case 2: // reguid page 2: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool,'1N',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3:
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								foreach($sphere_array['pool'] as $tmp_pool):
									$result = 0;
									$a_param = [];
									$a_param[] = escapeshellarg($tmp_pool);
									$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
									render_command_result($result);
								endforeach;
							else:
								render_command_result($result);
							endif;
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'remove.cache':
					$subcommand = 'remove';
					switch($sphere_array['pageindex']):
						case 2: // remove cache: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_remove_cache,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flags']);
							break;
						case 3: // remove cache: select cache device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Cache Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_cache = $o_zpool->get_single_cache_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_remove_cache,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // remove cache: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_cache = $o_zpool->get_single_cache_devices();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_remove_cache);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'remove.log':
					$subcommand = 'remove';
					switch($sphere_array['pageindex']):
						case 2: // remove log: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_remove_log,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // remove log: select log device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Log Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_log = $o_zpool->get_single_log_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_remove_log,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // remove log: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_log = $o_zpool->get_single_log_devices();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_remove_log);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
				case 'remove.spare':
					$subcommand = 'remove';
					switch($sphere_array['pageindex']):
						case 2: // remove spare: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_remove_spare,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // remove spare: select spare device
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Spare Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_spare = $o_zpool->get_single_spare_devices();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_pool_device_for_remove_spare,'1');
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						case 4: // remove spare: process
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]);
							$a_pool_device_for_remove_spare = $o_zpool->get_single_spare_devices();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_pool_device_for_remove_spare);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
						break;
					endswitch;
					break;
				case 'replace': // parameter: force flag, pool, redundant device, new device (optional)
					$subcommand = 'replace';
					$o_flags = new co_zpool_flags(['force'],$sphere_array['flag']);
					switch($sphere_array['pageindex']):
						case 2: // replace data: select flags and pool
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool'),2);
							render_pool_edit($a_pool_for_replace_data,'1',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],[]);
							break;
						case 3: // replace data: select pool device and new device
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_available_keys();
							html_separator2(2);
							html_titleline2(gettext('Select Pool Device'),2);
							render_pool_view($sphere_array['pool']);
							render_zpool_status($sphere_array['pool'][0],$b_exec);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]); // limit next query to selected pool
							$a_device_for_replace_data = $o_zpool->get_pool_devices_for_replace_data();
							$o_zpool->set_devicepath_strip_regex('~^/dev/~');
							$a_spare_devices = $o_zpool->get_all_spare_devices();
							$o_zpool->set_devicepath_strip_regex();
							$o_zpool->set_poolname_filter();
							render_pooldev_edit($a_device_for_replace_data,'1',[],true);
							//	add spare devices of selected pool
							if(!empty($a_spare_devices)):
								foreach($a_spare_devices as $r_spare_device):
									$o_geom->get_provider_by_name($a_newdev,$r_spare_device['device.path'] ?? NULL);
								endforeach;
							endif;
							if(!empty($a_newdev)): // new device is an optional parameter
								html_separator2(2);
								html_titleline2(gettext('Select Data Device'),2);
								render_newdev_edit($a_newdev,'0'); // not mandatory
							endif;
							render_set_end();
							render_submit(4,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],[]);
							break;
						case 4: // replace data: process
							render_set_start();
							render_activity_view($c_activity);
							$o_flags->render_selected_keys();
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							$prerequisites_ok = render_pool_view($sphere_array['pool']);
							$o_zpool->set_poolname_filter($sphere_array['pool'][0]); // limit next query to selected pool
							$a_device_for_replace_data = $o_zpool->get_pool_devices_for_replace_data();
							$o_zpool->set_poolname_filter();
							$prerequisites_ok &= render_pooldev_view($sphere_array['pooldev'],$a_device_for_replace_data);
							if(!empty($sphere_array['newdev'][0])): // display only if a new device has been selected
								html_separator2(2);
								html_titleline2(gettext('Source'),2);
								$prerequisites_ok &= render_newdev_view($sphere_array['newdev']);
							endif;
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								$a_param = [];
								foreach($sphere_array['flag'] as $tmp_flag):
									switch($tmp_flag):
										case 'force':
											$a_param[] = '-f';
											break;
									endswitch;
								endforeach;
								$a_param[] = escapeshellarg($sphere_array['pool'][0]);
								if(empty($sphere_array['newdev'][0])):
									//	no new device was selected, in-place replacement, replace dev with dev
									$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
								else:
									//	a new device was selected, use guid of pooldev instead of dev
									$a_param[] = escapeshellarg($sphere_array['pooldev'][0]);
									$a_param[] = escapeshellarg($sphere_array['newdev'][0]);
								endif;
								$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
							endif;
							render_command_result($result);
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['keys']);
							break;
					endswitch;
					break;
				case 'scrub':
					$subcommand = 'scrub';
					$result = 0;
					if($b_pool):
						switch($sphere_array['pageindex']):
							case 2: // scrub page 2: select option and pool
								$ll_option = [];
								$ll_option['start'] = $l_option['start']; 
								$ll_option['stop'] = $l_option['stop'];
								$ll_option['start']['default'] = true;
								render_set_start();
								render_activity_view($c_activity);
								render_selector_radio(gettext('Options'),$ll_option,$sphere_array['option']);
								html_separator2(2);
								html_titleline2(gettext('Select Pool'),2);
								render_pool_edit($a_pool,'1',$sphere_array['pool']);
								render_set_end();
								render_submit(3,$sphere_array['activity'],'',[],$sphere_array['flag']);
								break;
							case 3: // process
								switch($sphere_array['option']):
									case 'start':
										render_set_start();
										render_activity_view($c_activity);
										render_option_view($l_option[$sphere_array['option']]['longname']);
										html_separator2(2);
										html_titleline2(gettext('Target'),2);
										$prerequisites_ok = render_pool_view($sphere_array['pool']);
										html_separator2(2);
										html_titleline2(gettext('Output'),2);
										$result = $prerequisites_ok ? 0 : 15;
										if($prerequisites_ok):
											$a_param = [];
											$a_param[] = escapeshellarg($sphere_array['pool'][0]);
											$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
										endif;
										render_command_result($result);
										render_set_end();
										render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
										break;
									case 'stop':
										render_set_start();
										render_activity_view($c_activity);
										render_option_view($l_option[$sphere_array['option']]['longname']);
										html_separator2(2);
										html_titleline2(gettext('Target'),2);
										$prerequisites_ok = render_pool_view($sphere_array['pool']);
										html_separator2(2);
										html_titleline2(gettext('Output'),2);
										$result = $prerequisites_ok ? 0 : 15;
										if($prerequisites_ok):
											$a_param = [];
											$a_param[] = '-s';
											$a_param[] = escapeshellarg($sphere_array['pool'][0]);
											$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
										endif;
										render_command_result($result);
										render_set_end();
										render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
										break;
								endswitch;
								break;
						endswitch;
					else:
						render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool']);
					endif;
					break;
				case 'upgrade':
					$subcommand = 'upgrade';
					$result = 0;
					switch($sphere_array['pageindex']):
						case 2: // upgrade page 2: select pool
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Select Pools'),2);
							render_pool_edit($a_pool,'0N',$sphere_array['pool']);
							render_set_end();
							render_submit(3,$sphere_array['activity'],$sphere_array['option'],[],$sphere_array['flag']);
							break;
						case 3: // upgrade page 2: process
							$prerequisites_ok = true;
							render_set_start();
							render_activity_view($c_activity);
							html_separator2(2);
							html_titleline2(gettext('Target'),2);
							render_pool_view($sphere_array['pool']);
							html_separator2(2);
							html_titleline2(gettext('Output'),2);
							$result = $prerequisites_ok ? 0 : 15;
							if($prerequisites_ok):
								if(0 < count($sphere_array['pool'])): // Upgrade list of pools
									foreach($sphere_array['pool'] as $tmp_pool):
										$result = 0;
										$a_param = [];
										$a_param[] = escapeshellarg($tmp_pool);
										$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
										render_command_result($result);
									endforeach;
								else: // View feature flags
//									$result = 0;
									$a_param = [];
									$a_param[] = '-v';
									$result |= render_command_and_execute($subcommand,$a_param,$b_exec);
									render_command_result($result);
								endif;
							else:
								render_command_result($result);
							endif;
							render_set_end();
							render_submit(1,$sphere_array['activity'],$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
							break;
					endswitch;
					break;
			endswitch;
		endif;
	endif;
	if(1 == $sphere_array['pageindex']):
		render_set_start();
		render_selector_radio(gettext('Activities'),$l_command,$sphere_array['activity']);
		render_set_end();
		render_submit(2,'',$sphere_array['option'],$sphere_array['pool'],$sphere_array['flag']);
	endif;
	include 'formend.inc';
?>
</form></td></tr></tbody></table>
<?php
include 'fend.inc';
