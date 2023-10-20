<?php

namespace IPS\awsses\Helpers\Form;

use IPS\Helpers\Form\Text;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (! \defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0').' 403 Forbidden');
    exit;
}

class _VerifiedIdentity extends Text
{
    public function __construct($name, $defaultValue = null, $required = false, $options = [], $customValidationCode = null, $prefix = null, $suffix = null, $id = null)
    {
        parent::__construct($name, $defaultValue, $required, $options, $customValidationCode, $prefix, $suffix, $id);

        $this->formType = 'text';
    }

    public function validate()
    {
        $pattern = '/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';
        if ($this->value !== '' && (filter_var($this->value, FILTER_VALIDATE_EMAIL) || preg_match($pattern, $this->value))) {
            return parent::validate();
        }

        throw new \InvalidArgumentException('awsses_form_bad_verified_identity');
    }
}
