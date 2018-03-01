<?php

namespace SAML2\XML\mdui;

use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class DiscoHints
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SAML2\XML\Chunk[]
     */
    public $children = array();

    /**
     * The IPHint, as an array of strings.
     *
     * @var string[]
     */
    public $IPHint = array();

    /**
     * The DomainHint, as an array of strings.
     *
     * @var string[]
     */
    public $DomainHint = array();

    /**
     * The GeolocationHint, as an array of strings.
     *
     * @var string[]
     */
    public $GeolocationHint = array();

    /**
     * Create a DiscoHints element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->IPHint =          Utils::extractStrings($xml, Common::NS, 'IPHint');
        $this->DomainHint =      Utils::extractStrings($xml, Common::NS, 'DomainHint');
        $this->GeolocationHint = Utils::extractStrings($xml, Common::NS, 'GeolocationHint');

        foreach (Utils::xpQuery($xml, "./*[namespace-uri()!='".Common::NS."']") as $node) {
            $this->children[] = new Chunk($node);
        }
    }

    /**
     * Convert this DiscoHints to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->IPHint)');
        assert('is_array($this->DomainHint)');
        assert('is_array($this->GeolocationHint)');
        assert('is_array($this->children)');

        if (!empty($this->IPHint)
         || !empty($this->DomainHint)
         || !empty($this->GeolocationHint)
         || !empty($this->children)) {
            $doc = $parent->ownerDocument;

            $e = $doc->createElementNS(Common::NS, 'mdui:DiscoHints');
            $parent->appendChild($e);

            if (!empty($this->children)) {
                foreach ($this->children as $child) {
                    $child->toXML($e);
                }
            }

            Utils::addStrings($e, Common::NS, 'mdui:IPHint', false, $this->IPHint);
            Utils::addStrings($e, Common::NS, 'mdui:DomainHint', false, $this->DomainHint);
            Utils::addStrings($e, Common::NS, 'mdui:GeolocationHint', false, $this->GeolocationHint);

            return $e;
        }

        return null;
    }
}
