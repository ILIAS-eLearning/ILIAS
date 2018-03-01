<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 SSODescriptorType.
 *
 * @package SimpleSAMLphp
 */
abstract class SSODescriptorType extends RoleDescriptor
{
    /**
     * List of ArtifactResolutionService endpoints.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SAML2\XML\md\IndexedEndpointType[]
     */
    public $ArtifactResolutionService = array();

    /**
     * List of SingleLogoutService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $SingleLogoutService = array();

    /**
     * List of ManageNameIDService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $ManageNameIDService = array();

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    public $NameIDFormat = array();

    /**
     * Initialize a SSODescriptor.
     *
     * @param string          $elementName The name of this element.
     * @param \DOMElement|null $xml         The XML element we should load.
     */
    protected function __construct($elementName, \DOMElement $xml = null)
    {
        assert('is_string($elementName)');

        parent::__construct($elementName, $xml);

        if ($xml === null) {
            return;
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:ArtifactResolutionService') as $ep) {
            $this->ArtifactResolutionService[] = new IndexedEndpointType($ep);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:SingleLogoutService') as $ep) {
            $this->SingleLogoutService[] = new EndpointType($ep);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:ManageNameIDService') as $ep) {
            $this->ManageNameIDService[] = new EndpointType($ep);
        }

        $this->NameIDFormat = Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');
    }

    /**
     * Add this SSODescriptorType to an EntityDescriptor.
     *
     * @param  \DOMElement $parent The EntityDescriptor we should append this SSODescriptorType to.
     * @return \DOMElement The generated SSODescriptor DOMElement.
     */
    protected function toXML(\DOMElement $parent)
    {
        assert('is_array($this->ArtifactResolutionService)');
        assert('is_array($this->SingleLogoutService)');
        assert('is_array($this->ManageNameIDService)');
        assert('is_array($this->NameIDFormat)');

        $e = parent::toXML($parent);

        foreach ($this->ArtifactResolutionService as $ep) {
            $ep->toXML($e, 'md:ArtifactResolutionService');
        }

        foreach ($this->SingleLogoutService as $ep) {
            $ep->toXML($e, 'md:SingleLogoutService');
        }

        foreach ($this->ManageNameIDService as $ep) {
            $ep->toXML($e, 'md:ManageNameIDService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormat);

        return $e;
    }
}
