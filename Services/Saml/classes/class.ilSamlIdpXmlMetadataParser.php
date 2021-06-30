<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpXmlMetadataParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdpXmlMetadataParser
{
    /** @var string[] */
    protected array $errors = [];
    protected string $entityId = '';

    public function parse(string $xmlString) : void
    {
        libxml_use_internal_errors(true);

        $xml = new SimpleXMLElement($xmlString);

        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
        $entityid = null;
        if ($idps && isset($idps[0])) {
            $entityid = (string) $idps[0]->attributes('', true)->entityID[0];
        }

        foreach (libxml_get_errors() as $error) {
            $this->pushError($error->line . ': ' . $error->message);
        }

        if ($entityid) {
            $this->entityId = $entityid;
        }

        libxml_clear_errors();
    }

    private function pushError(string $error) : void
    {
        $this->errors[] = $error;
    }

    public function hasErrors() : bool
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * @return string[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    public function getEntityId() : string
    {
        return $this->entityId;
    }
}
