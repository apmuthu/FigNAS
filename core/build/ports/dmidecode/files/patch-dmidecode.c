--- dmidecode.c.orig	2018-09-14 15:52:12.000000000 +0200
+++ dmidecode.c	2018-11-29 02:09:33.000000000 +0100
@@ -66,6 +66,7 @@
 #include <stdlib.h>
 #include <unistd.h>
 #include <arpa/inet.h>
+#include <sys/socket.h>
 
 #ifdef __FreeBSD__
 #include <errno.h>
@@ -3609,7 +3610,7 @@
 		hname = out_of_spec;
 		hlen = strlen(out_of_spec);
 	}
-	printf("%s\t\tRedfish Service Hostname: %*s\n", prefix, hlen, hname);
+	printf("%s\t\tRedfish Service Hostname: %.*s\n", prefix, hlen, hname);
 }
 
 /*
