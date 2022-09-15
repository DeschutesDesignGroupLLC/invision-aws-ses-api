<?php

namespace IPS\awsses\Api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

use Aws\Exception\AwsException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

class _SES extends \IPS\Patterns\Singleton
{
    /**
     * @param string $type
     *
     * @return $this
     */
    public function handleIncomingRequest($type = 'bounce')
    {
        // Get the message data from the POST request
        $message = Message::fromRawPostData();

        // If this is a subscription confirmation request
        if ($message['Type'] === 'SubscriptionConfirmation') {
            // Confirm subscription
            return $this->_confirmSubscription($message);
        }

        // Handle the request
        $this->_processRequest($message, $type);

        // Return
        return $this;
    }

    /**
     * Process an incoming API request
     *
     * @param          $message
     * @param string   $endpoint
     */
    protected function _processRequest($message, $endpoint = 'bounce')
    {
        // Try and process the SNS message
        try {
            // If not in dev, validate the message
            $valid = true;
            if (!\IPS\IN_DEV) {
                // Validate the message
                $validator = new MessageValidator();
                $validator->validate($message);
                $valid = $validator->isValid($message);
            }

            // If valid
            if ($valid) {
                if ($message['Type'] === 'Notification') {
                    // Get our message components
                    $notification = \is_array($message['Message']) ? $message['Message'] : json_decode($message['Message'], true, 512, JSON_THROW_ON_ERROR);
                    $type = $notification['notificationType'];

                    // If a bounce
                    if ($type === 'Bounce' && $endpoint === 'bounce') {
                        // Handle the bounced emails
                        $this->_parseBouncedEmails($notification);
                    }

                    // If a complaint
                    if ($type === 'Complaint' && $endpoint === 'complaint') {
                        // Handle the bounced emails
                        $this->_parseComplaintEmails($notification);
                    }
                }
            }
        } // We encountered an error
        catch (\Exception $exception) {
            // Log our exception
            \IPS\Log::log($exception, 'awsses');
        }
    }

    /**
     * Parse the bounced emails
     *
     * @param $message
     */
    protected function _parseBouncedEmails($message)
    {
        // Get our bounced email addresses
        $recipients = [];
        foreach ($message['bounce']['bouncedRecipients'] as $recipient) {
            $recipients[] = $recipient['emailAddress'];
        }

        // Handle soft/hard bounces
        switch ($message['bounce']['bounceType']) {
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
        $recipients = [];
        foreach ($message['complaint']['complainedRecipients'] as $recipient) {
            $recipients[] = $recipient['emailAddress'];
        }

        // Process complaints
        $manager = new \IPS\awsses\Manager\SES();
        $manager->processComplaintEmailAddresses($recipients);
    }

    /**
     * @param $message
     *
     * @return _SES
     */
    protected function _confirmSubscription($message)
    {
        // Try and subscribe to the subscription
        try {
            // Set up our SNS Client
            $manager = new \IPS\awsses\Manager\SNS();

            // Subscribe to the subscription
            $manager->client->confirmSubscription([
                'Token' => $message['Token'],
                'TopicArn' => $message['TopicArn']
            ]);
        } // Catch any exceptions
        catch (AwsException $exception) {
            // Log our exceptions
            \IPS\Log::log($exception, 'awsses');
        }

        // Return
        return $this;
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
