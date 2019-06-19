# XigmaNAS
XigmaNAS is an Open Source fork of NAS4Free which is a fork of FreeNAS

* Home Page: http://www.xigmanas.com - GeoIP blocks Asian IPs.
* Upstream Project Page: http://sf.net/p/xigmanas
* The offical code repo is at [SourceForge](https://sourceforge.net/p/xigmanas/code).
* IRC : https://webchat.freenode.net/?channels=#xigmanas
* This fork will be updated when time permits and comes with no claims - use it at your own risk.
* Only the `trunk` branch of the upstream repo (r6743) from June 2019 is available here

## Building XigmaNAS
* Read the file at `build/README`

## Post Release Updation of web facing files
* The contents of the repo folders need to be oplaced in the root filesystem of the NAS install thus:
	* www => /usr/local/www
	* etc => /etc
	* locale => /usr/local/share/locale

# find / -name "locale"
* Gets all locakle folders and files
