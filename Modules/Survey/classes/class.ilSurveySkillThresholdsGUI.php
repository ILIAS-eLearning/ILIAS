<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill thresholds GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillThresholdsGUI:
 * @ingroup ModulesSurvey
 */
class ilSurveySkillThresholdsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Constructor
     *
     * @param object $a_survey
     */
    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->survey = $a_survey;
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd();
        
        $ilCtrl->saveParameter($this, array("sk_id", "tref_id"));
        
        if (in_array($cmd, array("listCompetences", "listSkillThresholds", "selectSkill",
            "saveThresholds"))) {
            $this->$cmd();
        }
    }
    
    /**
     * List competences
     *
     * @param
     * @return
     */
    public function listCompetences()
    {
        $tpl = $this->tpl;
        
        include_once("./Modules/Survey/classes/class.ilSurveySkillTableGUI.php");
        $tab = new ilSurveySkillTableGUI($this, "listCompetences", $this->survey);
        $tpl->setContent($tab->getHTML());
    }
    
    
    /**
     * List skill thresholds
     */
    public function listSkillThresholds()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("svy_back"),
            $ilCtrl->getLinkTarget($this, "listCompetences")
        );
        
        include_once("./Modules/Survey/classes/class.ilSurveySkillThresholdsTableGUI.php");
        $tab = new ilSurveySkillThresholdsTableGUI(
            $this,
            "listSkillThresholds",
            $this->survey,
            (int) $_GET["sk_id"],
            (int) $_GET["tref_id"]
        );
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Select skill
     *
     * @param
     * @return
     */
    public function selectSkill()
    {
        $ilCtrl = $this->ctrl;
        
        $o = explode(":", $_POST["skill"]);
        $ilCtrl->setParameter($this, "sk_id", (int) $o[0]);
        $ilCtrl->setParameter($this, "tref_id", (int) $o[1]);
        $ilCtrl->redirect($this, "listSkillThresholds");
    }
    
    /**
     * Save Thresholds
     *
     * @param
     * @return
     */
    public function saveThresholds()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
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
