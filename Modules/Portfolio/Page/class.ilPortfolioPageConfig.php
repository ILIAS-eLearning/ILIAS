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
 * Portfolio page configuration
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioPageConfig extends ilPageConfig
{
    protected ilSetting $settings;
    protected ilRbacSystem $rbacsystem;

    public function init() : void
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();

        $request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();

        $rbacsystem = $this->rbacsystem;
        
        $prfa_set = new ilSetting("prfa");
        $this->setPreventHTMLUnmasking(!(bool) $prfa_set->get("mask", false));

        $this->setEnableInternalLinks(true);
        $this->setIntLinkFilterWhiteList(true);
        $this->addIntLinkFilter("User");
        $this->addIntLinkFilter("PortfolioPage");
        $this->removeIntLinkFilter("File");
        $this->setIntLinkHelpDefaultId($request->getPortfolioId(), false);
        $this->setIntLinkHelpDefaultType("PortfolioPage");
        $this->setEnablePCType("Profile", true);
        $this->setEditLockSupport(false);
        $this->setSectionProtection(ilPageConfig::SEC_PROTECT_PROTECTED);

        $validator = new ilCertificateActiveValidator();
        if (true === $validator->validate()) {
            $this->setEnablePCType("Verification", true);
        }
        $skmg_set = new ilSetting("skmg");
        if ($skmg_set->get("enable_skmg")) {
            $this->setEnablePCType("Skills", true);
        }
            
        $settings = ilCalendarSettings::_getInstance();
        if ($settings->isEnabled() &&
            $rbacsystem->checkAccess('add_consultation_hours', $settings->getCalendarSettingsId()) &&
            $settings->areConsultationHoursEnabled()) {
            $this->setEnablePCType("ConsultationHours", true);
        }
        
        $prfa_set = new ilSetting("prfa");
        if ($prfa_set->get("mycrs", true)) {
            $this->setEnablePCType("MyCourses", true);
        }

        $mset = new ilSetting("mobs");
        if ($mset->get("mep_activate_pages")) {
            $this->setEnablePCType("ContentInclude", true);
        }

        $this->setEnablePCType("LearningHistory", true);
    }
}
