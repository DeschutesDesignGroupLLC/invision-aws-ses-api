<?php

namespace IPS\awsses\modules\admin\system;

use IPS\awsses\Helpers\Form\VerifiedIdentity;
use IPS\awsses\Manager\LicenseKey;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Codemirror;
use IPS\Helpers\Form\Email;
use IPS\Helpers\Form\Password;
use IPS\Helpers\Form\Stack;
use IPS\Helpers\Form\Text;
use IPS\Helpers\Form\YesNo;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Session;
use IPS\Settings;
use IPS\Text\Encrypt;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _settings extends Controller
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

        $secret = null;
        if (Settings::i()->awsses_secret_key) {
            $secret = Encrypt::fromTag(Settings::i()->awsses_secret_key)->decrypt();
        }

        $form->addTab('awsses_license');
        $form->add(new Text('awsses_license_key', Settings::i()->awsses_license_key, true, [], function ($value) {
            if (! LicenseKey::i()->fetchLicenseStatus(true, $value)) {
                throw new \DomainException('The license key you entered is not valid.');
            }
        }));

        $form->addTab('awsses_aws');
        $form->addMessage('awsses_settings_email_override_message', ' ipsMessage ipsMessage_warning ipsType_reset ipsSpacer_top');
        $form->addMessage('awsses_settings_message');
        $form->add(new YesNo('awsses_enabled', Settings::i()->awsses_enabled, true));
        $form->add(new Text('awsses_access_key', Settings::i()->awsses_access_key, true));
        $form->add(new Password('awsses_secret_key', $secret, true));
        $form->add(new Text('awsses_region', Settings::i()->awsses_region, true, [
            'placeholder' => 'us-west-2',
            '',
        ]));
        $form->add(new Text('awsses_config_set_name', Settings::i()->awsses_config_set_name, false));
        $form->add(new Stack('awsses_verified_identities', explode(',', Settings::i()->awsses_verified_identities), true, ['stackFieldType' => VerifiedIdentity::class]));
        $form->add(new Email('awsses_default_verified_identity', Settings::i()->awsses_default_verified_identity, true));

        $form->addTab('awsses_debug');
        $form->addMessage('awsses_license_data_message');
        $form->add(new YesNo('awsses_license_status', LicenseKey::i()->isValid(), false, ['disabled' => true]));
        $form->add(new Text('awsses_license_fetched', Settings::i()->awsses_license_fetched ? date('m/d/Y h:i A', (int) Settings::i()->awsses_license_fetched) : null, false, ['disabled' => true]));
        $form->add(new Text('awsses_license_instance', Settings::i()->awsses_license_instance, false, ['disabled' => true]));
        $form->add(new Codemirror('awsses_license_status_payload', json_encode(json_decode(Settings::i()->awsses_license_status_payload), JSON_PRETTY_PRINT), false, ['disabled' => true, 'mode' => 'json']));
        $form->add(new Codemirror('awsses_license_activation_payload', json_encode(json_decode(Settings::i()->awsses_license_activation_payload), JSON_PRETTY_PRINT), false, ['disabled' => true, 'mode' => 'json']));

        if ($values = $form->values()) {
            Session::i()->log('awsses_settings_updated');

            if ($values['awsses_secret_key']) {
                $values['awsses_secret_key'] = Encrypt::fromPlaintext($values['awsses_secret_key'])->tag();
            }

            if ($values['awsses_verified_identities']) {
                $values['awsses_verified_identities'] = implode(',', $values['awsses_verified_identities']);
            }

            $form->saveAsSettings($values);
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack('settings');
        Output::i()->sidebar['actions']['refresh'] = [
            'icon' => 'refresh',
            'link' => Url::internal('app=awsses&module=system&controller=settings&do=refresh'),
            'title' => 'awsses_license_refresh_title',
        ];
        Output::i()->sidebar['actions']['reset'] = [
            'icon' => 'trash',
            'link' => Url::internal('app=awsses&module=system&controller=settings&do=reset'),
            'title' => 'awsses_license_reset_title',
        ];
        Output::i()->output = $form;
    }

    protected function refresh(): void
    {
        LicenseKey::i()->fetchLicenseStatus();

        Output::i()->redirect(Url::internal('app=awsses&module=system&controller=settings'), 'awsses_license_refreshed');
    }

    protected function reset(): void
    {
        LicenseKey::i()->resetLicenseKeyData();

        Output::i()->redirect(Url::internal('app=awsses&module=system&controller=settings'), 'awsses_license_reset');
    }
}
