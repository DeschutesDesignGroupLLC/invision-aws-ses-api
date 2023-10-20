<?php

namespace IPS\awsses\modules\admin\system;

use IPS\awsses\Manager\SES;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Interval;
use IPS\Helpers\Form\Select;
use IPS\Helpers\Form\YesNo;
use IPS\Member;
use IPS\Member\Group;
use IPS\Output;
use IPS\Session;
use IPS\Settings;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _bounces extends Controller
{
    public static bool $csrfProtected = true;

    public function execute(): void
    {
        Dispatcher::i()->checkAcpPermission('settings_manage');

        parent::execute();
    }

    protected function manage(): void
    {
        $form = new Form();

        $groups = [];
        foreach (Group::groups() as $group) {
            $groups[$group->g_id] = $group->name;
        }

        $form->addTab('awsses_settings_tab_soft_bounces');
        $form->addMessage('awsses_settings_bounce_message', 'ipsPad ipsMessage ipsMessage_warning ipsType_reset ipsSpacer_top');
        $form->addMessage('awsses_settings_header_bounces');
        $form->add(new Interval('awsses_soft_bounce_interval', Settings::i()->awsses_soft_bounce_interval, true, [
            'unlimited' => '-1',
            'unlimitedLang' => 'awsses_form_process_immediately',
        ]));
        $form->add(new YesNo('awsses_soft_bounce_ignore_admins', Settings::i()->awsses_soft_bounce_ignore_admins, true));
        $form->add(new Select('awsses_soft_bounce_action', Settings::i()->awsses_soft_bounce_action, true, [
            'options' => [
                SES::AWSSES_ACTION_NOTHING => 'Do Nothing',
                SES::AWSSES_ACTION_MOVE_GROUP => 'Add/Move To A Group',
                SES::AWSSES_ACTION_SET_VALIDATING => 'Set Member As Validating',
                SES::AWSSES_ACTION_SET_SPAMMER => 'Flag As Spammer',
                SES::AWSSES_ACTION_DELETE_MEMBER => 'Delete Recipient',
                SES::AWSSES_ACTION_TEMP_BAN => 'Temporarily Ban',
                SES::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL => 'Unsubscribe From Admin Emails/Newsletter',
            ],
            'multiple' => true,
            'toggles' => [
                'group' => ['awsses_soft_bounce_action_group'],
            ],
        ]));
        $form->add(new Select('awsses_soft_bounce_action_group', Settings::i()->awsses_soft_bounce_action_group, false, ['options' => $groups], null, null, null, 'awsses_soft_bounce_action_group'));

        $form->addTab('awsses_settings_tab_hard_bounces');
        $form->addMessage('awsses_settings_bounce_message', 'ipsPad ipsMessage ipsMessage_warning ipsType_reset ipsSpacer_top');
        $form->addMessage('awsses_settings_header_bounces');
        $form->add(new Interval('awsses_hard_bounce_interval', Settings::i()->awsses_hard_bounce_interval, true, [
            'unlimited' => '-1',
            'unlimitedLang' => 'awsses_form_process_immediately',
        ]));
        $form->add(new YesNo('awsses_hard_bounce_ignore_admins', Settings::i()->awsses_hard_bounce_ignore_admins, true));
        $form->add(new Select('awsses_hard_bounce_action', Settings::i()->awsses_hard_bounce_action, true, [
            'options' => [
                SES::AWSSES_ACTION_NOTHING => 'Do Nothing',
                SES::AWSSES_ACTION_MOVE_GROUP => 'Add/Move To A Group',
                SES::AWSSES_ACTION_SET_VALIDATING => 'Set Member As Validating',
                SES::AWSSES_ACTION_SET_SPAMMER => 'Flag As Spammer',
                SES::AWSSES_ACTION_DELETE_MEMBER => 'Delete Recipient',
                SES::AWSSES_ACTION_TEMP_BAN => 'Temporarily Ban',
                SES::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL => 'Unsubscribe From Admin Emails/Newsletter',
            ],
            'multiple' => true,
            'toggles' => [
                'group' => ['awsses_hard_bounce_action_group'],
            ],
        ]));
        $form->add(new Select('awsses_hard_bounce_action_group', Settings::i()->awsses_hard_bounce_action_group, false, ['options' => $groups], null, null, null, 'awsses_hard_bounce_action_group'));

        if ($values = $form->values()) {
            Session::i()->log('awsses_settings_updated');

            $form->saveAsSettings($values);
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack('settings');
        Output::i()->output = $form;
    }
}
