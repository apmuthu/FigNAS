PORTNAME=		exfat
DISTVERSIONPREFIX= v
PORTVERSION=	   1.2.8
PORTREVISION=	   0
CATEGORIES=	   sysutils
PKGNAMEPREFIX=	fusefs-

COMMENT=	Full-featured exFAT FS implementation as a FUSE module
MAINTAINER=	zoon01@xigmanas.com

LICENSE=	GPLv2

LIB_DEPENDS=	libublio.so:devel/libublio

USE_GITHUB=     yes
GH_ACCOUNT=     relan

USES=		autoreconf fuse
GNU_CONFIGURE=	yes
CPPFLAGS+=	-I${LOCALBASE}/include -DUSE_UBLIO
LDFLAGS+=	-L${LOCALBASE}/lib
LIBS+=		-lublio

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/fuse/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin
	${INSTALL_PROGRAM} ${WRKSRC}/fsck/exfatfsck ${STAGEDIR}${PREFIX}/bin
	${LN} -fs ${PREFIX}/bin/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin/mount.exfat

# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/mount.exfat-fuse ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/exfatfsck ${XIGMANAS_ROOTFS}/usr/local/bin
	@${LN} -fsv ${PREFIX}/bin/mount.exfat-fuse ${XIGMANAS_ROOTFS}/usr/local/bin/mount.exfat

.include <bsd.port.mk>
