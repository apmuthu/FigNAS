<?php
/*
	license.php

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

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
// Configure page permission
$pgperm['allowuser'] = true;

require_once 'auth.inc';
require_once 'guiconfig.inc';

$contributors = [
	['Michael Zoon','zoon01@xigmanas.com','Active Developer & Project Lead since 2011'],
	['Michael Schneider','ms49434@xigmanas.com','Active Developer since 2016'],
	['Daisuke Aoyama','aoyama@peach.ne.jp','Developer between 2008 and 2017'],
	['Volker Theile','votdev@gmx.de','Developer between 2006 and 2009'],
	['José Rivera','joserprg@gmail.com','Contributor'],
	['Andreas Schmidhuber','a.schmidhuber@gmail.com','Contributor'],
	['Tony Cat','tony1@xigmanas.com','User guide and Live support on irc #xigmanas (tony1)'],
	['Rhett Hillary','siftu@xigmanas.com','User guide and Live support on irc #xigmanas (siftu)'],
	['Samuel Tunis','killermist@gmail.com','User guide and Live support on irc between 2012 and 2015'],
	['Vicente Soriano Navarro','victek@gmail.com','Catalan translator of the WebGUI'],
	['Alex Lin','linuxant@gmail.com','Chinese translator of the WebGUI'],
	['Zhu Yan','tianmotrue@163.com','Chinese translator of the WebGUI'],
	['Pavel Borecki','pavel.borecki@gmail.com','Czech translator of the WebGUI'],
	['Carsten Vinkler','carsten@indysign.dk','Danish translator of the WebGUI'],
	['Christophe Lherieau','skimpax@gmail.com','French translator of the WebGUI'],
	['Edouard Richard','richard.edouard84@gmail.com','French translator of the WebGUI'],
	['Dominik Plaszewski','domme555@gmx.net','German translator of the WebGUI'],
	['Chris Kanatas','ckanatas@gmail.com','Greek translator of the WebGUI'],
	['Petros Kyladitis','petros.kyladitis@gmail.com','Greek translator of the WebGUI'],
	['Kiss-Kálmán Dániel','kisskalmandaniel@gmail.com','Hungarian translator of the WebGUI'],
	['Christian Sulmoni','csulmoni@gmail.com','Italian translator of the WebGUI and QuiXplorer'],
	['Frederico Tavares','frederico-tavares@sapo.pt','Portuguese translator of the WebGUI'],
	['Laurentiu Bubuianu','laurfb@yahoo.com','Romanian translator of the WebGUI'],
	['Raul Fernandez Garcia','raulfg3@gmail.com','Spanish translator of the WebGUI'],
	['Mucahid Zeyrek','mucahid.zeyrek@dhl.com','Turkish translator of the WebGUI']
];
$software = [
	['ataidle',[
		'Sets the idle timer on ATA (IDE) hard drives (<a href="http://bluestop.org/ataidle/" target="_blank" rel="noreferrer">http://bluestop.org/ataidle</a>).',
		'Copyright © 2004-2005 Bruce Cran (<a href="mailto:bruce@cran.org.uk">bruce@cran.org.uk</a>). All Rights Reserved.'
	]],
	['Apple Bonjour',[
		'Bonjour, known as zero-configuration networking, using multicast Domain Name System (mDNS) records (<a href="http://developer.apple.com/networking/bonjour" target="_blank" rel="noreferrer">http://developer.apple.com/networking/bonjour</a>).',
		'Copyright © Apple Public Source License. All Rights Reserved.'
	]],
	['cdialog',[
		'Display simple dialog boxes from shell scripts (<a href="http://invisible-island.net/dialog/" target="_blank" rel="noreferrer">http://invisible-island.net/dialog</a>).',
		'Copyright © 2000-2006, 2007 Thomas E. Dickey. All Rights Reserved.'
	]],
	['e2fsprogs',[
		'e2fsprogs (<a href="http://e2fsprogs.sourceforge.net" target="_blank" rel="noreferrer">http://e2fsprogs.sourceforge.net</a>).',
		"Copyright © 2007 Theodore Ts'o. All Rights Reserved."
	]],
	['ext4fuse',[
		'EXT4 implementation for FUSE (<a href="https://github.com/gerard/ext4fuse" target="_blank" rel="noreferrer">https://github.com/gerard/ext4fuse</a>).',
		'Copyright © 1989, 1991 Free Software Foundation. All Rights Reserved.'
	]],
	['FreeBSD',[
		'The FreeBSD Project (<a href="http://www.freebsd.org" target="_blank" rel="noreferrer">http://www.freebsd.org</a>).',
		'Copyright © 1995-2018 The FreeBSD Project. All Rights Reserved.'
	]],
	['Freenas 7',[
		'Freenas 7 (<a href="https://github.com/freenas/freenas7" target="_blank" rel="noreferrer">https://github.com/freenas/freenas7</a>).',
		'Copyright © 2005-2011 by Olivier Cochard (olivier@freenas.org). All Rights Reserved.'
	]],
	['FUPPES',[
		'Free UPnP Entertainment Service (<a href="http://fuppes.ulrich-voelkel.de" target="_blank" rel="noreferrer">http://fuppes.ulrich-voelkel.de</a>).',
		'Copyright © 2005 - 2011 Ulrich Völkel (<a href="mailto:mail@ulrich-voelkel.de">mail@ulrich-voelkel.de</a>). All Rights Reserved.'
	]],
	['Fuse',[
		'Filesystem in Userspace (<a href="https://github.com/libfuse/libfuse" target="_blank" rel="noreferrer">https://github.com/libfuse/libfuse</a>).',
		'Copyright © GNU General Public License. All Rights Reserved.'
	]],
	['GEOM RAID5',[
		'GEOM RAID5 module (<a href="http://www.wgboome.org/geom_raid5-html" target="_blank" rel="noreferrer">http://www.wgboome.org/geom_raid5-html</a> & (<a href="http://lev.serebryakov.spb.ru/download/graid5/" target="_blank" rel="noreferrer">http://lev.serebryakov.spb.ru/download/graid5</a>).',
		'Copyright © 2006-2010 Originally written by Arne Woerner (<a href="mailto:graid5@wgboome.org">graid5@wgboome.org</a>).',
		'Copyright © 2010-2014 Now maintained by Lev Serebryakov (<a href="mailto:lev@FreeBSD.org">lev@FreeBSD.org</a>).'
	]],
	['host',[
		'An utility to query DNS servers.',
		'Copyright © Rewritten by Eric Wassenaar, Nikhef-H, (<a href="mailto:e07@nikhef.nl">e07@nikhef.nl</a>). All Rights Reserved.'
	]],
	['inadyn-mt',[
		'Simple Dynamic DNS client (<a href="http://sourceforge.net/projects/inadyn-mt" target="_blank" rel="noreferrer">http://sourceforge.net/projects/inadyn-mt</a>).',
		'Inadyn Copyright © 2003-2004 Narcis Ilisei. All Rights Reserved.',
		'Inadyn-mt Copyright © 2007 Bryan Hoover (<a href="mailto:bhoover@wecs.com">bhoover@wecs.com</a>). All Rights Reserved.'
	]],
	['iperf3',[
		'A tool to measure TCP and UDP bandwidth. (<a href="http://software.es.net/iperf/" target="_blank" rel="noreferrer">http://software.es.net/iperf</a>).',
		'Copyright © 2014-2018 ESnet. All Rights Reserved.'
	]],
	['ipmitool',[
		'IPMItool provides a simple command-line interface to v1.5 & v2.0 IPMI-enabled devices. (<a href="http://sourceforge.net/projects/ipmitool/" target="_blank" rel="noreferrer">http://sourceforge.net/projects/ipmitool</a>).',
		'Copyright © 2003 Sun Microsystems. All Rights Reserved.'
	]],
	['iSCSI initiator',[
		'iSCSI initiator (<a href="ftp://ftp.cs.huji.ac.il/users/danny/freebsd" target="_blank" rel="noreferrer">ftp://ftp.cs.huji.ac.il/users/danny/freebsd</a>).',
		'Copyright © 2005-2011 Daniel Braniss (<a href="mailto:danny@cs.huji.ac.il">danny@cs.huji.ac.il</a>). All Rights Reserved.'
	]],
	['istgt',[
		'iSCSI target for FreeBSD (<a href="http://shell.peach.ne.jp/aoyama" target="_blank" rel="noreferrer">http://shell.peach.ne.jp/aoyama</a>).',
		'Copyright © 2008-2018 Daisuke Aoyama (<a href="mailto:aoyama@peach.ne.jp">aoyama@peach.ne.jp</a>). All Rights Reserved.'
	]],
	['jQuery',[
		'A fast, small, and feature-rich JavaScript library (<a href="http://jquery.com" target="_blank" rel="noreferrer">http://jquery.com</a>).',
		'Copyright © 2018 jQuery Foundation. All Rights Reserved.'
	]],
	['LCDproc',[
		'A client/server suite for LCD devices (<a href="http://lcdproc.org" target="_blank" rel="noreferrer">http://lcdproc.org</a>).',
		'Copyright © 1998-2017 William Ferrell, Selene Scriven and many other contributors. All Rights Reserved.'
	]],
	['Lighttpd',[
		'A lighty fast webserver (<a href="http://www.lighttpd.net" target="_blank" rel="noreferrer">http://www.lighttpd.net</a>).',
		'Copyright © 2004 Jan Kneschke (<a href="mailto:jan@kneschke.de">jan@kneschke.de</a>). All Rights Reserved.'
	]],
	['MariaDB',[
		'Multithreaded SQL database. (<a href="https://mariadb.org" target="_blank" rel="noreferrer">https://mariadb.org</a>).',
		'Copyright © 2018 MariaDB Foundation. All Rights Reserved.'
	]],
	['MiniDLNA',[
		'Media server software, with the aim of being fully compliant with DLNA/UPnP-AV clients. (<a href="https://sourceforge.net/projects/minidlna/" target="_blank" rel="noreferrer">https://sourceforge.net/projects/minidlna</a>).',
		'Copyright © 2008-2017  Justin Maggard. All Rights Reserved.'
	]],
	['m0n0wall',[
		'm0n0wall (<a href="http://m0n0.ch/wall/index.php" target="_blank" rel="noreferrer">http://m0n0.ch/wall/index.php</a>).',
		'Copyright © 2002-2006 by Manuel Kasper. All Rights Reserved.',
	]],
	['msmtp',[
		'A SMTP client with a sendmail compatible interface (<a href="http://msmtp.sourceforge.net" target="_blank" rel="noreferrer">http://msmtp.sourceforge.net</a>).',
		'Copyright © 2008 Martin Lambers and others. All Rights Reserved.'
	]],
	['mt-daapd',[
		'Multithread daapd Apple iTunes server (<a href="http://www.fireflymediaserver.org" target="_blank" rel="noreferrer">http://www.fireflymediaserver.org</a>).',
		'Copyright © 2003 Ron Pedde (<a href="mailto:ron@pedde.com">ron@pedde.com</a>). All Rights Reserved.'
	]],
	['Netatalk',[
		'Netatalk is a freely-available Open Source AFP fileserver (<a href="http://netatalk.sourceforge.net" target="_blank" rel="noreferrer">http://netatalk.sourceforge.net</a>).',
		'Copyright © 1990,1996 Regents of The University of Michigan. All Rights Reserved.'
	]],
	['NTFS-3G',[
		'NTFS-3G is a NTFS driver (<a href="http://www.tuxera.com/community/open-source-ntfs-3g/" target="_blank" rel="noreferrer">http://www.tuxera.com/community/open-source-ntfs-3g</a>).',
		'Copyright © 2008-2016 Tuxera Inc. All Rights Reserved.'
	]],
	['Open Virtual Machine Tools',[
		'Open Virtual Machine Tools - Virtualization utilities and drivers (<a href="http://sourceforge.net/projects/open-vm-tools/" target="_blank" rel="noreferrer">http://sourceforge.net/projects/open-vm-tools</a>).',
		'Copyright © 2007-2017 VMware Inc. All rights reserved.'
	]],
	['OpenSSH',[
		'OpenSSH (<a href="http://www.openssh.com" target="_blank" rel="noreferrer">http://www.openssh.com</a>).',
		'Copyright © 1999-2009 OpenBSD. All Rights Reserved.'
	]],
	['pfSense',[
		'pfSense (<a href="http://www.pfsense.com" target="_blank" rel="noreferrer">http://www.pfsense.com</a>).',
		'Copyright © 2004, 2005, 2006 Scott Ullrich. All Rights Reserved.'
	]],
	['PHP',[
		'A server-side scripting language (<a href="http://www.php.net" target="_blank" rel="noreferrer">http://www.php.net</a>).',
		'Copyright © 1999-2019 The PHP Group. All Rights Reserved.'
	]],
	['phpMyAdmin',[
		'Set of PHP-scripts to manage MySQL over the web (<a href="https://www.phpmyadmin.net" target="_blank" rel="noreferrer">https://www.phpmyadmin.net</a>).',
		'Copyright © 2003-2018 phpMyAdmin contributors. All Rights Reserved.'
	]],
	['phpVirtualBox',[
		'phpVirtualBox (<a href="https://github.com/phpvirtualbox/phpvirtualbox/" target="_blank" rel="noreferrer">https://github.com/phpvirtualbox/phpvirtualbox</a>).',
		'Copyright © 2011-2015 Ian Moore, Inc. All Rights Reserved.',
		'Copyright © 2017-2019 Now maintained by Smart Guide Pty Ltd (<a href="mailto:tudor@smartguide.com">tudor@smartguide.com</a>). All Rights Reserved.'
	]],
	['ProFTPD',[
		'A highly configurable FTP server (<a href="http://www.proftpd.org" target="_blank" rel="noreferrer">http://www.proftpd.org</a>).',
		'Copyright © 1999, 2000-2017 The ProFTPD Project. All Rights Reserved.'
	]],
	['Python',[
		'A programming language (<a href="http://www.python.org" target="_blank" rel="noreferrer">http://www.python.org</a>).',
		'Copyright © 2001-2018 Python Software Foundation. All Rights Reserved.'
	]],
	['QuiXplorer',[
		'A Web-based file-management browser (<a href="https://github.com/realtimeprojects/quixplorer" target="_blank" rel="noreferrer">https://github.com/realtimeprojects/quixplorer</a>).',
		'Copyright © Felix C. Stegerman. All Rights Reserved.'
	]],
	['Rsync',[
		'Utility that provides fast incremental file transfer. (<a href="http://www.samba.org/rsync" target="_blank" rel="noreferrer">http://www.samba.org/rsync</a>).',
		'Copyright © 2007 Free Software Foundation. All Rights Reserved.'
	]],
	['Samba',[
		'Suite providing secure, stable and fast file services for all clients using the SMB/CIFS protocol (<a href="http://www.samba.org" target="_blank" rel="noreferrer">http://www.samba.org</a>).',
		'Copyright © 2007 Free Software Foundation. All Rights Reserved.'
	]],
	['sipcalc',[
		'sipcalc (<a href="http://www.routemeister.net/projects/sipcalc/" target="_blank" rel="noreferrer">http://www.routemeister.net/projects/sipcalc</a>).',
		'Copyright © 2003 Simon Ekstrand. All Rights Reserved.'
	]],
	['smartmontools',[
		'Utility programs (smartctl, smartd) to control/monitor storage systems (<a href="http://sourceforge.net/projects/smartmontools/" target="_blank" rel="noreferrer">http://sourceforge.net/projects/smartmontools</a>).',
		'Copyright © 2002-2018 Bruce Allen, Christian Franke. All Rights Reserved.',
	]],
	['Spinner.js',[
		'A spinning activity indicator (<a href="https://github.com/fgnass/spin.js" target="_blank" rel="noreferrer">https://github.com/fgnass/spin.js</a>).',
		'Copyright © 2011-2015 Felix Gnass. All Rights Reserved.'
	]],
	['sudo',[
		'A tool to allow a sysadmin to give limited root privileges. (<a href="http://www.sudo.ws" target="_blank" rel="noreferrer">http://www.sudo.ws</a>).',
		'Copyright © 1994-1996, 1998-2018 Todd C. Miller. All Rights Reserved.'
	]],
	['Syncthing',[
		'Syncthing replaces proprietary sync and cloud services with something open, trustworthy and decentralized. (<a href="https://syncthing.net" target="_blank" rel="noreferrer">https://syncthing.net</a>).',
		'Copyright © Syncthing Development Team. All Rights Reserved.'
	]],
	['syslogd',[
		'Circular log support for FreeBSD syslogd.',
		'Copyright © 2001 Jeff Wheelhouse (<a href="mailto:jdw@wheelhouse.org">jdw@wheelhouse.org</a>).'
	]],
	['tablesorter',[
		'jQuery plugin for turning a standard HTML table into a sortable table (<a href="https://github.com/Mottie/tablesorter" target="_blank" rel="noreferrer">https://github.com/Mottie/tablesorter</a>).',
		'A Github fork of Rob Garrison (<a href="mailto:wowmotty@gmail.com">wowmotty@gmail.com</a>).',
		'Copyright © 2007 Christian Bach (<a href="mailto:christian@tablesorter.com">christian@tablesorter.com</a>).'
	]],
	['TFTPD-HPA',[
		'TFTPD-HPA (Trivial File Transfer Protocol Server) (<a href="http://www.kernel.org/pub/software/network/tftp" target="_blank" rel="noreferrer">http://www.kernel.org/pub/software/network/tftp</a>).',
		'Copyright © 1999, 2000-2009 The tftp-hpa series is maintained by H. Peter Anvin (<a href="mailto:hpa@zytor.com">hpa@zytor.com</a>). All Rights Reserved.'
	]],
	['tmux',[
		'A terminal multiplexer. (<a href="http://tmux.github.io/" target="_blank" rel="noreferrer">http://tmux.github.io</a>).',
		'Copyright © 2010 Nicholas Marriott. All Rights Reserved.'
	]],
	['Transmission',[
		'A fast, easy, and free multi-platform BitTorrent client (<a href="http://www.transmissionbt.com" target="_blank" rel="noreferrer">http://www.transmissionbt.com</a>).',
		'Copyright © 2008-2018 Transmission Project. All Rights Reserved.'
	]],
	['VirtualBox',[
		'Open Source Edition (OSE) & (Guest Additions) (<a href="http://www.virtualbox.org" target="_blank" rel="noreferrer">http://www.virtualbox.org</a>).',
		'Copyright © 2010-2018, Oracle and/or its affiliates. All Rights Reserved.'
	]],
	['VMXNET3',[
		'A NIC driver for FreeBSD (<a href="http://www.vmware.com" target="_blank" rel="noreferrer">http://www.vmware.com</a>).',
		'Copyright © 2010 VMware, Inc. All Rights Reserved.'
	]],
	['XMLStarlet',[
		'Command Line XML Toolkit (<a href="http://xmlstar.sourceforge.net" target="_blank" rel="noreferrer">http://xmlstar.sourceforge.net</a>).',
		'Copyright © 2002 Mikhail Grushinskiy. All Rights Reserved.'
	]],
];
$software_licenses = [
	sprintf('<a href="third-party_licenses/gpl-license.txt">%s</a> (',gettext('GNU General Public License')) .
	sprintf('<a href="third-party_licenses/gpl-license.txt">%s</a>, ',gettext('GPLv2')) .
	sprintf('<a href="third-party_licenses/gpl3-license.txt">%s</a>)',gettext('GPLv3')),
	sprintf('<a href="third-party_licenses/lgpl-license.txt">%s</a>',gettext('GNU Lesser General Public License (LGPL)')),
	sprintf('<a href="third-party_licenses/mpl2-license.txt">%s</a>',gettext('Mozilla Public License Version 2.0 (MPLv2)')),
	sprintf('<a href="third-party_licenses/apple-license.txt">%s</a>',gettext('Apple Public Source License')),
	sprintf('<a href="third-party_licenses/php-license.txt">%s</a>',gettext('PHP License'))
];
$pgtitle = [gettext('Help'), gettext('License & Credits')];
$a_col_width = ['100%'];
$n_col_width = count($a_col_width);
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	create data area
$content = $pagecontent->add_area_data();
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY(['class' => 'donothighlight']);
$thead->ins_titleline(gettext('License'),$n_col_width);
$td = $tbody->addTR()->addTDwC('lcebl');
$td->addP()->addElement('strong',[],'XigmaNAS® is copyright © 2018-2019 XigmaNAS® (<a href="mailto:info@xigmanas.com">info@xigmanas.com</a>).<br />All Rights Reserved.');
$td->addP()->addElement('strong',[],'XigmaNAS® is a registered trademark of Michael Zoon (<a href="mailto:zoon01@xigmanas.com">zoon01@xigmanas.com</a>).<br />All Rights Reserved.');
$td->addP([],'The compilation of software, code and documentation known as XigmaNAS is distributed under the following terms:');
$td->addP([],'Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:');
$td->
	addElement('ol',['start' => '1','style' => 'padding-left: 2em;'])->
		insElement('li',[],'Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.')->
		insElement('li',[],'Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.');
$td->addP([],
	'THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ' .
	'ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED ' .
	'WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE ' .
	'DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ' .
	'ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES ' .
	'(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; ' .
	'LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ' .
	'ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT ' .
	'(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS ' .
	'SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.');
$td->addP([],
	'The views and conclusions contained in the software and documentation are those of the authors and should not be interpreted as representing official policies of XigmaNAS, either expressed or implied.');
$a_col_width = ['20%','25%','55%'];
$n_col_width = count($a_col_width);
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY(['class' => 'donothighlight']);
$thead->
	ins_separator($n_col_width)->
	ins_titleline(gettext('Credits'),$n_col_width)->
	addTR()->
		insTH(['colspan' => '3','class' => 'lcebld'],sprintf(gettext('The following persons have contributed to %s:'), get_product_name()));
foreach($contributors as $row):
	$tbody->
		addTR()->
			insTDwC('lcell',$row[0])->
			push()->
			addTDwC('lcell')->
				insA(['href' => sprintf('mailto:%s',$row[1])],$row[1])->
			pop()->
			insTDwC('lcebl',$row[2]);
endforeach;
$table = $content->add_table_data_settings();
$table->ins_colgroup_data_settings();
$thead = $table->addTHEAD();
$tbody = $table->addTBODY(['class' => 'donothighlight']);
$thead->
	c2_separator()->
	c2_titleline(gettext('Software Used'))->
	push()->
	addTR()->
		insTH(['colspan' => '2','class' => 'lcebld'],sprintf(gettext('%s is based upon/includes various free software packages.'),get_product_name()))->
	pop()->
	addTR()->
		insTH(['colspan' => '2','class' => 'lcebld'],sprintf(gettext('%s would like to thank the authors of this software for their efforts.'),get_product_name()));
foreach($software as $row):
	$tbody->
		addTR()->
			insTDwC('lcelld',$row[0])->
			insTDwC('lcebl',implode('<br />',$row[1]));
endforeach;
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',['100%']);
$tbody = $table->addTBODY(['class' => 'donothighlight']);
$tbody->
	addTR()->
		insTDwC('lcebld',sprintf(gettext('Some of the software used for %s are under the following licenses:'),get_product_name()));
foreach($software_licenses as $row):
	$tbody->
		addTR()->
			insTDwC('lcebld',$row);
endforeach;
$document->render();
