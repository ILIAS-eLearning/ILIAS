<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Survey skill thresholds GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilSurveySkillThresholdsGUI:
 */
class ilSurveySkillThresholdsGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;

    public function __construct(
        ilObjSurvey $a_survey
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->survey = $a_survey;
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd();
        
        $ilCtrl->saveParameter($this, array("sk_id", "tref_id"));
        
        if (in_array($cmd, array("listCompetences", "listSkillThresholds", "selectSkill",
            "saveThresholds"))) {
            $this->$cmd();
        }
    }
    
    public function listCompetences() : void
    {
        $tpl = $this->tpl;
        
        $tab = new ilSurveySkillTableGUI($this, "listCompetences", $this->survey);
        $tpl->setContent($tab->getHTML());
    }
    
    public function listSkillThresholds() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("svy_back"),
            $ilCtrl->getLinkTarget($this, "listCompetences")
        );
        
        $tab = new ilSurveySkillThresholdsTableGUI(
            $this,
            "listSkillThresholds",
            $this->survey,
            (int) $_GET["sk_id"],
            (int) $_GET["tref_id"]
        );
        $tpl->setContent($tab->getHTML());
    }
    
    public function selectSkill() : void
    {
        $ilCtrl = $this->ctrl;
        
        $o = explode(":", $_POST["skill"]);
        $ilCtrl->setParameter($this, "sk_id", (int) $o[0]);
        $ilCtrl->setParameter($this, "tref_id", (int) $o[1]);
        $ilCtrl->redirect($this, "listSkillThresholds");
    }
    
    public function saveThresholds() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $thres = new ilSurveySkillThresholds($this->survey);

        if (is_array($_POST["threshold"])) {
            foreach ($_POST["threshold"] as $l => $t) {
                $thres->writeThreshold(
                    (int) $_GET["sk_id"],
                    (int) $_GET["tref_id"],
                    (int) $l,
                    (int) $t
                );
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), 1);
        }
        
        $ilCtrl->redirect($this, "listSkillThresholds");
    }
}
