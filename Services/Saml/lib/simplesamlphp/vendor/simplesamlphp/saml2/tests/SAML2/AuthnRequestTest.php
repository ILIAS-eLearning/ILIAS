<?php

/**
 * Class SAML2_AuthnRequestTest
 */
class SAML2_AuthnRequestTest extends PHPUnit_Framework_TestCase
{
    public function testUnmarshalling()
    {
        $authnRequest = new SAML2_AuthnRequest();
        $authnRequest->setRequestedAuthnContext(array(
            'AuthnContextClassRef' => array(
                'accr1',
                'accr2',
            ),
            'Comparison' => 'better',
        ));

        $authnRequestElement = $authnRequest->toUnsignedXML();

        $requestedAuthnContextElements = SAML2_Utils::xpQuery(
            $authnRequestElement,
            './saml_protocol:RequestedAuthnContext'
        );
        $this->assertCount(1, $requestedAuthnContextElements);

        $requestedAuthnConextElement = $requestedAuthnContextElements[0];
        $this->assertEquals('better', $requestedAuthnConextElement->getAttribute("Comparison"));

        $authnContextClassRefElements = SAML2_Utils::xpQuery(
            $requestedAuthnConextElement,
            './saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(2, $authnContextClassRefElements);
        $this->assertEquals('accr1', $authnContextClassRefElements[0]->textContent);
        $this->assertEquals('accr2', $authnContextClassRefElements[1]->textContent);
    }

    /**
     * Test unmarshalling / marshalling of XML with Extensions element
     */
    public function testExtensionOrdering()
    {
        $document = new DOMDocument();
        $document->loadXML(<<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Extensions>
      <myns:AttributeList xmlns:myns="urn:mynamespace">
          <myns:Attribute name="UserName" value=""/>
      </myns:AttributeList>
  </samlp:Extensions>
  <samlp:NameIDPolicy
    AllowCreate="true"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
</samlp:AuthnRequest>
AUTHNREQUEST
        );
        $authnRequest = new SAML2_AuthnRequest($document->documentElement);

        $this->assertXmlStringEqualsXmlString($document->C14N(), $authnRequest->toUnsignedXML()->C14N());
    }
}
