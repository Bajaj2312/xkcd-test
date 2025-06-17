<?php
// This script is intended to be run by a CRON job.
// It will send the daily XKCD comic to all subscribers.
require_once __DIR__ . '/functions.php';

sendXKCDUpdatesToSubscribers();