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
 * Survey skill service GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilSurveySkillGUI: ilSurveySkillThresholdsGUI
 */
class ilSurveySkillGUI
{
    protected ilObjSurvey $survey;
    protected \ILIAS\Survey\Editing\EditingGUIRequest $edit_request;
    protected ilCtrl $ctrl;
    protected ilGlobalPageTemplate $tpl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;

    public function __construct(
        ilObjSurvey $a_survey
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->survey = $a_survey;
        $this->edit_request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();
    }
    
    public function executeCommand() : void
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
    public function listQuestionAssignment() : void
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
    public function assignSkillToQuestion() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilCtrl->saveParameter($this, "q_id");

        $sel = new ilSkillSelectorGUI(
            $this,
            "assignSkillToQuestion",
            $this,
            "selectSkillForQuestion",
            'selected_skill'
        );
        if (!$sel->handleCommand()) {
            $tpl->setContent($sel->getHTML());
        }
    }
    
    public function selectSkillForQuestion() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $skill_survey = new ilSurveySkill($this->survey);
        $skill_id_parts = explode(
            ":",
            $this->edit_request->getSelectedSkill()
        );
        $skill_survey->addQuestionSkillAssignment(
            $this->edit_request->getQuestionId(),
            (int) $skill_id_parts[0],
            (int) $skill_id_parts[1]
        );
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        
        $ilCtrl->redirect($this, "listQuestionAssignment");
    }
    
    public function removeSkillFromQuestion() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $skill_survey = new ilSurveySkill($this->survey);
        $skill_survey->removeQuestionSkillAssignment(
            $this->edit_request->getQuestionId()
        );
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        
        $ilCtrl->redirect($this, "listQuestionAssignment");
    }
    
    public function setSubTabs(string $a_activate) : void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab(
            "survey_skill_assign",
            $lng->txt("survey_skill_assign"),
            $ilCtrl->getLinkTargetByClass("ilsurveyskillgui", "listQuestionAssignment")
        );

        $ilTabs->addSubTab(
            "skill_thresholds",
            $lng->txt("survey_skill_thresholds"),
            $ilCtrl->getLinkTargetByClass("ilsurveyskillthresholdsgui", "listCompetences")
        );

        $ilTabs->activateSubTab($a_activate);
    }
}
