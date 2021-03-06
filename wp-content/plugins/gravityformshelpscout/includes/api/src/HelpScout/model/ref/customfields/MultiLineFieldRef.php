<?php
namespace HelpScout\model\ref\customfields;

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

use HelpScout\ValidationException;

class MultiLineFieldRef extends AbstractCustomFieldRef
{
    const LIMIT = 15000;

    public function validate($value)
    {
        if (strlen($value) > self::LIMIT) {
            throw new ValidationException('Multi line responses must be less than ' . self::LIMIT . ' characters long');
        }
    }
}
