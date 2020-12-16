<?php

namespace IPS\awsses\Outgoing;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

use Aws\Result;
use Aws\Exception\AwsException;

/**
 * Class Log
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
	public static $databaseTable = 'awsses_logs';

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
	public static $nodeTitle = 'Logs';

	/**
	 * [Node] Node Modal View
	 *
	 * @var bool
	 */
	public static $modalForms = FALSE;

	/**
	 * [Node] Node ACP Restrictions
	 *
	 * @var array
	 */
	protected static $restrictions = array(
		'app' => 'awsses',
		'module' => 'system',
		'prefix' => 'logs_'
	);

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
	public static $permissionMap = array(
		'view' => 'view',
	);

	/**
	 * Log a Successful Email
	 *
	 * @param  Result  $result
	 * @param          $to
	 * @param  null    $fromName
	 * @param  null    $fromEmail
	 * @param  null    $subject
	 *
	 * @return null
	 */
	public static function log($payload = array(), $messageId = NULL, $exception = NULL, $errorMessage = NULL)
	{
		// Unset the Message Body
		if (isset($payload['Message']['Body']['Html'])) {

			// Remove it from the payload
			unset($payload['Message']['Body']['Html']);
		}

		// Create our new log
		$log = new static;
		$log->date = time();
		$log->payload = $payload ? json_encode($payload) : NULL;
		$log->messageId = $messageId;
		$log->exception = $exception ? json_encode($exception) : NULL;
		$log->errorMessage = $errorMessage;
		$log->save();
	}

	/**
	 * Prune logs
	 *
	 * @param	int		$days	Older than (days) to prune
	 * @return	void
	 */
	public static function pruneLogs( $days )
	{
		// Select from the database where date is greater than
		\IPS\Db::i()->delete( static::$databaseTable, array( 'date<?', \IPS\DateTime::create()->sub( new \DateInterval( 'P' . $days . 'D' ) )->getTimestamp() ) );
	}

	public function codingStandards() {
		return NULL;
	}
}