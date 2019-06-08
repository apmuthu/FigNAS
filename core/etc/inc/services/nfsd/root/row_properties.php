<?php
/*
	row_properties.php

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
namespace services\nfsd\root;
use common\properties as myp;

final class row_properties extends grid_properties {
	public function init_filesystem(): myp\property_text {
		global $g;

		$description = gettext('Path to be shared.');
		$placeholder = $placeholderv = gettext('Path');
		$property = parent::init_filesystem();
		$property->
			set_defaultvalue($g['media_path'])->
			set_description($description)->
			set_id('path')->
			set_maxlength(128)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default();
		return $property;
	}
	public function init_client(): myp\property_cidr {
		$description = gettext('Enter the network that is authorised to access the NFS share.');
		$placeholder = $placeholderv = gettext('192.168.0.0/24');
		$property = parent::init_client();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('network')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_opt_sec(): myp\property_list_multi {
		$description = gettext('Specify a list of acceptable security flavors to be used for remote access. The	default	security flavor	is sys.');
		$options = [
			'sys' => gettext('sys - Use AUTH_SYS authentication.'),
			'krb5' => gettext('krb5 - Use Kerberos V5 protocol to authenticate users before granting access to the shared filesystem.'),
			'krb5i' => gettext('krb5i - Use Kerberos V5 authentication with data integrity checksums.'),
			'krb5p' => gettext('krb5p - Use Kerberos V5 authentication with data integrity checksums and data encryption.')
		];
		$property = parent::init_opt_sec();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('param')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
}
