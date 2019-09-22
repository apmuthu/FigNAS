PORTNAME=		python3
PORTVERSION=		${PYTHON_MAJOR_VER}
PORTREVISION=		0
CATEGORIES=		lang python

COMMENT=		Interpreted object-oriented programming language
MAINTAINER=		zoon01@xigmanas.com

USES=			python:run

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:lang/python36
DEP_STAGEDIR=		${PORTSDIR}/lang/python36/work/stage

# To test python, run samba-tool in XigmaNAS

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
# bin
.for file in python3.6
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/${file} ${XIGMANAS_ROOTFS}/usr/local/bin
.endfor

# lib
.for file in python3.6
	find ${DEP_STAGEDIR}${PREFIX}/lib/libpython3.6m.so.1.0-gdb.py -delete
.endfor
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/lib/libpython3.6m.so.* ${XIGMANAS_ROOTFS}/usr/local/lib
	${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python3.6 ${XIGMANAS_ROOTFS}/usr/local/lib
.include <bsd.port.mk>
