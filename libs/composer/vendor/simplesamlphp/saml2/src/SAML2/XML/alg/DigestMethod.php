<?php

namespace SAML2\XML\alg;

/**
 * Class for handling the alg:DigestMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class DigestMethod
{
    /**
     * An URI identifying an algorithm supported for digest operations.
     *
     * @var string
     */
    public $Algorithm;


    /**
     * Create/parse an alg:DigestMethod element.
     *
     * @param \DOMElement|null $xml The XML element we should load or null to create a new one from scratch.
     *
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Algorithm')) {
            throw new \Exception('Missing required attribute "Algorithm" in alg:DigestMethod element.');
        }
        $this->Algorithm = $xml->getAttribute('Algorithm');
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->Algorithm)');

        $doc = $parent->ownerDocument;
        $e = $doc->createElementNS(Common::NS, 'alg:DigestMethod');
        $parent->appendChild($e);
        $e->setAttribute('Algorithm', $this->Algorithm);

        return $e;
    }
}
