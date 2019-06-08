<?php
/*
	footer.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Authors: quix@free.fr, ck@realtime-projects.com.
	The Initial Developer of the Original Code is The QuiX project.

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
// XigmaNAS Code
// footer for html-page
function show_footer() {
	global $d_sysrebootreqd_path;
	$output = [];
	$output[] = '</td></tr></tbody></table>';
	$output[] = '</div>';
//	$output[] = '</div>';
	$output[] = '</main>';
	$output[] = '<footer id="g4f">';
	$output[] = '<div id="gapfooter"></div>';
	$output[] = '<div id="pagefooter">';

	$output[] = '<table class="area_data_settings">';
	$output[] = '<colgroup>';
	$output[] = '<col style="width:10%">';
	$output[] = '<col style="width:80%">';
	$output[] = '<col style="width:10%">';
	$output[] = '</colgroup>';
	$output[] = '<tbody>';
	$output[] = '<tr>';
	$output[] = '<td class="g4fl">';
	if(Session::isAdmin()):
		if(file_exists($d_sysrebootreqd_path)):
			$output[] = '<a href="/reboot.php" class="g4fi">';
			$output[] = '<img src="/images/notify_reboot.png" title="' . gtext('A reboot is required') . '" alt="' . gtext('Reboot Required') . '>">';
			$output[] = '</a>';
		endif;
	endif;
	$output[] = '</td>';
	$output[] = '<td class="g4fc">' . htmlspecialchars(get_product_copyright()) . '</td>';
	$output[] = '<td class="g4fr"></td>';
	$output[] = '</tr>';
	$output[] = '</tbody>';
	$output[] = '</table>';
	$output[] = '</div>';
	$output[] = '</footer>';
	$output[] = '</body>';
	$output[] = '</html>';
	echo implode("\n",$output);
}
?>
