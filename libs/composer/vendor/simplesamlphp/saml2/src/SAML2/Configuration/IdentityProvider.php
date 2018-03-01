<?php

namespace SAML2\Configuration;

/**
 * Basic configuration wrapper
 */
class IdentityProvider extends ArrayAdapter implements
    CertificateProvider,
    DecryptionProvider,
    EntityIdProvider
{
    public function getKeys()
    {
        return $this->get('keys');
    }

    public function getCertificateData()
    {
        return $this->get('certificateData');
    }

    public function getCertificateFile()
    {
        return $this->get('certificateFile');
    }

    /**
     * @deprecated Please use getCertifiateFile() or getCertificateData()
     */
    public function getCertificateFingerprints()
    {
        return $this->get('certificateFingerprints');
    }

    public function isAssertionEncryptionRequired()
    {
        return $this->get('assertionEncryptionEnabled');
    }

    public function getSharedKey()
    {
        return $this->get('sharedKey');
    }

    public function hasBase64EncodedAttributes()
    {
        return $this->get('base64EncodedAttributes');
    }

    public function getPrivateKey($name, $required = false)
    {
        $privateKeys = $this->get('privateKeys');
        $key = array_filter($privateKeys, function (PrivateKey $key) use ($name) {
            return $key->getName() === $name;
        });

        $keyCount = count($key);
        if ($keyCount !== 1 && $required) {
            throw new \RuntimeException(sprintf(
                'Attempted to get privateKey by name "%s", found "%d" keys, where only one was expected. Please '
                . 'verify that your configuration is correct',
                $name,
                $keyCount
            ));
        }

        if (!$keyCount) {
            return null;
        }

        return array_pop($key);
    }

    public function getBlacklistedAlgorithms()
    {
        return $this->get('blacklistedEncryptionAlgorithms');
    }

    /**
     * @return null|string
     */
    public function getEntityId()
    {
        return $this->get('entityId');
    }
}
