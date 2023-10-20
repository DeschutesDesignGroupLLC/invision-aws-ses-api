<?php

namespace IPS\awsses;

class _Application extends \IPS\Application
{
    public function __construct()
    {
        if (! class_exists('Aws\\Ses\\SesClient') || ! class_exists('Aws\\Sns\\SnsClient')) {
            require_once static::getRootPath().'/applications/awsses/sources/vendor/autoload.php';
        }
    }

    protected function get__icon(): string
    {
        return 'amazon';
    }
}
