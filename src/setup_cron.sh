#!/bin/bash
# This script sets up a CRON job to run cron.php every 24 hours.


SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"


CRON_SCRIPT_PATH="${SCRIPT_DIR}/cron.php"


CRON_JOB="0 0 * * * /usr/bin/php ${CRON_SCRIPT_PATH}"


(crontab -l 2>/dev/null | grep -v -F "${CRON_SCRIPT_PATH}" ; echo "${CRON_JOB}") | crontab -

echo "Cron job for XKCD updates has been installed."
echo "It will run daily at midnight."