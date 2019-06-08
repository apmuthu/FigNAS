#!/bin/sh
#
# Part of XigmaNAS (https://www.xigmanas.com).
# Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
# All rights reserved.

PNG_S="xigmanas_icon_s.png"
PNG_L="xigmanas_icon_l.png"
JPG_S="xigmanas_icon_s.jpg"
JPG_L="xigmanas_icon_l.jpg"

bin2chex()
{
	local file

	file="$1"
	if [ -f "$file" ]; then
		hexdump -ve '16/1 "\\x%02x" "\n"' $file | sed -e '$s/\\x  //g' -e 's/^\(.*\)$/"\1"/' -e '$s/$/;/'
	fi
}

echo "unsigned char png_sm[] = "
bin2chex $PNG_S
echo

echo "unsigned char png_lrg[] = "
bin2chex $PNG_L
echo

echo "unsigned char jpeg_sm[] = "
bin2chex $JPG_S
echo

echo "unsigned char jpeg_lrg[] = "
bin2chex $JPG_L
echo
