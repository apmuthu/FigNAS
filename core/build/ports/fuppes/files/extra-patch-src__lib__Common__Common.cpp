--- src/lib/Common/Common.cpp.orig	2011-09-22 17:45:44.000000000 +0900
+++ src/lib/Common/Common.cpp	2017-07-02 12:39:52.815080000 +0900
@@ -46,6 +46,7 @@
 #include <algorithm>
 #include <cctype>
 #include <time.h>
+#include <unistd.h>
 
 #ifndef WIN32
 #include <dlfcn.h>
@@ -412,7 +413,7 @@
 		icv = iconv_open("UTF-8", p_sEncoding.c_str());
 	//}
 	
-  if(icv < 0)  
+  if(icv == (iconv_t) -1)  
     return p_sValue;  
   
   size_t nInbytes  = p_sValue.length(); 
