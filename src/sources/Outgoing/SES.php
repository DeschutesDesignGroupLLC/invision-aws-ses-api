<?php

namespace IPS\awsses\Outgoing;

use Aws\Exception\AwsException;
use IPS\awsses\Manager\LicenseKey;
use IPS\awsses\Manager\SES;
use IPS\Email;
use IPS\Log;
use IPS\Settings;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _SES extends Email
{
    public function _send($to, $cc = [], $bcc = [], $fromEmail = null, $fromName = null, $additionalHeaders = [])
    {
        if (LicenseKey::i()->isValid()) {
            $manager = new SES();

            try {
                $payload = $this->_composeEmailPayload($to, $cc, $bcc, $fromEmail, $fromName, $additionalHeaders, $manager->configSet);

                $result = $manager->client->sendEmail($payload);

                \IPS\awsses\Outgoing\Log::log($payload, $result['MessageId']);
            } catch (AwsException $exception) {
                \IPS\awsses\Outgoing\Log::log($payload, null, preg_replace("/\n/", '<br>', $exception->getTraceAsString()), $exception->getAwsErrorMessage());

                Log::log($exception, 'awsses');
            }
        }
    }

    public function _composeEmailPayload($to, $cc = [], $bcc = [], $fromEmail = null, $fromName = null, $additionalHeaders = [], $configSet = null): array
    {
        $toRecipients = array_unique(array_map('trim', explode(',', static::_parseRecipients($to, true))));

        $manager = new SES();

        $fromName = $fromName ?? Settings::i()->board_name;
        $fromEmail = $manager->getSendingEmailAddress($fromEmail) ?? Settings::i()->email_out;

        $payload = [
            'Destination' => [
                'ToAddresses' => $toRecipients,
            ],
            'ReplyToAddresses' => [$fromEmail],
            'Source' => static::encodeHeader($fromName, $fromEmail),
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $this->compileContent('html', static::_getMemberFromRecipients($to)),
                    ],
                    'Text' => [
                        'Charset' => 'UTF-8',
                        'Data' => $this->compileContent('plaintext', static::_getMemberFromRecipients($to)),
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => $this->compileSubject(static::_getMemberFromRecipients($to)),
                ],
            ],
            'ConfigurationSet' => $configSet,
        ];

        if ($cc) {
            $payload['Destination']['CcAddresses'] = array_unique(array_map('trim', explode(',', static::_parseRecipients($cc, true))));
        }

        if ($bcc) {
            $payload['Destination']['BccAddresses'] = array_unique(array_map('trim', explode(',', static::_parseRecipients($bcc, true))));
        }

        return $payload;
    }
}
