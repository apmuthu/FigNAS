PORTNAME=	beadm
PORTVERSION=	1.3.0
PORTREVISION=	0
CATEGORIES=	sysutils
MASTER_SITES=	\
		LOCAL/bdrewery/${PORTNAME} \
		http://mirror.shatow.net/freebsd/${PORTNAME}/ \
		GH

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Solaris-like utility to manage Boot Environments on ZFS

USE_GITHUB=	yes
GH_ACCOUNT=	vermaden
GH_TAGNAME=	14e2700b0369876d5288cc6a11fdc146b2e267ac

NO_BUILD=	YES

CONFLICTS_INSTALL=	beadm-devel-*

do-install:
# XigmaNAS install
	${INSTALL_SCRIPT} -v ${WRKSRC}/beadm ${XIGMANAS_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>