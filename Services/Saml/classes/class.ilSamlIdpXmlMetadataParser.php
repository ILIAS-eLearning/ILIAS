<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpXmlMetadataParser
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdpXmlMetadataParser
{
    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @var string
     */
    protected $entityId = '';

    /**
     * @param string $xml
     */
    public function parse($xml)
    {
        \libxml_use_internal_errors(true);

        $xml = new \SimpleXMLElement($xml);

        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $idps     = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
        $entityid = null;
        if ($idps && isset($idps[0])) {
            $entityid = (string) $idps[0]->attributes('', true)->entityID[0];
        }

        foreach (\libxml_get_errors() as $error) {
            $this->pushError($error->line . ': ' . $error->message);
        }

        if ($entityid) {
            $this->entityId = $entityid;
        }

        \libxml_clear_errors();
    }

    /**
     * @param string $error
     */
    private function pushError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
