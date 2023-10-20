<?php

namespace IPS\awsses\Api;

use Aws\Exception\AwsException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use IPS\awsses\Manager\SES;
use IPS\awsses\Manager\SNS;
use IPS\Log;
use IPS\Patterns\Singleton;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _SES extends Singleton
{
    public function handleIncomingRequest($type = 'bounce'): static
    {
        $message = Message::fromRawPostData();

        if ($message['Type'] === 'SubscriptionConfirmation') {
            return $this->_confirmSubscription($message);
        }

        $this->_processRequest($message, $type);

        return $this;
    }

    protected function _processRequest($message, $endpoint = 'bounce'): void
    {
        try {
            $valid = true;
            if (! \IPS\IN_DEV) {
                $validator = new MessageValidator();
                $validator->validate($message);
                $valid = $validator->isValid($message);
            }

            if ($valid) {
                if ($message['Type'] === 'Notification') {
                    $notification = \is_array($message['Message']) ? $message['Message'] : json_decode($message['Message'], true, 512, JSON_THROW_ON_ERROR);
                    $type = $notification['notificationType'];

                    if ($type === 'Bounce' && $endpoint === 'bounce') {
                        $this->_parseBouncedEmails($notification);
                    }

                    if ($type === 'Complaint' && $endpoint === 'complaint') {
                        $this->_parseComplaintEmails($notification);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::log($exception, 'awsses');
        }
    }

    protected function _parseBouncedEmails($message): void
    {
        $recipients = [];
        foreach ($message['bounce']['bouncedRecipients'] as $recipient) {
            $recipients[] = $recipient['emailAddress'];
        }

        switch ($message['bounce']['bounceType']) {
            case 'Transient':
                $manager = new SES();
                $manager->processSoftBouncedEmailAddresses($recipients);
                break;

            case 'Permanent':
                $manager = new SES();
                $manager->processHardBouncedEmailAddresses($recipients);
                break;
        }
    }

    protected function _parseComplaintEmails($message): void
    {
        $recipients = [];
        foreach ($message['complaint']['complainedRecipients'] as $recipient) {
            $recipients[] = $recipient['emailAddress'];
        }

        $manager = new SES();
        $manager->processComplaintEmailAddresses($recipients);
    }

    protected function _confirmSubscription($message): static
    {
        try {
            $manager = new SNS();

            $manager->client->confirmSubscription([
                'Token' => $message['Token'],
                'TopicArn' => $message['TopicArn'],
            ]);
        } catch (AwsException $exception) {
            Log::log($exception, 'awsses');
        }

        return $this;
    }

    public function getOutput(): ?string
    {
        return null;
    }
}
