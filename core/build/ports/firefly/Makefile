PORTNAME=			firefly
PORTVERSION=	1696
PORTREVISION=	1
CATEGORIES=			audio
COMMENT=			Multithread daapd yet another server for Apple iTunes
MAINTAINER=			zoon01@xigmanas.com

MASTER_SITES=			http://nightlies.fireflymediaserver.org/nightlies/svn-${PORTVERSION}/ \
									http://www.magnesium.net/~mdf/distfiles/
DISTNAME=				mt-daapd-svn-${PORTVERSION}

FORCE_PKG_REGISTER=yes
LIB_DEPENDS+=				libsqlite3.so:databases/sqlite3 \
										libid3tag.so:audio/libid3tag \
										libvorbis.so:audio/libvorbis \
										libFLAC.so:audio/flac

USE_PKGCONFIG=	yes
MAKE_JOBS_UNSAFE=	yes
#USE_GCC=		any
USE_LDCONFIG=		yes
USE_ICONV=		yes
GNU_CONFIGURE=	yes


CONFIGURE_ENV=			CFLAGS="${CFLAGS} ${PTHREAD_CFLAGS}" \
										CPPFLAGS="${CPPFLAGS} -I${LOCALBASE}/include" \
										LDFLAGS="-L${LOCALBASE}/lib" \
										LIBS="-L${LOCALBASE}/lib ${PTHREAD_LIBS}"
CONFIGURE_ARGS=			--disable-gdbm \
										--enable-sqlite3 \
										--with-id3tag=${PREFIX} \
										--enable-oggvorbis \
										--enable-flac
										
.include <bsd.port.options.mk>
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
USE_GCC=		any
.endif
.if ${OSVERSION} >= 1100000
EXTRA_PATCHES+=		${FILESDIR}/extra-patch-configure
.endif

do-install:
	-@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd
	-@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins

	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/mt-daapd ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libout-daap.so* ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/librsp.so* ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	#@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libssc-ffmpeg.so* ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/libout-daap.so.0.0 ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/libout-daap.so
	${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/librsp.so.0.0 ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/librsp.so
	#${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/libssc-ffmpeg.so.0.0 ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/libssc-ffmpeg.so
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mt-daapd.in ${XIGMANAS_ROOTFS}/etc/rc.d/mt-daapd

	-@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	-@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	-@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us

	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/about.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.css ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.xml ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config-update.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/CREDITS ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/feedback.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ff_logo_sm.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.css ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ftr.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/gpl-license.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/hdr.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.css ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkOpaque.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkTransparent.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/no_access.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/pngfix.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/required.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smartpopup.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner_stopped.gif ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/status.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/thanks.html ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/util.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/prototype.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/rico.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/builder.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/controls.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/dragdrop.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/effects.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/scriptaculous.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/slider.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/unittest.js ${XIGMANAS_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us

.include <bsd.port.mk>
