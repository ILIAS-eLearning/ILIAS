<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpMetadataInputGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
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

    public function checkInput()
    {
        $valid = parent::checkInput();
        if (!$valid) {
            return false;
        }

        try {
            $httpValue = (string) ($_POST[$this->getPostVar()] ?? '');

            $this->idpMetadataParser->parse($httpValue);
            if ($this->idpMetadataParser->hasErrors()) {
                $this->setAlert(implode('<br />', $this->idpMetadataParser->getErrors()));
                return false;
            }

            if (!$this->idpMetadataParser->getEntityId()) {
                $this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
                return false;
            }
        } catch (Exception $e) {
            $this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
            return false;
        }

        return true;
    }
}
