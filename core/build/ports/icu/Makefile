PORTNAME=		xigmanas-icu
PORTVERSION=		1.0
PORTREVISION=		0
CATEGORIES=		devel

COMMENT=		International Components for Unicode (from IBM)
MAINTAINER=		zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=	${NONEXISTENT}:devel/icu
DEP_STAGEDIR=	${PORTSDIR}/devel/icu/work/stage

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
# bin
.	for file in uconv pkgdata makeconv icuinfo genrb gendict gencnval gencfu genbrk derb
		@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/${file} ${XIGMANAS_ROOTFS}${PREFIX}/bin
.	endfor
.	for file in icupkg gensprep gennorm2 gencmn genccode
		@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/${file} ${XIGMANAS_ROOTFS}${PREFIX}/sbin
.	endfor
	@${CP} -v ${DEP_STAGEDIR}${PREFIX}/bin/icu-config ${XIGMANAS_ROOTFS}${PREFIX}/bin
# lib
	${RM} ${XIGMANAS_ROOTFS}${PREFIX}/lib/icu/current
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib ${XIGMANAS_ROOTFS}${PREFIX}
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/libdata ${XIGMANAS_ROOTFS}${PREFIX}
# include
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/include ${XIGMANAS_ROOTFS}${PREFIX}
# share
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/share/icu ${XIGMANAS_ROOTFS}${PREFIX}/share

.include <bsd.port.mk>
