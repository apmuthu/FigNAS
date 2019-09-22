PORTNAME=		rrdtool
PORTVERSION=		1.7.2
PORTREVISION=		0
CATEGORIES=		databases graphics

COMMENT=		Round Robin Database Tools
MAINTAINER=		zoon01@xigmanas.com

USES=			perl5 python

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:databases/rrdtool
DEP_STAGEDIR=		${PORTSDIR}/databases/rrdtool/work-py36/stage
PANGO_MODULES=		${PREFIX}/lib/pango/1.8.0/modules

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
# share
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-graph.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/rrd-graph.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-start.php ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/rrd-start.php
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-update.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/rrd-update.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu_frequency.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu_frequency.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu_temperature.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu_temperature.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/disk_usage.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/disk_usage.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/latency.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/latency.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/load_averages.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/load_averages.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/memory.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/memory.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/memory-detailed.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/memory-detailed.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/network_traffic.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/network_traffic.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/processes.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/processes.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/ups.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/ups.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/uptime.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/uptime.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/zfs_arc.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/zfs_arc.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/zfs_l2arc.sh ${XIGMANAS_ROOTFS}/usr/local/share/rrdgraphs/templates/zfs_l2arc.sh

	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcached ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcgi ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcreate ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdinfo ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdtool ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdupdate ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/lib/librrd.so.8.2.1 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${LN} -fsv /usr/local/lib/librrd.so.8.2.1 ${XIGMANAS_ROOTFS}/usr/local/lib/librrd.so
	@${LN} -fsv /usr/local/lib/librrd.so.8.2.1 ${XIGMANAS_ROOTFS}/usr/local/lib/librrd.so.8
# bindings
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs/RRDs.so ${XIGMANAS_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs/
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/RRDs.pm ${XIGMANAS_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/RRDp.pm ${XIGMANAS_ROOTFS}${SITE_PERL}/
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PYTHON_SITELIBDIR}
	@${CP} -pv ${DEP_STAGEDIR}${PYTHON_SITELIBDIR}/rrdtool.so ${XIGMANAS_ROOTFS}${PYTHON_SITELIBDIR}/
# fonts
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/share/fonts
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts/conf.d
	@${RM} -rf ${XIGMANAS_ROOTFS}${PREFIX}/share/fonts/dejavu
	@${CP} -Rpv ${PREFIX}/share/fonts/dejavu ${XIGMANAS_ROOTFS}${PREFIX}/share/fonts
	@${CP} -pv ${PREFIX}/etc/fonts/fonts.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts
	@${CP} -pv ${PREFIX}/etc/fonts/fonts.dtd ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts

	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans-mono.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-serif.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans-mono.conf ${XIGMANAS_ROOTFS}/usr/local/etc/fonts/conf.d/
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans.conf ${XIGMANAS_ROOTFS}/usr/local/etc/fonts/conf.d/
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-serif.conf ${XIGMANAS_ROOTFS}/usr/local/etc/fonts/conf.d/

.include <bsd.port.mk>
