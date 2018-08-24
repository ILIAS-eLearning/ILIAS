<?php

namespace SAML2\Signature;

use Psr\Log\LoggerInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\SignedElement;

abstract class AbstractChainedValidator implements ChainedValidator
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * BC compatible version of the signature check
     *
     * @param \SAML2\SignedElement      $element
     * @param \SAML2\Certificate\X509[] $pemCandidates
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function validateElementWithKeys(SignedElement $element, $pemCandidates)
    {
        $lastException = null;
        foreach ($pemCandidates as $index => $candidateKey) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'public'));
            $key->loadKey($candidateKey->getCertificate());

            try {
                /*
                 * Make sure that we have a valid signature on either the response or the assertion.
                 */
                $result = $element->validate($key);
                if ($result) {
                    $this->logger->debug(sprintf('Validation with key "#%d" succeeded', $index));
                    return true;
                }
                $this->logger->debug(sprintf('Validation with key "#%d" failed without exception.', $index));
            } catch (\Exception $e) {
                $this->logger->debug(sprintf(
                    'Validation with key "#%d" failed with exception: %s',
                    $index,
                    $e->getMessage()
                ));

                $lastException = $e;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        } else {
            return false;
        }
    }
}
