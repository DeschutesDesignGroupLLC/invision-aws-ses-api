<?php

namespace IPS\awsses\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * complaints
 */
class _complaints extends \IPS\Dispatcher\Controller
{
    /**
     * @brief   Has been CSRF-protected
     */
    public static $csrfProtected = true;

    /**
     * Execute
     *
     * @return  void
     */
    public function execute()
    {
        // Check permissions
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');

        // Call parent
        parent::execute();
    }

    /**
     * Settings form
     *
     * @return  void
     */
    protected function manage()
    {
        // Modify groups array
        $groups = [];
        foreach (\IPS\Member\Group::groups() as $group) {
            $groups[$group->g_id] = $group->name;
        }

        // Create a new form
        $form = new \IPS\Helpers\Form;
        $form->addMessage('awsses_settings_bounce_message', 'ipsPad ipsMessage ipsMessage_warning ipsType_reset ipsSpacer_top');
        $form->addMessage('awsses_settings_header_complaints');
        $form->add(new \IPS\Helpers\Form\Interval('awsses_complaint_interval', \IPS\Settings::i()->awsses_complaint_interval, true, [
            'unlimited' => '-1',
            'unlimitedLang' => 'awsses_form_process_immediately'
        ]));
        $form->add(new \IPS\Helpers\Form\Select('awsses_complaint_action', \IPS\Settings::i()->awsses_complaint_action, true, [
            'options' => [
                \IPS\awsses\Manager\SES::AWSSES_ACTION_NOTHING => 'Do Nothing',
                \IPS\awsses\Manager\SES::AWSSES_ACTION_MOVE_GROUP => 'Add/Move To A Group',
                \IPS\awsses\Manager\SES::AWSSES_ACTION_SET_VALIDATING => 'Set Member As Validating',
                \IPS\awsses\Manager\SES::AWSSES_ACTION_SET_SPAMMER => 'Flag As Spammer',
                \IPS\awsses\Manager\SES::AWSSES_ACTION_DELETE_MEMBER => 'Delete Recipient',
                \IPS\awsses\Manager\SES::AWSSES_ACTION_TEMP_BAN => 'Temporarily Ban'
            ],
            'multiple' => true,
            'toggles' => [
                'group' => ['awsses_complaint_action_group'],
            ]
        ]));
        $form->add(new \IPS\Helpers\Form\Select('awsses_complaint_action_group', \IPS\Settings::i()->awsses_complaint_action_group, false, ['options' => $groups], null, null, null, 'awsses_complaint_action_group'));

        // If we have values in our form
        if ($values = $form->values()) {
            // Log
            \IPS\Session::i()->log('awsses_settings_updated');

            // Save the settings
            $form->saveAsSettings($values);
        }

        // Output the form
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
        \IPS\Output::i()->output = $form;
    }
}
