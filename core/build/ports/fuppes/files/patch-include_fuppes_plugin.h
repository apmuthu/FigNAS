--- include/fuppes_plugin.h.orig	2011-11-01 10:50:10.000000000 +0100
+++ include/fuppes_plugin.h	2018-06-11 13:39:11.000000000 +0200
@@ -55,7 +55,7 @@
 	int size = sizeof(char) * (strlen(in) + 1);  
 	*out = (char*)realloc(*out, size);	
 	strncpy(*out, in, size - 1);
-	out[size - 1] = '\0';
+	*out[size - 1] = '\0';
   return size;
 }
 
@@ -83,9 +83,9 @@
 	arg_list_t* list = (arg_list_t*)malloc(sizeof(arg_list_t));
 	
 	list->key = (char*)malloc(sizeof(char));
-	list->key = '\0';
+	list->key[0] = '\0';
 	list->value = (char*)malloc(sizeof(char));
-	list->value = '\0';	
+	list->value[0] = '\0';	
 
 	list->next = NULL;
 	
