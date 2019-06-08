#!/bin/sh
#
# Part of XigmaNAS (https://www.xigmanas.com).
# Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
# All rights reserved.
#

. /etc/rc.subr
. /etc/email.subr
. /etc/configxml.subr

name="smartdreport"

load_rc_config "${name}"
load_rc_config "smartd"

smartd_drivedb=${smartd_drivedb:-""}

#	load additional drivedb data if set in config.xml or rc smartd_drivedb
_drivedb=`configxml_get "//smartd/drivedb"`
_param="-a -d ${SMARTD_DEVICETYPE} ${SMARTD_DEVICE}"
if [ -n "${_drivedb}" -a -f "${_drivedb}" -a -s "${_drivedb}" ]; then
	_param="--drivedb=${_drivedb} ${_param}"
elif [ -n "${smartd_drivedb}" -a -f "${smartd_drivedb}" -a -s "${smartd_drivedb}" ]; then
	_param="--drivedb=${smartd_drivedb} ${_param}"
fi

# Send output of smartctl -a to as message.
_message=`cat`
_report=`/usr/local/sbin/smartctl ${_param}`

# Send email.
send_email "${SMARTD_ADDRESS}" "${SMARTD_SUBJECT}" "${_message} ${_report}"
