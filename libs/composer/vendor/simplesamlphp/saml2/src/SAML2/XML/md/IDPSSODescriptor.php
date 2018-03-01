<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;

/**
 * Class representing SAML 2 IDPSSODescriptor.
 *
 * @package SimpleSAMLphp
 */
class IDPSSODescriptor extends SSODescriptorType
{
    /**
     * Whether AuthnRequests sent to this IdP should be signed.
     *
     * @var bool|null
     */
    public $WantAuthnRequestsSigned = null;

    /**
     * List of SingleSignOnService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $SingleSignOnService = array();

    /**
     * List of NameIDMappingService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $NameIDMappingService = array();

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AssertionIDRequestService = array();

    /**
     * List of supported attribute profiles.
     *
     * Array with strings.
     *
     * @var array
     */
    public $AttributeProfile = array();

    /**
     * List of supported attributes.
     *
     * Array with \SAML2\XML\saml\Attribute objects.
     *
     * @var \SAML2\XML\saml\Attribute[]
     */
    public $Attribute = array();

    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('md:IDPSSODescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $this->WantAuthnRequestsSigned = Utils::parseBoolean($xml, 'WantAuthnRequestsSigned', null);

        foreach (Utils::xpQuery($xml, './saml_metadata:SingleSignOnService') as $ep) {
            $this->SingleSignOnService[] = new EndpointType($ep);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:NameIDMappingService') as $ep) {
            $this->NameIDMappingService[] = new EndpointType($ep);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionIDRequestService') as $ep) {
            $this->AssertionIDRequestService[] = new EndpointType($ep);
        }

        $this->AttributeProfile = Utils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile');

        foreach (Utils::xpQuery($xml, './saml_assertion:Attribute') as $a) {
            $this->Attribute[] = new Attribute($a);
        }
    }

    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_null($this->WantAuthnRequestsSigned) || is_bool($this->WantAuthnRequestsSigned)');
        assert('is_array($this->SingleSignOnService)');
        assert('is_array($this->NameIDMappingService)');
        assert('is_array($this->AssertionIDRequestService)');
        assert('is_array($this->AttributeProfile)');
        assert('is_array($this->Attribute)');

        $e = parent::toXML($parent);

        if ($this->WantAuthnRequestsSigned === true) {
            $e->setAttribute('WantAuthnRequestsSigned', 'true');
        } elseif ($this->WantAuthnRequestsSigned === false) {
            $e->setAttribute('WantAuthnRequestsSigned', 'false');
        }

        foreach ($this->SingleSignOnService as $ep) {
            $ep->toXML($e, 'md:SingleSignOnService');
        }

        foreach ($this->NameIDMappingService as $ep) {
            $ep->toXML($e, 'md:NameIDMappingService');
        }

        foreach ($this->AssertionIDRequestService as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->AttributeProfile);

        foreach ($this->Attribute as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
