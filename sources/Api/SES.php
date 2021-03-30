<?php

namespace IPS\awsses\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
	header(( isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden');
	exit;
}

use Aws\Sns\Message;

class _SES extends \IPS\Patterns\Singleton
{
	/**
	 * Process an incoming API request
	 */
	protected function _processRequest($endpoint = 'bounce')
	{
		// Try and process the SNS message
		try
		{
			// Get the message data from the POST request
			$sns = Message::fromRawPostData();

			// Validate the message
//			$validator = new MessageValidator();
//			$validator->validate($sns);
//
//			// If validated
//			if ($validator->isValid($sns)) {
				if ($sns['Type'] === 'Notification') {
					// Get our message components
					$message = $sns['Message'];
					$type = $message['notificationType'];

					// If a bounce
					if ($type === 'Bounce' && $endpoint === 'bounce') {
						// Handle the bounced emails
						$this->_parseBouncedEmails($message);
					}

					// If a complaint
					if ($type === 'Complaint' && $endpoint === 'complaint') {
						// Handle the bounced emails
						$this->_parseComplaintEmails($message);
					}
				}
			}
//		}

		// We encountered an error
		catch (\Exception $exception)
		{
			// Log our exception
			\IPS\Log::log($exception, 'awsses');
		}
	}

	/**
	 * Handle POST request to complaints endpoint
	 */
	public function handleIncomingComplaintRequest()
	{
		// Process request
		$this->_processRequest('complaint');

		// Return
		return $this;
	}

	/**
	 * Handle POST request to bounces endpoint
	 */
	public function handleIncomingBounceRequest()
	{
		// Process request
		$this->_processRequest('bounce');

		// Return
		return $this;
	}

	/**
	 * Parse the bounced emails
	 *
	 * @param $message
	 */
	protected function _parseBouncedEmails($message)
	{
		// Get our bounced email addresses
		$recipients = array();
		foreach ($message['bounce']['bouncedRecipients'] as $recipient) {
			$recipients[] = $recipient['emailAddress'];
		}

		// Handle soft/hard bounces
		switch ($message['bounce']['bounceType'])
		{
			// Soft bounce
			case 'Transient':
				// Process the email addresses
				$manager = new \IPS\awsses\Manager\SES();
				$manager->processSoftBouncedEmailAddresses($recipients);
				break;

			// Hard bounce
			case 'Permanent':
				// Process the email addresses
				$manager = new \IPS\awsses\Manager\SES();
				$manager->processHardBouncedEmailAddresses($recipients);
				break;
		}
	}

	/**
	 * Parse the complaint emails
	 *
	 * @param $message
	 */
	protected function _parseComplaintEmails($message)
	{
		// Get our bounced email addresses
		$recipients = array();
		foreach ($message['complaint']['complainedRecipients'] as $recipient) {
			$recipients[] = $recipient['emailAddress'];
		}

		// Process complaints
		$manager = new \IPS\awsses\Manager\SES();
		$manager->processComplaintEmailAddresses($recipients);
	}

	/**
	 * Get output for API
	 *
	 * @return string
	 */
	public function getOutput()
	{
		// Return empty string
		return null;
	}
}