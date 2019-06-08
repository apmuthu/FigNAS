PORTNAME=		python2
PORTVERSION=		${PYTHON_MAJOR_VER}
PORTREVISION=		5
CATEGORIES=		lang python

COMMENT=		Interpreted object-oriented programming language
MAINTAINER=		aoyama@xigmanas.com

USES=			python:run

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:lang/python27
DEP_STAGEDIR=		${PORTSDIR}/lang/python27/work/stage

# To test python, run samba-tool in XigmaNAS

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
# bin
.for file in python2.7
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/${file} ${XIGMANAS_ROOTFS}/usr/local/bin
.endfor

# lib
.for file in python2.7
	find ${DEP_STAGEDIR}${PREFIX}/lib/libpython2.7.so.1-gdb.py -delete
.endfor
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/lib/libpython2.7.so.* ${XIGMANAS_ROOTFS}/usr/local/lib
	${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python2.7 ${XIGMANAS_ROOTFS}/usr/local/lib
.include <bsd.port.mk>
