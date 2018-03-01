<?php

namespace SAML2\Certificate;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Configuration\DecryptionProvider;
use SAML2\Configuration\PrivateKey as PrivateKeyConfiguration;
use SAML2\Utilities\ArrayCollection;
use SAML2\Utilities\File;

class PrivateKeyLoader
{
    /**
     * Loads a private key based on the configuration given.
     *
     * @param \SAML2\Configuration\PrivateKey $key
     *
     * @return \SAML2\Certificate\PrivateKey
     */
    public function loadPrivateKey(PrivateKeyConfiguration $key)
    {
        $privateKey = File::getFileContents($key->getFilePath());

        return PrivateKey::create($privateKey, $key->getPassPhrase());
    }

    /**
     * @param \SAML2\Configuration\DecryptionProvider $identityProvider
     * @param \SAML2\Configuration\DecryptionProvider $serviceProvider
     *
     * @return \SAML2\Utilities\ArrayCollection
     * @throws \Exception
     */
    public function loadDecryptionKeys(
        DecryptionProvider $identityProvider,
        DecryptionProvider $serviceProvider
    ) {
        $decryptionKeys = new ArrayCollection();

        $senderSharedKey = $identityProvider->getSharedKey();
        if ($senderSharedKey) {
            $key = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
            $key->loadKey($senderSharedKey);
            $decryptionKeys->add($key);

            return $decryptionKeys;
        }

        $newPrivateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_NEW);
        if ($newPrivateKey instanceof PrivateKeyConfiguration) {
            $loadedKey = $this->loadPrivateKey($newPrivateKey);
            $decryptionKeys->add($this->convertPrivateKeyToRsaKey($loadedKey));
        }

        $privateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_DEFAULT, true);
        $loadedKey  = $this->loadPrivateKey($privateKey);
        $decryptionKeys->add($this->convertPrivateKeyToRsaKey($loadedKey));

        return $decryptionKeys;
    }

    /**
     * @param \SAML2\Certificate\PrivateKey $privateKey
     *
     * @return XMLSecurityKey
     * @throws \Exception
     */
    private function convertPrivateKeyToRsaKey(PrivateKey $privateKey)
    {
        $key        = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, array('type' => 'private'));
        $passphrase = $privateKey->getPassphrase();
        if ($passphrase) {
            $key->passphrase = $passphrase;
        }

        $key->loadKey($privateKey->getKeyAsString());

        return $key;
    }
}
