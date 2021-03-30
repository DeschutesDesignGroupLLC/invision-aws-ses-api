<?php

namespace IPS\awsses\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Simple Notification Service API
 */
class _bounces extends \IPS\Api\Controller
{
	/**
	 * POST /awsses/bounces
	 * Webhook for processing bounce notifications from AWS Simple Notification Service.
	 *
	 * @return  null
	 */
	public function POSTindex()
	{
		// Handle POST request
		return \IPS\awsses\Api\SES::i()->handleIncomingBounceRequest();
	}
}


