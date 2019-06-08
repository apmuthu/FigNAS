PORTNAME=		autosnapshot
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Auto Snapshot tool
MAINTAINER=		aoyama@peach.ne.jp

NO_PKG_REGISTER=yes

DISTFILES=
NO_BUILD=	yes
NO_WRKSUBDIR=	yes

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot.in ${XIGMANAS_ROOTFS}/etc/rc.d/autosnapshot

.include <bsd.port.mk>