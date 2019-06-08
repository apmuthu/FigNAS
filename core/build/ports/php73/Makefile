PORTNAME=		php73
PORTVERSION=	7.3.6
PORTREVISION=	0
CATEGORIES=		lang
COMMENT=		PHP7 Scripting Language
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=		${MASTER_SITE_PHP:S,$,:release,}
MASTER_SITE_SUBDIR=	distributions/:release
DISTNAME=		php-${PORTVERSION:S/.r/RC/}
DISTFILES=			${DISTNAME}${EXTRACT_SUFX}:release
USES+=			compiler:c++11-lib tar:xz cpe iconv
USE_CXXSTD=		gnu++11
CPE_PRODUCT=		php
NO_OPTIONS_SORT=	yes

LIB_DEPENDS=		libpcre2-8.so:devel/pcre2
LIB_DEPENDS+=		libcurl.so:ftp/curl
LIB_DEPENDS+=		libmcrypt.so:security/libmcrypt
LIB_DEPENDS+=		libonig.so:devel/oniguruma
LIB_DEPENDS+=		libfreetype.so:print/freetype2
LIB_DEPENDS+=		libpng.so:graphics/png
LIB_DEPENDS+=		libjpeg.so:graphics/jpeg
LIB_DEPENDS+=		libvpx.so:multimedia/libvpx
LIB_DEPENDS+=		libgmp.so:math/gmp
LIB_DEPENDS+=		libc-client4.so.9:mail/cclient
LIB_DEPENDS+=		libicui18n.so:devel/icu
LIB_DEPENDS+=		libzip.so:archivers/libzip

CPPFLAGS+=	-DU_USING_ICU_NAMESPACE=1
CONFIGURE_ENV=	CPPFLAGS="-I${LOCALBASE}/include"
USES+=          autoreconf:build
AUTOTOOLSFILES=	 scripts/phpize.in
USE_GNOME=	 libxml2
USE_OPENLDAP=	 yes
GNU_CONFIGURE=	 yes
NO_PKG_REGISTER= yes
USES+=		 ssl sqlite

CONFIGURE_ARGS+=	\
		--with-pear \
		--with-openssl \
		--with-gettext \
		--enable-libxml \
		--enable-mysqlnd \
		--with-mysql=mysqlnd \
		--with-mysqli=mysqlnd \
		--with-libxml-dir=${LOCALBASE} \
		--with-pcre-regex=${LOCALBASE} \
		--with-zlib-dir=/usr \
		--with-config-file-path=/usr/local/etc \
		--with-config-file-scan-dir=/usr/local/etc/php \
		--enable-pdo \
		--with-pdo-mysql=mysqlnd \
		--with-pdo-sqlite=${LOCALBASE} \
		--with-sqlite3 \
		--with-iconv-dir=${LOCALBASE} \
		--with-bz2=/usr \
		--with-curl=${LOCALBASE} \
		--with-gd \
		--with-freetype-dir=${LOCALBASE} \
		--with-jpeg-dir=${LOCALBASE} \
		--with-png-dir=${LOCALBASE} \
		--enable-intl \
		--with-ldap=${LOCALBASE} \
		--enable-mbstring \
		--with-onig=${LOCALBASE} \
		--with-pcre-dir=${LOCALBASE} \
		--with-mcrypt=${LOCALBASE} \
		--with-gmp=${LOCALBASE} \
		--enable-bcmath \
		--enable-hash \
		--with-mhash \
		--enable-ftp \
		--with-imap=${LOCALBASE} \
		--with-imap-ssl=${OPENSSLBASE} \
		--enable-soap \
		--enable-sockets \
		--enable-sysvmsg \
		--enable-sysvsem \
		--enable-sysvshm \
		--enable-tokenizer \
		--with-xmlrpc \
		--enable-zip \
		--with-zlib=/usr \
		--enable-opcache \

OPTIONS=	CLI "Build CLI version" on \
		CGI "Build CGI version" on \
		FPM "Build FPM version" off \
		IPV6 "Enable ipv6 support" on \
		APACHE "Build Apache module" off \
		AP2FILTER " Use Apache 2.x filter interface (experimental)" off \
		EMBED "Build embedded library" off \
		DEBUG "Enable debug" off \
		PHPDBG "Enable debug" off \
		DTRACE "Enable DTrace support" off \
		LINKTHR "Link thread lib (for threaded extensions)" on \
		ZTS "Force Zend Thread Safety (ZTS) build" off

CONFLICTS=	php56-* php70-* php71-* php72-*

.if defined(WITH_DTRACE)
.if ${OSVERSION} < 900000
IGNORE=		cannot be built with DTrace support on FreeBSD < 9.x
.endif
CONFIGURE_ARGS+=--enable-dtrace
.endif

post-patch:
	@${TOUCH} ${WRKSRC}/ext/php_config.h
	@${REINPLACE_CMD} "s|^\(extension_dir\)|; \1|" ${WRKSRC}/php.ini-*

pre-configure:
	@(cd ${WRKSRC} && ${SETENV} MAKE=${MAKE_CMD} ./buildconf --force)

post-build:
	@${ECHO_CMD} "PHP_VER=73" > ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_VERSION=${PORTVERSION}" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_SAPI=${PHP_SAPI}" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_EXT_INC=pcre spl" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} -n "PHP_EXT_DIR=" >> ${WRKDIR}/php.conf
	@${SH} ${WRKSRC}/scripts/php-config --extension-dir | ${SED} -ne 's,^${PREFIX}/lib/php/,,p' >> ${WRKDIR}/php.conf

test: build
	@(cd ${WRKSRC} && ${MAKE} test)

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cgi/php-cgi ${XIGMANAS_ROOTFS}/${PREFIX}/bin/
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cli/php ${XIGMANAS_ROOTFS}/${PREFIX}/bin/
	@${INSTALL_SCRIPT} -v ${FILESDIR}/php-cgi-websrv ${XIGMANAS_ROOTFS}/${PREFIX}/bin/
	@${CP} -pv ${FILESDIR}/php.ini ${XIGMANAS_ROOTFS}/${PREFIX}/etc

	@${MKDIR} ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20180731
	${INSTALL_DATA} ${WRKSRC}/ext/opcache/.libs/opcache.so ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20180731
	@${CP} -pv ${FILESDIR}/opcache.ini ${XIGMANAS_ROOTFS}/${PREFIX}/etc/php

.include <bsd.port.mk>
