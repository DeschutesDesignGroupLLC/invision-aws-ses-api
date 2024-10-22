<?php

namespace IPS\awsses\Bounce;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

use Aws\Result;

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
    public static $databaseTable = 'awsses_bounce_logs';

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
    public static $nodeTitle = 'Bounce Logs';

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
        'prefix' => 'logs_',
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
    public static $permType = 'bounce_log';

    /**
     * [Node] Node Permission Map
     *
     * @var array
     */
    public static $permissionMap = [
        'view' => 'view',
    ];

    /**
     * Log a Bounced Email
     *
     * @param  Result  $result
     * @param    $to
     * @param  null  $fromName
     * @param  null  $fromEmail
     * @param  null  $subject
     * @return null
     */
    public static function log($member, $email, $action, $type)
    {
        // Create our new log
        $log = new static();
        $log->date = time();
        $log->member_id = $member instanceof \IPS\Member ? $member->member_id : null;
        $log->email = $email;
        $log->type = $type;
        $log->action = $action;
        $log->save();
    }

    /**
     * Prune logs
     *
     * @param  int  $days Older than (days) to prune
     * @return  void
     */
    public static function pruneLogs($days)
    {
        // Select from the database where date is greater than
        \IPS\Db::i()->delete(static::$databaseTable, [
            'date<?',
            \IPS\DateTime::create()->sub(new \DateInterval('P'.$days.'D'))->getTimestamp(),
        ]);
    }

    public function codingStandards()
    {
        return null;
    }
}
