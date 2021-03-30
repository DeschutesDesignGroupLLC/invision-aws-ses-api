<?php

namespace IPS\awsses\Manager;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
	header(( isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden');
	exit;
}

class _SES
{
	/**
	 * Actions on receipt of bounce or complaint
	 */
	const AWSSES_ACTION_NOTHING = 'nothing';
	const AWSSES_ACTION_MOVE_GROUP = 'group';
	const AWSSES_ACTION_SET_VALIDATING = 'validating';
	const AWSSES_ACTION_SET_SPAMMER = 'spam';
	const AWSSES_ACTION_DELETE_MEMBER = 'delete';
	const AWSSES_ACTION_TEMP_BAN = 'ban';

	/**
	 * @param  array  $emailAddresses
	 */
	public function processSoftBouncedEmailAddresses($emailAddresses = array())
	{
		// Make sure the email address is an array
		if (!\is_array($emailAddresses)) {
			$emailAddresses = [$emailAddresses];
		}

		// Get soft bounce settings
		$actions = \IPS\Settings::i()->awsses_soft_bounce_action;

		// Loop through the email addresses
		foreach ($emailAddresses as $emailAddress) {

			// Make sure nothing is not checked
			if (!\in_array(static::AWSSES_ACTION_NOTHING, $actions)) {

				// Try to find the member
				$member = \IPS\Member::load($emailAddress, 'email');

				// If we found the member
				if ($member->email) {

					// Loop through the actions
					foreach ($actions as $action) {

						// Switch between the actions
						switch ($action) {

							// Move Groups
							case static::AWSSES_ACTION_MOVE_GROUP:
								$this->_moveToGroup($member, \IPS\Settings::i()->awsses_soft_bounce_action_group);
								$this->_logBounceAction($member, static::AWSSES_ACTION_MOVE_GROUP, 'soft');
								break;

							// Set validating
							case static::AWSSES_ACTION_SET_VALIDATING:
								$this->_setAsValidating($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_SET_VALIDATING, 'soft');
								break;

							// Set as spammer
							case static::AWSSES_ACTION_SET_SPAMMER:
								$this->_setAsSpammer($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_SET_SPAMMER, 'soft');
								break;

							// Delete member
							case static::AWSSES_ACTION_DELETE_MEMBER:
								$this->_deleteMember($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_DELETE_MEMBER, 'soft');
								break;

							// Temp Ban
							case static::AWSSES_ACTION_TEMP_BAN:
								$this->_tempBan($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_TEMP_BAN, 'soft');
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param  array  $emailAddresses
	 */
	public function processHardBouncedEmailAddresses($emailAddresses = array())
	{
		// Make sure the email address is an array
		if (!\is_array($emailAddresses)) {
			$emailAddresses = [$emailAddresses];
		}

		// Get hard bounce settings
		$actions = \IPS\Settings::i()->awsses_hard_bounce_action;

		// Loop through the email addresses
		foreach ($emailAddresses as $emailAddress) {

			// Make sure nothing is not checked
			if (!\in_array(static::AWSSES_ACTION_NOTHING, $actions)) {

				// Try to find the member
				$member = \IPS\Member::load($emailAddress, 'email');

				// If we found the member
				if ($member->email) {

					// Loop through the actions
					foreach ($actions as $action) {

						// Switch between the actions
						switch ($action) {

							// Move Groups
							case static::AWSSES_ACTION_MOVE_GROUP:
								$this->_moveToGroup($member, \IPS\Settings::i()->awsses_hard_bounce_action_group);
								$this->_logBounceAction($member, static::AWSSES_ACTION_MOVE_GROUP, 'hard');
								break;

							// Set validating
							case static::AWSSES_ACTION_SET_VALIDATING:
								$this->_setAsValidating($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_SET_VALIDATING, 'hard');
								break;

							// Set as spammer
							case static::AWSSES_ACTION_SET_SPAMMER:
								$this->_setAsSpammer($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_SET_SPAMMER, 'hard');
								break;

							// Delete member
							case static::AWSSES_ACTION_DELETE_MEMBER:
								$this->_deleteMember($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_DELETE_MEMBER, 'hard');
								break;

							// Temp Ban
							case static::AWSSES_ACTION_TEMP_BAN:
								$this->_tempBan($member);
								$this->_logBounceAction($member, static::AWSSES_ACTION_TEMP_BAN, 'hard');
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param  array  $emailAddresses
	 */
	public function processComplaintEmailAddresses($emailAddresses = array())
	{
		// Make sure the email address is an array
		if (!\is_array($emailAddresses)) {
			$emailAddresses = [$emailAddresses];
		}

		// Get complaint settings
		$actions = \IPS\Settings::i()->awsses_complaint_action;

		// Loop through the email addresses
		foreach ($emailAddresses as $emailAddress) {

			// Make sure nothing is not checked
			if (!\in_array(static::AWSSES_ACTION_NOTHING, $actions)) {

				// Try to find the member
				$member = \IPS\Member::load($emailAddress, 'email');

				// If we found the member
				if ($member->email) {

					// Loop through the actions
					foreach ($actions as $action) {

						// Switch between the actions
						switch ($action) {

							// Move Groups
							case static::AWSSES_ACTION_MOVE_GROUP:
								$this->_moveToGroup($member, \IPS\Settings::i()->awsses_complaint_action_group);
								$this->_logComplaintAction($member, static::AWSSES_ACTION_MOVE_GROUP);
								break;

							// Set validating
							case static::AWSSES_ACTION_SET_VALIDATING:
								$this->_setAsValidating($member);
								$this->_logComplaintAction($member, static::AWSSES_ACTION_SET_VALIDATING);
								break;

							// Set as spammer
							case static::AWSSES_ACTION_SET_SPAMMER:
								$this->_setAsSpammer($member);
								$this->_logComplaintAction($member, static::AWSSES_ACTION_SET_SPAMMER);
								break;

							// Delete member
							case static::AWSSES_ACTION_DELETE_MEMBER:
								$this->_deleteMember($member);
								$this->_logComplaintAction($member, static::AWSSES_ACTION_DELETE_MEMBER);
								break;

							// Temp Ban
							case static::AWSSES_ACTION_TEMP_BAN:
								$this->_tempBan($member);
								$this->_logComplaintAction($member, static::AWSSES_ACTION_TEMP_BAN);
								break;
						}
					}
				}
			}
		}
	}

	/**
	 * @param  null  $member
	 * @param  null  $group
	 */
	protected function _moveToGroup($member = null, $group = null)
	{
		// Add the member to the group
		$groups = explode(',', $member->mgroup_others);
		$groups[] = $group;
		$member->mgroup_others = implode(',', array_filter($groups));
		$member->save();
	}

	/**
	 * @param  null  $member
	 */
	protected function _setAsValidating($member = null)
	{
		// Add validation entry
		$vid = md5( $member->members_pass_hash . \IPS\Login::generateRandomString() );
		\IPS\Db::i()->insert( 'core_validating', array(
			'vid' => $vid,
			'member_id' => $member->member_id,
			'user_verified' => false,
			'spam_flag' => false,
			'entry_date' => time()
		) );

		// Set the member as validating
		$member->members_bitoptions['validating'] = true;
		$member->save();
	}

	/**
	 * @param  null  $member
	 */
	protected function _setAsSpammer($member = null)
	{
		// Set as spammer
		$member->flagAsSpammer();
	}

	/**
	 * @param  null  $member
	 */
	protected function _tempBan($member = null)
	{
		// Place ban
		$member->temp_ban = -1;
		$member->save();
	}

	/**
	 * @param  null  $member
	 */
	protected function _deleteMember($member = null)
	{
		// Set as spammer
		$member->delete();
	}

	/**
	 * @param  null    $member
	 * @param  null    $action
	 * @param  string  $type
	 */
	protected function _logBounceAction($member = null, $action = null, $type = 'soft')
	{
		// Create our log
		\IPS\awsses\Bounce\Log::log($member, $action, $type);
	}

	/**
	 * @param  null  $member
	 * @param  null  $action
	 */
	protected function _logComplaintAction($member = null, $action = null)
	{
		// Create our log
		\IPS\awsses\Complaint\Log::log($member, $action);
	}
}