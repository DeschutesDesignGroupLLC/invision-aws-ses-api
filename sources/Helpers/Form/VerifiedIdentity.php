<?php

namespace IPS\awsses\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Verified Identity
 */
class _VerifiedIdentity extends \IPS\Helpers\Form\Text
{
    /**
     * Constructor
     *
     * @param string    $name                 Name
     * @param mixed     $defaultValue         Default value
     * @param bool|NULL $required             Required? (NULL for not required, but appears to be so)
     * @param array     $options              Type-specific options
     * @param callback  $customValidationCode Custom validation code
     * @param string    $prefix               HTML to show before input field
     * @param string    $suffix               HTML to show after input field
     * @param string    $id                   The ID to add to the row
     *
     * @return    void
     */
    public function __construct($name, $defaultValue = null, $required = false, $options = [], $customValidationCode = null, $prefix = null, $suffix = null, $id = null)
    {
        // Call parent
        parent::__construct($name, $defaultValue, $required, $options, $customValidationCode, $prefix, $suffix, $id);

        // Set form type
        $this->formType = 'text';
    }

    /**
     * Validate
     *
     * @return    TRUE
     * @throws    \DomainException
     * @throws    \InvalidArgumentException
     */
    public function validate()
    {
        // Check if the valid email or domain
        $pattern = '/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';
        if ($this->value !== '' && (filter_var($this->value, FILTER_VALIDATE_EMAIL) || preg_match($pattern, $this->value))) {
            // Call parent
            return parent::validate();
        }

        // Throw exception
        throw new \InvalidArgumentException('awsses_form_bad_verified_identity');
    }
}


