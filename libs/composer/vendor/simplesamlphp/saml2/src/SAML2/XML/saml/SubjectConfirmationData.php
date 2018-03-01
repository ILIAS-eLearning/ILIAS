<?php

namespace SAML2\XML\saml;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package SimpleSAMLphp
 */
class SubjectConfirmationData
{
    /**
     * The time before this element is valid, as an unix timestamp.
     *
     * @var int|null
     */
    public $NotBefore;

    /**
     * The time after which this element is invalid, as an unix timestamp.
     *
     * @var int|null
     */
    public $NotOnOrAfter;

    /**
     * The Recipient this Subject is valid for. Either an entity or a location.
     *
     * @var string|null
     */
    public $Recipient;

    /**
     * The ID of the AuthnRequest this is a response to.
     *
     * @var string|null
     */
    public $InResponseTo;

    /**
     * The IP(v6) address of the user.
     *
     * @var string|null
     */
    public $Address;

    /**
     * The various key information elements.
     *
     * Array with various elements describing this key.
     * Unknown elements will be represented by \SAML2\XML\Chunk.
     *
     * @var (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[]
     */
    public $info = array();

    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('NotBefore')) {
            $this->NotBefore = Utils::xsDateTimeToTimestamp($xml->getAttribute('NotBefore'));
        }
        if ($xml->hasAttribute('NotOnOrAfter')) {
            $this->NotOnOrAfter = Utils::xsDateTimeToTimestamp($xml->getAttribute('NotOnOrAfter'));
        }
        if ($xml->hasAttribute('Recipient')) {
            $this->Recipient = $xml->getAttribute('Recipient');
        }
        if ($xml->hasAttribute('InResponseTo')) {
            $this->InResponseTo = $xml->getAttribute('InResponseTo');
        }
        if ($xml->hasAttribute('Address')) {
            $this->Address = $xml->getAttribute('Address');
        }
        for ($n = $xml->firstChild; $n !== null; $n = $n->nextSibling) {
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if ($n->namespaceURI !== XMLSecurityDSig::XMLDSIGNS) {
                $this->info[] = new Chunk($n);
                continue;
            }
            switch ($n->localName) {
                case 'KeyInfo':
                    $this->info[] = new KeyInfo($n);
                    break;
                default:
                    $this->info[] = new Chunk($n);
                    break;
            }
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
        assert('is_null($this->NotBefore) || is_int($this->NotBefore)');
        assert('is_null($this->NotOnOrAfter) || is_int($this->NotOnOrAfter)');
        assert('is_null($this->Recipient) || is_string($this->Recipient)');
        assert('is_null($this->InResponseTo) || is_string($this->InResponseTo)');
        assert('is_null($this->Address) || is_string($this->Address)');

        $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:SubjectConfirmationData');
        $parent->appendChild($e);

        if (isset($this->NotBefore)) {
            $e->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->NotBefore));
        }
        if (isset($this->NotOnOrAfter)) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->NotOnOrAfter));
        }
        if (isset($this->Recipient)) {
            $e->setAttribute('Recipient', $this->Recipient);
        }
        if (isset($this->InResponseTo)) {
            $e->setAttribute('InResponseTo', $this->InResponseTo);
        }
        if (isset($this->Address)) {
            $e->setAttribute('Address', $this->Address);
        }
        /** @var \SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk $n */
        foreach ($this->info as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
