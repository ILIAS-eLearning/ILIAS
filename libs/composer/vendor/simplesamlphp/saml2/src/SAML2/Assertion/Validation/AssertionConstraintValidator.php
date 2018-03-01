<?php

namespace SAML2\Assertion\Validation;

use SAML2\Assertion;

interface AssertionConstraintValidator
{
    public function validate(Assertion $assertion, Result $result);
}
