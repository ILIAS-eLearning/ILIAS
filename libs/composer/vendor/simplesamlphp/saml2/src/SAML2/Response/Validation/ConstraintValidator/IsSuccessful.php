<?php

namespace SAML2\Response\Validation\ConstraintValidator;

use SAML2\Constants;
use SAML2\Response;
use SAML2\Response\Validation\ConstraintValidator;
use SAML2\Response\Validation\Result;

class IsSuccessful implements
    ConstraintValidator
{
    public function validate(
        Response $response,
        Result $result
    ) {
        if (!$response->isSuccess()) {
            $result->addError($this->buildMessage($response->getStatus()));
        }
    }

    /**
     * @param array $responseStatus
     *
     * @return string
     */
    private function buildMessage(array $responseStatus)
    {
        return sprintf(
            '%s%s%s',
            $this->truncateStatus($responseStatus['Code']),
            $responseStatus['SubCode'] ? '/' . $this->truncateStatus($responseStatus['SubCode']) : '',
            $responseStatus['Message'] ? ' ' . $responseStatus['Message'] : ''
        );
    }

    /**
     * Truncate the status if it is prefixed by its urn.
     * @param string $status
     *
     * @return string
     */
    private function truncateStatus($status)
    {
        $prefixLength = strlen(Constants::STATUS_PREFIX);
        if (strpos($status, Constants::STATUS_PREFIX) !== 0) {
            return $status;
        }

        return substr($status, $prefixLength);
    }
}
