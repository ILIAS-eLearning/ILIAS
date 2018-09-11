<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\Result;
use SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SAML2\Constants;
use SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationMethod implements
    SubjectConfirmationConstraintValidator
{
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result
    ) {
        if ($subjectConfirmation->Method !== Constants::CM_BEARER) {
            $result->addError(sprintf(
                'Invalid Method on SubjectConfirmation, current;y only Bearer (%s) is supported',
                Constants::CM_BEARER
            ));
        }
    }
}
