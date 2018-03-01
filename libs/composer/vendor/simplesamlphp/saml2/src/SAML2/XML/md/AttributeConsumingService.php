<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 Metadata AttributeConsumingService element.
 *
 * @package SimpleSAMLphp
 */
class AttributeConsumingService
{
    /**
     * The index of this AttributeConsumingService.
     *
     * @var int
     */
    public $index;

    /**
     * Whether this is the default AttributeConsumingService.
     *
     * @var bool|null
     */
    public $isDefault = null;

    /**
     * The ServiceName of this AttributeConsumingService.
     *
     * This is an associative array with language => translation.
     *
     * @var array
     */
    public $ServiceName = array();

    /**
     * The ServiceDescription of this AttributeConsumingService.
     *
     * This is an associative array with language => translation.
     *
     * @var array
     */
    public $ServiceDescription = array();

    /**
     * The RequestedAttribute elements.
     *
     * This is an array of SAML_RequestedAttributeType elements.
     *
     * @var \SAML2\XML\md\RequestedAttribute[]
     */
    public $RequestedAttribute = array();

    /**
     * Initialize / parse an AttributeConsumingService.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('index')) {
            throw new \Exception('Missing index on AttributeConsumingService.');
        }
        $this->index = (int) $xml->getAttribute('index');

        $this->isDefault = Utils::parseBoolean($xml, 'isDefault', null);

        $this->ServiceName = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'ServiceName');
        if (empty($this->ServiceName)) {
            throw new \Exception('Missing ServiceName in AttributeConsumingService.');
        }

        $this->ServiceDescription = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'ServiceDescription');

        foreach (Utils::xpQuery($xml, './saml_metadata:RequestedAttribute') as $ra) {
            $this->RequestedAttribute[] = new RequestedAttribute($ra);
        }
    }

    /**
     * Convert to \DOMElement.
     *
     * @param \DOMElement $parent The element we should append this AttributeConsumingService to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_int($this->index)');
        assert('is_null($this->isDefault) || is_bool($this->isDefault)');
        assert('is_array($this->ServiceName)');
        assert('is_array($this->ServiceDescription)');
        assert('is_array($this->RequestedAttribute)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:AttributeConsumingService');
        $parent->appendChild($e);

        $e->setAttribute('index', (string) $this->index);

        if ($this->isDefault === true) {
            $e->setAttribute('isDefault', 'true');
        } elseif ($this->isDefault === false) {
            $e->setAttribute('isDefault', 'false');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:ServiceName', true, $this->ServiceName);
        Utils::addStrings($e, Constants::NS_MD, 'md:ServiceDescription', true, $this->ServiceDescription);

        foreach ($this->RequestedAttribute as $ra) {
            $ra->toXML($e);
        }

        return $e;
    }
}
