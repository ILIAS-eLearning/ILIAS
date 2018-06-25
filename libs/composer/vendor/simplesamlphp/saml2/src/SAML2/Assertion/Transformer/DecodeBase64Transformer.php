<?php

namespace SAML2\Assertion\Transformer;

use SAML2\Assertion;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;

class DecodeBase64Transformer implements
    Transformer,
    IdentityProviderAware
{
    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;

    public function setIdentityProvider(IdentityProvider $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    public function transform(Assertion $assertion)
    {
        if (!$this->identityProvider->hasBase64EncodedAttributes()) {
            return $assertion;
        }

        $attributes = $assertion->getAttributes();
        $keys = array_keys($attributes);
        $decoded = array_map(array($this, 'decodeValue'), $attributes);

        $attributes = array_combine($keys, $decoded);

        $assertion->setAttributes($attributes);
    }

    /**
     * @param $value
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function decodeValue($value)
    {
        $elements = explode('_', $value);
        return array_map('base64_decode', $elements);
    }
}
