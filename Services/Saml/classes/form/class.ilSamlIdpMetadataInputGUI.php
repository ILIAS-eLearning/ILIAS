<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilSamlIdpMetadataInputGUI extends ilTextAreaInputGUI
{
    protected ilSamlIdpXmlMetadataParser $idpMetadataParser;

    public function __construct(string $title, string $httpPostVar, ilSamlIdpXmlMetadataParser $idpMetadataParser)
    {
        parent::__construct($title, $httpPostVar);
        $this->idpMetadataParser = $idpMetadataParser;
    }

    public function getIdpMetadataParser() : ilSamlIdpXmlMetadataParser
    {
        return $this->idpMetadataParser;
    }

    public function checkInput() : bool
    {
        $valid = parent::checkInput();
        if (!$valid) {
            return false;
        }

        try {
            $httpValue = $this->getInput();

            $result = $this->idpMetadataParser->parse($httpValue);
            if ($result->isError()) {
                $this->setAlert(implode(' ', [$this->lng->txt('auth_saml_add_idp_md_error'), $result->error()]));
                return false;
            }

            if (!$result->value()) {
                $this->setAlert($this->lng->txt('auth_saml_add_idp_md_error'));
                return false;
            }
        } catch (Exception $e) {
            $this->setAlert($this->lng->txt('auth_saml_add_idp_md_error'));
            return false;
        }

        return true;
    }
}
