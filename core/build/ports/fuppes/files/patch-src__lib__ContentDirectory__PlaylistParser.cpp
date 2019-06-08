--- src/lib/ContentDirectory/PlaylistParser.cpp.orig	2011-08-15 10:25:54.000000000 +0200
+++ src/lib/ContentDirectory/PlaylistParser.cpp	2018-06-11 22:47:45.000000000 +0200
@@ -76,7 +76,7 @@
 {
   string sResult = ReadFile(p_sFileName);
   if (sResult.compare("") == 0) {
-    return false;
+    return NULL;
   }
   
   BasePlaylistParser* playlistResult = NULL;
