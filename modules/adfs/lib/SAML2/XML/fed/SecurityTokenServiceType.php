<?php

namespace SimpleSAML\Module\adfs\SAML2\XML\fed;

use DOMElement;
use Exception;
use SAML2\XML\md\RoleDescriptor;

/**
 * Class representing SecurityTokenServiceType RoleDescriptor.
 *
 * @package SimpleSAMLphp
 */

class SecurityTokenServiceType extends RoleDescriptor
{
    /**
     * List of supported protocols.
     *
     * @var array $protocolSupportEnumeration
     */
    public $protocolSupportEnumeration = [Constants::NS_FED];

    /**
     * The Location of Services.
     *
     * @var string|null $Location
     */
    public $Location = null;


    /**
     * Initialize a SecurityTokenServiceType element.
     *
     * @param \DOMElement|null $xml  The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('RoleDescriptor', $xml);
        if ($xml === null) {
            return;
        }
    }

    /**
     * Convert this SecurityTokenServiceType RoleDescriptor to XML.
     *
     * @param \DOMElement $parent  The element we should add this contact to.
     * @return \DOMElement  The new ContactPerson-element.
     */
    public function toXML(DOMElement $parent)
    {
        if (is_null($this->Location)) {
            throw new Exception('Location not set');
        }

        assert(is_string($this->Location));

        $e = parent::toXML($parent);
        $e->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:fed', Constants::NS_FED);
        $e->setAttributeNS(\SAML2\Constants::NS_XSI, 'xsi:type', 'fed:SecurityTokenServiceType');
        TokenTypesOffered::appendXML($e);
        Endpoint::appendXML($e, 'SecurityTokenServiceEndpoint', $this->Location);
        Endpoint::appendXML($e, 'fed:PassiveRequestorEndpoint', $this->Location);

        return $e;
    }


    /**
     * Get the location of this service.
     *
     * @return string|null The full URL where this service can be reached.
     */
    public function getLocation()
    {
        return $this->Location;
    }


    /**
     * Set the location of this service.
     *
     * @param string $location The full URL where this service can be reached.
     * @return void
     */
    public function setLocation($location)
    {
        $this->Location = $location;
    }
}
