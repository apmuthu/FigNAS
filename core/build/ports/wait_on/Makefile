PORTNAME=		wait_on
PORTVERSION=		1.1
PORTREVISION=		1
CATEGORIES=		sysutils

COMMENT=		Enable shell scripts to monitor directories for new files
MAINTAINER=		aoyama@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:sysutils/wait_on
DEP_STAGEDIR=		${PORTSDIR}/sysutils/wait_on/work/stage

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/wait_on ${XIGMANAS_ROOTFS}/usr/local/bin

.include <bsd.port.mk>
