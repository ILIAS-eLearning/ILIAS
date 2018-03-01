<?php

namespace SAML2\XML\shibmd;

use SAML2\Utils;

/**
 * Class which represents the Scope element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SHIB/ShibbolethMetadataProfile
 * @package SimpleSAMLphp
 */
class Scope
{
    /**
     * The namespace used for the Scope extension element.
     */
    const NS = 'urn:mace:shibboleth:metadata:1.0';

    /**
     * The scope.
     *
     * @var string
     */
    public $scope;

    /**
     * Whether this is a regexp scope.
     *
     * @var bool
     */
    public $regexp = false;

    /**
     * Create a Scope.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->scope = $xml->textContent;
        $this->regexp = Utils::parseBoolean($xml, 'regexp', false);
    }

    /**
     * Convert this Scope to XML.
     *
     * @param \DOMElement $parent The element we should append this Scope to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->scope)');
        assert('is_bool($this->regexp) || is_null($this->regexp)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Scope::NS, 'shibmd:Scope');
        $parent->appendChild($e);

        $e->appendChild($doc->createTextNode($this->scope));

        if ($this->regexp === true) {
            $e->setAttribute('regexp', 'true');
        } else {
            $e->setAttribute('regexp', 'false');
        }

        return $e;
    }
}
