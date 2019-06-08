<?php
/*
	common\sphere\row.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright © 2018-2019 XigmaNAS <info@xigmanas.com>.
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
namespace common\sphere;
/**
 *	sphere object for row pages
 */
final class row extends hub {
	public function doj(bool $with_envelope = true): string {
		$output = [];
		if($with_envelope):
			$output[] = '<script type="text/javascript">';
			$output[] = '//<![CDATA[';
		endif;
		$output[] = '$(window).on("load", function() {';
		//	Init spinner.
		$output[] = "\t" . '$("#iform").submit(function() { spinner(); });';
		$output[] = "\t" . '$(".spin").click(function() { spinner(); });';
		$output[] = '});';
		if($with_envelope):
			$output[] = '//]]>';
			$output[] = '</script>';
			$output[] = '';
		endif;
		return implode(PHP_EOL,$output);
	}
	public function upsert() {
//		soft update existing grid record with row record or add row record to grid
		if(false === $this->row_id):
			$this->grid[] = $this->row;
		else:
			foreach($this->row as $row_key => $row_val):
				$this->grid[$this->row_id][$row_key] = $row_val;
			endforeach;
		endif;
	}
}
