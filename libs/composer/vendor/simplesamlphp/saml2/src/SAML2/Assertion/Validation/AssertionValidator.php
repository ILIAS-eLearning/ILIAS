<?php

namespace SAML2\Assertion\Validation;

use SAML2\Assertion;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\Configuration\ServiceProvider;
use SAML2\Configuration\ServiceProviderAware;

class AssertionValidator
{
    /**
     * @var \SAML2\Assertion\Validation\AssertionConstraintValidator[]
     */
    protected $constraints;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    private $serviceProvider;

    /**
     * @param \SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SAML2\Configuration\ServiceProvider  $serviceProvider
     */
    public function __construct(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }

    public function addConstraintValidator(AssertionConstraintValidator $constraint)
    {
        if ($constraint instanceof IdentityProviderAware) {
            $constraint->setIdentityProvider($this->identityProvider);
        }

        if ($constraint instanceof ServiceProviderAware) {
            $constraint->setServiceProvider($this->serviceProvider);
        }

        $this->constraints[] = $constraint;
    }

    public function validate(Assertion $assertion)
    {
        $result = new Result();
        foreach ($this->constraints as $validator) {
            $validator->validate($assertion, $result);
        }

        return $result;
    }
}
