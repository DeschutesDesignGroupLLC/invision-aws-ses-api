<?php

namespace IPS\awsses\modules\admin\complaints;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * logs
 */
class _logs extends \IPS\Dispatcher\Controller
{
	/**
	 * @brief   Has been CSRF-protected
	 */
	public static $csrfProtected = true;

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		// Check permissions
		\IPS\Dispatcher::i()->checkAcpPermission( 'logs_manage' );

		// Call parent
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{		
		// Create the table
		$table = new \IPS\Helpers\Table\Db('awsses_complaint_logs', \IPS\Http\Url::internal('app=awsses&module=complaints&controller=logs'));
		$table->langPrefix = 'log_';
		$table->include = array( 'date', 'member_id', 'email', 'action' );
		$table->sortBy = $table->sortBy ?: 'date';
		$table->sortDirection = $table->sortDirection ?: 'desc';
		$table->rowClasses = array( 'messageId' => array( 'ipsTable_wrap ' ));

		// Quick Search
		$table->quickSearch = function ($search) {
			return array("action LIKE '%{$search}%'");
		};

		// Column widths
		$table->widths = array(
			'date' => '15',
			'member_id' => '15',
			'email' => '25'
		);

		// Table parsers
		$table->parsers = array(
			'date' => function ($val) {
				return \IPS\DateTime::ts($val);
			},
			'member_id' => function ($val) {
				$member = \IPS\Member::load($val);
				return "<a href='{$member->acpUrl()}' target='_blank'>{$member->name}</a>";
			},
			'email' => function ($val, $row) {
				return \IPS\Member::load($row['member_id'])->email;
			},
			'action' => function ($val) {
				return \IPS\Member::loggedIn()->language()->addToStack("awsses_action_$val");
			}
		);

		// Row Buttons
		$table->rowButtons = function ($row) {
			return array(
				'delete'    => array(
					'title' => 'delete',
					'icon'  => 'times-circle',
					'link'  => \IPS\Http\Url::internal('app=awsses&module=complaints&controller=logs&do=delete')->setQueryString('id', $row['id']),
					'data'  => array( 'delete' => '' )
				)
			);
		};

		// Display the table
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_complaint_logs');
		\IPS\Output::i()->output = $table;
	}

	/**
	 * Delete a log
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Try and load the log
		try {
			// Load the log
			$log = \IPS\awsses\Complaint\Log::load(\IPS\Request::i()->id);
		}

		// Unable to load the log
		catch (\OutOfRangeException $e) {
			// Return error
			\IPS\_Output::i()->error('awsses_error_log_not_found', '1AWSSES/1', 404);
		}

		// Make sure we confirmed deletion
		\IPS\Request::i()->confirmedDelete();

		// Delete the log
		$log->delete();

		// Redirect
		\IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=awsses&module=complaints&controller=logs'), 'deleted');
	}
}