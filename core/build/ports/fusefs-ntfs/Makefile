PORTNAME=			fusefs-ntfs
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=			Mount NTFS partitions and disk images
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=	${NONEXISTENT}:sysutils/fusefs-ntfs

.include <bsd.port.pre.mk>
.if ${OSVERSION} < 1000000
BUILD_DEPENDS+=	${NONEXISTENT}:sysutils/fusefs-kmod
.endif

BUILD_DEPENDS+=	${NONEXISTENT}:sysutils/fusefs-libs \
		${NONEXISTENT}:devel/libublio

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/fusefs.in ${XIGMANAS_ROOTFS}/etc/rc.d/fusefs

.if ${OSVERSION} < 1000000
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/mount_fusefs/mount_fusefs ${XIGMANAS_ROOTFS}/usr/local/sbin
.else
	@${CP} -pv /sbin/mount_fusefs ${XIGMANAS_ROOTFS}/sbin
.endif
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/src/.libs/ntfs-3g ${XIGMANAS_ROOTFS}/usr/local/bin

.if ${OSVERSION} < 1000000
#	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/fuse4bsd*/fuse_module/fuse.ko ${XIGMANAS_ROOTFS}/boot/kernel
	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/modules/fuse/fuse.ko ${XIGMANAS_ROOTFS}/boot/kernel
.else
	@${CP} -pv /boot/kernel/fuse.ko ${XIGMANAS_ROOTFS}/boot/kernel
.endif
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/libntfs-3g/.libs/libntfs-3g.so.88 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-libs/work/stage/usr/local/lib/lib*.so ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-libs/work/stage/usr/local/lib/lib*.so.2 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/devel/libublio/work/libublio-*/libubl*.so ${XIGMANAS_ROOTFS}/usr/local/lib

	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${XIGMANAS_ROOTFS}/sbin/mount_ntfs
	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${XIGMANAS_ROOTFS}/sbin/mount_ntfs-3g

.include <bsd.port.post.mk>
