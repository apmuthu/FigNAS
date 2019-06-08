--- src/lib/Plugins/Plugin.cpp.orig	2011-09-22 10:45:44.000000000 +0200
+++ src/lib/Plugins/Plugin.cpp	2018-06-11 15:03:05.000000000 +0200
@@ -1212,7 +1212,7 @@
 CDatabaseConnection* CDatabasePlugin::createConnection()
 {
 	if(!m_createConnection)
-		return false;
+		return NULL;
 	
 	return m_createConnection(&m_pluginInfo);
 }
