<?php

namespace IPS\awsses\Manager;

use IPS\Settings;
use IPS\Text\Encrypt;

class _Manager
{
    protected ?string $accessKey;

    protected ?string $secretKey;

    protected ?string $region;

    public function __construct($accessKey = null, $secretKey = null, $region = null)
    {
        $decryptedSecret = null;
        if (Settings::i()->awsses_secret_key) {
            $decryptedSecret = Encrypt::fromTag(Settings::i()->awsses_secret_key)->decrypt();
        }

        $this->accessKey = $accessKey ?? Settings::i()->awsses_access_key;
        $this->secretKey = $secretKey ?? $decryptedSecret;
        $this->region = $region ?? (Settings::i()->awsses_region ?? 'us-west-2');
    }
}
