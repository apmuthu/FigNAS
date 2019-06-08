PORTNAME=	php73-APCu
PORTREVISION=	0
CATEGORIES=	devel

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	APC User Caching

DISTFILES?=
NO_BUILD?=	         yes
NO_PKG_REGISTER?=	 yes
DEPENDS_TARGET=	clean build stage

DEP_STAGEDIR=	${PORTSDIR}/devel/php73-APCu/work-php73/stage
BUILD_DEPENDS=	${NONEXISTENT}:devel/php73-APCu

do-install:
# XigmaNAS install
	@${CP} -pv ${FILESDIR}/apcu.ini ${XIGMANAS_ROOTFS}/${PREFIX}/etc/php
	@${MKDIR} ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20180731
	${INSTALL_DATA} ${DEP_STAGEDIR}/usr/local/lib/php/20180731/apcu.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/php/extensions/no-debug-non-zts-20180731

.include <bsd.port.mk>
