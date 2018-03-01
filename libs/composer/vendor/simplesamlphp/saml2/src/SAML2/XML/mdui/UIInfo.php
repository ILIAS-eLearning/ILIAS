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
class UIInfo
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
     * The DisplayName, as an array of language => translation.
     *
     * @var array
     */
    public $DisplayName = array();

    /**
     * The Description, as an array of language => translation.
     *
     * @var array
     */
    public $Description = array();

    /**
     * The InformationURL, as an array of language => url.
     *
     * @var array
     */
    public $InformationURL = array();

    /**
     * The PrivacyStatementURL, as an array of language => url.
     *
     * @var array
     */
    public $PrivacyStatementURL = array();

    /**
     * The Keywords, as an array of Keywords objects
     *
     * @var \SAML2\XML\mdui\Keywords[]
     */
    public $Keywords = array();

    /**
     * The Logo, as an array of Logo objects
     *
     * @var \SAML2\XML\mdui\Logo[]
     */
    public $Logo = array();

    /**
     * Create a UIInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->DisplayName         = Utils::extractLocalizedStrings($xml, Common::NS, 'DisplayName');
        $this->Description         = Utils::extractLocalizedStrings($xml, Common::NS, 'Description');
        $this->InformationURL      = Utils::extractLocalizedStrings($xml, Common::NS, 'InformationURL');
        $this->PrivacyStatementURL = Utils::extractLocalizedStrings($xml, Common::NS, 'PrivacyStatementURL');

        foreach (Utils::xpQuery($xml, './*') as $node) {
            if ($node->namespaceURI === Common::NS) {
                switch ($node->localName) {
                    case 'Keywords':
                        $this->Keywords[] = new Keywords($node);
                        break;
                    case 'Logo':
                        $this->Logo[] = new Logo($node);
                        break;
                }
            } else {
                $this->children[] = new Chunk($node);
            }
        }
    }

    /**
     * Convert this UIInfo to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->DisplayName)');
        assert('is_array($this->InformationURL)');
        assert('is_array($this->PrivacyStatementURL)');
        assert('is_array($this->Keywords)');
        assert('is_array($this->Logo)');
        assert('is_array($this->children)');

        $e = null;
        if (!empty($this->DisplayName)
         || !empty($this->Description)
         || !empty($this->InformationURL)
         || !empty($this->PrivacyStatementURL)
         || !empty($this->Keywords)
         || !empty($this->Logo)
         || !empty($this->children)) {
            $doc = $parent->ownerDocument;

            $e = $doc->createElementNS(Common::NS, 'mdui:UIInfo');
            $parent->appendChild($e);

            Utils::addStrings($e, Common::NS, 'mdui:DisplayName', true, $this->DisplayName);
            Utils::addStrings($e, Common::NS, 'mdui:Description', true, $this->Description);
            Utils::addStrings($e, Common::NS, 'mdui:InformationURL', true, $this->InformationURL);
            Utils::addStrings($e, Common::NS, 'mdui:PrivacyStatementURL', true, $this->PrivacyStatementURL);

            if (!empty($this->Keywords)) {
                foreach ($this->Keywords as $child) {
                    $child->toXML($e);
                }
            }

            if (!empty($this->Logo)) {
                foreach ($this->Logo as $child) {
                    $child->toXML($e);
                }
            }

            if (!empty($this->children)) {
                foreach ($this->children as $child) {
                    $child->toXML($e);
                }
            }
        }

        return $e;
    }
}
