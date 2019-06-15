<?php
/*
	grid_properties.php

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
namespace services\sshd;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_port;
	public function init_port(): myp\property_int {
		$property = $this->x_port = new myp\property_int($this);
		$property->
			set_name('port')->
			set_title(gettext('Port'));
		return $property;
	}
	final public function get_port() {
		return $this->x_port ?? $this->init_port();
	}
	protected $x_allowpa;
	public function init_allowpa(): myp\property_bool {
		$property = $this->x_allowpa = new myp\property_bool($this);
		$property->
			set_name('passwordauthentication')->
			set_title(gettext('Password Authentication'));
		return $property;
	}
	final public function get_allowpa(): myp\property_bool {
		return $this->x_allowpa ?? $this->init_allowpa();
	}
	protected $x_allowcra;
	public function init_allowcra(): myp\property_bool {
		$property = $this->x_allowcra = new myp\property_bool($this);
		$property->
			set_name('challengeresponseauthentication')->
			set_title(gettext('Challenge-Response Authentication'));
		return $property;
	}
	final public function get_allowcra(): myp\property_bool {
		return $this->x_allowcra ?? $this->init_allowcra();
	}
	protected $x_allowkia;
	public function init_allowkia(): myp\property_bool {
		$property = $this->x_allowkia = new myp\property_bool($this);
		$property->
			set_name('kbdinteractiveauthentication')->
			set_title(gettext('Keyboard-Interactive Authentication'));
		return $property;
	}
	final public function get_allowkia(): myp\property_bool {
		return $this->x_allowkia ?? $this->init_allowkia();
	}
	protected $x_allowpka;
	public function init_allowpka(): myp\property_bool {
		$property = $this->x_allowpka = new myp\property_bool($this);
		$property->
			set_name('pubkeyauthentication')->
			set_title(gettext('Public Key Authentication'));
		return $property;
	}
	final public function get_allowpka(): myp\property_bool {
		return $this->x_allowpka ?? $this->init_allowpka();
	}
	protected $x_allowga;
	public function init_allowga(): myp\property_bool {
		$property = $this->x_allowga = new myp\property_bool($this);
		$property->
			set_name('gssapiauthentication')->
			set_title(gettext('GSSAPI Authentication'));
		return $property;
	}
	final public function get_allowga(): myp\property_bool {
		return $this->x_allowga ?? $this->init_allowga();
	}
	protected $x_permitrootlogin;
	public function init_permitrootlogin(): myp\property_bool {
		$property = $this->x_permitrootlogin = new myp\property_bool($this);
		$property->
			set_name('permitrootlogin')->
			set_title(gettext('Root Login'));
		return $property;
	}
	final public function get_permitrootlogin(): myp\property_bool {
		return $this->x_permitrootlogin ?? $this->init_permitrootlogin();
	}
	protected $x_allowtcpforwarding;
	public function init_allowtcpforwarding(): myp\property_bool {
		$property = $this->x_allowtcpforwarding = new myp\property_bool($this);
		$property->
			set_name('tcpforwarding')->
			set_title(gettext('TCP Forwarding'));
		return $property;
	}
	final public function get_allowtcpforwarding(): myp\property_bool {
		return $this->x_allowtcpforwarding ?? $this->init_allowtcpforwarding();
	}
	protected $x_compression;
	public function init_compression(): myp\property_bool {
		$property = $this->x_compression = new myp\property_bool($this);
		$property->
			set_name('compression')->
			set_title(gettext('Compression'));
		return $property;
	}
	final public function get_compression(): myp\property_bool {
		return $this->x_compression ?? $this->init_compression();
	}
	protected $x_privatekey;
	public function init_privatekey(): myp\property_text {
		$property = $this->x_privatekey = new myp\property_text;
		$property->
			set_name('private-key')->
			set_title(gettext('Private Key'));
		return $property;
	}
	final public function get_privatekey(): myp\property_text {
		return $this->x_privatekey ?? $this->init_privatekey();
	}
	protected $x_rawprivatekey;
	public function init_rawprivatekey(): myp\property_textarea {
		$property = $this->x_rawprivatekey = new myp\property_textarea;
		$property->
			set_name('rawprivatekey')->
			set_title(gettext('Private Key'));
		return $property;
	}
	final public function get_rawprivatekey(): myp\property_textarea {
		return $this->x_rawprivatekey ?? $this->init_rawprivatekey();
	}
	protected $x_subsystem;
	public function init_subsystem(): myp\property_text {
		$property = $this->x_subsystem = new myp\property_text;
		$property->
			set_name('subsystem')->
			set_title(gettext('Subsystem'));
		return $property;
	}
	final public function get_subsystem(): myp\property_text {
		return $this->x_subsystem ?? $this->init_subsystem();
	}
	protected $x_loglevel;
	public function init_loglevel(): myp\property_list {
		$property = $this->x_loglevel = new myp\property_list;
		$property->
			set_name('loglevel')->
			set_title(gettext('Log Level'));
		return $property;
	}
	final public function get_loglevel(): myp\property_list {
		return $this->x_loglevel ?? $this->init_loglevel();
	}
	protected $x_auxparam;
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
/*
	AllowTcpForwarding
	ChallengeResponseAuthentication
	Compression
	GSSAPIAuthentication
	KbdInteractiveAuthentication
	LogLevel
	PasswordAuthentication
	PermitRootLogin
	Port
	PubkeyAuthentication
	Subsystem

	AcceptEnv
	AddressFamily
	AllowAgentForwarding
	AllowGroups
	AllowStreamLocalForwarding
	AllowUsers
	AuthenticationMethods
	AuthorizedKeysCommand
	AuthorizedKeysCommandUser
	AuthorizedKeysFile
	AuthorizedPrincipalsCommand
	AuthorizedPrincipalsCommandUser
	AuthorizedPrincipalsFile
	Banner
	ChrootDirectory
	Ciphers
	ClientAliveCountMax
	ClientAliveInterval
	DenyGroups
	DenyUsers
	DisableForwarding
	ExposeAuthInfo
	FingerprintHash
	ForceCommand
	GatewayPorts
	GSSAPICleanupCredentials
	GSSAPIStrictAcceptorCheck
	HostbasedAcceptedKeyTypes
	HostbasedAuthentication
	HostbasedUsesNameFromPacketOnly
	HostCertificate
	HostKey
	HostKeyAgent
	HostKeyAlgorithms
	IgnoreRhosts
	IgnoreUserKnownHosts
	IPQoS
	KerberosAuthentication
	KerberosGetAFSToken
	KerberosOrLocalPasswd
	KerberosTicketCleanup
	KexAlgorithms
	ListenAddress
	LoginGraceTime
	MACs
	Match
	MaxAuthTries
	MaxSessions
	MaxStartups
	PermitEmptyPasswords
	PermitListen
	PermitOpen
	PermitTTY
	PermitTunnel
	PermitUserEnvironment
	PermitUserRC
	PidFile
	PrintLastLog
	PrintMotd
	PubkeyAcceptedKeyTypes
	PubkeyAuthentication
	RekeyLimit
	RevokedKeys
	RDomain
	SetEnv
	StreamLocalBindMask
	StreamLocalBindUnlink
	StrictModes
	SyslogFacility
	TCPKeepAlive
	TrustedUserCAKeys
	UseBlacklist
	UseDNS
	UsePAM
	VersionAddendum
	X11DisplayOffset
	X11Forwarding
	X11UseLocalhost
	XAuthLocation
 */
}
