--- src/lib/ContentDirectory/PlaylistFactory.cpp.orig	2011-09-22 10:45:44.000000000 +0200
+++ src/lib/ContentDirectory/PlaylistFactory.cpp	2018-06-11 22:47:33.000000000 +0200
@@ -234,7 +234,7 @@
   ssResult  << "<meta name=\"AverageRating\" content=\"0\"/>"
             << "<meta name=\"ItemCount\" content=\"" << itemCount << "\"/>"
             << "</head><body><seq>"
-            << ssMedia
+            << ssMedia.str()
             << "</seq></body></smil>";
 
   return ssResult.str();
