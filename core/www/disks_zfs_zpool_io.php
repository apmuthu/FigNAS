<?php
/*
	disks_zfs_zpool_io.php

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

function disks_zfs_zpool_io_ajax(bool $firstrun = false) {
	if($firstrun):
		//	calling zpool iostat with no configured pools returns ['no pools available'].
		if(isset($_GET['pool']) && is_string($_GET['pool'])):
			$cmd = sprintf('zpool iostat -v %s 2>&1',escapeshellarg($_GET['pool']));
		else:
			$cmd = 'zpool iostat -v 2>&1';
		endif;
		mwexec2($cmd,$rawdata);
		return implode(PHP_EOL,$rawdata);
	else:
		//	calling zpool iostat with no configured pools returns an empty array.
		if(isset($_GET['pool']) && is_string($_GET['pool'])):
			$cmd = sprintf('zpool iostat -v %s 5 2 2>&1',escapeshellarg($_GET['pool']));
		else:
			$cmd = 'zpool iostat -v 5 2 2>&1';
		endif;
		mwexec2($cmd,$rawdata);
		$divider = array_keys(preg_grep('/^\s*$/',$rawdata));
		if(count($divider) > 1):
			$n_high = array_pop($divider);
			$n_low = array_pop($divider);
			$returndata = array_slice($rawdata,$n_low - $n_high);
		else:
			$returndata = [gettext('no pools available')];
		endif;
		return implode(PHP_EOL,$returndata);
	endif;
}
if(is_ajax()):
	$status['area_refresh'] = disks_zfs_zpool_io_ajax();
	render_ajax($status);
endif;
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Pools'),gettext('I/O Statistics')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
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
			ins_tabnav_record('disks_zfs_zpool_info.php',gettext('Information'))->
			ins_tabnav_record('disks_zfs_zpool_io.php',gettext('I/O Statistics'),gettext('Reload page'),true);
$pagecontent->
	add_area_data()->
		add_table_data_settings()->
			push()->
			ins_colgroup_data_settings()->
			addTHEAD()->
				c2_titleline(gettext('ZFS Pool I/O Statistics'))->
			pop()->
			addTBODY()->
				addTR()->
					insTDwC('celltag',gettext('Information'))->
					addTDwC('celldata')->
						addElement('pre',['class' => 'cmdoutput'])->
							addElement('span',['id' => 'area_refresh'],disks_zfs_zpool_io_ajax(true));
//	add additional javascript code
$js_document_ready = <<<'EOJ'
	var gui = new GUI;
	gui.recall(0,7000,'disks_zfs_zpool_io.php',null,function(data) {
		if($('#area_refresh').length > 0) {
			$('#area_refresh').text(data.area_refresh);
		}
	});
EOJ;
$body->add_js_document_ready($js_document_ready);
$document->render();
