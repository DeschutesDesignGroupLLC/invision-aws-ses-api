<?php

namespace IPS\awsses\modules\admin\system;

use IPS\awsses\Outgoing\Log;
use IPS\DateTime;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Number;
use IPS\Helpers\Table\Db;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\Settings;
use IPS\Theme;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _logs extends Controller
{
    public static bool $csrfProtected = true;

    public function execute(): void
    {
        Dispatcher::i()->checkAcpPermission('logs_manage');

        parent::execute();
    }

    protected function manage(): void
    {
        $table = new Db(Log::$databaseTable, Url::internal('app=awsses&module=system&controller=logs'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'status', 'subject', 'from', 'to', 'cc', 'bcc'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap ']];

        $table->quickSearch = function ($search) {
            return ["payload LIKE '%{$search}%'"];
        };

        $table->parsers = [
            'date' => function ($val) {
                return DateTime::ts($val)->html();
            },
            'status' => function ($val, $row) {
                return Theme::i()->getTemplate('logs', 'awsses', 'admin')->status(isset($row['messageId']) ? true : false);
            },
            'from' => function ($val, $row) {
                $payload = json_decode($row['payload'], true);
                if (isset($payload['Source'])) {
                    if (\extension_loaded('imap')) {
                        $decoded = imap_mime_header_decode($payload['Source']);
                        $name = isset($decoded[0]) ? str_replace(['<', '>', '"'], '', $decoded[0]->text) : null;
                        $email = isset($decoded[1]) ? str_replace(['<', '>', '"'], '', $decoded[1]->text) : null;

                        return $email ? (string) $email : (string) $name;
                    }

                    return 'Please enable the PHP IMAP extension to view the source.';
                }

            },
            'to' => function ($val, $row) {
                $payload = json_decode($row['payload'], true);

                return isset($payload['Destination']['ToAddresses']) ? implode(', ', $payload['Destination']['ToAddresses']) : null;
            },
            'cc' => function ($val, $row) {
                $payload = json_decode($row['payload'], true);

                return isset($payload['Destination']['CcAddresses']) ? implode(', ', $payload['Destination']['CcAddresses']) : null;
            },
            'bcc' => function ($val, $row) {
                $payload = json_decode($row['payload'], true);

                return isset($payload['BccAddresses']['BccAddresses']) ? implode(', ', $payload['Destination']['BccAddresses']) : null;
            },
            'subject' => function ($val, $row) {
                $payload = json_decode($row['payload'], true);

                return isset($payload['Message']['Subject']) ? $payload['Message']['Subject']['Data'] : null;
            },
        ];

        $table->rowButtons = function ($row) {
            return [
                'view' => [
                    'title' => 'view',
                    'icon' => 'search',
                    'link' => Url::internal('app=awsses&module=system&controller=logs&do=view')->setQueryString('id', $row['id']),
                ],
                'delete' => [
                    'title' => 'delete',
                    'icon' => 'times-circle',
                    'link' => Url::internal('app=awsses&module=system&controller=logs&do=delete')->setQueryString('id', $row['id']),
                    'data' => ['delete' => ''],
                ],
            ];
        };

        $actions = [];

        if (Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => Member::loggedIn()->language()->addToStack('awsses_logs_prune'),
                ],
            ];
        }

        $actions['bounces'] = [
            'title' => 'awsses_bounce_logs',
            'icon' => 'exclamation-circle',
            'link' => Url::internal('app=awsses&module=system&controller=logs&do=bounces'),
        ];
        $actions['complaints'] = [
            'title' => 'awsses_complaint_logs',
            'icon' => 'exclamation-circle',
            'link' => Url::internal('app=awsses&module=system&controller=logs&do=complaints'),
        ];

        Output::i()->sidebar['actions'] = $actions;
        Output::i()->title = Member::loggedIn()->language()->addToStack('awsses_logs');
        Output::i()->output = $table;
    }

    protected function bounces(): void
    {
        $table = new Db('awsses_bounce_logs', Url::internal('app=awsses&module=system&controller=logs&do=bounces'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'member_id', 'email', 'type', 'action'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap']];

        $table->quickSearch = function ($search) {
            return ["action LIKE '%{$search}%'"];
        };

        $table->widths = [
            'date' => '15',
            'member_id' => '15',
            'email' => '20',
            'type' => '15',
        ];

        $table->parsers = [
            'date' => function ($val) {
                return DateTime::ts($val)->html();
            },
            'member_id' => function ($val) {
                $member = Member::load($val);

                return "<a href='{$member->acpUrl()}' target='_blank'>{$member->name}</a>";
            },
            'type' => function ($val) {
                $value = ucfirst($val);

                return "{$value} Bounce";
            },
            'action' => function ($val) {
                return Member::loggedIn()->language()->addToStack("awsses_action_$val");
            },
        ];

        $actions = [];

        if (Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => Member::loggedIn()->language()->addToStack('awsses_logs_prune'),
                ],
            ];
        }

        $actions['outgoing'] = [
            'title' => 'awsses_logs',
            'icon' => 'envelope',
            'link' => Url::internal('app=awsses&module=system&controller=logs'),
        ];
        $actions['complaints'] = [
            'title' => 'awsses_complaint_logs',
            'icon' => 'exclamation-circle',
            'link' => Url::internal('app=awsses&module=system&controller=logs&do=complaints'),
        ];

        Output::i()->sidebar['actions'] = $actions;
        Output::i()->title = Member::loggedIn()->language()->addToStack('awsses_bounce_logs');
        Output::i()->output = $table;
    }

    protected function complaints(): void
    {
        $table = new Db('awsses_complaint_logs', Url::internal('app=awsses&module=system&controller=logs&do=complaints'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'member_id', 'email', 'action'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap ']];

        $table->quickSearch = function ($search) {
            return ["action LIKE '%{$search}%'"];
        };

        $table->widths = [
            'date' => '15',
            'member_id' => '15',
            'email' => '25',
        ];

        $table->parsers = [
            'date' => function ($val) {
                return DateTime::ts($val)->html();
            },
            'member_id' => function ($val) {
                $member = Member::load($val);

                return "<a href='{$member->acpUrl()}' target='_blank'>{$member->name}</a>";
            },
            'action' => function ($val) {
                return Member::loggedIn()->language()->addToStack("awsses_action_$val");
            },
        ];

        $actions = [];

        if (Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => Member::loggedIn()->language()->addToStack('awsses_logs_prune'),
                ],
            ];
        }

        $actions['outgoing'] = [
            'title' => 'awsses_logs',
            'icon' => 'envelope',
            'link' => Url::internal('app=awsses&module=system&controller=logs'),
        ];
        $actions['bounces'] = [
            'title' => 'awsses_bounce_logs',
            'icon' => 'exclamation-circle',
            'link' => Url::internal('app=awsses&module=system&controller=logs&do=bounces'),
        ];

        Output::i()->sidebar['actions'] = $actions;
        Output::i()->title = Member::loggedIn()->language()->addToStack('awsses_complaint_logs');
        Output::i()->output = $table;
    }

    protected function view(): void
    {
        try {
            $log = Log::load(Request::i()->id);
        } catch (\OutOfRangeException $e) {
            Output::i()->error('awsses_error_log_not_found', '1AWSSES/2', 404);
        }

        Output::i()->sidebar['actions']['delete'] = [
            'icon' => 'times-circle',
            'link' => Url::internal('app=awsses&module=system&controller=logs&do=delete')->setQueryString('id', $log->id),
            'title' => 'delete',
            'data' => ['confirm' => ''],
        ];

        Output::i()->title = Member::loggedIn()->language()->addToStack('awsses_log');
        Output::i()->breadcrumb[] = [
            Url::internal('app=awsses&module=system&controller=logs'),
            Member::loggedIn()->language()->addToStack('awsses_logs'),
        ];
        Output::i()->output = Theme::i()->getTemplate('logs', 'awsses', 'admin')->log($log);
    }

    protected function delete(): void
    {
        try {
            $log = Log::load(Request::i()->id);
        } catch (\OutOfRangeException $e) {
            Output::i()->error('awsses_error_log_not_found', '1AWSSES/1', 404);
        }

        Request::i()->confirmedDelete();

        $log->delete();

        Output::i()->redirect(Url::internal('app=awsses&module=system&controller=logs'), 'deleted');
    }

    protected function pruneSettings(): void
    {
        Dispatcher::i()->checkAcpPermission('logs_prune_settings');

        $form = new Form();
        $form->add(new Number('awsses_log_prune_settings', Settings::i()->awsses_log_prune_settings, false, [
            'unlimited' => 0,
            'unlimitedLang' => 'never',
        ], null, Member::loggedIn()->language()->addToStack('after'), Member::loggedIn()->language()->addToStack('days'), 'prune_log_moderator'));

        if ($form->values()) {
            $form->saveAsSettings();

            Output::i()->redirect(Url::internal('app=awsses&module=system&controller=logs'), 'saved');
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack('awsses_log_prune_settings');
        Output::i()->output = Theme::i()->getTemplate('global', 'core', 'admin')->block('awsses_log_prune_settings', $form, false);
    }
}
