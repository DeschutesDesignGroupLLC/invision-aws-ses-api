<?php

namespace IPS\awsses\Manager;

use Aws\Sns\SnsClient;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _SNS extends Manager
{
    public SnsClient $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new SnsClient([
            'version' => '2010-03-31',
            'region' => $this->region,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ],
        ]);
    }
}
