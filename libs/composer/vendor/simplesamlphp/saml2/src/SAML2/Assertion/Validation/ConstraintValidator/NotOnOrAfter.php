<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion;
use SAML2\Assertion\Validation\AssertionConstraintValidator;
use SAML2\Assertion\Validation\Result;
use SAML2\Utilities\Temporal;

class NotOnOrAfter implements
    AssertionConstraintValidator
{
    public function validate(Assertion $assertion, Result $result)
    {
        $notValidOnOrAfterTimestamp = $assertion->getNotOnOrAfter();
        if ($notValidOnOrAfterTimestamp && $notValidOnOrAfterTimestamp <= Temporal::getTime() - 60) {
            $result->addError(
                'Received an assertion that has expired. Check clock synchronization on IdP and SP.'
            );
        }
    }
}
