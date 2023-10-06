<?php

namespace IPS\awsses\Manager;

class _Manager
{
    /**
     * AWS Credentials
     *
     * @var mixed
     */
    protected $accessKey;
    protected $secretKey;
    protected $region;

    /**
     * Constructor
     *
     * @param          $accessKey
     * @param          $secretKey
     * @param string   $region
     */
    public function __construct($accessKey = null, $secretKey = null, $region = null)
    {
        // Decrypt the secret key
        $decryptedSecret = null;
        if (\IPS\Settings::i()->awsses_secret_key) {
            $decryptedSecret = \IPS\Text\Encrypt::fromTag(\IPS\Settings::i()->awsses_secret_key)->decrypt();
        }

        // Set class properties
        $this->accessKey = $accessKey ?? \IPS\Settings::i()->awsses_access_key;
        $this->secretKey = $secretKey ?? $decryptedSecret;
        $this->region = $region ?? (\IPS\Settings::i()->awsses_region ?? 'us-west-2');
    }
}
