PORTNAME=		ipmitool
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils

COMMENT=		CLI tool for IPMI-enabled devices
MAINTAINER=		zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=		clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/sysutils/ipmitool
DEP_STAGEDIR=		${PORTSDIR}/sysutils/ipmitool/work/stage

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/ipmitool ${XIGMANAS_ROOTFS}/usr/local/bin

.include <bsd.port.mk>