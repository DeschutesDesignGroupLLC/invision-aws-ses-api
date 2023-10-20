<?php

namespace IPS\awsses\Manager;

use Aws\Ses\SesClient;
use IPS\awsses\Bounce\Log;
use IPS\DateTime;
use IPS\Db;
use IPS\Login;
use IPS\Member;
use IPS\Settings;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _SES extends Manager
{
    public const AWSSES_ACTION_NOTHING = 'nothing';

    public const AWSSES_ACTION_MOVE_GROUP = 'group';

    public const AWSSES_ACTION_SET_VALIDATING = 'validating';

    public const AWSSES_ACTION_SET_SPAMMER = 'spam';

    public const AWSSES_ACTION_DELETE_MEMBER = 'delete';

    public const AWSSES_ACTION_TEMP_BAN = 'ban';

    public const AWSSES_ACTION_INTERVAL = 'interval';

    public const AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL = 'admin_mail';

    public ?string $configSet;

    public SesClient $client;

    public function __construct()
    {
        parent::__construct();

        $this->configSet = Settings::i()->awsses_config_set_name ?? null;

        $this->client = new SesClient([
            'version' => '2010-12-01',
            'region' => $this->region,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ],
        ]);
    }

    public function processSoftBouncedEmailAddresses($emailAddresses = []): void
    {
        if (! \is_array($emailAddresses)) {
            $emailAddresses = [$emailAddresses];
        }

        $actions = Settings::i()->awsses_soft_bounce_action;

        if (! \is_array($actions)) {
            $actions = [$actions];
        }

        foreach ($emailAddresses as $emailAddress) {
            if (! \in_array(static::AWSSES_ACTION_NOTHING, $actions) && \count($actions)) {
                $member = Member::load($emailAddress, 'email');

                if ($member->email) {
                    $process = $this->_shouldProcessAction($member, 'soft');

                    if ($process) {
                        foreach ($actions as $action) {
                            switch ($action) {
                                case static::AWSSES_ACTION_MOVE_GROUP:
                                    $this->_moveToGroup($member, Settings::i()->awsses_soft_bounce_action_group);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_MOVE_GROUP, 'soft');
                                    break;

                                case static::AWSSES_ACTION_SET_VALIDATING:
                                    $this->_setAsValidating($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_SET_VALIDATING, 'soft');
                                    break;

                                case static::AWSSES_ACTION_SET_SPAMMER:
                                    $this->_setAsSpammer($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_SET_SPAMMER, 'soft');
                                    break;

                                case static::AWSSES_ACTION_DELETE_MEMBER:
                                    $this->_deleteMember($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_DELETE_MEMBER, 'soft');
                                    break;

                                case static::AWSSES_ACTION_TEMP_BAN:
                                    $this->_tempBan($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_TEMP_BAN, 'soft');
                                    break;

                                case static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL:
                                    $this->_unsubsribeFromAdminEmails($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL, 'soft');
                                    break;
                            }
                        }
                    } else {
                        $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_INTERVAL, 'soft');
                    }
                } else {
                    $this->_logBounceAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING, 'soft');
                }
            } else {
                $this->_logBounceAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING, 'soft');
            }
        }
    }

    public function processHardBouncedEmailAddresses($emailAddresses = []): void
    {
        if (! \is_array($emailAddresses)) {
            $emailAddresses = [$emailAddresses];
        }

        $actions = Settings::i()->awsses_hard_bounce_action;

        if (! \is_array($actions)) {
            $actions = [$actions];
        }

        foreach ($emailAddresses as $emailAddress) {
            if (! \in_array(static::AWSSES_ACTION_NOTHING, $actions) && \count($actions)) {
                $member = Member::load($emailAddress, 'email');

                if ($member->email) {
                    $process = $this->_shouldProcessAction($member, 'hard');

                    if ($process) {
                        foreach ($actions as $action) {
                            switch ($action) {
                                case static::AWSSES_ACTION_MOVE_GROUP:
                                    $this->_moveToGroup($member, Settings::i()->awsses_hard_bounce_action_group);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_MOVE_GROUP, 'hard');
                                    break;

                                case static::AWSSES_ACTION_SET_VALIDATING:
                                    $this->_setAsValidating($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_SET_VALIDATING, 'hard');
                                    break;

                                case static::AWSSES_ACTION_SET_SPAMMER:
                                    $this->_setAsSpammer($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_SET_SPAMMER, 'hard');
                                    break;

                                case static::AWSSES_ACTION_DELETE_MEMBER:
                                    $this->_deleteMember($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_DELETE_MEMBER, 'hard');
                                    break;

                                case static::AWSSES_ACTION_TEMP_BAN:
                                    $this->_tempBan($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_TEMP_BAN, 'hard');
                                    break;

                                case static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL:
                                    $this->_unsubsribeFromAdminEmails($member);
                                    $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL, 'hard');
                                    break;
                            }
                        }
                    } else {
                        $this->_logBounceAction($member, $emailAddress, static::AWSSES_ACTION_INTERVAL, 'hard');
                    }
                } else {
                    $this->_logBounceAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING, 'hard');
                }
            } else {
                $this->_logBounceAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING, 'hard');
            }
        }
    }

    public function processComplaintEmailAddresses($emailAddresses = []): void
    {
        if (! \is_array($emailAddresses)) {
            $emailAddresses = [$emailAddresses];
        }

        $actions = Settings::i()->awsses_complaint_action;

        if (! \is_array($actions)) {
            $actions = [$actions];
        }

        foreach ($emailAddresses as $emailAddress) {
            if (! \in_array(static::AWSSES_ACTION_NOTHING, $actions) && \count($actions)) {
                $member = Member::load($emailAddress, 'email');

                if ($member->email) {
                    $process = $this->_shouldProcessAction($member, 'complaint');

                    if ($process) {
                        foreach ($actions as $action) {
                            switch ($action) {
                                case static::AWSSES_ACTION_MOVE_GROUP:
                                    $this->_moveToGroup($member, Settings::i()->awsses_complaint_action_group);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_MOVE_GROUP);
                                    break;

                                case static::AWSSES_ACTION_SET_VALIDATING:
                                    $this->_setAsValidating($member);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_SET_VALIDATING);
                                    break;

                                case static::AWSSES_ACTION_SET_SPAMMER:
                                    $this->_setAsSpammer($member);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_SET_SPAMMER);
                                    break;

                                case static::AWSSES_ACTION_DELETE_MEMBER:
                                    $this->_deleteMember($member);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_DELETE_MEMBER);
                                    break;

                                case static::AWSSES_ACTION_TEMP_BAN:
                                    $this->_tempBan($member);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_TEMP_BAN);
                                    break;

                                case static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL:
                                    $this->_unsubsribeFromAdminEmails($member);
                                    $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_UNSUBSCRIBE_ADMIN_EMAIL);
                                    break;
                            }
                        }
                    } else {
                        $this->_logComplaintAction($member, $emailAddress, static::AWSSES_ACTION_INTERVAL);
                    }
                } else {
                    $this->_logComplaintAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING);
                }
            } else {
                $this->_logComplaintAction(null, $emailAddress, static::AWSSES_ACTION_NOTHING);
            }
        }
    }

    protected function _shouldProcessAction($member, $action): bool
    {
        $process = true;

        switch ($action) {
            case 'soft':
                $interval = Settings::i()->awsses_soft_bounce_interval !== '-1' ? Settings::i()->awsses_soft_bounce_interval : false;

                if ($interval) {
                    try {
                        $log = Db::i()->select('*', Log::$databaseTable, [
                            'type=? AND member_id=?',
                            'soft',
                            $member->member_id,
                        ], 'date DESC')->first();
                        $last = DateTime::ts($log['date']);

                        $cutoff = new \DateTime();
                        $cutoff->sub(new \DateInterval("PT{$interval}S"));

                        if ($last < $cutoff) {
                            $process = false;
                        }
                    } catch (\UnderflowException $exception) {
                        $process = false;
                    }
                }

                $ignoreAdmins = Settings::i()->awsses_soft_bounce_ignore_admins;

                if ($ignoreAdmins && ($member->isAdmin() || $member->modPermissions())) {
                    $process = false;
                }

                break;
            case 'hard':
                $interval = Settings::i()->awsses_hard_bounce_interval !== '-1' ? Settings::i()->awsses_hard_bounce_interval : false;

                if ($interval) {
                    try {
                        $log = Db::i()->select('*', Log::$databaseTable, [
                            'type=? AND member_id=?',
                            'hard',
                            $member->member_id,
                        ], 'date DESC')->first();
                        $last = DateTime::ts($log['date']);

                        $cutoff = new \DateTime();
                        $cutoff->sub(new \DateInterval("PT{$interval}S"));

                        if ($last < $cutoff) {
                            $process = false;
                        }
                    } catch (\UnderflowException $exception) {
                        $process = false;
                    }
                }

                $ignoreAdmins = Settings::i()->awsses_hard_bounce_ignore_admins;

                if ($ignoreAdmins && ($member->isAdmin() || $member->modPermissions())) {
                    $process = false;
                }
                break;
            case 'complaint':
                $interval = Settings::i()->awsses_complaint_interval !== '-1' ? Settings::i()->awsses_complaint_interval : false;

                if ($interval) {
                    try {
                        $log = Db::i()->select('*', \IPS\awsses\Complaint\Log::$databaseTable, [
                            'member_id=?',
                            $member->member_id,
                        ], 'date DESC')->first();
                        $last = DateTime::ts($log['date']);

                        $cutoff = new \DateTime();
                        $cutoff->sub(new \DateInterval("PT{$interval}S"));

                        if ($last < $cutoff) {
                            $process = false;
                        }
                    } catch (\UnderflowException $exception) {
                        $process = false;
                    }
                }

                $ignoreAdmins = Settings::i()->awsses_complaint_ignore_admins;

                if ($ignoreAdmins && ($member->isAdmin() || $member->modPermissions())) {
                    $process = false;
                }
                break;
        }

        return $process;
    }

    protected function _moveToGroup($member = null, $group = null): void
    {
        $groups = explode(',', $member->mgroup_others);
        $groups[] = $group;
        $member->mgroup_others = implode(',', array_filter($groups));
        $member->save();
    }

    protected function _setAsValidating($member = null): void
    {
        $vid = md5($member->members_pass_hash.Login::generateRandomString());
        Db::i()->insert('core_validating', [
            'vid' => $vid,
            'member_id' => $member->member_id,
            'user_verified' => false,
            'spam_flag' => false,
            'entry_date' => time(),
        ]);

        $member->members_bitoptions['validating'] = true;
        $member->save();
    }

    protected function _setAsSpammer($member = null): void
    {
        $member->flagAsSpammer();
    }

    protected function _tempBan($member = null): void
    {
        $member->temp_ban = -1;
        $member->save();
    }

    protected function _deleteMember($member = null): void
    {
        $member->delete();
    }

    protected function _unsubsribeFromAdminEmails($member = null): void
    {
        $member->allow_admin_mails = false;
        $member->save();
    }

    protected function _logBounceAction($member = null, $email = null, $action = null, $type = 'soft'): void
    {
        Log::log($member, $email, $action, $type);
    }

    protected function _logComplaintAction($member = null, $email = null, $action = null): void
    {
        \IPS\awsses\Complaint\Log::log($member, $email, $action);
    }

    public function getSendingEmailAddress($fromEmail = null): ?string
    {
        $email = Settings::i()->awsses_default_verified_identity;

        $identities = explode(',', Settings::i()->awsses_verified_identities);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailArray = explode('@', $fromEmail);
            $domain = array_pop($emailArray);

            if ($fromEmail && (\in_array($fromEmail, $identities) || \in_array($domain, $identities))) {
                return $fromEmail;
            }
        }

        return $email;
    }
}
