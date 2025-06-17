#!/bin/bash
# This script sets up a CRON job to run cron.php every 24 hours.

# Get the absolute path of the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# The full path to the cron.php script
CRON_SCRIPT_PATH="${SCRIPT_DIR}/cron.php"

# The command to add to the crontab (runs at midnight every day)
# Note: Ensure the 'php' command is available in the cron environment's PATH.
# Using an absolute path like /usr/bin/php is safer.
CRON_JOB="0 0 * * * /usr/bin/php ${CRON_SCRIPT_PATH}"

# Add the cron job without overwriting existing jobs
# 1. List current crontab entries
# 2. Filter out any previous versions of this same job
# 3. Append the new job
# 4. Load the result into the crontab
(crontab -l 2>/dev/null | grep -v -F "${CRON_SCRIPT_PATH}" ; echo "${CRON_JOB}") | crontab -

echo "Cron job for XKCD updates has been installed."
echo "It will run daily at midnight."