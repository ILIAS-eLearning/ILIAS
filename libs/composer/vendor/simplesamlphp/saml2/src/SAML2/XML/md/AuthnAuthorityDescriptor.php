<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 metadata AuthnAuthorityDescriptor.
 *
 * @package SimpleSAMLphp
 */
class AuthnAuthorityDescriptor extends RoleDescriptor
{
    /**
     * List of AuthnQueryService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AuthnQueryService = array();

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
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('md:AuthnAuthorityDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AuthnQueryService') as $ep) {
            $this->AuthnQueryService[] = new EndpointType($ep);
        }
        if (empty($this->AuthnQueryService)) {
            throw new \Exception('Must have at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionIDRequestService') as $ep) {
            $this->AssertionIDRequestService[] = new EndpointType($ep);
        }

        $this->NameIDFormat = Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');
    }

    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this AuthnAuthorityDescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->AuthnQueryService)');
        assert('!empty($this->AuthnQueryService)');
        assert('is_array($this->AssertionIDRequestService)');
        assert('is_array($this->NameIDFormat)');

        $e = parent::toXML($parent);

        foreach ($this->AuthnQueryService as $ep) {
            $ep->toXML($e, 'md:AuthnQueryService');
        }

        foreach ($this->AssertionIDRequestService as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormat);

        return $e;
    }
}
