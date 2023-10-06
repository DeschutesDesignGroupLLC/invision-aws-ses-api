<?php

namespace IPS\awsses\Outgoing;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

use Aws\Result;

/**
 * Class Log
 *
 * @package IPS\awsses\Outgoing
 */
class _Log extends \IPS\Node\Model
{
    /**
     * [ActiveRecord] Multiton Store
     *
     * @var string
     */
    protected static $multitons;

    /**
     * [ActiveRecord] Database Table
     *
     * @var string
     */
    public static $databaseTable = 'awsses_mail_logs';

    /**
     * [ActiveRecord] Database Prefix
     *
     * @var string
     */
    public static $databaseColumnId = 'id';

    /**
     * [Node] Order Database Column
     *
     * @var string
     */
    public static $databaseColumnOrder = 'date';

    /**
     * [Node] Node Title
     *
     * @var string
     */
    public static $nodeTitle = 'Outgoing Logs';

    /**
     * [Node] Node Modal View
     *
     * @var bool
     */
    public static $modalForms = false;

    /**
     * [Node] Node ACP Restrictions
     *
     * @var array
     */
    protected static $restrictions = [
        'app' => 'awsses',
        'module' => 'system',
        'prefix' => 'logs_'
    ];

    /**
     * [Node] Node Permission Language Prefix
     *
     * @var string
     */
    public static $permissionLangPrefix = 'perm_log_';

    /**
     * [Node] Node Permission App
     *
     * @var string
     */
    public static $permApp = 'awsses';

    /**
     * [Node] Node Permission Type
     *
     * @var string
     */
    public static $permType = 'log';

    /**
     * [Node] Node Permission Map
     *
     * @var array
     */
    public static $permissionMap = [
        'view' => 'view',
    ];

    /**
     * Log a Successful Email
     *
     * @param Result $result
     * @param          $to
     * @param null $fromName
     * @param null $fromEmail
     * @param null $subject
     *
     * @return null
     */
    public static function log($payload = [], $messageId = null, $exception = null, $errorMessage = null)
    {
        // Unset the Message Body
        if (isset($payload['Message']['Body']['Html'])) {
            // Remove it from the payload
            unset($payload['Message']['Body']['Html']);
        }

        // Create our new log
        $log = new static();
        $log->date = time();
        $log->payload = $payload ? json_encode($payload) : null;
        $log->messageId = $messageId;
        $log->exception = $exception ? json_encode($exception) : null;
        $log->errorMessage = $errorMessage;
        $log->save();
    }

    /**
     * Prune logs
     *
     * @param int $days Older than (days) to prune
     *
     * @return  void
     */
    public static function pruneLogs($days)
    {
        // Select from the database where date is greater than
        \IPS\Db::i()->delete(static::$databaseTable, [
            'date<?',
            \IPS\DateTime::create()->sub(new \DateInterval('P' . $days . 'D'))->getTimestamp()
        ]);
    }

    public function codingStandards()
    {
        return null;
    }
}
