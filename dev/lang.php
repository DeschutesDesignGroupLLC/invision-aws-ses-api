<?php

$lang = array(
	'__app_awsses'	=> "AWS Simple Email Service",

	// Menu
	'menutab__awsses' => 'AWS SES',
	'menutab__awsses_icon' => 'amazon',
	'menu__awsses_system' => 'Mail',
	'menu__awsses_system_settings' => 'Settings',
	'menu__awsses_system_logs' => 'Logs',
	'menu__awsses_system_bounces' => 'Bounces',
	'menu__awsses_system_complaints' => 'Complaints',

	// Restrictions
	'r__settings_manage' => 'Can change settings?',
	'r__logs_manage' => 'Can view logs?',
	'r__logs_prune_settings' => 'Can adjust prune settings?',

	// Settings
	'awsses_settings_tab_system' => 'System',
	'awsses_settings_email_override_message' => 'Enabling this application will override any native email settings you have configured within the ACP regardless of the mail delivery method you have selected.',
	'awsses_settings_message' => 'Before enabling this application, you will need to create an IAM role within the AWS Console and attach the AmazonSESFullAccess and AmazonSNSFullAccess policy. Allow programmatic access and keep track of your Access Key and Secret Key. Input the required information below to allow API access to AWS SES.',
	'awsses_enabled' => 'Enabled',
	'awsses_enabled_desc' => 'Checking yes will force your Invision Power Board community to start sending emails with AWS SES. All errors will be logged under your the ACP System Support tool.',
	'awsses_access_key' => 'Access Key',
	'awsses_access_key_desc' => 'You will need to make sure you have attached the AmazonSESFullAccess and AmazonSNSFullAccess policy to the IAM role you are using so that IPB can access the AWS Simple Email Service and AWS Simple Notification Service API.',
	'awsses_secret_key' => 'Secret Key',
	'awsses_config_set_name' => 'Config Set Name',
	'awsses_region' => 'Region',
	'awsses_region_desc' => 'View the list of supported regions <a href="https://docs.aws.amazon.com/general/latest/gr/rande.html" target="_blank">here</a>.',
	'awsses_config_set_name_desc' => 'If you have created a Configuration Set via the AWS Console, enter the name here to begin using it when sending emails.',
	'awsses_settings_tab_hard_bounces' => 'Hard Bounces',
	'awsses_settings_tab_soft_bounces' => 'Soft Bounces',
	'awsses_settings_bounce_message' => 'Bounce and complaint management will not work out of the box. You will need to setup and configure AWS to send notifications to an API endpoint via AWS Simple Notification Service (SNS) to handle bounces. See our documentation for instructions.',
	'awsses_settings_header_bounces' => 'In order to improve email deliverability and reputation, it is important to properly handle email bounces. AWS Simple Email Service provides a powerful notification system to inform Invision Power Board of bounced emails. Please select the action you\'d like to take in the event of an email bounce.',
	'awsses_settings_header_complaints' => 'In order to improve email deliverability and reputation, it is important to properly handle email complaints. AWS Simple Email Service provides a powerful notification system to inform Invision Power Board of emails that have received a complaint. Please select the action you\'d like to take in the event of an email complaint.',
	'awsses_soft_bounce_interval' => 'Process Conditions',
	'awsses_soft_bounce_interval_desc' => 'Immediately apply the action below when a soft bounce is encountered or if two or more bounces happen within the time specified.',
	'awsses_soft_bounce_action' => 'On Soft Bounce',
	'awsses_soft_bounce_action_desc' => 'Please select what will happen to a user when a soft bounce is encountered. Soft bounces may occur when a recipient\'s mailbox is full, the message is too large, the email contains an unacceptable attachment or the recipient\'s mail server rejects the content.',
	'awsses_soft_bounce_action_group' => 'Soft Bounce Group',
	'awsses_soft_bounce_action_group_desc' => 'When a soft bounce is encountered, the recipient will be moved to this group.',
	'awsses_soft_bounce_action_notification' => 'Soft Bounce Notification',
	'awsses_soft_bounce_action_notification_desc' => 'When a soft bounce is encountered, the recipient will be presented with the following notification.',
	'awsses_hard_bounce_interval' => 'Process Conditions',
	'awsses_hard_bounce_interval_desc' => 'Immediately apply the action below when a hard bounce is encountered or if two or more bounces happen within the time specified.',
	'awsses_hard_bounce_action' => 'On Hard Bounce',
	'awsses_hard_bounce_action_desc' => 'Please select what will happen to a user when a hard bounce is encountered. Hard bounces occur when a recipient\'s email is not valid.',
	'awsses_hard_bounce_action_group' => 'Hard Bounce Group',
	'awsses_hard_bounce_action_group_desc' => 'When a hard bounce is encountered, the recipient will be moved to this group.',
	'awsses_hard_bounce_action_notification' => 'Hard Bounce Notification',
	'awsses_hard_bounce_action_notification_desc' => 'When a hard bounce is encountered, the recipient will be presented with the following notification.',
	'awsses_complaint_interval' => 'Process Conditions',
	'awsses_complaint_interval_desc' => 'Immediately apply the action below when a complaint is encountered or if two or more bounces happen within the time specified.',
	'awsses_complaint_action' => 'On Complaint',
	'awsses_complaint_action_desc' => 'Please select what will happen to a user when a complaint is encountered. A complaint may occure when a recipient reports they don\'t want to receive an email such as marking the email as "Spam".',
	'awsses_complaint_action_group' => 'Complaint Group',
	'awsses_complaint_action_group_desc' => 'When a complaint is encountered, the recipient will be moved to this group.',
	'awsses_complaint_action_notification' => 'Complaint Notification',
	'awsses_complaint_action_notification_desc' => 'When a complaint is encountered, the recipient will be presented with the following notification.',
	'awsses_verified_identities' => 'Verified Identities',
	'awsses_verified_identities_desc' => 'Please enter all your verified identities (email addresses) that will be used to send email through AWS Simple Email Service. Each one must be added via the AWS Console. These identities will be checked against the sending email address leaving your Invision Power Board website. If the email does not match one of the verified identities below, the default email address below will be used.',
	'awsses_default_verified_identity' => 'Default Sending Email Address',
	'awsses_default_verified_identity_desc' => 'If the sending email address does not match one of the identities above, this email address will be used to send the email.',

	// Log
	'awsses_log' => 'Log',
	'awsses_logs' => 'Outgoing Logs',
	'awsses_bounce_logs' => 'Bounce Logs',
	'awsses_complaint_logs' => 'Complaint Logs',
	'awsses_logs_prune' => 'Prune Settings',

	// Settings
	'awsses_settings_updated' => 'The AWS Simple Email Service settings have been updated.',
	'awsses_log_prune_settings' => 'Prune Settings',

	// Log Table Columns
	'log_to' => 'To',
	'log_cc' => 'CC',
	'log_bcc' => 'BCC',
	'log_messageId' => 'Message ID',
	'log_exception' => 'Stack Trace',
	'log_error_message' => 'Error',
	'log_payload' => 'Payload',
	'log_subject' => 'Subject',
	'log_type' => 'Type',
	'log_action' => 'Action',
	'log_email' => 'Email',
	'log_status' => 'Status',

	// Form
	'awsses_form_process_immediately' => 'Process Immediately',

	// Action Messages
	'awsses_action_nothing' => 'No action was applied.',
	'awsses_action_group' => 'User was moved to a group.',
	'awsses_action_validating' => 'User status was set to validating.',
	'awsses_action_spam' => 'User was marked as a spammer.',
	'awsses_action_delete' => 'User was deleted.',
	'awsses_action_ban' => 'User was temporarily banned.',
	'awsses_action_interval' => 'No action applied. Date outside "Process Conditions" setting.',

	// Tasks
	'task__awsSesPruneLogs' => 'Run the prune logs task.',

	// Errors
	'awsses_error_log_not_found' => 'Unable to find the selected log.',

	// API
	'__api_awsses_bounces' => 'Bounce Notifications',
	'__api_awsses_complaints' => 'Complaint Notifications',
);
