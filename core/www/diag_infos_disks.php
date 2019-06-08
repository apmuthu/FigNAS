<?php
/*
	diag_infos_disks.php

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

global $config_disks;
// Get all physical disks.
$a_phy_disk = array_merge((array)get_physical_disks_list());
$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('Disks')];
$a_colwidth = ['5%','12%','11%','8%','10%','7%','7%','8%','5%','14%','7%','6%'];
$n_colwidth = count($a_colwidth);
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_infos_disks.php',gettext('Disks'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_infos_disks_info.php',gettext('Disks (Info)'))->
			ins_tabnav_record('diag_infos_part.php',gettext('Partitions'))->
			ins_tabnav_record('diag_infos_smart.php',gettext('S.M.A.R.T.'))->
			ins_tabnav_record('diag_infos_space.php',gettext('Space Used'))->
			ins_tabnav_record('diag_infos_swap.php',gettext('Swap'))->
			ins_tabnav_record('diag_infos_mount.php',gettext('Mounts'))->
			ins_tabnav_record('diag_infos_raid.php',gettext('Software RAID'))->
			ins_tabnav_record('diag_infos_iscsi.php',gettext('iSCSI Initiator'))->
			ins_tabnav_record('diag_infos_ad.php',gettext('MS Domain'))->
			ins_tabnav_record('diag_infos_samba.php',gettext('CIFS/SMB'))->
			ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
			ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'))->
			ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
			ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
//	create data area
$content = $pagecontent->add_area_data();
$tbody = $content->
	add_table_data_selection()->
		ins_colgroup_with_styles('width',$a_colwidth)->
		push()->
		addTHEAD()->
			ins_titleline(gettext('Detected Disks'),$n_colwidth)->
			addTR()->
				insTHwC('lhell',gettext('Device'))->
				insTHwC('lhell',gettext('Device Model'))->
				insTHwC('lhell',gettext("Description"))->
				insTHwC('lhell',gettext('Size'))->
				insTHwC('lhell',gettext('Serial Number'))->
				insTHwC('lhell',gettext('Rotation Rate'))->
				insTHwC('lhell',gettext('Transfer Rate'))->
				insTHwC('lhell',gettext('S.M.A.R.T.'))->
				insTHwC('lhell',gettext('Controller'))->
				insTHwC('lhell',gettext('Controller Model'))->
				insTHwC('lhell',gettext('Temperature'))->
				insTHwC('lhebl',gettext('Status'))->
		pop()->
		addTBODY();
foreach($a_phy_disk as $disk):
	$disk['desc'] = $config_disks[$disk['devicespecialfile']]['desc'];
	$temperature = (false !== ($device_temperature = system_get_device_temp($disk['devicespecialfile']))) ? sprintf('%s °C',$device_temperature) : gettext('n/a');
	if($disk['type'] == 'HAST'):
		$role = $a_phy_disk[$disk['name']]['role'];
		$status = sprintf("%s (%s)", (0 == disks_exists($disk['devicespecialfile'])) ? gettext('ONLINE') : gettext('MISSING'),$role);
		$disk['size'] = $a_phy_disk[$disk['name']]['size'];
	else:
		$status = (0 == disks_exists($disk['devicespecialfile'])) ? gettext('ONLINE') : gettext('MISSING');
	endif;
	$matches = preg_split('/[\s\,]+/',$disk['smart']['smart_support']);
	$smartsupport = '';
	if(isset($matches[0])):
		if(0 == strcasecmp($matches[0],'available')):
			$smartsupport .= gettext('Available');
			if(isset($matches[1])):
				if(0 == strcasecmp($matches[1],'enabled')):
					$smartsupport .= (', ' . gettext('Enabled'));
				elseif(0 ==  strcasecmp($matches[1],'disabled')):
					$smartsupport .= (', ' . gettext('Disabled'));
				endif;
			endif;
		elseif(0 == strcasecmp($matches[0],'unavailable')):
			$smartsupport .= gettext('Unavailable');
		endif;
	endif;
	$tbody->
		addTR()->
			insTDwC('lcell',$disk['name'])->
			insTDwC('lcell',$disk['model'])->
			insTDwC('lcell',empty($disk['desc']) ?  gettext('n/a') : $disk['desc'])->
			insTDwC('lcell',$disk['size'])->
			insTDwC('lcell',empty($disk['serial']) ? gettext('n/a') : $disk['serial'])->
			insTDwC('lcell',empty($disk['rotation_rate']) ? gettext('Unknown') : $disk['rotation_rate'])->
			insTDwC('lcell',empty($disk['transfer_rate']) ? gettext('n/a') : $disk['transfer_rate'])->
			insTDwC('lcell',$smartsupport)->
			insTDwC('lcell',$disk['controller'] . $disk['controller_id'])->
			insTDwC('lcell',$disk['controller_desc'])->
			insTDwC('lcell',$temperature)->
			insTDwC('lcebld',$status);
endforeach;
$document->render();
