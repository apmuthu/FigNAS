PORTNAME=		gzip
PORTVERSION=	1.9
PORTREVISION?=  0
CATEGORIES=	archivers
COMMENT=	A compression utility designed to be a replacement for compress
MAINTAINER=	zoon01@xigmanas.com

MASTER_SITES=				${MASTER_SITE_GNU}
MASTER_SITE_SUBDIR=	${PORTNAME}

GNU_CONFIGURE=	yes

post-configure:
	@${REINPLACE_CMD} -e 's|^SHELL =.*|SHELL=/bin/sh|' ${WRKSRC}/Makefile

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/gzip ${XIGMANAS_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/gunzip ${XIGMANAS_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/zcat ${XIGMANAS_ROOTFS}/usr/bin

.include <bsd.port.mk>