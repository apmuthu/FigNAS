--- sys/cddl/contrib/opensolaris/uts/common/fs/zfs/zfs_vfsops.c.orig	2019-01-20 04:41:34.227588000 +0100
+++ sys/cddl/contrib/opensolaris/uts/common/fs/zfs/zfs_vfsops.c	2019-01-20 04:49:07.000000000 +0100
@@ -144,7 +144,7 @@
 	quotaobj = isgroup ? zfsvfs->z_groupquota_obj : zfsvfs->z_userquota_obj;
 
 	if (quotaobj == 0 || zfsvfs->z_replay) {
-		error = ENOENT;
+		error = EINVAL;
 		goto done;
 	}
 	(void)sprintf(buf, "%llx", (longlong_t)id);
