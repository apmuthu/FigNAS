<?php
/*
	disks_zfs_dataset_info.php

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

function zfs_get_dataset_list(string $entity_name = NULL) {
	if(isset($entity_name)):
		$cmd = sprintf('zfs list -t filesystem -o name,used,avail,refer,mountpoint %s 2>&1',escapeshellarg($entity_name));
	else:
		$cmd = 'zfs list -t filesystem -o name,used,avail,refer,mountpoint 2>&1';
	endif;
	unset($output);
	mwexec2($cmd,$output);
	return implode(PHP_EOL,$output);
}
function zfs_get_dataset_properties(string $entity_name = NULL) {
	if(isset($entity_name)):
		$cmd = sprintf('zfs list -H -o name -t filesystem %s 2>&1',escapeshellarg($entity_name));
	else:
		$cmd = 'zfs list -H -o name -t filesystem 2>&1';
	endif;
	unset($a_names);
	mwexec2($cmd,$a_names);
	if(is_array($a_names) && count($a_names) > 0):
		$names = implode(' ',array_map('escapeshellarg',$a_names));
		$cmd = sprintf('zfs get all %s 2>&1',$names);
		unset($output);
		mwexec2($cmd,$output);
	else:
		$output = [gtext('No dataset information available.')];
	endif;
	return implode(PHP_EOL,$output);
}
$entity_name = NULL;
if(isset($_GET['uuid']) && is_string($_GET['uuid']) && is_uuid_v4($_GET['uuid'])):
	$sphere_array = &array_make_branch($config,'zfs','datasets','dataset');
	if(false !== ($sphere_rowid = array_search_ex($_GET['uuid'],$sphere_array,'uuid'))):
		$sphere_record = $sphere_array[$sphere_rowid];
		$sr_pool = $sphere_record['pool'][0] ?? NULL;
		$sr_name = $sphere_record['name'] ?? NULL;
		if(isset($sr_pool) && isset($sr_name) && is_string($sr_pool) && is_string($sr_name)):
			$entity_name = sprintf('%s/%s',$sr_pool,$sr_name);
		endif;
	endif;
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Datasets'),gtext('Information')];
if(isset($entity_name)):
	$pgtitle[] = htmlspecialchars($entity_name);
endif;
include 'fbegin.inc';
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
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Dataset'))->
			ins_tabnav_record('disks_zfs_dataset_info.php',gettext('Information'),gettext('Reload page'),true);
$document->render();
?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('ZFS Dataset Information & Status'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Information & Status');?></td>
				<td class="celldata">
					<pre><span id="zfs_get_dataset_list"><?=zfs_get_dataset_list($entity_name);?></span></pre>
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
			html_titleline2(gettext('ZFS Dataset Properties'));
?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Properties');?></td>
				<td class="celldata">
					<pre><span id="zfs_get_dataset_properties"><?=zfs_get_dataset_properties($entity_name);?></span></pre>
				</td>
			</tr>
		</tbody>
	</table>
</td></tr></tbody></table>
<?php
include 'fend.inc';
?>
