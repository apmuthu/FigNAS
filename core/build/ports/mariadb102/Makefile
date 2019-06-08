PORTNAME=		xigmanas-mariadb
PORTVERSION=		1.0
PORTREVISION=		0
CATEGORIES=		databases ipv6

COMMENT=		Multithreaded SQL database
MAINTAINER=		zoon01@xigmanas.com

DISTFILES?=
NO_BUILD?=		yes
NO_PKG_REGISTER?=	yes
DEPENDS_TARGET=	clean build stage

DEP_STAGEDIR=	${PORTSDIR}/databases/mariadb102-server/work/stage
CLIENT_STAGEDIR=	${PORTSDIR}/databases/mariadb102-client/work/stage

BUILD_DEPENDS=	${NONEXISTENT}:databases/mariadb102-server
BUILD_DEPENDS+=	${NONEXISTENT}:databases/mariadb102-client

do-install:
# XigmaNAS install
	@echo "install for ${XIGMANAS_ROOTFS}"
# rc
	@${INSTALL_SCRIPT} -v ${DEP_STAGEDIR}${PREFIX}/etc/rc.d/mysql-server ${XIGMANAS_ROOTFS}${PREFIX}/etc/rc.d
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mysqldb.in ${XIGMANAS_ROOTFS}/etc/rc.d/mysqldb
# bin
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/bin ${XIGMANAS_ROOTFS}${PREFIX}
	@${CP} -Rpv ${CLIENT_STAGEDIR}${PREFIX}/bin ${XIGMANAS_ROOTFS}${PREFIX}
# share
	@${RM} -rf  ${XIGMANAS_ROOTFS}${PREFIX}/share/mysql
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/share/mysql ${XIGMANAS_ROOTFS}${PREFIX}/share
	@${CP} -Rpv ${CLIENT_STAGEDIR}${PREFIX}/share/mysql ${XIGMANAS_ROOTFS}${PREFIX}/share
# lib
	@${RM} -rf  ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/mysql ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${CP} -Rpv ${CLIENT_STAGEDIR}${PREFIX}/lib/mysql ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/libexec/mysqld ${XIGMANAS_ROOTFS}${PREFIX}/libexec

post-install:
	${RM} ${XIGMANAS_ROOTFS}${PREFIX}/bin/wsrep*
	${RM} ${XIGMANAS_ROOTFS}${PREFIX}/share/aclocal/mysql.m4
	${RM} -r ${XIGMANAS_ROOTFS}${PREFIX}/share/pkgconfig
	${RM} -r ${XIGMANAS_ROOTFS}${PREFIX}/include/mysql/private
.	for file in mysql mysqladmin mysqlbinlog mysqlcheck mysqldump mysqlimport mysqlshow mysqlslap
		${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/bin/${file}
.	endfor
	${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/libmysqlclient.so
.	for file in dialog.so mysql_clear_password.so
		${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/plugin/${file}
.	endfor
	# Fix https://mariadb.atlassian.net/browse/MDEV-9388
	@${REINPLACE_CMD} 's/-l-pthread/-lpthread/' ${XIGMANAS_ROOTFS}${PREFIX}/bin/mysql_config

	${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/plugin/auth_gssapi_client.so

.include <bsd.port.mk>
