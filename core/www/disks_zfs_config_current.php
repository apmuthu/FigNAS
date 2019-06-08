<?php
/*
	disks_zfs_config_current.php

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

array_make_branch($config,'zfs','pools','pool');
array_make_branch($config,'zfs','vdevices','vdevice');
array_make_branch($config,'zfs','datasets','dataset');
array_make_branch($config,'zfs','volumes','volume');
$zfs = $config['zfs'];
foreach($zfs['pools']['pool'] as $index => $pool):
	$zfs['pools']['pool'][$index]['size'] = gettext('Unknown');
	$zfs['pools']['pool'][$index]['used'] = gettext('Unknown');
	$zfs['pools']['pool'][$index]['avail'] = gettext('Unknown');
	$zfs['pools']['pool'][$index]['cap'] = gettext('Unknown');
	$zfs['pools']['pool'][$index]['health'] = gettext('Unknown');
	foreach($pool['vdevice'] as $vdevice):
		if(false === ($index = array_search_ex($vdevice,$zfs['vdevices']['vdevice'],'name'))):
			continue;
		endif;
		$zfs['vdevices']['vdevice'][$index]['pool'] = $pool['name'];
	endforeach;
endforeach;
unset($rawdata);
unset($retval);
$cmd = 'zfs list -pH -t filesystem -o name,used,available';
mwexec2($cmd,$rawdata,$retval);
if(0 == $retval):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		list($fname,$used,$avail) = explode("\t",$line);
		if(false !== ($index = array_search_ex($fname,$zfs['pools']['pool'],'name'))):
			if(strpos($fname,'/') === false): // zpool
				$zfs['pools']['pool'][$index]['used'] = format_bytes($used,2,false,is_sidisksizevalues());
				$zfs['pools']['pool'][$index]['avail'] = format_bytes($avail,2,false,is_sidisksizevalues());
			endif;
		endif;
	endforeach;
endif;
unset($rawdata);
unset($retval);
$cmd = 'zpool list -pH -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup';
mwexec2($cmd,$rawdata,$retval);
if(0 == $retval):
	foreach($rawdata as $line):
		if($line == 'no pools available'):
			continue;
		endif;
		list($pool,$root,$size,$alloc,$free,$cap,$expandsz,$frag,$health,$dedup) = explode("\t",$line);
		if(false === ($index = array_search_ex($pool,$zfs['pools']['pool'],'name'))):
			continue;
		endif;
		unset($row);
		$row = &$zfs['pools']['pool'][$index];
		if($root != '-'):
			$row['root'] = $root;
		endif;
		$row['size'] = format_bytes($size,2,false,is_sidisksizevalues());
		$row['alloc'] = format_bytes($alloc,2,false,is_sidisksizevalues());
		$row['free'] = format_bytes($free,2,false,is_sidisksizevalues());
		$row['expandsz'] = $expandsz;
		$row['frag'] = $frag;
		$row['cap'] = sprintf('%d%%',$cap);
		$row['health'] = $health;
		$row['dedup'] = $dedup;
	endforeach;
endif;
unset($rawdata);
unset($retval);
if(updatenotify_exists('zfs_import_config')):
	$notifications = updatenotify_get('zfs_import_config');
	$retval = 0;
	foreach($notifications as $notification):
		$retval |= !($notification['data'] == true);
	endforeach;
	$savemsg = get_std_save_message($retval);
	if($retval == 0):
		updatenotify_delete('zfs_import_config');
	endif;
endif;
$showusedavail = isset($config['zfs']['settings']['showusedavail']);
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Configuration'),gettext('Current')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_config_current.php',gettext('Current'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config.php',gettext('Detected'))->
			ins_tabnav_record('disks_zfs_config_sync.php',gettext('Synchronize'));
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->ins_info_box($savemsg);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','10%','9%','9%','9%','9%','9%','9%','10%','10%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Pools'),count($zfs['pools']['pool'])),$n_col_width);
$tr = $thead->addTR();
$tr->
	insTHwC('lhell',gettext('Name'))->
	insTHwC('lhell',gettext('Size'));
if($showusedavail):
	$tr->
		insTHwC('lhell',gettext('Used'))->
		insTHwC('lhell',gettext('Avail'));
else:
	$tr->
		insTHwC('lhell',gettext('Alloc'))->
		insTHwC('lhell',gettext('Free'));
endif;
$tr->
	insTHwC('lhell',gettext('Expandsz'))->
	insTHwC('lhell',gettext('Frag'))->
	insTHwC('lhell',gettext('Dedup'))->
	insTHwC('lhell',gettext('Health'))->
	insTHwC('lhell',gettext('Mount Point'))->
	insTHwC('lhebl',gettext('AltRoot'));
foreach($zfs['pools']['pool'] as $pool):
	$tr = $tbody->addTR();
	$tr->
		insTDwC('lcell',$pool['name'])->
		insTDwC('lcell',$pool['size']);
		if($showusedavail):
			$tr->
				insTDwC('lcell',$pool['used'])->
				insTDwC('lcell',$pool['avail']);
		else:
			$tr->
				insTDwC('lcell',$pool['alloc'])->
				insTDwC('lcell',$pool['free']);
		endif;
	$tr->
		insTDwC('lcell',$pool['expandsz'])->
		insTDwC('lcell',$pool['frag'])->
		insTDwC('lcell',$pool['dedup'])->
		insTDwC('lcell',$pool['health'])->
		insTDwC('lcell',empty($pool['mountpoint']) ? sprintf('/mnt/%s',$pool['name']) : $pool['mountpoint'])->
		insTDwC('lcebl',empty($pool['root']) ? '-' : $pool['root']);
endforeach;
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','21%','21%','42%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Virtual Devices'),count($zfs['vdevices']['vdevice'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Type'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhebl',gettext('Devices'));
foreach($zfs['vdevices']['vdevice'] as $vdevice):
	$tbody->
		addTR()->
			insTDwC('lcell',$vdevice['name'])->
			insTDwC('lcell',$vdevice['type'])->
			insTDwC('lcell',!empty($vdevice['pool']) ? $vdevice['pool'] : '')->
			insTDwC('lcebl',implode(',',$vdevice['device']));
endforeach;
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['14%','14%','7%','7%','9%','9%','9%','7%','8%','7%','9%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Datasets'),count($zfs['datasets']['dataset'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhell',gettext('Compression'))->
		insTHwC('lhell',gettext('Dedup'))->
		insTHwC('lhell',gettext('Sync'))->
		insTHwC('lhell',gettext('ACL Inherit'))->
		insTHwC('lhell',gettext('ACL Mode'))->
		insTHwC('lhell',gettext('Canmount'))->
		insTHwC('lhell',gettext('Quota'))->
		insTHwC('lhell',gettext('Readonly'))->
		insTHwC('lhebl',gettext('Snapshot Visibility'));
$tbody = $table->addTBODY();
foreach($zfs['datasets']['dataset'] as $dataset):
	$tbody->
		addTR()->
			insTDwC('lcell',$dataset['name'])->
			insTDwC('lcell',$dataset['pool'][0])->
			insTDwC('lcell',$dataset['compression'])->
			insTDwC('lcell',$dataset['dedup'])->
			insTDwC('lcell',$dataset['sync'])->
			insTDwC('lcell',$dataset['aclinherit'])->
			insTDwC('lcell',$dataset['aclmode'])->
			insTDwC('lcell',isset($dataset['canmount']) ? gettext('on') : gettext('off'))->
			insTDwC('lcell',empty($dataset['quota']) ? gettext('none') : $dataset['quota'])->
			insTDwC('lcell',isset($dataset['readonly']) ? gettext('on') : gettext('off'))->
			insTDwC('lcebl',isset($dataset['snapdir']) ? gettext('visible') : gettext('hidden'));
endforeach;
$tfoot = $table->addTFOOT();
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','12%','12%','12%','12%','12%','12%','12%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Volumes'),count($zfs['volumes']['volume'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhell',gettext('Size'))->
		insTHwC('lhell',gettext('Blocksize'))->
		insTHwC('lhell',gettext('Sparse'))->
		insTHwC('lhell',gettext('Compression'))->
		insTHwC('lhell',gettext('Dedup'))->
		insTHwC('lhebl',gettext('Sync'));
foreach($zfs['volumes']['volume'] as $volume):
	$tbody->
		addTR()->
			insTDwC('lcell',$volume['name'])->
			insTDwC('lcell',$volume['pool'][0])->
			insTDwC('lcell',$volume['volsize'])->
			insTDwC('lcell',!empty($volume['volblocksize']) ? $volume['volblocksize'] : '-')->
			insTDwC('lcell',!isset($volume['sparse']) ? '-' : gettext('on'))->
			insTDwC('lcell',$volume['compression'])->
			insTDwC('lcell',$volume['dedup'])->
			insTDwC('lcebl',$volume['sync']);
endforeach;
$content->
	add_area_remarks()->
		ins_remark('note',gettext('Note'),gettext('This page reflects the configuration that has been created with the WebGUI.'));
$document->render();
