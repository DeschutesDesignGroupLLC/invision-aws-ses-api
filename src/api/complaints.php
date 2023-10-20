<?php

namespace IPS\awsses\api;

use IPS\Api\Controller;
use IPS\Api\Response;
use IPS\awsses\Api\SES;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _complaints extends Controller
{
    /**
     * POST /awsses/complaints
     * Webhook for processing complaint notifications from AWS Simple Notification Service.
     *
     * @return		null
     */
    public function POSTindex()
    {
        // Handle POST request
        SES::i()->handleIncomingRequest('complaint');

        return new Response(200, [
            'status' => 'success',
        ]);
    }
}
