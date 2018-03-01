<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\SignedElementHelper;
use SAML2\Utils;

/**
 * Class representing SAML 2 EntitiesDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class EntitiesDescriptor extends SignedElementHelper
{
    /**
     * The ID of this element.
     *
     * @var string|null
     */
    public $ID;

    /**
     * How long this element is valid, as a unix timestamp.
     *
     * @var int|null
     */
    public $validUntil;

    /**
     * The length of time this element can be cached, as string.
     *
     * @var string|null
     */
    public $cacheDuration;

    /**
     * The name of this entity collection.
     *
     * @var string|null
     */
    public $Name;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    public $Extensions = array();

    /**
     * Child EntityDescriptor and EntitiesDescriptor elements.
     *
     * @var (\SAML2\XML\md\EntityDescriptor|\SAML2\XML\md\EntitiesDescriptor)[]
     */
    public $children = array();

    /**
     * Initialize an EntitiesDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('ID')) {
            $this->ID = $xml->getAttribute('ID');
        }
        if ($xml->hasAttribute('validUntil')) {
            $this->validUntil = Utils::xsDateTimeToTimestamp($xml->getAttribute('validUntil'));
        }
        if ($xml->hasAttribute('cacheDuration')) {
            $this->cacheDuration = $xml->getAttribute('cacheDuration');
        }
        if ($xml->hasAttribute('Name')) {
            $this->Name = $xml->getAttribute('Name');
        }

        $this->Extensions = Extensions::getList($xml);

        foreach (Utils::xpQuery($xml, './saml_metadata:EntityDescriptor|./saml_metadata:EntitiesDescriptor') as $node) {
            if ($node->localName === 'EntityDescriptor') {
                $this->children[] = new EntityDescriptor($node);
            } else {
                $this->children[] = new EntitiesDescriptor($node);
            }
        }
    }

    /**
     * Convert this EntitiesDescriptor to XML.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntitiesDescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent = null)
    {
        assert('is_null($this->ID) || is_string($this->ID)');
        assert('is_null($this->validUntil) || is_int($this->validUntil)');
        assert('is_null($this->cacheDuration) || is_string($this->cacheDuration)');
        assert('is_null($this->Name) || is_string($this->Name)');
        assert('is_array($this->Extensions)');
        assert('is_array($this->children)');

        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_MD, 'md:EntitiesDescriptor');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:EntitiesDescriptor');
            $parent->appendChild($e);
        }

        if (isset($this->ID)) {
            $e->setAttribute('ID', $this->ID);
        }

        if (isset($this->validUntil)) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if (isset($this->cacheDuration)) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        if (isset($this->Name)) {
            $e->setAttribute('Name', $this->Name);
        }

        Extensions::addList($e, $this->Extensions);

        /** @var \SAML2\XML\md\EntityDescriptor|\SAML2\XML\md\EntitiesDescriptor $node */
        foreach ($this->children as $node) {
            $node->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
