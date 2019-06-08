--- src/lib/SSDP/UDPSocket.cpp.orig	2011-08-15 10:25:54.000000000 +0200
+++ src/lib/SSDP/UDPSocket.cpp	2018-06-11 15:19:34.000000000 +0200
@@ -104,16 +104,16 @@
      #ifdef WIN32     
     int nonblocking = 1;
     if(ioctlsocket(m_Socket, FIONBIO, (unsigned long*) &nonblocking) != 0)
-      return false;
+      return NULL;
     #else     
     int opts;
 	  opts = fcntl(m_Socket, F_GETFL);
 	  if (opts < 0) {
-      return false;
+      return NULL;
 	  }
 	  opts = (opts | O_NONBLOCK);
 	  if (fcntl(m_Socket, F_SETFL,opts) < 0) {		
-      return false;
+      return NULL;
 	  } 
 	  #endif
   
@@ -132,7 +132,7 @@
 	memset(&(m_LocalEndpoint.sin_zero), '\0', 8); // fill the rest of the structure with zero
 	
 	/* Bind socket */
-	ret = bind(m_Socket, (struct sockaddr*)&m_LocalEndpoint, sizeof(m_LocalEndpoint)); 
+	ret = (m_Socket, (struct sockaddr*)&m_LocalEndpoint, sizeof(m_LocalEndpoint)); 
   if(ret == -1) {
     throw fuppes::Exception(__FILE__, __LINE__, "failed to bind udp socket %s", p_sIPAddress.c_str());
   }
