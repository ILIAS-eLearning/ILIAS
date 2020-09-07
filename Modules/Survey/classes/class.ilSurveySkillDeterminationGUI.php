<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill determination GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillDeterminationGUI:
 * @ingroup ModulesSurvey
 */
class ilSurveySkillDeterminationGUI
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
        $this->survey = $a_survey;
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $cmd = $ilCtrl->getCmd("listSkillChanges");
        
        //$ilCtrl->saveParameter($this, array("sk_id", "tref_id"));
        
        if (in_array($cmd, array("listSkillChanges", "writeSkills"))) {
            $this->$cmd();
        }
    }
    
    /**
     * List skill changes
     */
    public function listSkillChanges()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("./Modules/Survey/classes/class.ilSurveySkillChangesTableGUI.php");

        //		$ilToolbar->addButton($lng->txt("survey_write_skills"),
        //			$ilCtrl->getLinkTarget($this, "writeSkills"));
        if ($this->survey->get360Mode()) {
            $apps = $this->survey->getAppraiseesData();
        } else { // Mode self evaluation, No Appraisee and Rater involved.
            $apps = $this->survey->getSurveyParticipants();
        }
        $ctpl = new ilTemplate("tpl.svy_skill_list_changes.html", true, true, "Modules/Survey");
        foreach ($apps as $app) {
            $changes_table = new ilSurveySkillChangesTableGUI(
                $this,
                "listSkillChanges",
                $this->survey,
                $app
            );
            
            $ctpl->setCurrentBlock("appraisee");
            $ctpl->setVariable("LASTNAME", $app["lastname"]);
            $ctpl->setVariable("FIRSTNAME", $app["firstname"]);
            
            $ctpl->setVariable("CHANGES_TABLE", $changes_table->getHTML());
            
            $ctpl->parseCurrentBlock();
        }
        
        $tpl->setContent($ctpl->get());
    }
    
    /**
     * Write skills
     *
     * @param
     * @return
     */
    public function writeSkills()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        return;
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        $sskill = new ilSurveySkill($this->survey);
        $apps = $this->survey->getAppraiseesData();
        $ctpl = new ilTemplate("tpl.svy_skill_list_changes.html", true, true, "Modules/Survey");
        foreach ($apps as $app) {
            $new_levels = $sskill->determineSkillLevelsForAppraisee($app["user_id"]);
            foreach ($new_levels as $nl) {
                if ($nl["new_level_id"] > 0) {
                    ilBasicSkill::writeUserSkillLevelStatus(
                        $nl["new_level_id"],
                        $app["user_id"],
                        $this->survey->getRefId(),
                        $nl["tref_id"],
                        ilBasicSkill::ACHIEVED
                    );
                }
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listSkillChanges");
    }
}
