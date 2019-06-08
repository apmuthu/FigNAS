<?php
/*
	diag_routes.php

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

function diag_routes_get(string $family,bool $resolve): array {
	$sphere_grid = [];
	$a_command = ['netstat','-r','-W'];
	if(!$resolve):
		$a_command[] = '-n';
	endif;
	if('inet6' === $family):
		$a_command[] = '-f inet6';
	else:
		$a_command[] = '-f inet';
	endif;
	$command = implode(' ',$a_command);
	$command_output = explode(PHP_EOL,shell_exec($command));
	$skip = 4;
	foreach($command_output as $command_output_row):
		if($skip > 0):
			$skip--;
		else:
			list($destination,$gateway,$flags,$use,$mtu,$netif,$expire) = preg_split('/\s+/',$command_output_row,7);
			if('' !== $destination):
				$sphere_row = [
					'destination' => $destination,
					'gateway' => $gateway,
					'flags' => $flags,
					'use' => $use,
					'mtu' => $mtu,
					'netif' => $netif,
					'expire' => $expire
				];
				$sphere_grid[] = $sphere_row;
			endif;
		endif;
	endforeach;
	return $sphere_grid;
}
function diag_routes_selection() {
	$resolve = (true === filter_input(INPUT_POST,'resolve',FILTER_VALIDATE_BOOLEAN));
//	IPv4
	$ipv4_grid = diag_routes_get('inet',$resolve);
	$ipv4_record_exists = count($ipv4_grid) > 0;
	$ipv4_use_tablesort = count($ipv4_grid) > 1;
//	IPv6	
	$ipv6_grid = diag_routes_get('inet6',$resolve);
	$ipv6_record_exists = count($ipv6_grid) > 0;
	$ipv6_use_tablesort = count($ipv6_grid) > 1;
	$pgtitle = [gettext('Diagnostics'),gettext('Routing Tables')];
	$use_tablesort = $ipv4_record_exists || $ipv6_record_exists;
	$a_col_width = ['20%','20%','12%','12%','12%','12%','12%'];
	$n_col_width = count($a_col_width);
	if($use_tablesort):
		$document = new_page($pgtitle,NULL,'tablesort');
	else:
		$document = new_page($pgtitle);
	endif;
//	get areas
//	$body = $document->getElementById('main');
	$pagecontent = $document->getElementById('pagecontent');
	//	create data area
	$content = $pagecontent->add_area_data();
//	add content
	$ipv4_table = $content->add_table_data_selection();
	$ipv4_table->ins_colgroup_with_styles('width',$a_col_width);
	$ipv4_thead = $ipv4_table->addTHEAD();
	if($ipv4_record_exists):
		$ipv4_tbody = $ipv4_table->addTBODY();
	else:
		$ipv4_tbody = $ipv4_table->addTBODY(['class' => 'donothighlight']);
	endif;
	$ipv4_thead->
		ins_titleline(gettext('IPv4 Routes'),$n_col_width)->
		addTR($ipv4_use_tablesort ? [] : ['class' => 'tablesorter-ignoreRow'])->
			insTHwC('lhell',gettext('Destination'))->
			insTHwC('lhell',gettext('Gateway'))->
			insTHwC('lhell',gettext('Flags'))->
			insTHwC('lhell',gettext('Use'))->
			insTHwC('lhell',gettext('MTU'))->
			insTHwC('lhell',gettext('NetIf'))->
			insTHwC('lhebl',gettext('Expire'));
	if($ipv4_record_exists):
		foreach($ipv4_grid as $ipv4_row):
			$ipv4_tbody->
				addTR()->
					insTDwC('lcell',$ipv4_row['destination'])->
					insTDwC('lcell',$ipv4_row['gateway'])->
					insTDwC('lcell',$ipv4_row['flags'])->
					insTDwC('lcell',$ipv4_row['use'])->
					insTDwC('lcell',$ipv4_row['mtu'])->
					insTDwC('lcell',$ipv4_row['netif'])->
					insTDwC('lcebl',$ipv4_row['expire']);
		endforeach;
	else:
		$ipv4_tbody->ins_no_records_found($n_col_width,gettext('No IPv4 routing information found.'));
	endif;
	$ipv4_table->addTFOOT()->ins_separator($n_col_width);
//	IPv6
	$ipv6_table = $content->add_table_data_selection();
	$ipv6_table->ins_colgroup_with_styles('width',$a_col_width);
	$ipv6_thead = $thead = $ipv6_table->addTHEAD();
	if($ipv6_record_exists):
		$ipv6_tbody = $ipv6_table->addTBODY();
	else:
		$ipv6_tbody = $ipv6_table->addTBODY(['class' => 'donothighlight']);
	endif;
	$ipv6_thead->
		ins_titleline(gettext('IPv6 Routes'),$n_col_width)->
		addTR($ipv6_use_tablesort ? [] : ['class' => 'tablesorter-ignoreRow'])->
			insTHwC('lhell',gettext('Destination'))->
			insTHwC('lhell',gettext('Gateway'))->
			insTHwC('lhell',gettext('Flags'))->
			insTHwC('lhell',gettext('Use'))->
			insTHwC('lhell',gettext('MTU'))->
			insTHwC('lhell',gettext('NetIf'))->
			insTHwC('lhebl',gettext('Expire'));
	if($ipv6_record_exists):
		foreach($ipv6_grid as $ipv6_row):
			$ipv6_tbody->
				addTR()->
					insTDwC('lcell',$ipv6_row['destination'])->
					insTDwC('lcell',$ipv6_row['gateway'])->
					insTDwC('lcell',$ipv6_row['flags'])->
					insTDwC('lcell',$ipv6_row['use'])->
					insTDwC('lcell',$ipv6_row['mtu'])->
					insTDwC('lcell',$ipv6_row['netif'])->
					insTDwC('lcebl',$ipv6_row['expire']);
		endforeach;
	else:
		$ipv6_tbody->ins_no_records_found($n_col_width,gettext('No IPv6 routing information found.'));
	endif;
	$document->render();
}
diag_routes_selection();
