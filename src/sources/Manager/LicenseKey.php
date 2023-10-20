<?php

namespace IPS\awsses\Manager;

use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Log;
use IPS\Patterns\Singleton;
use IPS\Settings;

class _LicenseKey extends Singleton
{
    public function isValid(): bool
    {
        if (! Store::i()->awsses_license_fetched || Store::i()->awsses_license_fetched < (time() - 1814400)) {
            $this->fetchLicenseStatus();
        }

        return (bool) Store::i()->awsses_license_status;
    }

    public function fetchLicenseStatus(): bool
    {
        $response = Url::external('https://api.lemonsqueezy.com/v1/licenses/validate')
            ->request()
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(
                json_encode([
                    'license_key' => Settings::i()->awsses_license_key,
                ])
            );

        $content = $response->decodeJson();

        $valid = $response->isSuccessful() && array_key_exists('valid', $content) && $content['valid'] === true;

        Store::i()->awsses_license_status = $valid;
        Store::i()->awsses_license_fetched = time();

        $payload = json_encode($content);

        Log::log("Fetched license key data. Payload: $payload", 'awsses');

        return $valid;
    }
}
