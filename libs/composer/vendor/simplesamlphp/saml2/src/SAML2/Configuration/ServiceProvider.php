<?php

namespace SAML2\Configuration;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Basic Configuration Wrapper
 */
class ServiceProvider extends ArrayAdapter implements
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
     * @deprecated Please use getCertificateData() or getCertificateFile().
     */
    public function getCertificateFingerprints()
    {
        return $this->get('certificateFingerprints');
    }

    public function getEntityId()
    {
        return $this->get('entityId');
    }

    public function isAssertionEncryptionRequired()
    {
        return $this->get('assertionEncryptionEnabled');
    }

    public function getSharedKey()
    {
        return $this->get('sharedKey');
    }

    public function getPrivateKey($name, $required = false)
    {
        $privateKeys = $this->get('privateKeys');
        $key         = array_filter($privateKeys, function (PrivateKey $key) use ($name) {
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
        return $this->get('blacklistedEncryptionAlgorithms', array(XMLSecurityKey::RSA_1_5));
    }
}
