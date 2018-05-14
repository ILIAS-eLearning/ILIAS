<?php

namespace SAML2\Assertion\Validation;

use SAML2\XML\saml\SubjectConfirmation;

interface SubjectConfirmationConstraintValidator
{
    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result
    );
}
