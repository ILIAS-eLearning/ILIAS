<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpMetadataInputGUI
 */
class ilSamlIdpMetadataInputGUI extends \ilTextAreaInputGUI
{
    /**
     * @var \ilSamlIdpXmlMetadataParser
     */
    protected $idpMetadataParser;

    /**
     * ilSamlIdpMetadataInputGUI constructor.
     * @param string                          $a_title
     * @param string                          $a_postvar
     * @param ilSamlIdpXmlMetadataParser|null $idpMetadataParser
     */
    public function __construct($a_title = '', $a_postvar = '', \ilSamlIdpXmlMetadataParser $idpMetadataParser = null)
    {
        parent::__construct($a_title, $a_postvar);
        $this->idpMetadataParser = $idpMetadataParser;
    }

    /**
     * @return ilSamlIdpXmlMetadataParser
     */
    public function getIdpMetadataParser()
    {
        return $this->idpMetadataParser;
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {
        $valid = parent::checkInput();
        if (!$valid) {
            return false;
        }

        try {
            $httpValue = $_POST[$this->getPostVar()];

            $this->idpMetadataParser->parse($httpValue);
            if ($this->idpMetadataParser->hasErrors()) {
                $this->setAlert(implode('<br />', $this->idpMetadataParser->getErrors()));
                return false;
            }

            if (!$this->idpMetadataParser->getEntityId()) {
                $this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
                return false;
            }
        } catch (\Exception $e) {
            $this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
            return false;
        }

        return true;
    }
}
