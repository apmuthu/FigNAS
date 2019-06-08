--- src/data.c.orig	2017-04-10 04:31:02.000000000 +0200
+++ src/data.c	2018-09-24 16:06:03.000000000 +0200
@@ -1135,7 +1135,7 @@
         while (len < 0) {
           int xerrno = errno;
  
-          if (xerrno == EAGAIN) {
+          if (xerrno == EAGAIN || xerrno == EINTR) {
             /* Since our socket is in non-blocking mode, read(2) can return
              * EAGAIN if there is no data yet for us.  Handle this by
              * delaying temporarily, then trying again.
@@ -1257,7 +1257,7 @@
       while (len < 0) {
         int xerrno = errno;
 
-        if (xerrno == EAGAIN) {
+        if (xerrno == EAGAIN || xerrno == EINTR) {
           /* Since our socket is in non-blocking mode, read(2) can return
            * EAGAIN if there is no data yet for us.  Handle this by
            * delaying temporarily, then trying again.
@@ -1354,7 +1354,7 @@
       while (bwrote < 0) {
         int xerrno = errno;
 
-        if (xerrno == EAGAIN) {
+        if (xerrno == EAGAIN || xerrno == EINTR) {
           /* Since our socket is in non-blocking mode, write(2) can return
            * EAGAIN if there is not enough from for our data yet.  Handle
            * this by delaying temporarily, then trying again.
