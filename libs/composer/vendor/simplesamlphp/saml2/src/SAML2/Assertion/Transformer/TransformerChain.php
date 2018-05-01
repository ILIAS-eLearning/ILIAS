<?php

namespace SAML2\Assertion\Transformer;

use SAML2\Assertion;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\Configuration\ServiceProvider;
use SAML2\Configuration\ServiceProviderAware;

class TransformerChain implements Transformer
{
    /**
     * @var \SAML2\Assertion\Transformer\Transformer[]
     */
    private $transformers = array();

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    private $serviceProvider;

    public function __construct(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider  = $serviceProvider;
    }

    public function addTransformerStep(Transformer $transformer)
    {
        if ($transformer instanceof IdentityProviderAware) {
            $transformer->setIdentityProvider($this->identityProvider);
        }

        if ($transformer instanceof ServiceProviderAware) {
            $transformer->setServiceProvider($this->serviceProvider);
        }

        $this->transformers[] = $transformer;
    }

    /**
     * @param \SAML2\Assertion $assertion
     *
     * @return \SAML2\Assertion
     */
    public function transform(Assertion $assertion)
    {
        foreach ($this->transformers as $transformer) {
            $assertion = $transformer->transform($assertion);
        }

        return $assertion;
    }
}
