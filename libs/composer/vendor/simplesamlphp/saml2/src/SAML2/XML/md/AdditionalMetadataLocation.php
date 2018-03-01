<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package SimpleSAMLphp
 */
class AdditionalMetadataLocation
{
    /**
     * The namespace of this metadata.
     *
     * @var string
     */
    public $namespace;

    /**
     * The URI where the metadata is located.
     *
     * @var string
     */
    public $location;

    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('namespace')) {
            throw new \Exception('Missing namespace attribute on AdditionalMetadataLocation element.');
        }
        $this->namespace = $xml->getAttribute('namespace');

        $this->location = $xml->textContent;
    }

    /**
     * Convert this AdditionalMetadataLocation to XML.
     *
     * @param  \DOMElement $parent The element we should append to.
     * @return \DOMElement This AdditionalMetadataLocation-element.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->namespace)');
        assert('is_string($this->location)');

        $e = Utils::addString($parent, Constants::NS_MD, 'md:AdditionalMetadataLocation', $this->location);
        $e->setAttribute('namespace', $this->namespace);

        return $e;
    }
}
