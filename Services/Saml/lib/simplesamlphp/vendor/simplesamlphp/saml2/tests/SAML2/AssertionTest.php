<?php

/**
 * Class SAML2_AssertionTest
 */
class SAML2_AssertionTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        // Create an assertion
        $assertion = new \SAML2_Assertion();
        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));
        $assertion->setAuthnContext('someAuthnContext');

        // Marshall it to a DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $issuerElements = \SAML2_Utils::xpQuery($assertionElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);

        // Test for an AudienceRestriction
        $audienceElements = \SAML2_Utils::xpQuery(
            $assertionElement,
            './saml_assertion:Conditions/saml_assertion:AudienceRestriction/saml_assertion:Audience'
        );
        $this->assertCount(2, $audienceElements);
        $this->assertEquals('audience1', $audienceElements[0]->textContent);
        $this->assertEquals('audience2', $audienceElements[1]->textContent);

        // Test for an Authentication Context
        $authnContextElements = \SAML2_Utils::xpQuery(
            $assertionElement,
            './saml_assertion:AuthnStatement/saml_assertion:AuthnContext/saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('someAuthnContext', $authnContextElements[0]->textContent);

    }

    public function testUnmarshalling()
    {
        // Unmarshall an assertion
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );
        $assertion = new \SAML2_Assertion($document->firstChild);

        // Test for valid audiences
        $assertionValidAudiences = $assertion->getValidAudiences();
        $this->assertCount(2, $assertionValidAudiences);
        $this->assertEquals('audience1', $assertionValidAudiences[0]);
        $this->assertEquals('audience2', $assertionValidAudiences[1]);

        // Test for Authenticating Authorities
        $assertionAuthenticatingAuthorities = $assertion->getAuthenticatingAuthority();
        $this->assertCount(2, $assertionAuthenticatingAuthorities);
        $this->assertEquals('someIdP1', $assertionAuthenticatingAuthorities[0]);
        $this->assertEquals('someIdP2', $assertionAuthenticatingAuthorities[1]);
    }

    public function testAuthnContextDeclAndClassRef()
    {
        // Try with unmarshalling
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );


        $assertion = new \SAML2_Assertion($document->documentElement);
        $authnContextDecl = $assertion->getAuthnContextDecl();
        $this->assertNotEmpty($authnContextDecl);
        $this->assertEquals('AuthnContextDecl', $authnContextDecl->localName);
        $childLocalName = $authnContextDecl->getXML()->childNodes->item(1)->localName;
        $this->assertEquals('AuthenticationContextDeclaration', $childLocalName);

        $this->assertEquals('someAuthnContext', $assertion->getAuthnContextClassRef());
    }

    public function testAuthnContextDeclRefAndClassRef()
    {
        // Try with unmarshalling
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );


        $assertion = new \SAML2_Assertion($document->documentElement);
        $this->assertEquals('/relative/path/to/document.xml', $assertion->getAuthnContextDeclRef());
        $this->assertEquals('someAuthnContext', $assertion->getAuthnContextClassRef());
    }

    public function testAuthnContextDeclAndRefConstraint()
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
</samlac:AuthenticationContextDeclaration>
XML
    );

        $assertion = new \SAML2_Assertion();

        $e = null;
        try {
            $assertion->setAuthnContextDecl(new SAML2_XML_Chunk($document->documentElement));
            $assertion->setAuthnContextDeclRef('/relative/path/to/document.xml');
        }
        catch (Exception $e) {}
        $this->assertNotEmpty($e);

        // Try again in reverse order for good measure.
        $assertion = new \SAML2_Assertion();

        $e = null;
        try {
            $assertion->setAuthnContextDeclRef('/relative/path/to/document.xml');
            $assertion->setAuthnContextDecl(new SAML2_XML_Chunk($document->documentElement));
        }
        catch (Exception $e) {}
        $this->assertNotEmpty($e);

        // Try with unmarshalling
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );

        $e = null;
        try {
            new \SAML2_Assertion($document->documentElement);
        }
        catch (Exception $e) {}
        $this->assertNotEmpty($e);
    }

    public function testMustHaveClassRefOrDeclOrDeclRef()
    {
        // Unmarshall an assertion
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );
        $e = null;
        try {
            $assertion = new \SAML2_Assertion($document->firstChild);
        }
        catch (Exception $e) {
        }
        $this->assertNotEmpty($e);
    }

    /**
     * Tests that AuthnContextDeclRef is not mistaken for AuthnContextClassRef.
     *
     * This tests against reintroduction of removed behavior.
     */
    public function testNoAuthnContextDeclRefFallback()
    {
        $authnContextDeclRef = 'relative/url/to/authcontext.xml';

        // Unmarshall an assertion
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDeclRef>$authnContextDeclRef</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );
        $assertion = new \SAML2_Assertion($document->firstChild);
        $this->assertEmpty($assertion->getAuthnContextClassRef());
        $this->assertEquals($authnContextDeclRef, $assertion->getAuthnContextDeclRef());
    }
}

