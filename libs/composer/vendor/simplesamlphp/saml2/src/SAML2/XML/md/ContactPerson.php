<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 ContactPerson.
 *
 * @package SimpleSAMLphp
 */
class ContactPerson
{
    /**
     * The contact type.
     *
     * @var string
     */
    public $contactType;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    public $Extensions = array();

    /**
     * The Company of this contact.
     *
     * @var string
     */
    public $Company = null;

    /**
     * The GivenName of this contact.
     *
     * @var string
     */
    public $GivenName = null;

    /**
     * The SurName of this contact.
     *
     * @var string
     */
    public $SurName = null;

    /**
     * The EmailAddresses of this contact.
     *
     * @var array
     */
    public $EmailAddress = array();

    /**
     * The TelephoneNumbers of this contact.
     *
     * @var array
     */
    public $TelephoneNumber = array();

    /**
     * Extra attributes on the contact element.
     *
     * @var array
     */
    public $ContactPersonAttributes = array();

    /**
     * Initialize a ContactPerson element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('contactType')) {
            throw new \Exception('Missing contactType on ContactPerson.');
        }
        $this->contactType = $xml->getAttribute('contactType');

        $this->Extensions = Extensions::getList($xml);

        $this->Company = self::getStringElement($xml, 'Company');
        $this->GivenName = self::getStringElement($xml, 'GivenName');
        $this->SurName = self::getStringElement($xml, 'SurName');
        $this->EmailAddress = self::getStringElements($xml, 'EmailAddress');
        $this->TelephoneNumber = self::getStringElements($xml, 'TelephoneNumber');

        foreach ($xml->attributes as $attr) {
            if ($attr->nodeName == "contactType") {
                continue;
            }

            $this->ContactPersonAttributes[$attr->nodeName] = $attr->nodeValue;
        }
    }

    /**
     * Retrieve the value of a child \DOMElements as an array of strings.
     *
     * @param  \DOMElement $parent The parent element.
     * @param  string     $name   The name of the child elements.
     * @return array      The value of the child elements.
     */
    private static function getStringElements(\DOMElement $parent, $name)
    {
        assert('is_string($name)');

        $e = Utils::xpQuery($parent, './saml_metadata:' . $name);

        $ret = array();
        foreach ($e as $i) {
            $ret[] = $i->textContent;
        }

        return $ret;
    }

    /**
     * Retrieve the value of a child \DOMElement as a string.
     *
     * @param  \DOMElement  $parent The parent element.
     * @param  string      $name   The name of the child element.
     * @return string|null The value of the child element.
     * @throws \Exception
     */
    private static function getStringElement(\DOMElement $parent, $name)
    {
        assert('is_string($name)');

        $e = self::getStringElements($parent, $name);
        if (empty($e)) {
            return null;
        }
        if (count($e) > 1) {
            throw new \Exception('More than one ' . $name . ' in ' . $parent->tagName);
        }

        return $e[0];
    }

    /**
     * Convert this ContactPerson to XML.
     *
     * @param  \DOMElement $parent The element we should add this contact to.
     * @return \DOMElement The new ContactPerson-element.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->contactType)');
        assert('is_array($this->Extensions)');
        assert('is_null($this->Company) || is_string($this->Company)');
        assert('is_null($this->GivenName) || is_string($this->GivenName)');
        assert('is_null($this->SurName) || is_string($this->SurName)');
        assert('is_array($this->EmailAddress)');
        assert('is_array($this->TelephoneNumber)');
        assert('is_array($this->ContactPersonAttributes)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:ContactPerson');
        $parent->appendChild($e);

        $e->setAttribute('contactType', $this->contactType);

        foreach ($this->ContactPersonAttributes as $attr => $val) {
            $e->setAttribute($attr, $val);
        }

        Extensions::addList($e, $this->Extensions);

        if (isset($this->Company)) {
            Utils::addString($e, Constants::NS_MD, 'md:Company', $this->Company);
        }
        if (isset($this->GivenName)) {
            Utils::addString($e, Constants::NS_MD, 'md:GivenName', $this->GivenName);
        }
        if (isset($this->SurName)) {
            Utils::addString($e, Constants::NS_MD, 'md:SurName', $this->SurName);
        }
        if (!empty($this->EmailAddress)) {
            Utils::addStrings($e, Constants::NS_MD, 'md:EmailAddress', false, $this->EmailAddress);
        }
        if (!empty($this->TelephoneNumber)) {
            Utils::addStrings($e, Constants::NS_MD, 'md:TelephoneNumber', false, $this->TelephoneNumber);
        }

        return $e;
    }
}
