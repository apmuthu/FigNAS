PORTNAME=		netatalk
PORTVERSION=	3.1.12
PORTREVISION=	0
PORTEPOCH=	1
CATEGORIES=	net
MASTER_SITES=	SF
PKGNAMESUFFIX=  3

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	File server for Mac OS X

LICENSE=	GPLv2

LIB_DEPENDS=	libgcrypt.so:security/libgcrypt \
		libevent.so:devel/libevent

USES=           bdb:5+ gettext gmake iconv libtool perl5 pkgconfig shebangfix ssl tar:bzip2

GNU_CONFIGURE=	yes
USE_LDCONFIG=	yes
#USE_RC_SUBR=	netatalk
SHEBANG_GLOB=   afpstats

CONFIGURE_ARGS+=	--with-pkgconfdir=${PREFIX}/etc \
			--with-libgcrypt-dir=${LOCALBASE} \
			--with-uams-path=/etc/uams \
			--with-bdb=${LOCALBASE} \
			${ICONV_CONFIGURE_BASE} \
			--localstatedir=/var/db \
			--sysconfdir=/var/etc \
			--disable-bundled-libevent \
			--with-libevent-header=${LOCALBASE}/include \
			--with-libevent-lib=${LOCALBASE}/lib \
			--with-ssl-dir=${OPENSSLBASE}

LIB_DEPENDS+=	libsunacl.so:sysutils/libsunacl

CONFIGURE_ARGS+= 	--without-dtrace \
			--without-kerberos \
			--with-pam \
			--disable-zeroconf \
			--without-ldap \
			--disable-sendfile \
			--with-mysql-config=/nonexistent \
			--with-acls \
			--disable-tcp-wrappers

.include <bsd.port.options.mk>

#CONFLICTS=	bigloo-2.* cap-6.0.* tct-1.* netatalk-[12]* yudit-[0-9]*

post-patch:
	@${REINPLACE_CMD} -e 's|%%DB_NAME%%|${BDB_INCLUDE_DIR:T}| ; \
	    	s|%%DB_LIB%%|-l${BDB_LIB_NAME}|g ; \
		s|%%LOCALBASE%%|${LOCALBASE}|g' \
		${WRKSRC}/configure

post-stage:
	@${RM} -f ${STAGEDIR}${PREFIX}/etc/afp.conf \
		${STAGEDIR}${PREFIX}/etc/extmap.conf

post-install:
	${INSTALL_SCRIPT} ${WRKSRC}/contrib/macusers/macusers \
		${STAGEDIR}${PREFIX}/bin/macusers
	[ -f ${STAGEDIR}${PREFIX}/etc/afp.conf ] || \
		${INSTALL_DATA} -c ${STAGEDIR}${PREFIX}/etc/afp.conf.dist ${STAGEDIR}${PREFIX}/etc/afp.conf
	[ -f ${STAGEDIR}${PREFIX}/etc/extmap.conf ] || \
		${INSTALL_DATA} -c ${STAGEDIR}${PREFIX}/etc/extmap.conf.dist ${STAGEDIR}${PREFIX}/etc/extmap.conf

# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"

	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/dbd ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/afpd ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_metad ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_dbd ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/netatalk ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_LIB} -v ${STAGEDIR}${PREFIX}/lib/libatalk.so.18 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_DATA} -v ${STAGEDIR}${PREFIX}/etc/extmap.conf.dist ${XIGMANAS_ROOTFS}/usr/local/etc/extmap.conf

	@${MKDIR} -v ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx_pam.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx_passwd.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx2_pam.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx2_passwd.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_guest.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_pam.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_passwd.so ${XIGMANAS_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_randnum.so ${XIGMANAS_ROOTFS}/etc/uams

	@${INSTALL_SCRIPT} -v ${FILESDIR}/netatalk.in ${XIGMANAS_ROOTFS}/etc/rc.d/netatalk

	@cd ${XIGMANAS_ROOTFS}/etc/uams && ${LN} -fsv uams_passwd.so uams_clrtxt.so && ${LN} -fsv uams_dhx_passwd.so uams_dhx.so && ${LN} -fsv uams_dhx2_passwd.so uams_dhx2.so

.include <bsd.port.mk>
