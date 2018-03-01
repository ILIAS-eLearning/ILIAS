<?php

namespace SAML2\XML\alg;

/**
 * Class for handling the alg:SigningMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class SigningMethod
{
    /**
     * An URI identifying the algorithm supported for XML signature operations.
     *
     * @var string
     */
    public $Algorithm;


    /**
     * The smallest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * minimum is implied.
     *
     * @var int|null
     */
    public $MinKeySize;


    /**
     * The largest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * maximum is implied.
     *
     * @var int|null
     */
    public $MaxKeySize;


    /**
     * Create/parse an alg:SigningMethod element.
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
            throw new \Exception('Missing required attribute "Algorithm" in alg:SigningMethod element.');
        }
        $this->Algorithm = $xml->getAttribute('Algorithm');

        if ($xml->hasAttribute('MinKeySize')) {
            $this->MinKeySize = intval($xml->getAttribute('MinKeySize'));
        }

        if ($xml->hasAttribute('MaxKeySize')) {
            $this->MaxKeySize = intval($xml->getAttribute('MaxKeySize'));
        }
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
        assert('is_int($this->MinKeySize) || is_null($this->MinKeySize)');
        assert('is_int($this->MaxKeySize) || is_null($this->MaxKeySize)');

        $doc = $parent->ownerDocument;
        $e = $doc->createElementNS(Common::NS, 'alg:SigningMethod');
        $parent->appendChild($e);
        $e->setAttribute('Algorithm', $this->Algorithm);

        if ($this->MinKeySize !== null) {
            $e->setAttribute('MinKeySize', $this->MinKeySize);
        }

        if ($this->MaxKeySize !== null) {
            $e->setAttribute('MaxKeySize', $this->MaxKeySize);
        }

        return $e;
    }
}
