<?php
/*
	properties_system_advanced.php

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
require_once 'properties.php';

class properties_system_advanced extends co_property_container {
	protected $x_adddivsubmittodataframe;
	public function get_adddivsubmittodataframe() {
		return $this->x_adddivsubmittodataframe ?? $this->init_adddivsubmittodataframe();
	}
	public function init_adddivsubmittodataframe() {
		$property = $this->x_adddivsubmittodataframe = new property_bool($this);
		$property->
			set_name('adddivsubmittodataframe')->
			set_title(gettext('Button Location'));
		$caption = gettext('Display action buttons in the scrollable area instead of the footer area.');
		$property->
			set_id('adddivsubmittodataframe')->
			set_caption($caption)->
//			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_aesni;
	public function get_aesni() {
		return $this->x_aesni ?? $this->init_aesni();
	}
	public function init_aesni() {
		$property = $this->x_aesni = new property_list($this);
		$property->
			set_name('aesni')->
			set_title(gettext('Hardware AES'));
		$description = gettext('AES-NI (Advanced Encryption Standard New Instructions) is an extension to the x86 instruction set architecture for microprocessors.');
		$options = [
			'auto' => gettext('Load driver automatically when GELI devices have been defined in the configuration file and the instruction set is supported.'),
			'yes' => gettext('Load driver during boot when the instruction set is supported.'),
			'no' => gettext('Do not load driver.')
		];
		$property->
			set_id('aesni')->
			set_description($description)->
			set_defaultvalue('auto')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_disableconsolemenu;
	public function get_disableconsolemenu() {
		return $this->x_disableconsolemenu ?? $this->init_disableconsolemenu();
	}
	public function init_disableconsolemenu() {
		$property = $this->x_disableconsolemenu = new property_bool($this);
		$property->
			set_name('disableconsolemenu')->
			set_title(gettext('Console Menu'));
		$caption = gettext('Disable console menu.');
		$description = gettext('Changes to this option will take effect after a reboot.');
		$property->
			set_id('disableconsolemenu')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_disablefm;
	public function get_disablefm() {
		return $this->x_disablefm ?? $this->init_disablefm();
	}
	public function init_disablefm() {
		$property = $this->x_disablefm = new property_bool($this);
		$property->
			set_name('disablefm')->
			set_title(gettext('File Manager'));
		$property->
			set_id('disablefm')->
			set_caption(gettext('Disable file manager.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_disablefirmwarecheck;
	public function get_disablefirmwarecheck() {
		return $this->x_disablefirmwarecheck ?? $this->init_disablefirmwarecheck();
	}
	public function init_disablefirmwarecheck() {
		$property = $this->x_disablefirmwarecheck = new property_bool($this);
		$property->
			set_name('disablefirmwarecheck')->
			set_title(gettext('Firmware Check'));
		$link = '<a href="system_firmware.php">' . gettext('System') . ': ' . gettext('Firmware Update') . '</a>';
		$description = sprintf(gettext('Do not let the server check for newer firmware versions when the %s page gets loaded.'),$link);
		$property->
			set_id('disablefirmwarecheck')->
			set_caption(gettext('Disable firmware version check.'))->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_disablebeep;
	public function get_disablebeep() {
		return $this->x_disablebeep ?? $this->init_disablebeep();
	}
	public function init_disablebeep() {
		$property = $this->x_disablebeep = new property_bool($this);
		$property->
			set_name('disablebeep')->
			set_title(gettext('Internal Speaker'));
		$property->
			set_id('disablebeep')->
			set_caption(gettext('Disable speaker beep on startup and shutdown.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_microcode_update;
	public function get_microcode_update() {
		return $this->x_microcode_update ?? $this->init_microcode_update();
	}
	public function init_microcode_update() {
		$property = $this->x_microcode_update = new property_bool($this);
		$property->
			set_name('microcode_update')->
			set_title(gettext('CPU Microcode Update'));
		$property->
			set_id('microcode_update')->
			set_caption(gettext('Enable this option to update the CPU microcode on startup.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_enabletogglemode;
	public function get_enabletogglemode() {
		return $this->x_enabletogglemode ?? $this->init_enabletogglemode();
	}
	public function init_enabletogglemode() {
		$property = $this->x_enabletogglemode = new property_bool($this);
		$property->
			set_name('enabletogglemode')->
			set_title(gettext('Toggle Mode'));
		$property->
			set_id('enabletogglemode')->
			set_caption(gettext('Use toggle button instead of enable/disable buttons.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_nonsidisksizevalues;
	public function get_nonsidisksizevalues() {
		return $this->x_nonsidisksizevalues ?? $this->init_nonsidisksizevalues();
	}
	public function init_nonsidisksizevalues() {
		$property = $this->x_nonsidisksizevalues = new property_bool($this);
		$property->
			set_name('nonsidisksizevalues')->
			set_title(gettext('Binary Prefix'));
		$property->
			set_id('nonsidisksizevalues')->
			set_caption(gettext('Display disk size values using binary prefixes instead of decimal prefixes.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_skipviewmode;
	public function get_skipviewmode() {
		return $this->x_skipviewmode ?? $this->init_skipviewmode();
	}
	public function init_skipviewmode() {
		$property = $this->x_skipviewmode = new property_bool($this);
		$property->
			set_name('skipviewmode')->
			set_title(gettext('Skip View Mode'));
		$property->
			set_id('skipviewmode')->
			set_caption(gettext('Enable this option if you want to edit configuration pages directly without the need to switch to edit mode.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_showcolorfulmeter;
	public function get_showcolorfulmeter() {
		return $this->x_showcolorfulmeter ?? $this->init_showcolorfulmeter();
	}
	public function init_showcolorfulmeter() {
		$caption = gettext('Enable this option if you want to display colorful meter states.');
		$property = $this->x_showcolorfulmeter = new property_bool($this);
		$property->
			set_name('showcolorfulmeter')->
			set_title(gettext('Colorful Meters'));
		$property->
			set_id('showcolorfulmeter')->
			set_caption($caption)->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_disableextensionmenu;
	public function get_disableextensionmenu() {
		return $this->x_disableextensionmenu ?? $this->init_disableextensionmenu();
	}
	public function init_disableextensionmenu() {
		$property = $this->x_disableextensionmenu = new property_bool($this);
		$property->
			set_name('disableextensionmenu')->
			set_title(gettext('Disable Extension Menu'));
		$property->
			set_id('disableextensionmenu')->
			set_caption(gettext('Disable scanning of folders for existing extension menus.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_zeroconf;
	public function get_zeroconf() {
		return $this->x_zeroconf ?? $this->init_zeroconf();
	}
	public function init_zeroconf() {
		$property = $this->x_zeroconf = new property_bool($this);
		$property->
			set_name('zeroconf')->
			set_title(gettext('Zeroconf/Bonjour'));
		$property->
			set_id('zeroconf')->
			set_caption(gettext('Enable Zeroconf/Bonjour to advertise services of this device.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_powerd;
	public function get_powerd() {
		return $this->x_powerd ?? $this->init_powerd();
	}
	public function init_powerd() {
		$property = $this->x_powerd = new property_bool($this);
		$property->
			set_name('powerd')->
			set_title(gettext('Power Daemon'));
		$property->
			set_id('powerd')->
			set_caption(gettext('Enable the server power control utility.'))->
			set_description(gettext('The powerd utility monitors the server state and sets various power control options accordingly.'))->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_pwmode;
	public function get_pwmode() {
		return $this->x_pwmode ?? $this->init_pwmode();
	}
	public function init_pwmode() {
		$property = $this->x_pwmode = new property_list($this);
		$property->
			set_name('pwmode')->
			set_title(gettext('Power Mode'));
		$options = [
			'maximum' => gettext('Maximum (Highest Performance)'),
			'hiadaptive' => gettext('Hiadaptive (High Performance)'),
			'adaptive' => gettext('Adaptive (Low Power Consumption)'),
			'minimum' => gettext('Minimum (Lowest Performance)')
		];
		$property->
			set_id('pwmode')->
			set_description(gettext('Controls the power consumption mode.'))->
			set_defaultvalue('hiadaptive')->
			set_options($options)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_pwmax;
	public function get_pwmax() {
		return $this->x_pwmax ?? $this->init_pwmax();
	}
	public function init_pwmax() {
		$property = $this->x_pwmax = new property_list($this);
		return $property;
/*
		$clocks = @exec("/sbin/sysctl -q -n dev.cpu.0.freq_levels");
		$a_freq = [];
		if(!empty($clocks)):
			$a_tmp = preg_split("/\s/", $clocks);
			foreach ($a_tmp as $val):
				list($freq,$tmp) = preg_split("/\//", $val);
				if(!empty($freq)):
					$a_freq[] = $freq;
				endif;
			endforeach;
		endif;
		html_inputbox2('pwmax',gettext('CPU Maximum Frequency'),$pconfig['pwmax'],sprintf('%s %s',gettext('CPU frequencies:'),join(', ',$a_freq)) . '.<br />' . gettext('An empty field is default.'),false,5);
*/
	}
	protected $x_pwmin;
	public function get_pwmin() {
		return $this->x_pwmin ?? $this->init_pwmin();
	}
	public function init_pwmin() {
		$property = $this->x_pwmin = new property_text($this);
		return $property;
//		html_inputbox2('pwmin',gettext('CPU Minimum Frequency'),$pconfig['pwmin'],gettext('An empty field is default.'),false,5);
	}
	protected $x_motd;
	public function get_motd() {
		return $this->x_motd ?? $this->init_motd();
	}
	public function init_motd() {
		$property = $this->x_motd = new property_text($this);
		return $property;
//		html_textarea2('motd',gettext('MOTD'),$pconfig['motd'],gettext('Message of the day.'),false,65,7,false,false);
	}
	protected $x_shrinkpageheader;
	public function get_shrinkpageheader() {
		return $this->x_shrinkpageheader ?? $this->init_shrinkpageheader();
	}
	public function init_shrinkpageheader() {
		$property = $this->x_shrinkpageheader = new property_bool($this);
		$property->
			set_name('shrinkpageheader')->
			set_title(gettext('Shrink Page Header'));
		$property->
			set_id('shrinkpageheader')->
			set_caption(gettext('Enable this option to reduce the height of the page header to a minimum.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_sysconsaver;
	public function get_sysconsaver() {
		return $this->x_sysconsaver ?? $this->init_sysconsaver();
	}
	public function init_sysconsaver() {
		$property = $this->x_sysconsaver = new property_bool($this);
		$property->
			set_name('sysconsaver')->
			set_title(gettext('Console Screensaver'));
		$property->
			set_id('sysconsaver')->
			set_caption(gettext('Enable console screensaver.'))->
			set_description('')->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
	protected $x_sysconsaverblanktime;
	public function get_sysconsaverblanktime() {
		return $this->x_sysconsaverblanktime ?? $this->init_sysconsaverblanktime();
	}
	public function init_sysconsaverblanktime() {
		$property = $this->x_sysconsaverblanktime = new property_text($this);
		return $property;
//		html_inputbox2('sysconsaverblanktime',gettext('Blank Time'),$pconfig['sysconsaverblanktime'],gettext('Turn the monitor to standby after N seconds.'),true,5);
	}
	protected $x_enableserialconsole;
	public function get_enableserialconsole() {
		return $this->x_enableserialconsole ?? $this->init_enableserialconsole();
	}
	public function init_enableserialconsole() {
		$property = $this->x_enableserialconsole = new property_bool($this);
		$property->
			set_name('enableserialconsole')->
			set_title(gettext('Serial Console'));
		$property->
			set_id('enableserialconsole')->
			set_caption(gettext('Enable serial console.'))->
			set_description(sprintf('<span class="red"><strong>%s</strong></span><br />%s',gettext('The COM port in BIOS has to be enabled before enabling this option.'), gettext('Changes to this option will take effect after a reboot.')))->
			set_defaultvalue(false)->
			filter_use_default()->
			set_editableonadd(true)->
			set_editableonmodify(true)->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('The value is invalid.')));
		return $property;
	}
}
