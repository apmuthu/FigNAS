COMMENT=	Ports options (located in /var/db/ports) used \
					by ports build system

MAINTAINER=		zoon01@xigmanas.com

ARCH!=		uname -p

install:
	@for i in `cd files && ls *-options | sed -e 's/-options//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options /var/db/ports/$$i/options; \
	done;
.if ${ARCH} == "i386"
	@for i in `cd files && ls *-options-i386 | sed -e 's/-options-i386//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options-i386 /var/db/ports/$$i/options; \
	done;
.endif
.if ${ARCH} == "armv6"
	@for i in `cd files && ls *-options-armv6 | sed -e 's/-options-armv6//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options-armv6 /var/db/ports/$$i/options; \
	done;
.endif
