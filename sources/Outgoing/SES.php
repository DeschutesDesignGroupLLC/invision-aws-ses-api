<?php

namespace IPS\awsses\Outgoing;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

/**
 * Class AWS SES
 * @package IPS\awsses\Outgoing
 */
class _SES extends \IPS\Email
{
	/**
	 * @brief	AWS Credentials
	 */
	protected $accessKey;
	protected $secretKey;
	protected $region;
	protected $client;

	/**
	 * Constructor
	 *
	 * @param          $accessKey
	 * @param          $secretKey
	 * @param  string  $region
	 */
	public function __construct( $accessKey = NULL, $secretKey = NULL, $region = NULL )
	{
		// Set class properties
		$this->accessKey = $accessKey ?: \IPS\Settings::i()->awsses_access_key;
		$this->secretKey = $secretKey ?: \IPS\Settings::i()->awsses_secret_key;
		$this->region = $region ?: ( \IPS\Settings::i()->awsses_region ?: 'us-west-2' );

		// Set config set
		$this->configSet = \IPS\Settings::i()->awsses_config_set_name ?: NULL;

		// Set up our SES Client
		$this->client = new SesClient([
			'version' => '2010-12-01',
			'region'  => $this->region,
			'credentials' => [
				'key' => $this->accessKey,
				'secret' => $this->secretKey
			]
		]);
	}

	/**
	 * Send the email
	 *
	 * @param	mixed	$to					The member or email address, or array of members or email addresses, to send to
	 * @param	mixed	$cc					Addresses to CC (can also be email, member or array of either)
	 * @param	mixed	$bcc				Addresses to BCC (can also be email, member or array of either)
	 * @param	mixed	$fromEmail			The email address to send from. If NULL, default setting is used
	 * @param	mixed	$fromName			The name the email should appear from. If NULL, default setting is used
	 * @param	array	$additionalHeaders	The name the email should appear from. If NULL, default setting is used
	 * @return	void
	 * @throws	\IPS\Email\Outgoing\Exception
	 */
	public function _send( $to, $cc=array(), $bcc=array(), $fromEmail = NULL, $fromName = NULL, $additionalHeaders = array() )
	{
		// Try and send the email
		try {

			// Compose the payload
			$payload = $this->_composeEmailPayload($to, $cc, $bcc, $fromEmail, $fromName, $additionalHeaders);

			// Get the email and store the result
			$result = $this->client->sendEmail($payload);

			// Log the message
			\IPS\awsses\Outgoing\Log::log($payload, $result['MessageId']);

		// Email send failed with exception
		} catch (AwsException $exception) {

			// Log the message
			\IPS\awsses\Outgoing\Log::log($payload, NULL, preg_replace("/\n/", '<br>', $exception->getTraceAsString()), $exception->getAwsErrorMessage());

			// Log our exceptions
			\IPS\Log::log( $exception, 'awsses' );
		}
	}

	/**
	 * Compose the email payload
	 *
	 * @param         $to
	 * @param  array  $cc
	 * @param  array  $bcc
	 * @param  null   $fromEmail
	 * @param  null   $fromName
	 * @param  array  $additionalHeaders
	 *
	 * @return array
	 */
	public function _composeEmailPayload( $to, $cc=array(), $bcc=array(), $fromEmail = NULL, $fromName = NULL, $additionalHeaders = array() )
	{
		// Convert our to to the appropriate data structure
		$recipients = \is_array($to) ? $to : [$to];
		$toEmails = array();
		foreach ($recipients as $recipient) {

			// Is this a member or email string
			if ($recipient instanceof \IPS\Member) {

				// Add the email to the recipient list
				$toEmails[] = $recipient->email;
				continue;
			}

			// Add the email to the recipient list
			$toEmails[] = $recipient;
		}

		// Create our message parts
		$subject = $this->compileSubject(static::_getMemberFromRecipients($to));

		// Compose the email payload
		return [
			'Destination' => [
				'ToAddresses' => $toEmails
			],
			'ReplyToAddresses' => [$fromEmail ?: \IPS\Settings::i()->email_out],
			'Source' => $fromEmail ?: \IPS\Settings::i()->email_out,
			'Message' =>[
				'Body' => [
					'Html' => [
						'Charset' => 'UTF-8',
						'Data' => $this->compileContent('html', static::_getMemberFromRecipients($to))
					],
					'Text' => [
						'Charset' => 'UTF-8',
						'Data' => $this->compileContent('plaintext', static::_getMemberFromRecipients($to))
					],
				],
				'Subject' => [
					'Charset' => 'UTF-8',
					'Data' => $subject,
				],
			],
			'ConfigurationSet' => $this->configSet,
		];
	}
}