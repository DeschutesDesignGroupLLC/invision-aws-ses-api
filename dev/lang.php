<?php

$lang = array(
	'__app_awsses'	=> "AWS Simple Email Service",

	// Menu
	'menutab__awsses' => 'AWS SES',
	'menutab__awsses_icon' => 'amazon',
	'menu__awsses_system' => 'System',
	'menu__awsses_system_settings' => 'Settings',
	'menu__awsses_system_logs' => 'Logs',

	// Settings
	'awsses_settings_header' => 'System Settings',
	'awsses_settings_email_override_message' => 'Enabling this application will override any native email settings you have configured within the ACP regardless of the mail delivery method you have selected.',
	'awsses_settings_message' => 'Before enabling this application, you will need to create an IAM role within the AWS Console and attach the AmazonSESFullAccess policy. Allow programmatic access and keep track of your Access Key and Secret Key. Input the required information below to allow API access to AWS SES.',
	'awsses_enabled' => 'Enable',
	'awsses_enabled_desc' => 'Checking yes will force your Invision Power Board community to start sending emails with AWS SES. All errors will be logged under your the ACP System Support tool.',
	'awsses_access_key' => 'Access Key',
	'awsses_access_key_desc' => 'You will need to make sure you have attached the AmazonSESFullAccess policy to the IAM role you are using so that IPB can access the AWS SES API.',
	'awsses_secret_key' => 'Secret Key',
	'awsses_config_set_name' => 'Config Set Name',
	'awsses_region' => 'Region',
	'awsses_region_desc' => 'View the list of supported regions <a href="https://docs.aws.amazon.com/general/latest/gr/rande.html" target="_blank">here</a>.',
	'awsses_config_set_name_desc' => 'If you have created a Configuration Set via the AWS Console, enter the name here to begin using it when sending emails.',

	// Log
	'awsses_log' => 'Log',
	'awsses_logs' => 'Logs',
	'awsses_logs_prune' => 'Prune Settings',

	// Settings
	'awsses_settings_updated' => 'The AWS Simple Email Service settings have been updated.',
	'awsses_log_prune_settings' => 'Prune Settings',

	// Log Table Columns
	'log_to' => 'To',
	'log_messageId' => 'Message ID',
	'log_exception' => 'Stack Trace',
	'log_error_message' => 'Error',
	'log_payload' => 'Payload',
	'log_subject' => 'Subject',

	// Task
	'task__awsSesPruneLogs' => 'Run the prune logs task.',
);
