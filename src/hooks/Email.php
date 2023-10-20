//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

abstract class awsses_hook_Email extends _HOOK_CLASS_
{
    public static function classToUse($type)
    {
        if (\IPS\Settings::i()->awsses_enabled) {
            return 'IPS\awsses\Outgoing\SES';
        }

        return parent::classToUse($type);
    }
}
