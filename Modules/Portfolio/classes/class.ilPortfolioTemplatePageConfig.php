<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Portfolio/classes/class.ilPortfolioPageConfig.php");

/**
 * Portfolio template page configuration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesPortfolio
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
        
        if (!$ilSetting->get('disable_wsp_certificates')) {
            $all[] = ilPCPlaceHolderGUI::TYPE_VERIFICATION;
        }
        
        return $all;
    }
}
