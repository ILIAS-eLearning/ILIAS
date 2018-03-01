<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;

/**
 * Class representing a KeyDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class KeyDescriptor
{
    /**
     * What this key can be used for.
     *
     * 'encryption', 'signing' or null.
     *
     * @var string|null
     */
    public $use;

    /**
     * The KeyInfo for this key.
     *
     * @var \SAML2\XML\ds\KeyInfo
     */
    public $KeyInfo;

    /**
     * Supported EncryptionMethods.
     *
     * Array of \SAML2\XML\Chunk objects.
     *
     * @var \SAML2\XML\Chunk[]
     */
    public $EncryptionMethod = array();

    /**
     * Initialize an KeyDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('use')) {
            $this->use = $xml->getAttribute('use');
        }

        $keyInfo = Utils::xpQuery($xml, './ds:KeyInfo');
        if (count($keyInfo) > 1) {
            throw new \Exception('More than one ds:KeyInfo in the KeyDescriptor.');
        } elseif (empty($keyInfo)) {
            throw new \Exception('No ds:KeyInfo in the KeyDescriptor.');
        }
        $this->KeyInfo = new KeyInfo($keyInfo[0]);

        foreach (Utils::xpQuery($xml, './saml_metadata:EncryptionMethod') as $em) {
            $this->EncryptionMethod[] = new Chunk($em);
        }
    }

    /**
     * Convert this KeyDescriptor to XML.
     *
     * @param \DOMElement $parent The element we should append this KeyDescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_null($this->use) || is_string($this->use)');
        assert('$this->KeyInfo instanceof \SAML2\XML\ds\KeyInfo');
        assert('is_array($this->EncryptionMethod)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:KeyDescriptor');
        $parent->appendChild($e);

        if (isset($this->use)) {
            $e->setAttribute('use', $this->use);
        }

        $this->KeyInfo->toXML($e);

        foreach ($this->EncryptionMethod as $em) {
            $em->toXML($e);
        }

        return $e;
    }
}
