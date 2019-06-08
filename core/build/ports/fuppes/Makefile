PORTNAME=		fuppes
PORTVERSION=	0.692
PORTREVISION=	5
CATEGORIES=		net
COMMENT=		Free UPnP Entertainment Service
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=		SF/${PORTNAME}/${PORTNAME}/SVN-660
DISTNAME=		${PORTNAME}-${PORTVERSION}

LIB_DEPENDS+=		libsqlite3.so:databases/sqlite3 \
			libtag.so:audio/taglib \
			libcurl.so:ftp/curl \
			libpcre.so:devel/pcre \
			libuuid.so:misc/e2fsprogs-libuuid \
			libmp3lame.so:audio/lame \
			libtwolame.so:audio/twolame \
			libvorbis.so:audio/libvorbis \
			libmp4v2.so:multimedia/mp4v2 \
			libavformat.so:multimedia/ffmpeg

USE_AUTOTOOLS=	autoconf
USE_GNOME=		libxml2
USE_ICONV=		yes
USE_GMAKE=		yes
MAKE_JOBS_UNSAFE=yes
GNU_CONFIGURE=		yes 
CONFIGURE_ARGS+=	--enable-largefile \
			--enable-transcoder-ffmpeg \
			--enable-faad \
			--enable-twolame \
			--enable-mp4v2 \
			--enable-taglib \
			--enable-lame \
			--enable-vorbis \
			--enable-musepack \
			--enable-flac \
			--enable-dlna \
			--disable-exiv2 \
			--disable-inotify
CONFIGURE_ARGS+=	--localstatedir=/var/db

CONFIGURE_ENV=		CPPFLAGS="-I${LOCALBASE}/include -I${LOCALBASE}/include/ffmpeg ${PTHREAD_CFLAGS}" \
			LIBS="-L${LOCALBASE}/lib ${PTHREAD_LIBS}"

.include <bsd.port.options.mk>
.if ${OSVERSION} >= 1000000
EXTRA_PATCHES+=	${FILESDIR}/extra-patch-src__lib__Common__Common.cpp \
		${FILESDIR}/extra-patch-src__lib__Common__Directory.cpp \
		${FILESDIR}/extra-patch-src__lib__ContentDirectory__DatabaseConnection.cpp \
		${FILESDIR}/extra-patch-src__plugins__database_sqlite3.cpp
.endif

.include <bsd.port.pre.mk>

pre-patch:
.if ${ARCH} == "amd64" || ${ARCH} == "i386"
	${CP} ${FILESDIR}/extra-patch-ffmpeg-configure-xigmanas ${PORTSDIR}/multimedia/ffmpeg/files/patch-configure-xigmanas
.endif

pre-configure:
	@if [ -f "${WRKSRC}/m4/version.sh" ]; then \
		${REINPLACE_CMD} -e 's,/bin/bash,${LOCALBASE}/bin/bash,' ${WRKSRC}/m4/version.sh; \
	fi
	@(cd ${WRKDIR}/${DISTNAME}; autoreconf -vfi)

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/${PORTNAME}d ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/libfuppes.so.* ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${XIGMANAS_ROOTFS}/etc/rc.d/${PORTNAME}

	# Presentation
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.js ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.png ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.jpg ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/style.css ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}

	# Configs
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/devices
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/vfolders
	@${INSTALL_DATA} -v ${WRKSRC}/config/devices/*.cfg ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/devices
	@${INSTALL_DATA} -v ${WRKSRC}/config/vfolders/*.cfg ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/vfolders
	@${INSTALL_DATA} -v ${FILESDIR}/devices/*.cfg ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/devices

	# Install plugins
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdatabase_sqlite3.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdatabase_sqlite3.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdecoder_flac.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdecoder_flac.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdecoder_vorbis.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdecoder_vorbis.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libencoder_pcm.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libencoder_pcm.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libencoder_wav.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libencoder_wav.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_libavformat.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_libavformat.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_libmp4v2.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_libmp4v2.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_taglib.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_taglib.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libsource_bliptv.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libsource_bliptv.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libsource_itunes.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libsource_itunes.so.* ${XIGMANAS_ROOTFS}/${PREFIX}/lib/${PORTNAME}

.include <bsd.port.mk>
