<?php
/*
	disks_zfs_zpool_info.php

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

function disks_zfs_zpool_info_ajax() {
	if(isset($_GET['pool']) && is_string($_GET['pool'])):
		$cmd = sprintf('zpool status -v -T d %s 2>&1',escapeshellarg($_GET['pool']));
	else:
		$cmd = 'zpool status -v -T d 2>&1';
	endif;
	unset($output);
	mwexec2($cmd,$output);
	$return = implode(PHP_EOL,$output);
	return $return;
}
function zfs_get_pool_list(string $entity_name = NULL) {
	if(isset($entity_name)):
		$cmd = sprintf('zpool list -o name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot %s 2>&1',escapeshellarg($entity_name));
	else:
		$cmd = 'zpool list -o name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot 2>&1';
	endif;
	unset($output);
	mwexec2($cmd,$output);
	$return_data = implode(PHP_EOL,$output);
	return $return_data;
}
function zfs_get_pool_properties(string $entity_name = NULL) {
	if(isset($entity_name)):
		$cmd = sprintf('zpool list -H -o name %s 2>&1',escapeshellarg($entity_name));
	else:
		$cmd = 'zpool list -H -o name 2>&1';
	endif;
	unset($a_names);
	mwexec2($cmd,$a_names);
	if(is_array($a_names) && count($a_names) > 0):
		$names = implode(' ',array_map('escapeshellarg',$a_names));
		$cmd = sprintf('zpool get all %s 2>&1',$names);
		unset($output);
		mwexec2($cmd,$output);
		$return_data = implode(PHP_EOL,$output);
	else:
		$output = [gtext('No volume information available.')];
		$return_data = implode(PHP_EOL,$output);
	endif;
	return $return_data;
}
$entity_name = NULL;
if(isset($_GET['uuid']) && is_string($_GET['uuid']) && is_uuid_v4($_GET['uuid'])):
	$sphere_array = &array_make_branch($config,'zfs','pools','pool');
	if(false !== ($sphere_rowid = array_search_ex($_GET['uuid'],$sphere_array,'uuid'))):
		$sphere_record = $sphere_array[$sphere_rowid];
		$sr_name = $sphere_record['name'] ?? NULL;
		if(isset($sr_name) && is_string($sr_name)):
			$entity_name = sprintf('%s',$sr_name);
		endif;
	endif;
endif;
$pgtitle = [gtext('Disks'), gtext('ZFS'), gtext('Pools'), gtext('Information')];
if(isset($entity_name)):
	$pgtitle[] = htmlspecialchars($entity_name);
endif;
if(!isset($entity_name)):
	if(is_ajax()):
		$status = disks_zfs_zpool_info_ajax();
		render_ajax($status);
	endif;
endif;
include 'fbegin.inc';
if(!isset($entity_name)):
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'disks_zfs_zpool_info.php', null, function(data) {
		$('#area_refresh').text(data.data);
	});
});
//]]>
</script>
<?php
endif;
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
			ins_tabnav_record('disks_zfs_zpool_tools.php',gettext('Tools'))->
			ins_tabnav_record('disks_zfs_zpool_info.php',gettext('Information'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_zpool_io.php',gettext('I/O Statistics'));
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
if(isset($entity_name)):
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Pool Information & Status'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Information');?></td>
				<td class="celldata">
					<pre><span id="zfs_pool_list"><?=htmlspecialchars(zfs_get_pool_list($entity_name),ENT_QUOTES | ENT_HTML5,NULL,false);?></span></pre>
				</td>
			</tr>
		</tbody>
		<tfoot>
<?php
			html_separator2();
?>
		</tfoot>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('ZFS Pool Properties'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Properties');?></td>
				<td class="celldata">
					<pre><span id="zfs_get_pool_properties"><?=htmlspecialchars(zfs_get_pool_properties($entity_name),ENT_QUOTES | ENT_HTML5,NULL,false);?></span></pre>
				</td>
			</tr>
		</tbody>
	</table>
<?php
else:
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Pool Information & Status'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Information');?></td>
				<td class="celldata">
					<pre><span id="area_refresh"><?=htmlspecialchars(disks_zfs_zpool_info_ajax(),ENT_QUOTES | ENT_HTML5,NULL,false);?></span></pre>
				</td>
			</tr>
		</tbody>
	</table>
<?php
endif;
?>
</td></tr></tbody></table>
<?php
include 'fend.inc';
