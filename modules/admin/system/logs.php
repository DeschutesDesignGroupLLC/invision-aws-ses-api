<?php

namespace IPS\awsses\modules\admin\system;

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
	 * @brief	Has been CSRF-protected
	 */
	public static $csrfProtected = TRUE;

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
	 * Display Logs
	 *
	 * @return	void
	 */
	protected function manage()
	{
		// Create the table
		$table = new \IPS\Helpers\Table\Db( \IPS\awsses\Outgoing\Log::$databaseTable, \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs' ) );
		$table->langPrefix = 'log_';
		$table->include = array( 'date', 'status', 'subject', 'to', 'messageId');
		$table->sortBy = $table->sortBy ?: 'date';
		$table->sortDirection = $table->sortDirection ?: 'desc';
		$table->quickSearch = array( array( 'recipient' ), 'recipient' );
		$table->rowClasses = array( 'messageId' => array( 'ipsTable_wrap ' ));

		// Table parsers
		$table->parsers = array(
			'date' => function( $val )
			{
				// Return the date
				return \IPS\DateTime::ts( $val );
			},
			'status' => function($val, $row)
			{
				// Return the status
				return \IPS\Theme::i()->getTemplate('logs', 'awsses', 'admin')->status(isset($row['messageId']) ? true : false);
			},
			'to' => function($val, $row)
			{
				// Return the date
				$payload = json_decode($row['payload'], TRUE);
				return array_key_exists('ToAddresses', $payload['Destination']) ? implode(', ', $payload['Destination']['ToAddresses']) : NULL;
			},
			'subject' => function($val, $row)
			{
				// Return the recipient
				$payload = json_decode($row['payload'], TRUE);
				return array_key_exists('Subject', $payload['Message']) ? $payload['Message']['Subject']['Data'] : NULL;
			}
		);

		// Row Buttons
		$table->rowButtons = function( $row ) {
			return array(
				'view'		=> array(
					'title'	=> 'view',
					'icon'	=> 'search',
					'link'	=> \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs&do=view' )->setQueryString( 'id', $row['id'] )
				),
				'delete'	=> array(
					'title'	=> 'delete',
					'icon'	=> 'times-circle',
					'link'	=> \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs&do=delete' )->setQueryString( 'id', $row['id'] ),
					'data'	=> array( 'delete' => '' )
				)
			);
		};

		// Add prune settings
		if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'awsses', 'logs', 'logs_prune_settings' ) )
		{
			// Add prune button
			\IPS\Output::i()->sidebar['actions'] = array(
				'settings'	=> array(
					'title'	=> 'awsses_logs_prune',
					'icon' => 'cog',
					'link' => \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs&do=pruneSettings' ),
					'data' => array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('awsses_logs_prune') )
				)
			);
		}

		// Output it
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_logs');
		\IPS\Output::i()->output = $table;
	}

	/**
	 * View a log
	 *
	 * @return void
	 */
	protected function view()
	{
		// Try and load the log
		try {

			// Load the log
			$log = \IPS\awsses\Outgoing\Log::load( \IPS\Request::i()->id );
		}

		// Unable to load the log
		catch ( \OutOfRangeException $e ) {

			// Return an error
			\IPS\Output::i()->error( 'node_error', '2C324/1', 404, '' );
		}

		// Add delete button
		\IPS\Output::i()->sidebar['actions']['delete'] = array(
			'icon'	=> 'times-circle',
			'link'	=> \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs&do=delete' )->setQueryString( 'id', $log->id ),
			'title'	=> 'delete',
			'data'	=> array( 'confirm' => '' )
		);

		// Display the log
		\IPS\Output::i()->title	 = \IPS\Member::loggedIn()->language()->addToStack('awsses_log');
		\IPS\Output::i()->breadcrumb[] = array( \IPS\Http\Url::internal( "app=awsses&module=system&controller=logs" ), \IPS\Member::loggedIn()->language()->addToStack('awsses_logs') );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'logs', 'awsses', 'admin' )->log( $log );
	}


	/**
	 * Delete a log
	 *
	 * @return void
	 */
	protected function delete()
	{
		// Try and load the log
		try
		{
			// Load the log
			$log = \IPS\awsses\Outgoing\Log::load( \IPS\Request::i()->id );
		}

		// Unable to load the log
		catch ( \OutOfRangeException $e )
		{
			// Return error
			\IPS\Output::i()->error( 'node_error', '2C324/2', 404, '' );
		}

		// Make sure we confirmed deletion
		\IPS\Request::i()->confirmedDelete();

		// Delete the log
		$log->delete();

		// Redirect
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs' ), 'deleted' );
	}

	/**
	 * Prune Settings
	 *
	 * @return	void
	 */
	protected function pruneSettings()
	{
		// Check permissions
		\IPS\Dispatcher::i()->checkAcpPermission( 'logs_prune_settings' );

		// Create our form
		$form = new \IPS\Helpers\Form;
		$form->add( new \IPS\Helpers\Form\Number( 'awsses_log_prune_settings', \IPS\Settings::i()->awsses_log_prune_settings, FALSE, array( 'unlimited' => 0, 'unlimitedLang' => 'never' ), NULL, \IPS\Member::loggedIn()->language()->addToStack('after'), \IPS\Member::loggedIn()->language()->addToStack('days'), 'prune_log_moderator' ) );

		// If we have values
		if ( $values = $form->values() )
		{
			// Save the form settings
			$form->saveAsSettings();

			// Redirect back to logs
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=awsses&module=system&controller=logs' ), 'saved' );
		}

		// Set title and output
		\IPS\Output::i()->title	= \IPS\Member::loggedIn()->language()->addToStack( 'awsses_log_prune_settings' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core', 'admin' )->block( 'awsses_log_prune_settings', $form, FALSE );
	}
}