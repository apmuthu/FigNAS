--- src/mod_webdav.c.orig	2019-05-27 23:03:50.000000000 +0200
+++ src/mod_webdav.c	2019-06-07 03:30:32.000000000 +0200
@@ -1275,8 +1275,8 @@
      *   across a fork() system call into the child process.
      */
     plugin_data * const p = (plugin_data *)p_d;
-    plugin_config *s = p->config_storage[0];
-    for (int n_context = p->nconfig+1; --n_context; ++s) {
+    for (int i = 0; i < p->nconfig; ++i) {
+        plugin_config *s = p->config_storage[i];
         if (!buffer_is_empty(s->sqlite_db_name)
             && mod_webdav_sqlite3_prep(s->sql, s->sqlite_db_name, srv->errh)
                == HANDLER_ERROR)
