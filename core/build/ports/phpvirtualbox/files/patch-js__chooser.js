--- js/chooser.js.orig	2018-12-21 12:10:39.688449000 +0100
+++ js/chooser.js	2018-12-21 12:14:31.000000000 +0100
@@ -720,7 +720,7 @@
 				var vStr = $('#vboxPane').data('vboxConfig').phpvboxver.substring(0,$('#vboxPane').data('vboxConfig').phpvboxver.indexOf('-'));
 				var vers = $('#vboxPane').data('vboxConfig').version.string.replace('_OSE','').split('.');
 				if(vers[0]+'.'+vers[1] != vStr) {
-					vboxAlert('This version of phpVirtualBox ('+$('#vboxPane').data('vboxConfig').phpvboxver+') is incompatible with VirtualBox ' + $('#vboxPane').data('vboxConfig').version.string + ". You probably need to <a href='http://sourceforge.net/projects/phpvirtualbox/files/' target=_blank>download the latest phpVirtualBox " + vers[0]+'.'+vers[1] + "-x</a>.<p>See the Versioning section below the file list in the link for more information</p>",{'width':'auto'});
+					vboxAlert('This version of phpVirtualBox ('+$('#vboxPane').data('vboxConfig').phpvboxver+') is incompatible with VirtualBox ' + $('#vboxPane').data('vboxConfig').version.string + ". You probably need to <a href='https://github.com/phpvirtualbox/phpvirtualbox/' target=_blank>download the latest phpVirtualBox " + vers[0]+'.'+vers[1] + "-x</a>.<p>See the Versioning section below the file list in the link for more information</p>",{'width':'auto'});
 				}
 			}			
 		} else {
