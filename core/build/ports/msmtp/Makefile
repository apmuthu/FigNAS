PORTNAME=		msmtp
PORTVERSION=	1.8.4
PORTREVISION=	0
CATEGORIES=		mail ipv6
COMMENT=		SMTP client
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=   https://marlam.de/msmtp/releases/

GNU_CONFIGURE=	yes
USES=		gmake makeinfo pkgconfig tar:xz
CPPFLAGS+=	-I${LOCALBASE}/include
LDFLAGS+=	-L${LOCALBASE}/lib
INFO=	msmtp

CONFIGURE_ENV=	CPPFLAGS="${CPPFLAGS}" LDFLAGS="${LDFLAGS}"

OPTIONS_DEFINE=	DOCS GSASL NLS IDN LIBSECRET MSMTPQ
OPTIONS_RADIO=	TLS
OPTIONS_RADIO_TLS=      GNUTLS OPENSSL
OPTIONS_DEFAULT=        GNUTLS MSMTPD MSMTPQ
OPTIONS_SUB=    yes

GSASL_DESC=     GSASL support
LIBSECRET_DESC= GNOME password management
MSMTPQ_DESC=    Include minimal SMTP server that pipes mails to msmtp

GSASL_CONFIGURE_WITH=	libgsasl
GSASL_LIB_DEPENDS=	libgsasl.so:security/gsasl
IDN_CONFIGURE_WITH=	libidn
IDN_LIB_DEPENDS=	libidn2.so:dns/libidn2
LIBSECRET_CONFIGURE_WITH=	libsecret
LIBSECRET_LIB_DEPENDS=	libsecret-1.so:security/libsecret
MSMTPD_CONFIGURE_WITH=	msmtpd
MSMTPQ_PORTDOCS=	README.msmtpq
MSMTPQ_RUN_DEPENDS=     bash:shells/bash
NLS_CONFIGURE_ENABLE=	nls
NLS_USES=		gettext

GNUTLS_CONFIGURE_ON=	--with-ssl=gnutls
GNUTLS_LIB_DEPENDS=	libgnutls.so:security/gnutls
OPENSSL_USES=           ssl
OPENSSL_CONFIGURE_ON=	--with-ssl=openssl
OPENSSL_CONFIGURE_ENV=	libssl_CFLAGS="-I${OPENSSLINC}" \
			libssl_LIBS="-L${OPENSSLLIB} -lssl -lcrypto"
CERTSFILE=		${LOCALBASE}/share/certs/ca-root-nss.crt

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MGNUTLS} || ${PORT_OPTIONS:MOPENSSL}
RUN_DEPENDS+=	${LOCALBASE}/share/certs/ca-root-nss.crt:security/ca_root_nss
.else
CONFIGURE_ARGS+=	--with-ssl=no
.endif

post-extract:
	@${REINPLACE_CMD} -e 's|/etc/ssl/certs/ca\\-certificates.crt|${CERTSFILE}|' \
		${WRKSRC}/doc/msmtp.1

.if ${PORT_OPTIONS:MMSMTPQ}
post-stage:
	${INSTALL_SCRIPT} ${WRKSRC}/scripts/msmtpq/msmtp-queue ${STAGEDIR}${PREFIX}/bin
	${INSTALL_SCRIPT} ${WRKSRC}/scripts/msmtpq/msmtpq ${STAGEDIR}${PREFIX}/bin
	${MKDIR} ${STAGEDIR}${DOCSDIR}
	${INSTALL_SCRIPT} ${WRKSRC}/scripts/msmtpq/README.msmtpq ${STAGEDIR}${DOCSDIR}
.endif

post-install:
# XigmaNAS install
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${XIGMANAS_ROOTFS}/etc/rc.d/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/msmtp ${XIGMANAS_ROOTFS}/usr/local/bin

.include <bsd.port.mk>