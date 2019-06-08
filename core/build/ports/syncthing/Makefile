PORTNAME=		syncthing
PORTVERSION=	1.1.4
PORTREVISION=	0
DISTVERSIONPREFIX= v
CATEGORIES=	net
MASTER_SITES=	https://github.com/syncthing/syncthing/releases/download/v${PORTVERSION}/
DISTNAME=	${PORTNAME}-source-${DISTVERSIONPREFIX}${PORTVERSION}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Encrypted file sync tool

BUILD_DEPENDS=	${LOCALBASE}/bin/go:lang/go

BINS=		syncthing stdiscosrv strelaysrv strelaypoolsrv

.for x in ${BINS}
# XigmaNAS
PLIST_FILES=	bin/syncthing
#PLIST_FILES+=   bin/${x} 
.endfor 

PORTDOCS=	*

USERS=		syncthing
GROUPS=		syncthing

STRIP=		# stripping can break go binaries

NO_WRKSUBDIR=   yes

# XigmaNAS
NO_PKG_REGISTER= yes

.include <bsd.port.pre.mk>

do-build:
	@( cd ${WRKSRC}/${PORTNAME} ; \
		${SETENV} ${MAKE_ENV} CGO_ENABLED=0 GOFLAGS="-mod=vendor" go run build.go -version v${PORTVERSION} -no-upgrade build ; \
		${SETENV} ${MAKE_ENV} CGO_ENABLED=0 GOFLAGS="-mod=vendor" go run build.go -version v${PORTVERSION} -no-upgrade build stdiscosrv ; \
		${SETENV} ${MAKE_ENV} CGO_ENABLED=0 GOFLAGS="-mod=vendor" go run build.go -version v${PORTVERSION} -no-upgrade build strelaysrv ; \
		${SETENV} ${MAKE_ENV} CGO_ENABLED=0 GOFLAGS="-mod=vendor" go run build.go -version v${PORTVERSION} -no-upgrade build strelaypoolsrv ; \
		)

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME}/syncthing \
		${XIGMANAS_ROOTFS}${PREFIX}/bin/
	@${INSTALL_SCRIPT} -v ${FILESDIR}/syncthing.in ${XIGMANAS_ROOTFS}/etc/rc.d/syncthing

.include <bsd.port.post.mk>
