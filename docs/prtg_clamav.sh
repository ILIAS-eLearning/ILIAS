#!/bin/bash

LOG_DIR=/srv/www/hal/log
STATUS_LOG=clamd_status.log

warning() {
	echo "1:1:$1";
}

ok() {
	echo "0:0:OK";
}


if [ ! -d "$LOG_DIR" ]
then
	warning "Wrong ILIAS-clamav configuration"
	exit 0
fi

if [ ! -f "$LOG_DIR/$STATUS_LOG" ]
then
	warning "Cannot find ILIAS-clamav status file in $LOG_DIR/$STATUS_LOG"
	exit 0
fi

CLAMAV_STATUS=`grep "^1$" $LOG_DIR/$STATUS_LOG`
if [ $CLAMAV_STATUS == 1 ]
then
	warning "Infected file upload detected."
	exit 0
fi

ok
exit 0;

