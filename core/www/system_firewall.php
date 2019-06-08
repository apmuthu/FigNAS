<?php
/*
	system_firewall.php

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

$pconfig['enable'] = isset($config['system']['firewall']['enable']);
if(isset($_POST['export']) && $_POST['export']):
	$doc = new DOMDocument('1.0', 'UTF-8');
	$doc->formatOutput = true;
	$elm = $doc->createElement(get_product_name());
	$elm->setAttribute('version', get_product_version());
	$elm->setAttribute('revision', get_product_revision());
	$node = $doc->appendChild($elm);

	// export as XML
	array_sort_key($config['system']['firewall']['rule'],'ruleno');
	foreach($config['system']['firewall']['rule'] as $k => $v):
		$elm = $doc->createElement('rule');
		foreach($v as $k2 => $v2):
			$elm2 = $doc->createElement($k2, $v2);
			$elm->appendChild($elm2);
		endforeach;
		$node->appendChild($elm);
	endforeach;
	$xml = $doc->saveXML();
	if($xml === false):
		$errormsg = gtext('Invalid file format.');
	else:
		$fn = sprintf('firewall-%1$s.%2$s-%3$s.rules',$config['system']['hostname'],$config['system']['domain'],date('YmdHis'));
		$data = $xml;
		header('Content-Type: application/octet-stream');
		header(sprintf('Content-Disposition: attachment; filename="%s"',$fn));
		header(sprintf('Content-Length: %d',strlen($data)));
		header('Pragma: hack');
		echo $data;
		ob_flush(); 
		flush(); 
		exit;
	endif;
elseif(isset($_POST['import']) && $_POST['import']):
	if(is_uploaded_file($_FILES['rulesfile']['tmp_name'])):
		// import from XML
		$xml = file_get_contents($_FILES['rulesfile']['tmp_name']);
		$doc = new DOMDocument();
		$data = [];
		$data['rule'] = [];
		if($doc->loadXML($xml) != FALSE):
			$doc->normalizeDocument();
			$rules = $doc->getElementsByTagName('rule');
			foreach($rules as $rule):
				$a = [];
				foreach($rule->childNodes as $node):
					if($node->nodeType != XML_ELEMENT_NODE):
						continue;
					endif;
					$name = !empty($node->nodeName) ? (string)$node->nodeName : '';
					$value = !empty($node->nodeValue) ? (string)$node->nodeValue : '';
					if(!empty($name)):
						$a[$name] = $value;
					endif;
				endforeach;
				$data['rule'][] = $a;
			endforeach;
		endif;
		if(empty($data['rule'])):
			$errormsg = gtext('Invalid file format.');
		else:
			// Take care array already exists.
			array_make_branch($config,'system','firewall','rule');
			// Import rules.
			foreach ($data['rule'] as $rule):
				// Check if rule already exists.
				$index = array_search_ex($rule['uuid'], $config['system']['firewall']['rule'], "uuid");
				if(false !== $index):
					// Create new uuid and mark rule as duplicate (modify description).
					$rule['uuid'] = uuid();
					$rule['desc'] = gtext('*** Imported duplicate ***') . " {$rule['desc']}";
				endif;
				$config['system']['firewall']['rule'][] = $rule;
				updatenotify_set('firewall',UPDATENOTIFY_MODE_NEW,$rule['uuid']);
			endforeach;
			write_config();
			header('Location: system_firewall.php');
			exit;
		endif;
	else:
		$errormsg = sprintf('%s %s',gtext('Failed to upload file.'),$g_file_upload_error[$_FILES['rulesfile']['error']]);
	endif;
elseif($_POST):
	$pconfig = $_POST;
	$config['system']['firewall']['enable'] = isset($_POST['enable']) ? true : false;
	write_config();
	$retval = 0;
	if(!file_exists($d_sysrebootreqd_path)):
		$retval |= updatenotify_process('firewall','firewall_process_updatenotification');
		config_lock();
		$retval |= rc_update_service('ipfw');
		config_unlock();
	endif;
	$savemsg = get_std_save_message($retval);
	if($retval == 0):
		updatenotify_delete('firewall');
	endif;
endif;

$a_rule = &array_make_branch($config,'system','firewall','rule');
if(empty($a_rule)):
else:
	array_sort_key($a_rule,'ruleno');
endif;

if(isset($_GET['act']) && $_GET['act'] === 'del'):
	if($_GET['uuid'] === 'all'):
		foreach($a_rule as $rulek => $rulev):
			updatenotify_set('firewall',UPDATENOTIFY_MODE_DIRTY,$a_rule[$rulek]['uuid']);
		endforeach;
	else:
		updatenotify_set('firewall',UPDATENOTIFY_MODE_DIRTY,$_GET['uuid']);
	endif;
	header('Location: system_firewall.php');
	exit;
endif;

function firewall_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = array_search_ex($data, $config['system']['firewall']['rule'],'uuid');
			if(false !== $cnid):
				unset($config['system']['firewall']['rule'][$cnid]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
$pgtitle = [gtext('Network'),gtext('Firewall')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
}
$(window).on("load", function() {
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<form action="system_firewall.php" method="post" name="iform" id="iform" enctype="multipart/form-data">
	<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
		if($input_errors):
			print_input_errors($input_errors);
		endif;
		if($errormsg):
			print_error_box($errormsg);
		endif;
		if($savemsg):
			print_info_box($savemsg);
		endif;
		if(updatenotify_exists('firewall')):
			print_config_change_box();
		endif;
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline_checkbox2('enable',gettext('Firewall'),!empty($pconfig['enable']) ? true : false,gettext('Enable'),'enable_change(false)');
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('Export Rules');?></td>
					<td class="celldata">
						<input name="export" type="submit" class="formbtn" value="<?=gtext('Export');?>"/>
					</td>
				</tr>
				<tr>
					<td class="celltag"><?=gtext('Import Rules');?></td>
					<td class="celldata">
						<input name="import" type="submit" class="formbtn spin" id="import" value="<?=gtext('Import');?>"/>
						<input name="rulesfile" type="file" class="formfld" id="rulesfile" size="40" accept="*.rules" />&nbsp;
					</td>
				</tr>
			</tbody>
		</table>

		<table class="area_data_selection">
			<colgroup>
				<col style="width:4%">
				<col style="width:5%">
				<col style="width:20%">
				<col style="width:5%">
				<col style="width:20%">
				<col style="width:5%">
				<col style="width:5%">
				<col style="width:26%">
				<col style="width:10%">
			</colgroup>
			<thead>
<?php
				html_separator(9);
				html_titleline2(gettext('Firewall Rules'),9);
?>
				<tr>
					<th class="lhelc">&nbsp;</th>
					<th class="lhell"><?=gtext('Protocol');?></th>
					<th class="lhell"><?=gtext('Source');?></th>
					<th class="lhell"><?=gtext('Port');?></th>
					<th class="lhell"><?=gtext('Destination');?></th>
					<th class="lhell"><?=gtext('Port');?></th>
					<th class="lhelc"><?=htmlspecialchars('<->');?></th>
					<th class="lhell"><?=gtext('Description');?></th>
					<th class="lhebl"><?=gtext('Toolbox');?></th>
				</tr>
			</thead>
			<tbody>
<?php
				foreach($a_rule as $rule):
					$notificationmode = updatenotify_get_mode('firewall',$rule['uuid']);
					$enable = isset($rule['enable']);
					switch($rule['action']):
						case 'allow':
							$actionimg = 'images/fw_action_allow.png';
							break;
						case 'deny':
							$actionimg = 'images/fw_action_deny.png';
							break;
						case 'unreach host':
							$actionimg = 'images/fw_action_reject.png';
							break;
					endswitch;
?>
					<tr>
						<td class="<?=$enable?'lcelc':'lcelcd';?>"><img src="<?=$actionimg;?>" alt=""/></td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=strtoupper($rule['protocol']);?>&nbsp;</td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=htmlspecialchars(empty($rule['src']) ? "*" : $rule['src']);?>&nbsp;</td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=htmlspecialchars(empty($rule['srcport']) ? "*" : $rule['srcport']);?>&nbsp;</td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=htmlspecialchars(empty($rule['dst']) ? "*" : $rule['dst']);?>&nbsp;</td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=htmlspecialchars(empty($rule['dstport']) ? "*" : $rule['dstport']);?>&nbsp;</td>
						<td class="<?=$enable?'lcelc':'lcelcd';?>"><?=empty($rule['direction']) ? "*" : strtoupper($rule['direction']);?>&nbsp;</td>
						<td class="<?=$enable?'lcell':'lcelld';?>"><?=htmlspecialchars($rule['desc']);?>&nbsp;</td>
						<td class="lcebld">
<?php
							if(UPDATENOTIFY_MODE_DIRTY != $notificationmode):
?>
								<a href="system_firewall_edit.php?uuid=<?=$rule['uuid'];?>"><img src="images/edit.png" title="<?=gtext('Edit rule');?>" border="0" alt="<?=gtext('Edit rule');?>"/></a>
								<a href="system_firewall.php?act=del&amp;uuid=<?=$rule['uuid'];?>" onclick="return confirm('<?=gtext('Do you really want to delete this rule?');?>')"><img src="images/delete.png" title="<?=gtext('Delete rule');?>" border="0" alt="<?=gtext('Delete rule');?>" /></a>
<?php
							else:
?>
								<img src="images/delete.png" border="0" alt="" />
<?php
							endif;
?>
						</td>
					</tr>
<?php
				endforeach;
?>
			</tbody>
			<tfoot>
				<tr>
					<td class="lcenl" colspan="8"></td>
					<td class="lceadd">
						<a href="system_firewall_edit.php"><img src="images/add.png" title="<?=gtext('Add rule');?>" border="0" alt="<?=gtext('Add rule');?>" /></a>
<?php
						if(!empty($a_rule)):
?>
							<a href="system_firewall.php?act=del&amp;uuid=all" onclick="return confirm('<?=gtext('Do you really want to delete all rules?');?>')"><img src="images/delete.png" title="<?=gtext('Delete all rules');?>" border="0" alt="<?=gtext('Delete all rules');?>" /></a>
<?php
						endif;
?>
					</td>
				</tr>
			</tfoot>
		</table>
		<div id="submit">
			<input name="submit" type="submit" class="formbtn spin" value="<?=gtext('Save & Restart');?>" />
		</div>
	</td></tr></tbody></table>
<?php
	include 'formend.inc';
?>
</form>
<?php
include 'fend.inc';
