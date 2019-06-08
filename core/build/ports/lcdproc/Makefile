PORTNAME=		lcdproc
PORTVERSION=  0.5.9
PORTREVISION= 0
DISTVERSIONPREFIX=	lcdproc-
CATEGORIES=		sysutils

MAINTAINER=		zoon01@xigmanas.com
COMMENT=		Client/Server suite for LCD devices

USE_GITHUB=	yes
GH_ACCOUNT=	lcdproc

GNU_CONFIGURE=	yes
USES=           autoreconf pkgconfig

# Options
OPTIONS_DEFINE= USB FTDI HID FREETYPE SERDISP

LIB_DEPENDS+=		libftdi1.so:${PORTSDIR}/devel/libftdi1
LIB_DEPENDS+=		libhid.so:${PORTSDIR}/devel/libhid

CONFIGURE_ARGS=	--enable-drivers="${LCDPROC_DRIVERS}" \
						--with-libusb \
						--disable-dependency-tracking \
						--disable-doxygen

OPTIONS_DEFINE=  USB FTDI HID FREETYPE PNG SERDISP
USB_DESC=	   USB support
FTDI_DESC=	   Build drivers using libftdi (requires USB)
HID_DESC=	   Build drivers using libhid (requires USB)
SERDISP_DESC=	   Build drivers using serdisplib


OPTIONS_DEFAULT= USB FREETYPE
NO_OPTIONS_SORT= yes

FREETYPE_LIB_DEPENDS=	libfreetype.so:${PORTSDIR}/print/freetype2
FREETYPE_CONFIGURE_ENABLE=	freetype

PNG_LIB_DEPENDS=	libpng.so:${PORTSDIR}/graphics/png
PNG_CONFIGURE_ENABLE=	libpng

SERDISP_LIB_DEPENDS=	libserdisp.so:${PORTSDIR}/devel/serdisplib
SERDISP_CFLAGS=		-I${LOCALBASE}/include
SERDISP_LDFLAGS=	-L${LOCALBASE}/lib
SERDISP_CPPFLAGS=	-I${LOCALBASE}/include

LCDPROC_DRIVERS=	bayrad \
					CFontz \
					CFontzPacket \
					curses \
					CwLnx \
					ea65 \
					EyeboxOne \
					futaba \
					glcd \
					glk \
					hd44780 \
					icp_a106 \
					lb216 \
					lcdm001 \
					lcterm \
					MD8800 \
					ms6931 \
					mtc_s16209x \
					MtxOrb \
					NoritakeVFD \
					pyramid \
					SureElec \
					sdeclcd \
					sed1330 \
					sed1520 \
					serialPOS \
					sli \
					stv5730 \
					t6963 \
					text \
					tyan \
					vlsys_m428
LCDPROC_DRIVERS+=	IOWarrior \
					picolcd \
					shuttleVFD
LCDPROC_DRIVERS+=	i2500vfd \
					lis \
					ula200
LCDPROC_DRIVERS+=	mdm166a

.include <bsd.port.pre.mk>
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
LCDPROC_DRIVERS+=			serialVFD
.endif

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MUSB}
CONFIGURE_ARGS+=	--with-libusb=/usr
PLIST_SUB+=		USB=""
LCDPROC_DRIVERS+=IOWarrior \
		picolcd \
		shuttleVFD

. if ${PORT_OPTIONS:MFTDI}
LIB_DEPENDS+=		libftdi1.so:${PORTSDIR}/devel/libftdi1
CONFIGURE_ARGS+=	--enable-libftdi
PLIST_SUB+=		FTDI=""
LCDPROC_DRIVERS+=i2500vfd \
		lis \
		ula200
. else
CONFIGURE_ARGS+=	--disable-libftdi
PLIST_SUB+=		FTDI="@comment "
. endif

. if ${PORT_OPTIONS:MHID}
LIB_DEPENDS+=		libhid.so:${PORTSDIR}/devel/libhid
PLIST_SUB+=		HID=""
LCDPROC_DRIVERS+=mdm166a
. else
CONFIGURE_ARGS+=	--disable-libhid
PLIST_SUB+=		HID="@comment "
. endif

.else
CONFIGURE_ARGS+=	--without-libusb --disable-libftdi --disable-libhid
PLIST_SUB+=		USB="@comment " FTDI="@comment " HID="@comment "
.endif

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/LCDd ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdexec/lcdexec ${XIGMANAS_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdproc/lcdproc ${XIGMANAS_ROOTFS}/${PREFIX}/bin
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/lib/lcdproc
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/drivers/*.so ${XIGMANAS_ROOTFS}/${PREFIX}/lib/lcdproc
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/LCDd.conf ${XIGMANAS_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdexec/lcdexec.conf ${XIGMANAS_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdproc/lcdproc.conf ${XIGMANAS_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/LCDd.in ${XIGMANAS_ROOTFS}/etc/rc.d/LCDd
	@${INSTALL_SCRIPT} -v ${FILESDIR}/lcdproc.in ${XIGMANAS_ROOTFS}/etc/rc.d/lcdproc

.include <bsd.port.post.mk>
