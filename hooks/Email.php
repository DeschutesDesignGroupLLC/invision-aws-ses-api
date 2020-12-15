//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class awsses_hook_Email extends _HOOK_CLASS_
{
	/**
	 * Get the class to use
	 *
	 * @param	string	$type	See TYPE_* constants
	 * @return	string
	 */
	static public function classToUse( $type )
	{
	    // If AWS SES is enabled
        if (\IPS\Settings::i()->awsses_enabled) {

            // Tell IPB to use our AWS SES factory
            return 'IPS\awsses\Outgoing\SES';
        }

	    // Return parent
		return parent::classToUse( $type );
	}
}
