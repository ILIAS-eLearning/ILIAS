<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\SignedElementHelper;
use SAML2\Utils;

/**
 * Class representing SAML 2 EntityDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class EntityDescriptor extends SignedElementHelper
{
    /**
     * The entityID this EntityDescriptor represents.
     *
     * @var string
     */
    public $entityID;

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
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    public $Extensions = array();

    /**
     * Array with all roles for this entity.
     *
     * Array of \SAML2\XML\md\RoleDescriptor objects (and subclasses of RoleDescriptor).
     *
     * @var (\SAML2\XML\md\UnknownRoleDescriptor|\SAML2\XML\md\IDPSSODescriptor|\SAML2\XML\md\SPSSODescriptor|\SAML2\XML\md\AuthnAuthorityDescriptor|\SAML2\XML\md\AttributeAuthorityDescriptor|\SAML2\XML\md\PDPDescriptor)[]
     */
    public $RoleDescriptor = array();

    /**
     * AffiliationDescriptor of this entity.
     *
     * @var \SAML2\XML\md\AffiliationDescriptor|null
     */
    public $AffiliationDescriptor = null;

    /**
     * Organization of this entity.
     *
     * @var \SAML2\XML\md\Organization|null
     */
    public $Organization = null;

    /**
     * ContactPerson elements for this entity.
     *
     * @var \SAML2\XML\md\ContactPerson[]
     */
    public $ContactPerson = array();

    /**
     * AdditionalMetadataLocation elements for this entity.
     *
     * @var \SAML2\XML\md\AdditionalMetadataLocation[]
     */
    public $AdditionalMetadataLocation = array();

    /**
     * Initialize an EntitiyDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('entityID')) {
            throw new \Exception('Missing required attribute entityID on EntityDescriptor.');
        }
        $this->entityID = $xml->getAttribute('entityID');

        if ($xml->hasAttribute('ID')) {
            $this->ID = $xml->getAttribute('ID');
        }
        if ($xml->hasAttribute('validUntil')) {
            $this->validUntil = Utils::xsDateTimeToTimestamp($xml->getAttribute('validUntil'));
        }
        if ($xml->hasAttribute('cacheDuration')) {
            $this->cacheDuration = $xml->getAttribute('cacheDuration');
        }

        $this->Extensions = Extensions::getList($xml);

        for ($node = $xml->firstChild; $node !== null; $node = $node->nextSibling) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            if ($node->namespaceURI !== Constants::NS_MD) {
                continue;
            }

            switch ($node->localName) {
                case 'RoleDescriptor':
                    $this->RoleDescriptor[] = new UnknownRoleDescriptor($node);
                    break;
                case 'IDPSSODescriptor':
                    $this->RoleDescriptor[] = new IDPSSODescriptor($node);
                    break;
                case 'SPSSODescriptor':
                    $this->RoleDescriptor[] = new SPSSODescriptor($node);
                    break;
                case 'AuthnAuthorityDescriptor':
                    $this->RoleDescriptor[] = new AuthnAuthorityDescriptor($node);
                    break;
                case 'AttributeAuthorityDescriptor':
                    $this->RoleDescriptor[] = new AttributeAuthorityDescriptor($node);
                    break;
                case 'PDPDescriptor':
                    $this->RoleDescriptor[] = new PDPDescriptor($node);
                    break;
            }
        }

        $affiliationDescriptor = Utils::xpQuery($xml, './saml_metadata:AffiliationDescriptor');
        if (count($affiliationDescriptor) > 1) {
            throw new \Exception('More than one AffiliationDescriptor in the entity.');
        } elseif (!empty($affiliationDescriptor)) {
            $this->AffiliationDescriptor = new AffiliationDescriptor($affiliationDescriptor[0]);
        }

        if (empty($this->RoleDescriptor) && is_null($this->AffiliationDescriptor)) {
            throw new \Exception('Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.');
        } elseif (!empty($this->RoleDescriptor) && !is_null($this->AffiliationDescriptor)) {
            throw new \Exception('AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.');
        }

        $organization = Utils::xpQuery($xml, './saml_metadata:Organization');
        if (count($organization) > 1) {
            throw new \Exception('More than one Organization in the entity.');
        } elseif (!empty($organization)) {
            $this->Organization = new Organization($organization[0]);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:ContactPerson') as $cp) {
            $this->ContactPerson[] = new ContactPerson($cp);
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AdditionalMetadataLocation') as $aml) {
            $this->AdditionalMetadataLocation[] = new AdditionalMetadataLocation($aml);
        }
    }

    /**
     * Create this EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntityDescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent = null)
    {
        assert('is_string($this->entityID)');
        assert('is_null($this->ID) || is_string($this->ID)');
        assert('is_null($this->validUntil) || is_int($this->validUntil)');
        assert('is_null($this->cacheDuration) || is_string($this->cacheDuration)');
        assert('is_array($this->Extensions)');
        assert('is_array($this->RoleDescriptor)');
        assert('is_null($this->AffiliationDescriptor) || $this->AffiliationDescriptor instanceof \SAML2\XML\md\AffiliationDescriptor');
        assert('is_null($this->Organization) || $this->Organization instanceof \SAML2\XML\md\Organization');
        assert('is_array($this->ContactPerson)');
        assert('is_array($this->AdditionalMetadataLocation)');

        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_MD, 'md:EntityDescriptor');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:EntityDescriptor');
            $parent->appendChild($e);
        }

        $e->setAttribute('entityID', $this->entityID);

        if (isset($this->ID)) {
            $e->setAttribute('ID', $this->ID);
        }

        if (isset($this->validUntil)) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if (isset($this->cacheDuration)) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        Extensions::addList($e, $this->Extensions);

        /** @var \SAML2\XML\md\UnknownRoleDescriptor|\SAML2\XML\md\IDPSSODescriptor|\SAML2\XML\md\SPSSODescriptor|\SAML2\XML\md\AuthnAuthorityDescriptor|\SAML2\XML\md\AttributeAuthorityDescriptor|\SAML2\XML\md\PDPDescriptor $n */
        foreach ($this->RoleDescriptor as $n) {
            $n->toXML($e);
        }

        if (isset($this->AffiliationDescriptor)) {
            $this->AffiliationDescriptor->toXML($e);
        }

        if (isset($this->Organization)) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPerson as $cp) {
            $cp->toXML($e);
        }

        foreach ($this->AdditionalMetadataLocation as $n) {
            $n->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
