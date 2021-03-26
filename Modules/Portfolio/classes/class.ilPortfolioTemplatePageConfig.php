<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio template page configuration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTemplatePageConfig extends ilPortfolioPageConfig
{
    public function init()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::init();
        $this->setIntLinkHelpDefaultId($_GET["ref_id"]);
        $this->addIntLinkFilter("PortfolioTemplatePage");
        $this->removeIntLinkFilter("PortfolioPage");
        $this->setIntLinkHelpDefaultType("PortfolioTemplatePage");

        $this->setEnablePCType("Verification", false);
        $this->setEnablePCType("PlaceHolder", true);
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
