PORTNAME=		pam_ldap
PORTVERSION=	1.0
PORTREVISION=	1
CATEGORIES=		security
COMMENT=		A pam module for authenticating with LDAP
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	        yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	 yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS= yes
DEPENDS_TARGET=	clean build
DISABLE_VULNERABILITIES=yes

BUILD_DEPENDS=	${NONEXISTENT}:security/pam_ldap

do-install:
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/pam_ldap/work/pam_ldap-*/pam_ldap.so ${XIGMANAS_ROOTFS}/usr/local/lib
	@${LN} -fsv /var/etc/ldap.conf ${XIGMANAS_ROOTFS}/usr/local/etc/ldap.conf
	@${LN} -fsv /var/etc/ldap.secret ${XIGMANAS_ROOTFS}/usr/local/etc/ldap.secret

.include <bsd.port.mk>
