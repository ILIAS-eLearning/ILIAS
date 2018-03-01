<?php

namespace SAML2\Assertion\Validation\ConstraintValidator;

use SAML2\Assertion\Validation\Result;
use SAML2\Assertion\Validation\SubjectConfirmationConstraintValidator;
use SAML2\Response;
use SAML2\XML\saml\SubjectConfirmation;

class SubjectConfirmationResponseToMatches implements
    SubjectConfirmationConstraintValidator
{
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function validate(
        SubjectConfirmation $subjectConfirmation,
        Result $result
    ) {
        $inResponseTo = $subjectConfirmation->SubjectConfirmationData->InResponseTo;
        if ($inResponseTo && $this->getInResponseTo() && $this->getInResponseTo() !== $inResponseTo) {
            $result->addError(sprintf(
                'InResponseTo in SubjectConfirmationData ("%s") does not match the Response InResponseTo ("%s")',
                $inResponseTo,
                $this->getInResponseTo()
            ));
        }
    }

    private function getInResponseTo()
    {
        $inResponseTo = $this->response->getInResponseTo();
        if ($inResponseTo === null) {
            return false;
        }

        return $inResponseTo;
    }
}
