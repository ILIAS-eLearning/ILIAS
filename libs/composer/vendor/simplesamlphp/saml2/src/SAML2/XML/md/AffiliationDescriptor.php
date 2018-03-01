<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\SignedElementHelper;
use SAML2\Utils;

/**
 * Class representing SAML 2 AffiliationDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class AffiliationDescriptor extends SignedElementHelper
{
    /**
     * The affiliationOwnerID.
     *
     * @var string
     */
    public $affiliationOwnerID;

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
     * The AffiliateMember(s).
     *
     * Array of entity ID strings.
     *
     * @var array
     */
    public $AffiliateMember = array();

    /**
     * KeyDescriptor elements.
     *
     * Array of \SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SAML2\XML\md\KeyDescriptor[]
     */
    public $KeyDescriptor = array();

    /**
     * Initialize a AffiliationDescriptor.
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

        if (!$xml->hasAttribute('affiliationOwnerID')) {
            throw new \Exception('Missing affiliationOwnerID on AffiliationDescriptor.');
        }
        $this->affiliationOwnerID = $xml->getAttribute('affiliationOwnerID');

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

        $this->AffiliateMember = Utils::extractStrings($xml, Constants::NS_MD, 'AffiliateMember');
        if (empty($this->AffiliateMember)) {
            throw new \Exception('Missing AffiliateMember in AffiliationDescriptor.');
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:KeyDescriptor') as $kd) {
            $this->KeyDescriptor[] = new KeyDescriptor($kd);
        }
    }

    /**
     * Add this AffiliationDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->affiliationOwnerID)');
        assert('is_null($this->ID) || is_string($this->ID)');
        assert('is_null($this->validUntil) || is_int($this->validUntil)');
        assert('is_null($this->cacheDuration) || is_string($this->cacheDuration)');
        assert('is_array($this->Extensions)');
        assert('is_array($this->AffiliateMember)');
        assert('!empty($this->AffiliateMember)');
        assert('is_array($this->KeyDescriptor)');

        $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:AffiliationDescriptor');
        $parent->appendChild($e);

        $e->setAttribute('affiliationOwnerID', $this->affiliationOwnerID);

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

        Utils::addStrings($e, Constants::NS_MD, 'md:AffiliateMember', false, $this->AffiliateMember);

        foreach ($this->KeyDescriptor as $kd) {
            $kd->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
