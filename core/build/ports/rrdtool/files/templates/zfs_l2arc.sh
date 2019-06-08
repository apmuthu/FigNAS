/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Bytes" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"-l 0" \
"DEF:L2_SIZE=$STORAGE_PATH/rrd/${GRAPH}.rrd:L2_SIZE:AVERAGE" \
"DEF:L2_ASIZE=$STORAGE_PATH/rrd/${GRAPH}.rrd:L2_ASIZE:AVERAGE" \
"AREA:L2_SIZE#3AD9E7BF:L2ARC Uncompressed (L2_SIZE)" \
"GPRINT:L2_SIZE:MIN:Min\\:%6.1lf %s" \
"GPRINT:L2_SIZE:MAX:Max\\:%6.1lf %s" \
"GPRINT:L2_SIZE:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:L2_SIZE:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"AREA:L2_ASIZE#EEFF337F:L2ARC Compressed (L2_ASIZE)" \
"GPRINT:L2_ASIZE:MIN:Min\\:%6.1lf %s" \
"GPRINT:L2_ASIZE:MAX:Max\\:%6.1lf %s" \
"GPRINT:L2_ASIZE:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:L2_ASIZE:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"TEXTALIGN:right" "COMMENT:Last update\: $LAST_UPDATE"
