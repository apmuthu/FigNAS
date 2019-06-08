--- upnpevents.c.orig	2017-05-17 22:55:17.000000000 +0200
+++ upnpevents.c	2017-09-03 19:46:15.000000000 +0200
@@ -47,6 +47,7 @@
  */
 #include "config.h"
 
+#define FD_SETSIZE	8192
 #include <stdio.h>
 #include <string.h>
 #include <errno.h>
