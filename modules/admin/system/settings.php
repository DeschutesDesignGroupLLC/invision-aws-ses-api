<?php

namespace IPS\awsses\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
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
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );

		// Call parent
		parent::execute();
	}

	/**
	 * Settings form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		// Create a new form
		$form = new \IPS\Helpers\Form;

		// Decrypt the secret key
		$secret = NULL;
		if (\IPS\Settings::i()->awsses_secret_key) {
			$secret = \IPS\Text\Encrypt::fromTag(\IPS\Settings::i()->awsses_secret_key)->decrypt();
		}

		// Add settings
		$form->addHeader(\IPS\Member::loggedIn()->language()->addToStack('awsses_settings_header'));
		$form->addMessage('awsses_settings_email_override_message', 'ipsPad ipsMessage ipsMessage_warning ipsType_reset ipsSpacer_top');
		$form->addMessage('awsses_settings_message');
		$form->add( new \IPS\Helpers\Form\YesNo( 'awsses_enabled', \IPS\Settings::i()->awsses_enabled, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'awsses_access_key', \IPS\Settings::i()->awsses_access_key, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Password( 'awsses_secret_key', $secret, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Text( 'awsses_region', \IPS\Settings::i()->awsses_region, TRUE, array( 'placeholder' => 'us-west-2', '' ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'awsses_config_set_name', \IPS\Settings::i()->awsses_config_set_name, FALSE ) );

		// If we have values in our form
		if ( $values = $form->values() )
		{
			// Log
			\IPS\Session::i()->log('awsses_settings_updated');

			// Encrypt the secret key
			if ($values['awsses_secret_key']) {
				$values['awsses_secret_key'] = \IPS\Text\Encrypt::fromPlaintext($values['awsses_secret_key'])->tag();
			}

			// Save the settings
			$form->saveAsSettings($values);
		}

		// Output the form
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'settings' );
		\IPS\Output::i()->output = $form;
	}
}