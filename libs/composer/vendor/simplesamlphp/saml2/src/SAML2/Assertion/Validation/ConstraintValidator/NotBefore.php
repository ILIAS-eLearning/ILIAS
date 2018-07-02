<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion;
use SAML2\Assertion\Validation\AssertionConstraintValidator;
use SAML2\Assertion\Validation\Result;
use SAML2\Utilities\Temporal;

class NotBefore implements
    AssertionConstraintValidator
{
    public function validate(Assertion $assertion, Result $result)
    {
        $notBeforeTimestamp = $assertion->getNotBefore();
        if ($notBeforeTimestamp && $notBeforeTimestamp > Temporal::getTime() + 60) {
            $result->addError(
                'Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.'
            );
        }
    }
}
