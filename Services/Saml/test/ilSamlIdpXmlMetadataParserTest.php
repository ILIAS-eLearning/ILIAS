<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

class ilSamlIdpXmlMetadataParserTest extends TestCase
{
    private ilSamlIdpXmlMetadataParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new ilSamlIdpXmlMetadataParser(
            new ILIAS\Data\Factory(),
            new ilSamlIdpXmlMetadataErrorFormatter()
        );
    }

    public function testErrorsCanBeRetrievedWhenParsingNonXmlDocument(): void
    {
        $this->parser->parse('phpunit');

        $this->assertTrue($this->parser->result()->isError());
        $this->assertFalse($this->parser->result()->isOK());

        $this->assertNotEmpty($this->parser->result()->error());
    }

    /**
     * @see https://en.wikipedia.org/wiki/SAML_metadata#Identity_provider_metadata
     * @license https://creativecommons.org/licenses/by-sa/3.0/
     */
    public function testMetadataCanBeParsedFromValidXmlDocument(): void
    {
        $xml = <<<EOT
<md:EntityDescriptor entityID="https://sso.example.org/idp" validUntil="2017-08-30T19:10:29Z"
                     xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                     xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                     xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute"
                     xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <md:Extensions>
        <mdrpi:RegistrationInfo registrationAuthority="https://registrar.example.net"/>
        <mdrpi:PublicationInfo creationInstant="2017-08-16T19:10:29Z" publisher="https://registrar.example.net"/>
        <mdattr:EntityAttributes>
            <saml:Attribute Name="http://registrar.example.net/entity-category"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                <saml:AttributeValue>https://registrar.example.net/category/self-certified</saml:AttributeValue>
            </saml:Attribute>
        </mdattr:EntityAttributes>
    </md:Extensions>
    <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:Extensions>
            <mdui:UIInfo>
                <mdui:DisplayName xml:lang="en">Example.org</mdui:DisplayName>
                <mdui:Description xml:lang="en">The identity provider at Example.org</mdui:Description>
                <mdui:Logo height="32" width="32" xml:lang="en">https://idp.example.org/myicon.png</mdui:Logo>
            </mdui:UIInfo>
        </md:Extensions>
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo>...</ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://idp.example.org/SAML2/SSO/Redirect"/>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                Location="https://idp.example.org/SAML2/SSO/POST"/>
    </md:IDPSSODescriptor>
    <md:Organization>
        <md:OrganizationName xml:lang="en">Example.org Non-Profit Org</md:OrganizationName>
        <md:OrganizationDisplayName xml:lang="en">Example.org</md:OrganizationDisplayName>
        <md:OrganizationURL xml:lang="en">https://www.example.org/</md:OrganizationURL>
    </md:Organization>
    <md:ContactPerson contactType="technical">
        <md:SurName>SAML Technical Support</md:SurName>
        <md:EmailAddress>mailto:technical-support@example.org</md:EmailAddress>
    </md:ContactPerson>
</md:EntityDescriptor>
EOT;

        $this->parser->parse($xml);

        $this->assertFalse($this->parser->result()->isError(), $this->parser->result()->isError() ? $this->parser->result()->error() : '');
        $this->assertTrue($this->parser->result()->isOK());

        $this->assertSame('https://sso.example.org/idp', $this->parser->result()->value());
    }

    /**
     * @see https://en.wikipedia.org/wiki/SAML_metadata#Identity_provider_metadata
     * @license https://creativecommons.org/licenses/by-sa/3.0/
     */
    public function testMetadataCannotBeParsedFromValidXmlDocument(): void
    {
        $xml = <<<EOT
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                     xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                     xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute"
                     xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <md:Extensions>
        <mdrpi:RegistrationInfo registrationAuthority="https://registrar.example.net"/>
        <mdrpi:PublicationInfo creationInstant="2017-08-16T19:10:29Z" publisher="https://registrar.example.net"/>
        <mdattr:EntityAttributes>
            <saml:Attribute Name="http://registrar.example.net/entity-category"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                <saml:AttributeValue>https://registrar.example.net/category/self-certified</saml:AttributeValue>
            </saml:Attribute>
        </mdattr:EntityAttributes>
    </md:Extensions>
    <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:Extensions>
            <mdui:UIInfo>
                <mdui:DisplayName xml:lang="en">Example.org</mdui:DisplayName>
                <mdui:Description xml:lang="en">The identity provider at Example.org</mdui:Description>
                <mdui:Logo height="32" width="32" xml:lang="en">https://idp.example.org/myicon.png</mdui:Logo>
            </mdui:UIInfo>
        </md:Extensions>
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo>...</ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                                Location="https://idp.example.org/SAML2/SSO/Redirect"/>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                                Location="https://idp.example.org/SAML2/SSO/POST"/>
    </md:IDPSSODescriptor>
    <md:Organization>
        <md:OrganizationName xml:lang="en">Example.org Non-Profit Org</md:OrganizationName>
        <md:OrganizationDisplayName xml:lang="en">Example.org</md:OrganizationDisplayName>
        <md:OrganizationURL xml:lang="en">https://www.example.org/</md:OrganizationURL>
    </md:Organization>
    <md:ContactPerson contactType="technical">
        <md:SurName>SAML Technical Support</md:SurName>
        <md:EmailAddress>mailto:technical-support@example.org</md:EmailAddress>
    </md:ContactPerson>
</md:EntityDescriptor>
EOT;

        $this->parser->parse($xml);

        $this->assertTrue($this->parser->result()->isError());
        $this->assertFalse($this->parser->result()->isOK());

        $this->assertNotEmpty($this->parser->result()->error());
    }
}
