PORTNAME=		ca_root_nss
PORTVERSION=		${VERSION_NSS}
PORTREVISION=		1
CATEGORIES=		security

COMMENT=		The root certificate bundle from the Mozilla Project
MAINTAINER=		aoyama@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/security/ca_root_nss
DEP_STAGEDIR=		${PORTSDIR}/security/ca_root_nss/work/stage

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${MKDIR} -p ${XIGMANAS_ROOTFS}/usr/local/etc/ssl
	@${INSTALL_DATA} -v ${DEP_STAGEDIR}${PREFIX}/etc/ssl/cert.pem.sample ${XIGMANAS_ROOTFS}/usr/local/etc/ssl/cert.pem
	@${LN} -fsv ${PREFIX}/etc/ssl/cert.pem ${XIGMANAS_ROOTFS}/etc/ssl/cert.pem

.include <bsd.port.mk>
