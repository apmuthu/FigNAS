PORTNAME=		spindown
PORTVERSION=		1.0
PORTREVISION=		1
CATEGORIES=		sysutils

COMMENT=		SCSI/firewire harddrive spindown daemon
MAINTAINER=		aoyama@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:sysutils/spindown
DEP_STAGEDIR=		${PORTSDIR}/sysutils/spindown/work/stage

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/spindown ${XIGMANAS_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>
