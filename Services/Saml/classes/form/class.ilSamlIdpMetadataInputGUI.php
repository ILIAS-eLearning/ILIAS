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

class ilSamlIdpMetadataInputGUI extends ilTextAreaInputGUI
{
    protected ilSamlIdpXmlMetadataParser $idpMetadataParser;

    public function __construct(string $title, string $httpPostVar, ilSamlIdpXmlMetadataParser $idpMetadataParser)
    {
        parent::__construct($title, $httpPostVar);
        $this->idpMetadataParser = $idpMetadataParser;
    }

    public function getIdpMetadataParser(): ilSamlIdpXmlMetadataParser
    {
        return $this->idpMetadataParser;
    }

    public function checkInput(): bool
    {
        $valid = parent::checkInput();
        if (!$valid) {
            return false;
        }

        try {
            $httpValue = $this->raw($this->getPostVar());

            $this->idpMetadataParser->parse($httpValue);
            if ($this->idpMetadataParser->result()->isError()) {
                $this->setAlert(implode(' ', [$this->lng->txt('auth_saml_add_idp_md_error'), $this->idpMetadataParser->result()->error()]));
                return false;
            }

            if (!$this->idpMetadataParser->result()->value()) {
                $this->setAlert($this->lng->txt('auth_saml_add_idp_md_error'));
                return false;
            }

            $this->value = $this->stripSlashesAddSpaceFallback($this->idpMetadataParser->result()->value());
        } catch (Exception $e) {
            $this->setAlert($this->lng->txt('auth_saml_add_idp_md_error'));
            return false;
        }

        return true;
    }
}
