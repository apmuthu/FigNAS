PORTNAME=	lighttpd
PORTVERSION=	1.4.54
PORTREVISION=	0
CATEGORIES=	www

COMMENT=	A secure, fast, compliant, and very flexible Web Server
MAINTAINER=	zoon01@xigmanas.com

MASTER_SITES=	https://download.lighttpd.net/lighttpd/releases-1.4.x/

LIB_DEPENDS+=	libpcre.so:${PORTSDIR}/devel/pcre
LIB_DEPENDS+=	libuuid.so:${PORTSDIR}/misc/e2fsprogs-libuuid \
		libsqlite3.so:${PORTSDIR}/databases/sqlite3
LIB_DEPENDS+=	libmemcached.so:${PORTSDIR}/databases/libmemcached
LIB_DEPENDS+=	libev.so:${PORTSDIR}/devel/libev

GNU_CONFIGURE=	yes
USES=		autoreconf gmake libtool pkgconfig tar:xz
#USE_CSTD=	gnu99
USE_BZIP2=	yes

OPENSSL_USES=	ssl
OPENSSL_CONFIGURE_WITH=	openssl
OPENSSL_CONFIGURE_ON=	--with-openssl-includes=${OPENSSLINC} --with-openssl-libs=${OPENSSLLIB}

# Only used for webdav
USE_GNOME+=	libxml2
#WEBDAV_LIB_DEPENDS=	libuuid.so:misc/e2fsprogs-libuuid \
#			libsqlite3.so:databases/sqlite3

CPPFLAGS+=	-I${LOCALBASE}/include
LDFLAGS+=	-L${LOCALBASE}/lib

.include <bsd.port.options.mk>

CONFIGURE_ARGS+=	--libdir=${PREFIX}/lib/lighttpd --enable-static \
			--sysconfdir=/var/etc/ \
			--enable-lfs \
			--enable-ipv6 \
			--with-libev \
			--without-geoip \
			--without-mysql \
			--without-ldap \
			--with-openssl \
			--without-lua \
			--with-bzip2 \
			--with-webdav-props \
			--with-webdav-locks

do-install:
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/lighttpd ${XIGMANAS_ROOTFS}/usr/local/sbin

		@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_access.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_accesslog.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_alias.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_auth.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_authn_file.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_cgi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_compress.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_deflate.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_dirlisting.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_evasive.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_evhost.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_expire.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_extforward.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_fastcgi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_flv_streaming.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_indexfile.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_openssl.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_proxy.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_redirect.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_rewrite.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_rrdtool.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_scgi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_secdownload.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_setenv.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_simple_vhost.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_sockproxy.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_ssi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_staticfile.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_status.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_uploadprogress.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_userdir.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_usertrack.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_vhostdb.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_webdav.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_wstunnel.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd

.include <bsd.port.mk>
