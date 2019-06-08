PORTNAME=		xigmanas-virtualbox-ose-additions
PORTVERSION=	1.0
#PORTREVISION=	1
CATEGORIES=		emulators
MAINTAINER=		aoyama@peach.ne.jp
COMMENT=		VirtualBox additions for FreeBSD guests

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build

BUILD_DEPENDS=	${NONEXISTENT}:emulators/virtualbox-ose-additions:build

BLDSRC=${PORTSDIR}/emulators/virtualbox-ose-additions/work/VirtualBox-*/
WRKSRC=${WRKDIR}/virtualbox-ose-additions

.include <../xigmanas.mk>
.if ${XIGMANAS_ARCH} == amd64
ARCH_DIR=	freebsd.amd64
.else
ARCH_DIR=	freebsd.x86
.endif

do-install:
	@${INSTALL_DATA} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/src/vboxguest.ko ${XIGMANAS_ROOTFS}/boot/modules
	@${INSTALL_PROGRAM} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/VBoxControl ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/VBoxService ${XIGMANAS_ROOTFS}/${PREFIX}/sbin

.include <bsd.port.mk>