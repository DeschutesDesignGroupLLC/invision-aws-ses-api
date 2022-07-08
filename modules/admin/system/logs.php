<?php

namespace IPS\awsses\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
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
     * @return  void
     */
    public function execute()
    {
        // Check permissions
        \IPS\Dispatcher::i()->checkAcpPermission('logs_manage');

        // Call parent
        parent::execute();
    }

    /**
     * Display Logs
     *
     * @return  void
     */
    protected function manage()
    {
        // Create the table
        $table = new \IPS\Helpers\Table\Db(\IPS\awsses\Outgoing\Log::$databaseTable, \IPS\Http\Url::internal('app=awsses&module=system&controller=logs'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'status', 'subject', 'from', 'to', 'cc', 'bcc'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap ']];

        // Quick Search
        $table->quickSearch = function ($search) {
            return ["payload LIKE '%{$search}%'"];
        };

        // Table parsers
        $table->parsers = [
            'date' => function ($val) {
                // Return the date
                return \IPS\DateTime::ts($val)->html();
            },
            'status' => function ($val, $row) {
                // Return the status
                return \IPS\Theme::i()->getTemplate('logs', 'awsses', 'admin')->status(isset($row['messageId']) ? true : false);
            },
            'from' => function ($val, $row) {
                // Return the date
                $payload = json_decode($row['payload'], true);
                if (isset($payload['Source'])) {
                    if (\extension_loaded('imap')) {
                        $decoded = imap_mime_header_decode($payload['Source']);
                        $name = isset($decoded[0]) ? str_replace(['<', '>', '"'], "", $decoded[0]->text) : null;
                        $email = isset($decoded[1]) ? str_replace(['<', '>', '"'], "", $decoded[1]->text) : null;
                        return $email ? (string) $email : (string) $name;
                    }
                    return 'Please enable the PHP IMAP extension to view the source.';
                }

            },
            'to' => function ($val, $row) {
                // Return the date
                $payload = json_decode($row['payload'], true);

                return isset($payload['Destination']['ToAddresses']) ? implode(', ', $payload['Destination']['ToAddresses']) : null;
            },
            'cc' => function ($val, $row) {
                // Return the date
                $payload = json_decode($row['payload'], true);

                return isset($payload['Destination']['CcAddresses']) ? implode(', ', $payload['Destination']['CcAddresses']) : null;
            },
            'bcc' => function ($val, $row) {
                // Return the date
                $payload = json_decode($row['payload'], true);

                return isset($payload['BccAddresses']['BccAddresses']) ? implode(', ', $payload['Destination']['BccAddresses']) : null;
            },
            'subject' => function ($val, $row) {
                // Return the recipient
                $payload = json_decode($row['payload'], true);

                return isset($payload['Message']['Subject']) ? $payload['Message']['Subject']['Data'] : null;
            }
        ];

        // Row Buttons
        $table->rowButtons = function ($row) {
            return [
                'view' => [
                    'title' => 'view',
                    'icon' => 'search',
                    'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=view')->setQueryString('id', $row['id'])
                ],
                'delete' => [
                    'title' => 'delete',
                    'icon' => 'times-circle',
                    'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=delete')->setQueryString('id', $row['id']),
                    'data' => ['delete' => '']
                ]
            ];
        };

        // Create our actions
        $actions = [];

        // Add prune settings
        if (\IPS\Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            // Add prune button
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('awsses_logs_prune')
                ]
            ];
        }

        // Add our other logs
        $actions['bounces'] = [
            'title' => 'awsses_bounce_logs',
            'icon' => 'exclamation-circle',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=bounces'),
        ];
        $actions['complaints'] = [
            'title' => 'awsses_complaint_logs',
            'icon' => 'exclamation-circle',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=complaints'),
        ];

        // Output it
        \IPS\Output::i()->sidebar['actions'] = $actions;
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_logs');
        \IPS\Output::i()->output = $table;
    }

    /**
     * Manage
     *
     * @return  void
     */
    protected function bounces()
    {
        // Create the table
        $table = new \IPS\Helpers\Table\Db('awsses_bounce_logs', \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=bounces'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'member_id', 'email', 'type', 'action'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap']];

        // Quick Search
        $table->quickSearch = function ($search) {
            return ["action LIKE '%{$search}%'"];
        };

        // Column widths
        $table->widths = [
            'date' => '15',
            'member_id' => '15',
            'email' => '20',
            'type' => '15',
        ];

        // Table parsers
        $table->parsers = [
            'date' => function ($val) {
                return \IPS\DateTime::ts($val)->html();
            },
            'member_id' => function ($val) {
                $member = \IPS\Member::load($val);

                return "<a href='{$member->acpUrl()}' target='_blank'>{$member->name}</a>";
            },
            'type' => function ($val) {
                $value = ucfirst($val);

                return "{$value} Bounce";
            },
            'action' => function ($val) {
                return \IPS\Member::loggedIn()->language()->addToStack("awsses_action_$val");
            }
        ];

        // Create our actions
        $actions = [];

        // Add prune settings
        if (\IPS\Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            // Add prune button
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('awsses_logs_prune')
                ]
            ];
        }

        // Add our other logs
        $actions['outgoing'] = [
            'title' => 'awsses_logs',
            'icon' => 'envelope',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs'),
        ];
        $actions['complaints'] = [
            'title' => 'awsses_complaint_logs',
            'icon' => 'exclamation-circle',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=complaints'),
        ];

        // Display the table
        \IPS\Output::i()->sidebar['actions'] = $actions;
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_bounce_logs');
        \IPS\Output::i()->output = $table;
    }

    /**
     * Manage
     *
     * @return  void
     */
    protected function complaints()
    {
        // Create the table
        $table = new \IPS\Helpers\Table\Db('awsses_complaint_logs', \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=complaints'));
        $table->langPrefix = 'log_';
        $table->include = ['date', 'member_id', 'email', 'action'];
        $table->sortBy = $table->sortBy ?: 'date';
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->rowClasses = ['messageId' => ['ipsTable_wrap ']];

        // Quick Search
        $table->quickSearch = function ($search) {
            return ["action LIKE '%{$search}%'"];
        };

        // Column widths
        $table->widths = [
            'date' => '15',
            'member_id' => '15',
            'email' => '25'
        ];

        // Table parsers
        $table->parsers = [
            'date' => function ($val) {
                return \IPS\DateTime::ts($val)->html();
            },
            'member_id' => function ($val) {
                $member = \IPS\Member::load($val);

                return "<a href='{$member->acpUrl()}' target='_blank'>{$member->name}</a>";
            },
            'action' => function ($val) {
                return \IPS\Member::loggedIn()->language()->addToStack("awsses_action_$val");
            }
        ];

        // Create our actions
        $actions = [];

        // Add prune settings
        if (\IPS\Member::loggedIn()->hasAcpRestriction('awsses', 'logs', 'logs_prune_settings')) {
            // Add prune button
            $actions['settings'] = [
                'title' => 'awsses_logs_prune',
                'icon' => 'cog',
                'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=pruneSettings'),
                'data' => [
                    'ipsDialog' => '',
                    'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('awsses_logs_prune')
                ]
            ];
        }

        // Add our other logs
        $actions['outgoing'] = [
            'title' => 'awsses_logs',
            'icon' => 'envelope',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs'),
        ];
        $actions['bounces'] = [
            'title' => 'awsses_bounce_logs',
            'icon' => 'exclamation-circle',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=bounces'),
        ];

        // Display the table
        \IPS\Output::i()->sidebar['actions'] = $actions;
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_complaint_logs');
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
            $log = \IPS\awsses\Outgoing\Log::load(\IPS\Request::i()->id);
        } // Unable to load the log
        catch (\OutOfRangeException $e) {
            // Return an error
            \IPS\Output::i()->error('awsses_error_log_not_found', '1AWSSES/2', 404);
        }

        // Add delete button
        \IPS\Output::i()->sidebar['actions']['delete'] = [
            'icon' => 'times-circle',
            'link' => \IPS\Http\Url::internal('app=awsses&module=system&controller=logs&do=delete')->setQueryString('id', $log->id),
            'title' => 'delete',
            'data' => ['confirm' => '']
        ];

        // Display the log
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_log');
        \IPS\Output::i()->breadcrumb[] = [
            \IPS\Http\Url::internal("app=awsses&module=system&controller=logs"),
            \IPS\Member::loggedIn()->language()->addToStack('awsses_logs')
        ];
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('logs', 'awsses', 'admin')->log($log);
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
            $log = \IPS\awsses\Outgoing\Log::load(\IPS\Request::i()->id);
        } // Unable to load the log
        catch (\OutOfRangeException $e) {
            // Return error
            \IPS\Output::i()->error('awsses_error_log_not_found', '1AWSSES/1', 404);
        }

        // Make sure we confirmed deletion
        \IPS\Request::i()->confirmedDelete();

        // Delete the log
        $log->delete();

        // Redirect
        \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=awsses&module=system&controller=logs'), 'deleted');
    }

    /**
     * Prune Settings
     *
     * @return  void
     */
    protected function pruneSettings()
    {
        // Check permissions
        \IPS\Dispatcher::i()->checkAcpPermission('logs_prune_settings');

        // Create our form
        $form = new \IPS\Helpers\Form;
        $form->add(new \IPS\Helpers\Form\Number('awsses_log_prune_settings', \IPS\Settings::i()->awsses_log_prune_settings, false, [
            'unlimited' => 0,
            'unlimitedLang' => 'never'
        ], null, \IPS\Member::loggedIn()->language()->addToStack('after'), \IPS\Member::loggedIn()->language()->addToStack('days'), 'prune_log_moderator'));

        // If we have values
        if ($values = $form->values()) {
            // Save the form settings
            $form->saveAsSettings();

            // Redirect back to logs
            \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=awsses&module=system&controller=logs'), 'saved');
        }

        // Set title and output
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('awsses_log_prune_settings');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('global', 'core', 'admin')->block('awsses_log_prune_settings', $form, false);
    }
}
