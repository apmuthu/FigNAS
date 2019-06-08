--- sys/vm/swap_pager.c.orig	2018-12-12 16:50:25.545053000 +0100
+++ sys/vm/swap_pager.c	2018-12-12 17:07:52.000000000 +0100
@@ -895,7 +895,7 @@
 	VM_OBJECT_WLOCK(object);
 	while (size) {
 		if (n == 0) {
-			n = BLIST_MAX_ALLOC;
+			n = min(BLIST_MAX_ALLOC, size); /* Happy with small size */
 			while ((blk = swp_pager_getswapspace(n)) == SWAPBLK_NONE) {
 				n >>= 1;
 				if (n == 0) {
