<?php

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package SimpleSAMLphp
 */
class SubjectConfirmation
{
    /**
     * The method we can use to verify this Subject.
     *
     * @var string
     */
    public $Method;

    /**
     * The NameID of the entity that can use this element to verify the Subject.
     *
     * @var \SAML2\XML\saml\NameID|null
     */
    public $NameID;

    /**
     * SubjectConfirmationData element with extra data for verification of the Subject.
     *
     * @var \SAML2\XML\saml\SubjectConfirmationData|null
     */
    public $SubjectConfirmationData;

    /**
     * Initialize (and parse? a SubjectConfirmation element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Method')) {
            throw new \Exception('SubjectConfirmation element without Method attribute.');
        }
        $this->Method = $xml->getAttribute('Method');

        $nid = Utils::xpQuery($xml, './saml_assertion:NameID');
        if (count($nid) > 1) {
            throw new \Exception('More than one NameID in a SubjectConfirmation element.');
        } elseif (!empty($nid)) {
            $this->NameID = new NameID($nid[0]);
        }

        $scd = Utils::xpQuery($xml, './saml_assertion:SubjectConfirmationData');
        if (count($scd) > 1) {
            throw new \Exception('More than one SubjectConfirmationData child in a SubjectConfirmation element.');
        } elseif (!empty($scd)) {
            $this->SubjectConfirmationData = new SubjectConfirmationData($scd[0]);
        }
    }

    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_string($this->Method));
        assert(is_null($this->NameID) || $this->NameID instanceof NameID);
        assert(is_null($this->SubjectConfirmationData) || $this->SubjectConfirmationData instanceof SubjectConfirmationData);

        $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:SubjectConfirmation');
        $parent->appendChild($e);

        $e->setAttribute('Method', $this->Method);

        if (isset($this->NameID)) {
            $this->NameID->toXML($e);
        }
        if (isset($this->SubjectConfirmationData)) {
            $this->SubjectConfirmationData->toXML($e);
        }

        return $e;
    }
}
