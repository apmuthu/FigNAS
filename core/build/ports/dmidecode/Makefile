PORTNAME=	dmidecode
PORTVERSION=	3.2
PORTREVISION?=	0
CATEGORIES=	sysutils
MASTER_SITES=	SAVANNAH

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	A tool for dumping DMI (SMBIOS) contents in human-readable format

EXTRACT_SUFX= .tar.xz
LICENSE=	GPLv2


.include <bsd.port.pre.mk>

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/dmidecode ${XIGMANAS_ROOTFS}/usr/local/sbin
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
	@${INSTALL_PROGRAM} -v ${WRKSRC}/biosdecode ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ownership ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/vpddecode ${XIGMANAS_ROOTFS}/usr/local/sbin
.endif

.include <bsd.port.post.mk>
