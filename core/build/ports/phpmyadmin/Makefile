PORTNAME=	phpMyAdmin
DISTVERSION=	4.9.0
PORTREVISION=	0
CATEGORIES=	databases www
MASTER_SITES=	https://files.phpmyadmin.net/${PORTNAME}/${DISTVERSION}/
DISTNAME=	${PORTNAME}-${DISTVERSION}-all-languages

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Set of PHP-scripts to manage MySQL over the web

LICENSE=	GPLv2

DISABLE_VULNERABILITIES=yes
USES=		cpe tar:xz
CPE_PRODUCT=	${PORTNAME:tl}
ETCDIR=	${WWWDIR}

NO_ARCH=	yes
NO_BUILD=	yes

CFG_FILE=	config.inc.php

SUB_LIST+=	PKGNAME=${PKGNAME}	        \
		CFG_FILE=${WWWDIR}/${CFG_FILE}

.SILENT:

do-install:
	cd ${WRKSRC} ; \
	for src in $$( ${FIND} . ! -name .cvsignore ! -name '*.bak' ) ; do \
	    dst=${STAGEDIR}${WWWDIR}$${src#.} ; \
	    if ${TEST} -d "$$src" ; then \
		${MKDIR} "$$dst" ; \
	    else \
		${INSTALL_DATA} "$$src" "$$dst" ; \
	    fi \
	done

# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${RM} -rf ${STAGEDIR}${WWWDIR}$${dst}/test
	@${RM} -rf ${STAGEDIR}${WWWDIR}$${dst}/setup
	@${CP} -Rpv ${STAGEDIR}${WWWDIR} ${XIGMANAS_ROOTFS}${PREFIX}/www

.include <bsd.port.mk>
