<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill service GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillGUI: ilSurveySkillThresholdsGUI
 * @ingroup ModulesSurvey
 */
class ilSurveySkillGUI
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
     * @var ilObjUser
     */
    protected $user;

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
        $this->user = $DIC->user();
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
        $next_class = $ilCtrl->getNextClass();
        
        switch ($next_class) {
            case 'ilsurveyskillthresholdsgui':
                $this->setSubTabs("skill_thresholds");
                $gui = new ilSurveySkillThresholdsGUI($this->survey);
                $ilCtrl->forwardCommand($gui);
                break;
                
            default:
                if (in_array($cmd, array("listQuestionAssignment",
                    "assignSkillToQuestion", "selectSkillForQuestion",
                    "removeSkillFromQuestion"))) {
                    $this->setSubTabs("survey_skill_assign");
                    $this->$cmd();
                }
                break;
        }
    }
    
    /**
     * List question to skill assignment
     */
    public function listQuestionAssignment()
    {
        $tpl = $this->tpl;

        $tab = new ilSurveySkillAssignmentTableGUI(
            $this,
            "listQuestionAssignment",
            $this->survey
        );
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Assign skill to question
     */
    public function assignSkillToQuestion()
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilCtrl->saveParameter($this, "q_id");
        

        $sel = new ilSkillSelectorGUI($this, "assignSkillToQuestion", $this, "selectSkillForQuestion");
        if (!$sel->handleCommand()) {
            $tpl->setContent($sel->getHTML());
        }
    }
    
    /**
     * Select skill for question
     */
    public function selectSkillForQuestion()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $skill_survey = new ilSurveySkill($this->survey);
        $skill_id_parts = explode(":", $_GET["selected_skill"]);
        $skill_survey->addQuestionSkillAssignment(
            (int) $_GET["q_id"],
            (int) $skill_id_parts[0],
            (int) $skill_id_parts[1]
        );
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        
        $ilCtrl->redirect($this, "listQuestionAssignment");
    }
    
    /**
     * Remove skill from question
     */
    public function removeSkillFromQuestion()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $skill_survey = new ilSurveySkill($this->survey);
        $skill_survey->removeQuestionSkillAssignment((int) $_GET["q_id"]);
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        
        $ilCtrl->redirect($this, "listQuestionAssignment");
    }
    
    /**
     * Set subtabs
     *
     * @param string $a_activate activate sub tab (ID)
     */
    public function setSubTabs($a_activate)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubtab(
            "survey_skill_assign",
            $lng->txt("survey_skill_assign"),
            $ilCtrl->getLinkTargetByClass("ilsurveyskillgui", "listQuestionAssignment")
        );

        $ilTabs->addSubTab(
            "skill_thresholds",
            $lng->txt("survey_skill_thresholds"),
            $ilCtrl->getLinkTargetByClass("ilsurveyskillthresholdsgui", "listCompetences")
        );

        $ilTabs->activateSubtab($a_activate);
    }
}
