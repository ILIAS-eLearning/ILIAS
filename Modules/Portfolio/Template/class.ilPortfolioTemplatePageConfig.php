<?php

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

/**
 * Portfolio template page configuration
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTemplatePageConfig extends ilPortfolioPageConfig
{
    public function init(): void
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $lng = $DIC->language();
        $lng->loadLanguageModule("prtf");
        $request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();

        parent::init();
        $this->setIntLinkHelpDefaultId($request->getRefId());
        $this->addIntLinkFilter("PortfolioTemplatePage");
        $this->removeIntLinkFilter("PortfolioPage");
        $this->setIntLinkHelpDefaultType("PortfolioTemplatePage");

        $this->setEnablePCType("Verification", false);
        $this->setEnablePCType("PlaceHolder", true);
        $this->setEnablePCType("AMDForm", true);

        $this->setSectionProtection(ilPageConfig::SEC_PROTECT_EDITABLE);
        $this->setSectionProtectionInfo($lng->txt("prtf_sec_protected_info"));
    }

    public function getAvailablePlaceholderTypes(): array
    {
        // no questions
        $all = array(
            ilPCPlaceHolderGUI::TYPE_TEXT,
            ilPCPlaceHolderGUI::TYPE_MEDIA
        );

        $validator = new ilCertificateActiveValidator();
        if (true === $validator->validate()) {
            $all[] = ilPCPlaceHolderGUI::TYPE_VERIFICATION;
        }

        return $all;
    }
}
