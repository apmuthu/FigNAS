# FigNAS
* This codebase initially tries to keep abreast of XigmaNAS codebase consolidating commits to the same file
* The name change was done to avoid misrepresentation and a missive from the Trademark owner on 2019-09-23
* This codebase primarily came into being since the XigmaNAS code base could not be compiled by me (will update on successful compilation)
* It serves as a base for understanding code changes by organising whitespace and code block movement changes from the actual code changes in separate commits
* It also provides for code extensions by indicating which files need to be changed for what functionality class
* This codebase will be updated when time permits and comes with no claims - use it at your own risk.
* Only the `trunk` branch of the XigmaNAS repo (r6743) from June 2019 is available here

# About XigmaNAS and history
* NAS4Free was a continuation of Open Source FreeNAS v7.x based on [FreeBSD](https://www.freebsd.org/) / [PHP](http://www.php.net)
* **FreeNAS** Trademark got owned by [IXSystems](https://www.ixsystems.com/) from Olivier
* FreeNAS (from v8.x) is IXSystems' fork (Volker) of the FreeNAS 7.x
* IXSystems, San Jose, California sells TrueNAS, FreeNAS mini and TrueRack storage solutions
* XigmaNAS was renamed to NAS4Free since it was similar to the trademarked FreeNAS name
* The [Trademark owner of XigmaNAS](http://tsdr.uspto.gov/#caseNumber=87689146&amp;caseSearchType=US_APPLICATION&amp;caseType=SERIAL_NO&amp;searchType=statusSearch) is Michael Zoon (works together with Daisuke)
* XigmaNAS is supported by:
  * [Aspen Systems](https://www.aspsys.com/), Wheat Ridge, Colorado sells [Panasas ActiveStor](https://www.aspsys.com/solutions/storage-solutions/panasas-file-system/)
  * [Jetstream Systems](http://www.jetstreamsys.com/), Wichita, Kansas sells Cloud Video Surveillance and Analytics solutions
* XigmaNAS remains Open Source (for now) according to the Trademark Owner

* Home Page: http://www.xigmanas.com - GeoIP may block access from some countries
* Upstream Project Page: http://sf.net/p/xigmanas
* The offical code repo is at [SourceForge](https://sourceforge.net/p/xigmanas/code).
* IRC : https://webchat.freenode.net/?channels=#xigmanas

## Building FigNAS
* Read the file at `build/README`

## Post Release Updation of web facing files
* The contents of the repo folders need to be placed in the root filesystem of the NAS install thus:
	* www => /usr/local/www
	* etc => /etc
	* locale => /usr/local/share/locale

# find / -name "locale"
* Gets all locale folders and files
