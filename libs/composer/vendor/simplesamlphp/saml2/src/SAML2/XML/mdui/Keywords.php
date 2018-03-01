<?php

namespace SAML2\XML\mdui;

/**
 * Class for handling the Keywords metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class Keywords
{
    /**
     * The keywords of this item.
     *
     * Array of strings.
     *
     * @var string[]
     */
    public $Keywords;

    /**
     * The language of this item.
     *
     * @var string
     */
    public $lang;

    /**
     * Initialize a Keywords.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('xml:lang')) {
            throw new \Exception('Missing lang on Keywords.');
        }
        if (!is_string($xml->textContent) || !strlen($xml->textContent)) {
            throw new \Exception('Missing value for Keywords.');
        }
        $this->Keywords = array();
        foreach (explode(' ', $xml->textContent) as $keyword) {
            $this->Keywords[] = str_replace('+', ' ', $keyword);
        }
        $this->lang = $xml->getAttribute('xml:lang');
    }

    /**
     * Convert this Keywords to XML.
     *
     * @param \DOMElement $parent The element we should append this Keywords to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->lang)');
        assert('is_array($this->Keywords)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Common::NS, 'mdui:Keywords');
        $e->setAttribute('xml:lang', $this->lang);
        $value = '';
        foreach ($this->Keywords as $keyword) {
            if (strpos($keyword, "+") !== false) {
                throw new \Exception('Keywords may not contain a "+" character.');
            }
            $value .= str_replace(' ', '+', $keyword) . ' ';
        }
        $value = rtrim($value);
        $e->appendChild($doc->createTextNode($value));
        $parent->appendChild($e);

        return $e;
    }
}
