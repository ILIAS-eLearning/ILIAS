<?php

namespace SAML2\XML\ecp;

use DOMElement;
use InvalidArgumentException;

use SAML2\Constants;

/**
 * Class representing the ECP Response element.
 */
class Response
{
    /**
     * The AssertionConsumerServiceURL.
     *
     * @var string
     */
    public $AssertionConsumerServiceURL;

    /**
     * Create a ECP Response element.
     *
     * @param DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        }
        if ($xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'actor')) {
            throw new Exception('Missing SOAP-ENV:actor attribute in <ecp:Response>.');
        }
        if ($xml->getAttributeNS(Constants::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            throw new Exception('Invalid value of SOAP-ENV:actor attribute in <ecp:Response>.');
        }

        if (!$xml->hasAttribute('AssertionConsumerServiceURL')) {
            throw new Exception('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');
        }

        $this->AssertionConsumerServiceURL = $xml->getAttribute('AssertionConsumerServiceURL');
    }
    /**
     * Convert this ECP Response to XML.
     *
     * @param DOMElement $parent The element we should append this element to.
     */
    public function toXML(DOMElement $parent)
    {
        if (!is_string($this->AssertionConsumerServiceURL)) {
            throw new InvalidArgumentException("AssertionConsumerServiceURL must be a string");
        }

        $doc = $parent->ownerDocument;
        $response = $doc->createElementNS(Constants::NS_ECP, 'ecp:Response');

        $parent->appendChild($response);

        $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');
        $response->setAttribute('AssertionConsumerServiceURL', $this->AssertionConsumerServiceURL);

        return $response;
    }
}
