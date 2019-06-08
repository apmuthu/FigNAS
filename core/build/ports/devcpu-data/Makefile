PORTNAME=	devcpu-data
PORTVERSION=	1.22
PORTREVISION=   0
CATEGORIES=	sysutils
MASTER_SITES=   LOCAL/sbruno:amd
PKGNAMEPREFIX=	devcpu-
DISTFILES=	amd64-microcode.20181214.tar.xz:amd

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Intel and AMD CPUs microcode updates

LICENSE=	EULA
LICENSE_NAME=	Intel Software License Agreement
LICENSE_FILE=	${FILESDIR}/LICENSE
LICENSE_PERMS=	dist-mirror no-dist-sell pkg-mirror no-pkg-sell auto-accept

USE_GITHUB=	yes
GH_ACCOUNT=	intel
GH_PROJECT=	Intel-Linux-Processor-Microcode-Data-Files
GH_TAGNAME=	microcode-20190514

ONLY_FOR_ARCHS=	amd64 i386
NO_ARCH=	yes
NO_WRKSUBDIR=	yes

DATADIR=	${PREFIX}/share/cpucontrol
FWDIR=          /boot/firmware

USE_RC_SUBR=	microcode_update

post-extract:
	${CP} -p ${FILESDIR}/Makefile ${FILESDIR}/ucode-split.c ${WRKSRC}
	${REINPLACE_CMD} 's|%%GH_TAGNAME%%|${GH_TAGNAME}|g' ${WRKSRC}/Makefile
	${CAT} ${WRKSRC}/Intel-Linux-Processor-Microcode-Data-Files-${GH_TAGNAME}/intel-ucode/* ${WRKSRC}/Intel-Linux-Processor-Microcode-Data-Files-${GH_TAGNAME}/intel-ucode-with-caveats/* > ${WRKSRC}/intel-ucode.bin

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/cpucontrol
	@${INSTALL_SCRIPT} -v ${FILESDIR}/microcode_update.in ${XIGMANAS_ROOTFS}/etc/rc.d/microcode_update
# mcodes
	${INSTALL_DATA} ${WRKSRC}/microcode_amd.bin ${XIGMANAS_ROOTFS}/${PREFIX}/share/cpucontrol/
	${INSTALL_DATA} ${WRKSRC}/microcode_amd_fam15h.bin ${XIGMANAS_ROOTFS}/${PREFIX}/share/cpucontrol/
	${INSTALL_DATA} ${WRKSRC}/microcode_amd_fam16h.bin ${XIGMANAS_ROOTFS}/${PREFIX}/share/cpucontrol/
	${INSTALL_DATA} ${WRKSRC}/microcode_amd_fam17h.bin ${XIGMANAS_ROOTFS}/${PREFIX}/share/cpucontrol/
	${INSTALL_DATA} ${WRKSRC}/mcodes/* ${XIGMANAS_ROOTFS}/${PREFIX}/share/cpucontrol/
	${MKDIR} ${XIGMANAS_ROOTFS}${FWDIR}
	${INSTALL_DATA} ${WRKSRC}/intel-ucode.bin ${XIGMANAS_ROOTFS}${FWDIR}/
.include <bsd.port.mk>
