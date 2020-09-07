<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
include_once("./Services/Skill/classes/class.ilBasicSkill.php");

/**
 * TableGUI class for survey skill changes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSurveySkillChangesTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_survey, $a_appraisee)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->survey = $a_survey;
        $this->appraisee = $a_appraisee;
        
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
        $this->survey_skill = new ilSurveySkill($a_survey);
        $this->thresholds = new ilSurveySkillThresholds($a_survey);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getSkillLevelsForAppraisee();

        $this->setTitle($lng->txt(""));
        $this->setLimit(9999);
        $this->disable("footer");

        $this->addColumn($this->lng->txt("survey_skill"));
        $this->addColumn($this->lng->txt("survey_sum_of_means"));
        $this->addColumn($this->lng->txt("survey_reached_level"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.survey_skill_change.html", "Modules/Survey");

        //$this->addMultiCommand("", $lng->txt(""));
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Get Skills
     *
     * @param
     * @return
     */
    public function getSkillLevelsForAppraisee()
    {
        $sskill = new ilSurveySkill($this->survey);

        if ($this->survey->get360Mode()) {
            $new_levels = $sskill->determineSkillLevelsForAppraisee($this->appraisee["user_id"]);
        } else {			//Svy self evaluation mode.
            $new_levels = $sskill->determineSkillLevelsForAppraisee(ilObjUser::getUserIdByLogin($this->appraisee["login"]), true);
        }

        $this->setData($new_levels);
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        //var_dump($a_set);
        $this->tpl->setVariable("SKILL", $a_set["skill_title"]);
        $this->tpl->setVariable("MEAN_SUM", $a_set["mean_sum"]);
        $this->tpl->setVariable("NEW_LEVEL", $a_set["new_level"]);
    }
}
