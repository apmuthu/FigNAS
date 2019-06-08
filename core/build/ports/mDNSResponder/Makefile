PORTNAME=		mDNSResponder
PORTVERSION=	878.200.35
PORTREVISION=	0
CATEGORIES=	net dns
COMMENT=	Apple's mDNSResponder
MAINTAINER=	zoon01@xigmanas.com

MASTER_SITES=	http://www.opensource.apple.com/tarballs/mDNSResponder/
DISTNAME=	${PORTNAME}-${PORTVERSION}

USE_LDCONFIG=	YES

MAKE_ARGS+=	os=freebsd LOCALBASE=${PREFIX}
BUILD_WRKSRC=	${WRKSRC}/mDNSPosix
# lib/libdns_sd.so.1 sbin/mdnsd
CONFLICTS_INSTALL=      avahi-libdns- openmdns-

OPTIONS_DEFINE=	IPV6
USES=		cpe gmake

CPE_VENDOR=	apple
CPE_PRODUCT=	${PORTNAME:tl}

.include <bsd.port.pre.mk>

pre-build:
	@${REINPLACE_CMD} -e 's|-lpthread|${PTHREAD_LIBS}|g' \
		-e 's|mDNSResponder.8|mDNSResponderPosix.8|g' \
		${WRKSRC}/mDNSPosix/Makefile
	@${REINPLACE_CMD} -e '/^CFLAGS_DEBUG/ s|-Os|${CFLAGS}|' ${WRKSRC}/mDNSPosix/Makefile
	@${REINPLACE_CMD} -e 's|\(#define IPV6_2292_PKTINFO \).*|\1IPV6_PKTINFO|; s|\(#define IPV6_2292_HOPLIMIT \).*|\1IPV6_HOPLIMIT|' \
		${WRKSRC}/mDNSPosix/mDNSUNP.h

do-install:
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSClientPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSIdentify ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSNetMonitor ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSProxyResponderPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSResponderPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/libdns_sd.so ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mdnsresponder.in ${XIGMANAS_ROOTFS}/etc/rc.d/mdnsresponder

.include <bsd.port.post.mk>
