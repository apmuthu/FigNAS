# FigNAS
* This fork initially tries to keep abreast of XigmaNAS codebase consolidating commits to the same file
* The name change was done to avoid misrepresentation and a missive from the Trademark owner on 2019-09-23
* This fork primarily came into being since the XigmaNAS code base could not be compiled by me
* It serves me as a base for understanding code changes by organising whitespace and code block movement changes from the actual code changes
* It also provides for code extensions by indicating which files need to be changed for what functionality class

# About XigmaNAS
* XigmaNAS is an Open Source fork of NAS4Free which is a fork of FreeNAS
* [The Trademark owner of XigmaNAS is Michael Zoon](http://tsdr.uspto.gov/#caseNumber=87689146&amp;caseSearchType=US_APPLICATION&amp;caseType=SERIAL_NO&amp;searchType=statusSearch)

* Home Page: http://www.xigmanas.com - GeoIP blocks Asian IPs.
* Upstream Project Page: http://sf.net/p/xigmanas
* The offical code repo is at [SourceForge](https://sourceforge.net/p/xigmanas/code).
* IRC : https://webchat.freenode.net/?channels=#xigmanas
* This fork will be updated when time permits and comes with no claims - use it at your own risk.
* Only the `trunk` branch of the upstream repo (r6743) from June 2019 is available here

## Building FigNAS
* Read the file at `build/README`

## Post Release Updation of web facing files
* The contents of the repo folders need to be oplaced in the root filesystem of the NAS install thus:
	* www => /usr/local/www
	* etc => /etc
	* locale => /usr/local/share/locale

# find / -name "locale"
* Gets all locale folders and files
