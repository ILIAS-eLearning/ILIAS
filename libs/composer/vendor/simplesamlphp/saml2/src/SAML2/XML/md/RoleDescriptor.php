<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\SignedElementHelper;
use SAML2\Utils;

/**
 * Class representing SAML 2 RoleDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class RoleDescriptor extends SignedElementHelper
{
    /**
     * The name of this descriptor element.
     *
     * @var string
     */
    private $elementName;

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
     * List of supported protocols.
     *
     * @var array
     */
    public $protocolSupportEnumeration = array();

    /**
     * Error URL for this role.
     *
     * @var string|null
     */
    public $errorURL;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    public $Extensions = array();

    /**
     * KeyDescriptor elements.
     *
     * Array of \SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SAML2\XML\md\KeyDescriptor[]
     */
    public $KeyDescriptor = array();

    /**
     * Organization of this role.
     *
     * @var \SAML2\XML\md\Organization|null
     */
    public $Organization = null;

    /**
     * ContactPerson elements for this role.
     *
     * Array of \SAML2\XML\md\ContactPerson objects.
     *
     * @var \SAML2\XML\md\ContactPerson[]
     */
    public $ContactPerson = array();

    /**
     * Initialize a RoleDescriptor.
     *
     * @param string          $elementName The name of this element.
     * @param \DOMElement|null $xml         The XML element we should load.
     * @throws \Exception
     */
    protected function __construct($elementName, \DOMElement $xml = null)
    {
        assert('is_string($elementName)');

        parent::__construct($xml);
        $this->elementName = $elementName;

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

        if (!$xml->hasAttribute('protocolSupportEnumeration')) {
            throw new \Exception('Missing protocolSupportEnumeration attribute on ' . $xml->localName);
        }
        $this->protocolSupportEnumeration = preg_split('/[\s]+/', $xml->getAttribute('protocolSupportEnumeration'));

        if ($xml->hasAttribute('errorURL')) {
            $this->errorURL = $xml->getAttribute('errorURL');
        }

        $this->Extensions = Extensions::getList($xml);

        foreach (Utils::xpQuery($xml, './saml_metadata:KeyDescriptor') as $kd) {
            $this->KeyDescriptor[] = new KeyDescriptor($kd);
        }

        $organization = Utils::xpQuery($xml, './saml_metadata:Organization');
        if (count($organization) > 1) {
            throw new \Exception('More than one Organization in the entity.');
        } elseif (!empty($organization)) {
            $this->Organization = new Organization($organization[0]);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:ContactPerson') as $cp) {
            $this->contactPersons[] = new ContactPerson($cp);
        }
    }

    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    protected function toXML(\DOMElement $parent)
    {
        assert('is_null($this->ID) || is_string($this->ID)');
        assert('is_null($this->validUntil) || is_int($this->validUntil)');
        assert('is_null($this->cacheDuration) || is_string($this->cacheDuration)');
        assert('is_array($this->protocolSupportEnumeration)');
        assert('is_null($this->errorURL) || is_string($this->errorURL)');
        assert('is_array($this->Extensions)');
        assert('is_array($this->KeyDescriptor)');
        assert('is_null($this->Organization) || $this->Organization instanceof \SAML2\XML\md\Organization');
        assert('is_array($this->ContactPerson)');

        $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, $this->elementName);
        $parent->appendChild($e);

        if (isset($this->ID)) {
            $e->setAttribute('ID', $this->ID);
        }

        if (isset($this->validUntil)) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if (isset($this->cacheDuration)) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        $e->setAttribute('protocolSupportEnumeration', implode(' ', $this->protocolSupportEnumeration));

        if (isset($this->errorURL)) {
            $e->setAttribute('errorURL', $this->errorURL);
        }

        Extensions::addList($e, $this->Extensions);

        foreach ($this->KeyDescriptor as $kd) {
            $kd->toXML($e);
        }

        if (isset($this->Organization)) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPerson as $cp) {
            $cp->toXML($e);
        }

        return $e;
    }
}
