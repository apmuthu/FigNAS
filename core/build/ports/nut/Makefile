PORTNAME=		nut
PORTVERSION=	2.7.4
PORTREVISION=	7
CATEGORIES=		sysutils
COMMENT=		Network UPS tools
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=	http://www.networkupstools.org/source/${PORTVERSION:R}/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}
EXTRACT_ONLY=	${DISTNAME}${EXTRACT_SUFX}

GNU_CONFIGURE=	yes
USE_LDCONFIG=	yes
USES=		autoreconf gmake pkgconfig libtool

NUT_USER=		root
NUT_GROUP=		wheel
NUT_PIDDIR=		/var/run
NUT_STATEDIR=		/var/db/nut
NUT_SYSCONFDIR=		/var/etc
NUT_DATADIR=		/usr/local/etc/${PORTNAME}
NUT_DRVDIR=		/usr/local/libexec/${PORTNAME}

CONFIGURE_ARGS+=	--sysconfdir=${NUT_SYSCONFDIR} \
		--program-transform-name="" \
		--localstatedir=${NUT_STATEDIR} \
		--datadir=${NUT_DATADIR} \
		--with-devd-dir=${PREFIX}/etc/devd \
		--with-drvpath=${NUT_DRVDIR} \
		--with-statepath=${NUT_STATEDIR} \
		--with-altpidpath=${NUT_PIDDIR} \
		--with-pidpath=${NUT_PIDDIR} \
		--without-pkgconfig-dir \
		--without-cgi \
		--with-serial \
		--with-usb=auto \
		--with-snmp \
		--with-neon \
		--with-powerman \
		--without-dev \
		--with-user=${NUT_USER} \
		--with-group=${NUT_GROUP} \
		--with-wrap

.include <bsd.port.pre.mk>

LIB_DEPENDS+=	libnetsnmp.so:net-mgmt/net-snmp
LIB_DEPENDS+=   libneon.so:www/neon
LIB_DEPENDS+=	libpowerman.so:sysutils/powerman
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
LIB_DEPENDS+=	libfreeipmi.so:sysutils/freeipmi
CONFIGURE_ARGS+=--with-ipmi \
		--with-freeipmi
.endif

pre-install:
	@${REINPLACE_CMD} -e 's/device-name\*/cdev/g' ${WRKSRC}/scripts/devd/nut-usb.conf

do-install:
	@${MKDIR} -pv ${XIGMANAS_ROOTFS}${PREFIX}/etc/devd
	@${INSTALL_SCRIPT} -v ${WRKSRC}/scripts/devd/nut-usb.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/devd/nut-usb.conf

	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/cmdvartab ${XIGMANAS_ROOTFS}/${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/driver.list ${XIGMANAS_ROOTFS}/${NUT_DATADIR}

	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/al175 ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart-old ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcupsd-ups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkin ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkinunv ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfcom ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfortress ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestuferrups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_ser ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone-outlet ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/.libs/dummy-ups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/etapro ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/everups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/gamatronic ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/genericups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/isbmex ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/ivtscd ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert-esp2 ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/masterguard ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/metasys ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-shut ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-utalk ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/microdowell ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/netxml-ups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_atcl_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_qx ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nut-ipmipsu ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
.endif
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/oldmge-shut ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/oneac ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/optiups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powercom ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerman-pdu ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerpanel ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/rhino ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/richcomm_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_ser ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/safenet ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/skel ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/snmp-ups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/solis ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite_usb ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplitesu ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upscode2 ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/usbhid-ups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/victronups ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/libupsclient.so.4 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${LN} -f -v -s /usr/local/lib/libupsclient.so.4 ${XIGMANAS_ROOTFS}/usr/local/lib/libupsclient.so

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsc ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upscmd ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upslog ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsmon ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsrw ${XIGMANAS_ROOTFS}/usr/local/bin

	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/upsd ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upsdrvctl ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/upssched ${XIGMANAS_ROOTFS}/usr/local/sbin

	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upslog.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut_upslog
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upsmon.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut_upsmon
	@${INSTALL_SCRIPT} -v ${FILESDIR}/upssched-cmd.in ${XIGMANAS_ROOTFS}/usr/local/bin/upssched-cmd

.include <bsd.port.post.mk>
