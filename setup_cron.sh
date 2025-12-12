#!/bin/bash

# Define the Cron command
CRON_CMD="* * * * * cd /home/anxipunk.icu/public_html && php artisan schedule:run >> /dev/null 2>&1"

# Check if the job already exists
(crontab -l 2>/dev/null | grep -F "php artisan schedule:run") && echo "Cron job already exists!" && exit 0

# Add the job
(crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -

echo "Cron job installed successfully!"
echo "Laravel Scheduler will now run every minute."
