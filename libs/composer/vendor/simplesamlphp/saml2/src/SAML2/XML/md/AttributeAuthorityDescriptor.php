<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;

/**
 * Class representing SAML 2 metadata AttributeAuthorityDescriptor.
 *
 * @package SimpleSAMLphp
 */
class AttributeAuthorityDescriptor extends RoleDescriptor
{
    /**
     * List of AttributeService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AttributeService = array();

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AssertionIDRequestService = array();

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    public $NameIDFormat = array();

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
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('md:AttributeAuthorityDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AttributeService') as $ep) {
            $this->AttributeService[] = new EndpointType($ep);
        }
        if (empty($this->AttributeService)) {
            throw new \Exception('Must have at least one AttributeService in AttributeAuthorityDescriptor.');
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionIDRequestService') as $ep) {
            $this->AssertionIDRequestService[] = new EndpointType($ep);
        }

        $this->NameIDFormat = Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');

        $this->AttributeProfile = Utils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile');

        foreach (Utils::xpQuery($xml, './saml_assertion:Attribute') as $a) {
            $this->Attribute[] = new Attribute($a);
        }
    }

    /**
     * Add this AttributeAuthorityDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->AttributeService)');
        assert('!empty($this->AttributeService)');
        assert('is_array($this->AssertionIDRequestService)');
        assert('is_array($this->NameIDFormat)');
        assert('is_array($this->AttributeProfile)');
        assert('is_array($this->Attribute)');

        $e = parent::toXML($parent);

        foreach ($this->AttributeService as $ep) {
            $ep->toXML($e, 'md:AttributeService');
        }

        foreach ($this->AssertionIDRequestService as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormat);

        Utils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->AttributeProfile);

        foreach ($this->Attribute as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
