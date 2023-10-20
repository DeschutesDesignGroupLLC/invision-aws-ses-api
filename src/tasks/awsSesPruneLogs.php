<?php

namespace IPS\awsses\tasks;

use IPS\Settings;
use IPS\Task;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _awsSesPruneLogs extends Task
{
    public function execute()
    {
        if (Settings::i()->awsses_log_prune_settings) {
            \IPS\awsses\Outgoing\Log::pruneLogs(Settings::i()->awsses_log_prune_settings);
            \IPS\awsses\Bounce\Log::pruneLogs(Settings::i()->awsses_log_prune_settings);
            \IPS\awsses\Complaint\Log::pruneLogs(Settings::i()->awsses_log_prune_settings);
        }

        return null;
    }

    public function cleanup()
    {
        //
    }
}
