--- src/xml_escape.c.orig	2013-03-17 00:27:32.000000000 +0100
+++ src/xml_escape.c	2018-10-21 23:52:04.000000000 +0200
@@ -59,9 +59,6 @@
     exit(status);
 }
 
-/* "apos" or "quot" are biggest, add 1 for leading "&" */
-enum { MAX_ENTITY_NAME = 1+4 };
-
 /* return 1 if entity was recognized and value output, 0 otherwise */
 static int
 put_entity_value(const char* entname, FILE* out)
@@ -208,6 +205,8 @@
 static const char *
 xml_unescape(const char* str, FILE* out)
 {
+    /* "apos" or "quot" are biggest, add 1 for leading "&" */
+    enum { MAX_ENTITY_NAME = 1+4 };
     static char entity[MAX_ENTITY_NAME+1]; /* +1 for NUL terminator */
     int i;
 
@@ -222,7 +221,7 @@
                 semicolon_off++;
             }
             entity_len = semicolon_off - i;
-            if (entity_len < MAX_ENTITY_NAME) {
+            if (entity_len < sizeof entity) {
                 memcpy(entity, &str[i], entity_len);
                 entity[entity_len] = '\0';
                 if (str[semicolon_off] == ';') {
