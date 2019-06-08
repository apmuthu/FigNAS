#!/bin/sh
#
# Extract gettext and gtext strings from source files and create new pot file.
#
# Part of XigmaNAS (https://www.xigmanas.com).
# Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
# All rights reserved.
#

# Global variables
XIGMANAS_ROOTDIR="/usr/local/xigmanas"
XIGMANAS_SVNDIR="$XIGMANAS_ROOTDIR/svn"
XIGMANAS_PRODUCTNAME=$(cat ${XIGMANAS_SVNDIR}/etc/prd.name)

OUTPUT="$(echo ${XIGMANAS_PRODUCTNAME} | tr '[:upper:]' '[:lower:]').pot"
OUTPUTDIR="${XIGMANAS_SVNDIR}/locale"
PARAMETERS="--output-dir=${OUTPUTDIR} --output=${OUTPUT} --language=PHP \
--force-po --no-location --no-wrap --sort-output --omit-header --keyword="gtext""

echo "==> Delete current pot file..."
cd ${XIGMANAS_SVNDIR}/locale
rm -f xigmanas.pot
echo "==> Delete current pot file completed!"
echo "==> Start building new translations pot file..."
sleep 3

cd ${XIGMANAS_SVNDIR}/www
xgettext ${PARAMETERS} *.*

cd ${XIGMANAS_SVNDIR}/www
xgettext ${PARAMETERS} --join-existing *.*

cd ${XIGMANAS_SVNDIR}/www/quixplorer/_include
xgettext ${PARAMETERS} --join-existing *.*

cd ${XIGMANAS_SVNDIR}/etc/inc
# xgettext ${PARAMETERS} --join-existing *.*
find . -iname "*.php" | xargs xgettext ${PARAMETERS} --join-existing
find . -iname "*.inc" | xargs xgettext ${PARAMETERS} --join-existing

DATE="$(date "+%Y-%m-%d %H:%M")+0000"
echo "msgid \"\"
msgstr \"\"
\"Project-Id-Version: ${XIGMANAS_PRODUCTNAME}\\n\"
\"POT-Creation-Date: ${DATE}\\n\"
\"PO-Revision-Date: \\n\"
\"Last-Translator: \\n\"
\"Language-Team: \\n\"
\"MIME-Version: 1.0\\n\"
\"Content-Type: text/plain; charset=UTF-8\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"
" >${OUTPUTDIR}/${OUTPUT}.tmp

cat ${OUTPUTDIR}/${OUTPUT} >>${OUTPUTDIR}/${OUTPUT}.tmp
mv -f ${OUTPUTDIR}/${OUTPUT}.tmp ${OUTPUTDIR}/${OUTPUT}
echo -e "\033\\033[32m";
echo -e "==> Translations exported into:""\033[37m ${OUTPUTDIR}/${OUTPUT}\033[37m";
echo "==> You now can commit this pot file to svn."
