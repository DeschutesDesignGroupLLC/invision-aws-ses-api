<?php

namespace IPS\awsses\Outgoing;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(( isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden');
    exit;
}

use Aws\Exception\AwsException;

/**
 * Class AWS SES
 * @package IPS\awsses\Outgoing
 */
class _SES extends \IPS\Email
{
    /**
     * Send the email
     *
     * @param   mixed   $to                 The member or email address, or array of members or email addresses, to send to
     * @param   mixed   $cc                 Addresses to CC (can also be email, member or array of either)
     * @param   mixed   $bcc                Addresses to BCC (can also be email, member or array of either)
     * @param   mixed   $fromEmail          The email address to send from. If NULL, default setting is used
     * @param   mixed   $fromName           The name the email should appear from. If NULL, default setting is used
     * @param   array   $additionalHeaders  The name the email should appear from. If NULL, default setting is used
     * @return  void
     * @throws  \IPS\Email\Outgoing\Exception
     */
    public function _send($to, $cc = array(), $bcc = array(), $fromEmail = null, $fromName = null, $additionalHeaders = array())
    {
        // Create an instance of our SES manager
        $manager = new \IPS\awsses\Manager\SES();

        // Try and send the email
        try {
            // Compose the payload
            $payload = $this->_composeEmailPayload($to, $cc, $bcc, $fromEmail, $fromName, $additionalHeaders, $manager->configSet);

            // Get the email and store the result
            $result = $manager->client->sendEmail($payload);

            // Log the message
            \IPS\awsses\Outgoing\Log::log($payload, $result['MessageId']);

        // Email send failed with exception
        } catch (AwsException $exception) {
            // Log the message
            \IPS\awsses\Outgoing\Log::log($payload, null, preg_replace("/\n/", '<br>', $exception->getTraceAsString()), $exception->getAwsErrorMessage());

            // Log our exceptions
            \IPS\Log::log($exception, 'awsses');
        }
    }

    /**
     * @param         $to
     * @param  array  $cc
     * @param  array  $bcc
     * @param  null   $fromEmail
     * @param  null   $fromName
     * @param  array  $additionalHeaders
     * @param  null   $configSet
     *
     * @return array
     */
    public function _composeEmailPayload($to, $cc = array(), $bcc = array(), $fromEmail = null, $fromName = null, $additionalHeaders = array(), $configSet = null)
    {
        // Parse our $to recipients
        $toRecipients = array_unique(array_map('trim', explode(',', static::_parseRecipients($to, true))));

        // Get from settings
        $newFromName = $fromName ?? \IPS\Settings::i()->board_name;
        $newFromEmail = $fromEmail ?? \IPS\Settings::i()->email_out;

        // Compose the email payload
        $payload = [
            'Destination' => [
                'ToAddresses' => $toRecipients
            ],
            'ReplyToAddresses' => [$fromEmail ?? \IPS\Settings::i()->email_out],
            'Source' => "{$newFromName} <{$newFromEmail}>",
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
                    'Data' => $this->compileSubject(static::_getMemberFromRecipients($to)),
                ],
            ],
            'ConfigurationSet' => $configSet,
        ];

        // If any carbon copy
        if ($cc) {
            // Add to recipients array
            $payload['Destination']['CcAddresses'] = array_unique(array_map('trim', explode(',', static::_parseRecipients($cc, true))));
        }

        // If any blind carbon copy
        if ($bcc) {
            // Add to recipients array
            $payload['Destination']['BccAddresses'] = array_unique(array_map('trim', explode(',', static::_parseRecipients($bcc, true))));
        }

        // Return our payload
        return $payload;
    }
}
