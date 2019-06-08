--- endpoints/lib/vboxServiceWrappers.php.orig	2018-06-28 00:28:19.487657000 +0200
+++ endpoints/lib/vboxServiceWrappers.php	2018-07-01 22:36:35.000000000 +0200
@@ -1499,7 +1499,7 @@
     {
         $request = new stdClass();
         $request->_this = $this->handle;
-        $request->enabled = (bool)$arg_enabled;
+        $request->enabled = $arg_enabled;
         $request->VBoxValues = $arg_VBoxValues;
         $request->extraConfigValues = $arg_extraConfigValues;
         $response = $this->connection->__soapCall('IVirtualSystemDescription_setFinalValues', array((array)$request));
