PORTNAME=		nss_ldap
PORTVERSION=	1.0
PORTREVISION=   0
CATEGORIES=		net
COMMENT=		RFC 2307 NSS module
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build

BUILD_DEPENDS=	${NONEXISTENT}:net/nss_ldap

do-install:
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/net/nss_ldap/work/nss_ldap-*/nss_ldap.so ${XIGMANAS_ROOTFS}/usr/local/lib/nss_ldap.so.1
	@${LN} -fsv /var/etc/ldap.conf ${XIGMANAS_ROOTFS}/usr/local/etc/nss_ldap.conf
	@${LN} -fsv /var/etc/ldap.secret ${XIGMANAS_ROOTFS}/usr/local/etc/nss_ldap.secret

.include <bsd.port.mk>
