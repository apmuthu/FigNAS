# quotactl() returns wrong error, committed as r342928 see Bug 234413

PATCHFILE=patch-sys__cddl__contrib__opensolaris__uts__common__fs__zfs__zfs_vfsops.c

all:

install:
	(cd /usr/src; patch < ${.CURDIR}/files/${PATCHFILE})
