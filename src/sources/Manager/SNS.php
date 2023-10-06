<?php

namespace IPS\awsses\Manager;

use Aws\Sns\SnsClient;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _SNS extends Manager
{
    /**
     * @var SnsClient The AWS SNS client object.
     */
    public $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call parent
        parent::__construct();

        // Set up our SES Client
        $this->client = new SnsClient([
            'version' => '2010-03-31',
            'region' => $this->region,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey
            ]
        ]);
    }
}
