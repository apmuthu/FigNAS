<?php
/*
	cs_scheduletime.php

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
?>
<table class="area_data_selection">
	<colgroup>
		<col style="width:20%">
		<col style="width:20%">
		<col style="width:24%">
		<col style="width:18%">
		<col style="width:18%">
	</colgroup>
	<thead>
		<tr>
			<th class="lhell"><?=gtext('Minutes');?></th>
			<th class="lhell"><?=gtext('Hours');?></th>
			<th class="lhell"><?=gtext('Days');?></th>
			<th class="lhell"><?=gtext('Months');?></th>
			<th class="lhebl"><?=gtext('Weekdays');?></th>
		</tr>
	</thead>
	<tbody class="donothighlight"><tr>
		<td class="lcell" style="vertical-align:top">
			<div class="rblo">
				<label>
					<input type="radio" class="rblo" name="all_mins" id="all_mins1" value="1" <?php if(1 == $pconfig['all_mins']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('All');?></span>
				</label>
			</div>
			<div class="rblo">
				<label>
					<input type="radio" class="rblo dimassoctable" name="all_mins" id="all_mins2" value="0" <?php if(1 != $pconfig['all_mins']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('Selected');?> ..</span>
					<table><tbody class="donothighlight"><tr>
<?php
						$val_min = $key = 0;
						$val_count = 60;
						$val_max = $val_min + $val_count - 1;
						$val_break = 15;
						$i_outer_max = ceil($val_count / $val_break) - 1;
						$i_inner_max = $val_min + $val_break - 1;
						for($i_outer = 0;$i_outer <= $i_outer_max;$i_outer++):
							echo '<td class="lcefl">',PHP_EOL;
							for($i_inner = $val_min;$i_inner <= $i_inner_max;$i_inner++):
								if($key <= $val_max):
									echo '<div class="cblo"><label>';
									echo '<input type="checkbox" class="cblo" name="minute[]" onchange="set_selected(\'all_mins\')" value="',$key,'"';
									if(isset($pconfig['minute']) && is_array($pconfig['minute']) && in_array((string)$key,$pconfig['minute'])):
										echo ' checked="checked"';
									endif;
									echo '/><span class="cblo">',sprintf('%02d',$key),'</span>';
									echo '</label></div>',PHP_EOL;
								else:
									break;
								endif;
								$key++;
							endfor;
							echo '</td>',PHP_EOL;
						endfor;
?>
					</tr></tbody></table>
				</label>
			</div>
		</td>
		<td class="lcell" style="vertical-align:top">
			<div class="rblo">
				<label>
					<input type="radio" class="rblo" name="all_hours" id="all_hours1" value="1" <?php if(1 == $pconfig['all_hours']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('All');?></span>
				</label>
			</div>
			<div class="rblo">
				<label>
					<input type="radio" class="rblo dimassoctable" name="all_hours" id="all_hours2" value="0" <?php if(1 != $pconfig['all_hours']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('Selected');?> ..</span>
					<table><tbody class="donothighlight"><tr>
<?php
						$val_min = $key = 0;
						$val_count = 24;
						$val_max = $val_min + $val_count - 1;
						$val_break = 6;
						$i_outer_max = ceil($val_count / $val_break) - 1;
						$i_inner_max = $val_min + $val_break - 1;
						for($i_outer = 0;$i_outer <= $i_outer_max;$i_outer++):
							echo '<td class="lcefl">',PHP_EOL;
							for($i_inner = $val_min;$i_inner <= $i_inner_max;$i_inner++):
								if($key <= $val_max):
									echo '<div class="cblo"><label>';
									echo '<input type="checkbox" class="cblo" name="hour[]" onchange="set_selected(\'all_hours\')" value="',$key,'"';
									if(isset($pconfig['hour']) && is_array($pconfig['hour']) && in_array((string)$key,$pconfig['hour'])):
										echo ' checked="checked"';
									endif;
									echo '/><span class="cblo">',sprintf('%02d',$key),'</span>';
									echo '</label></div>',PHP_EOL;
								else:
									break;
								endif;
								$key++;
							endfor;
							echo '</td>',PHP_EOL;
						endfor;
?>
					</tr></tbody></table>
				</label>
			</div>
		</td>
		<td class="lcell" style="vertical-align:top">
			<div class="rblo">
				<label>
					<input type="radio" class="rblo" name="all_days" id="all_days1" value="1" <?php if(1 == $pconfig['all_days']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('All');?></span>
				</label>
			</div>
			<div class="rblo">
				<label>
					<input type="radio" class="rblo dimassoctable" name="all_days" id="all_days2" value="0" <?php if(1 != $pconfig['all_days']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('Selected');?> ..</span>
					<table><tbody class="donothighlight"><tr>
<?php
						$val_min = $key = 1;
						$val_count = 31;
						$val_max = $val_min + $val_count - 1;
						$val_break = 7;
						$i_outer_max = ceil($val_count / $val_break) - 1;
						$i_inner_max = $val_min + $val_break - 1;
						for($i_outer = 0;$i_outer <= $i_outer_max;$i_outer++):
							echo '<td class="lcefl">',PHP_EOL;
							for($i_inner = $val_min;$i_inner <= $i_inner_max;$i_inner++):
								if($key <= $val_max):
									echo '<div class="cblo"><label>';
									echo '<input type="checkbox" class="cblo" name="day[]" onchange="set_selected(\'all_days\')" value="',$key,'"';
									if(isset($pconfig['day']) && is_array($pconfig['day']) && in_array((string)$key,$pconfig['day'])):
										echo ' checked="checked"';
									endif;
									echo '/><span class="cblo">',sprintf('%02d',$key),'</span>';
									echo '</label></div>',PHP_EOL;
								else:
									break;
								endif;
								$key++;
							endfor;
							echo '</td>',PHP_EOL;
						endfor;
?>
					</tr></tbody></table>
				</label>
			</div>
		</td>
		<td class="lcell" style="vertical-align:top">
			<div class="rblo">
				<label>
					<input type="radio" class="rblo" name="all_months" id="all_months1" value="1" <?php if(1 == $pconfig['all_months']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('All');?></span>
				</label>
			</div>
			<div class="rblo">
				<label>
					<input type="radio" class="rblo dimassoctable" name="all_months" id="all_months2" value="0" <?php if(1 != $pconfig['all_months']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('Selected');?> ..</span>
					<table><tbody class="donothighlight"><tr>
<?php
						echo '<td class="lcefl">',PHP_EOL;
						foreach($g_months as $key => $val):
							echo '<div class="cblo"><label>';
							echo '<input type="checkbox" class="cblo" name="month[]" onchange="set_selected(\'all_months\')" value="',$key,'"';
							if(is_array($pconfig['month']) && in_array((string)$key,$pconfig['month'])):
								echo ' checked="checked"';
							endif;
							echo '/><span class="cblo">',$val,'</span>';
							echo '</label></div>',PHP_EOL;
						endforeach;
						echo '</td>',PHP_EOL;
?>
					</tr></tbody></table>
				</label>
			</div>
		</td>
		<td class="lcebl" style="vertical-align:top">
			<div class="rblo">
				<label>
					<input type="radio" class="rblo" name="all_weekdays" id="all_weekdays1" value="1" <?php if(1 == $pconfig['all_weekdays']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('All');?></span>
				</label>
			</div>
			<div class="rblo">
				<label>
					<input type="radio" class="rblo dimassoctable" name="all_weekdays" id="all_weekdays2" value="0" <?php if(1 != $pconfig['all_weekdays']) echo 'checked="checked"';?>/>
					<span class="rblo"><?=gtext('Selected');?> ..</span>
					<table><tbody class="donothighlight"><tr>
<?php
						echo '<td class="lcefl">',PHP_EOL;
						foreach($g_weekdays as $key => $val):
							echo '<div class="cblo"><label>';
							echo '<input type="checkbox" class="cblo" name="weekday[]" onchange="set_selected(\'all_weekdays\')" value="',$key,'"';
							if(isset($pconfig['weekday']) && is_array($pconfig['weekday'])):
								if(in_array((string)$key,$pconfig['weekday'])):
									echo ' checked="checked"';
								endif;
								if(7 == $key): // Compatibility for non-ISO day of week 0 for Sunday
									if(in_array('0',$pconfig['weekday'])):
										echo ' checked="checked"';
									endif;
								endif;
							endif;
							echo '/><span class="cblo">',$val,'</span>';
							echo '</label></div>',PHP_EOL;
						endforeach;
						echo '</td>',PHP_EOL;
?>
					</tr></tbody></table>
				</label>
			</div>
		</td>
	</tr></tbody>
</table>
