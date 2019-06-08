PORTNAME=		tmux
PORTVERSION=	2.9a
PORTREVISION=	0
CATEGORIES=	sysutils
COMMENT=	Terminal Multiplexer
MAINTAINER=	zoon01@xigmanas.com

MASTER_SITES=	https://github.com/tmux/tmux/releases/download/${PORTVERSION}/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}

LICENSE=	ISCL
LICENSE_FILE=	${WRKSRC}/COPYING

USES=		pkgconfig ncurses

USE_GITHUB=	nodefault
GH_TUPLE=	imomaliev:tmux-bash-completion:ef56d3e:bash

GNU_CONFIGURE=	yes
NO_PKG_REGISTER=yes
CONFIGURE_ARGS=	--sysconfdir=${PREFIX}/etc --enable-utempter

PLIST_FILES=	bin/tmux \
		man/man1/tmux.1.gz \
		etc/bash_completion.d/tmux

PORTDOCS=	CHANGES README TODO
PORTEXAMPLES=	*

OPTIONS_DEFINE=	DOCS EXAMPLES LIBEVENT_STATIC BACKSPACE

LIBEVENT_STATIC_DESC=	 Build with static libevent
BACKSPACE_DESC=		 Build with tty/keys patch

BACKSPACE_EXTRA_PATCHES= ${PATCHDIR}/extra-patch-tty-keys.c

LIBEVENT_STATIC_LIB_DEPENDS_OFF=        libevent.so:devel/libevent
LIBEVENT_STATIC_BUILD_DEPENDS=  ${LOCALBASE}/lib/libevent.a:devel/libevent
LIBEVENT_STATIC_VARS=   LESTATIC=${LOCALBASE}/lib/libevent.a

post-patch:
	@${REINPLACE_CMD} -e 's|/etc/tmux.conf|${PREFIX}/etc/tmux.conf|g' ${WRKSRC}/CHANGES \
		${WRKSRC}/tmux.h
	@${REINPLACE_CMD} -e 's|@SYSCONFDIR@|${PREFIX}/etc|g' ${WRKSRC}/tmux.1
post-configure-LIBEVENT_STATIC-on:
	${REINPLACE_CMD} -e '/LIBS/s|-levent[^[:blank:]]*|${LESTATIC}|' \
		${WRKSRC}/${MAKEFILE}

# XigmaNAS install
do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tmux ${XIGMANAS_ROOTFS}/bin

.include <bsd.port.mk>