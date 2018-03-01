<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package SimpleSAMLphp
 */
class RequestedAttribute extends Attribute
{
    /**
     * Whether this attribute is required.
     *
     * @var bool|null
     */
    public $isRequired = null;

    /**
     * Initialize an RequestedAttribute.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        $this->isRequired = Utils::parseBoolean($xml, 'isRequired', null);
    }

    /**
     * Convert this RequestedAttribute to XML.
     *
     * @param \DOMElement $parent The element we should append this RequestedAttribute to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_bool($this->isRequired) || is_null($this->isRequired)');

        $e = $this->toXMLInternal($parent, Constants::NS_MD, 'md:RequestedAttribute');

        if ($this->isRequired === true) {
            $e->setAttribute('isRequired', 'true');
        } elseif ($this->isRequired === false) {
            $e->setAttribute('isRequired', 'false');
        }

        return $e;
    }
}
