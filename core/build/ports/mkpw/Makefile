PORTNAME=		mkpw
PORTVERSION=		1.1
CATEGORIES=		sysutils
COMMENT=		Password generation tool
MAINTAINER=		zoon01@xigmanas.com

DISTFILES?=
NO_PKG_REGISTER=	yes

do-build:
	@${MKDIR} -p ${WRKSRC}
	@${TAR} -xvf ${FILESDIR}/${PORTNAME}.tar -C ${WRKSRC}
	@${MAKE} -C ${WRKSRC}

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME} ${XIGMANAS_ROOTFS}/usr/sbin

.include <bsd.port.mk>
