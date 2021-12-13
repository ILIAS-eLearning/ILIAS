<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio template page configuration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTemplatePageConfig extends ilPortfolioPageConfig
{
    public function init() : void
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->settings = $DIC->settings();
        $lng = $DIC->language();
        $lng->loadLanguageModule("prtf");

        parent::init();
        $this->setIntLinkHelpDefaultId($_GET["ref_id"]);
        $this->addIntLinkFilter("PortfolioTemplatePage");
        $this->removeIntLinkFilter("PortfolioPage");
        $this->setIntLinkHelpDefaultType("PortfolioTemplatePage");

        $this->setEnablePCType("Verification", false);
        $this->setEnablePCType("PlaceHolder", true);
        $this->setEnablePCType("AMDForm", true);

        $this->setSectionProtection(ilPageConfig::SEC_PROTECT_EDITABLE);
        $this->setSectionProtectionInfo($lng->txt("prtf_sec_protected_info"));
    }
    
    public function getAvailablePlaceholderTypes()
    {
        $ilSetting = $this->settings;
        
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
