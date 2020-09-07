<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for competence thresholds
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSurveySkillThresholdsTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_survey,
        $a_base_skill_id,
        $a_tref_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->object = $a_survey;
        $this->base_skill_id = $a_base_skill_id;
        $this->tref_id = $a_tref_id;
        
        
        $this->determineMaxScalesAndQuestions();

        ilUtil::sendInfo(
            $lng->txt("survey_skill_nr_q") . ": " . count($this->question_ids) .
            ", " . $lng->txt("survey_skill_max_scale_points") . ": " . $this->scale_sum
        );
        
        include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
        $this->skill_thres = new ilSurveySkillThresholds($this->object);
        $this->thresholds = $this->skill_thres->getThresholds();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        $this->skill_survey = new ilSurveySkill($a_survey);
        $this->setData($this->getLevels());
        $this->setTitle(ilBasicSkill::_lookupTitle($this->base_skill_id, $this->tref_id));
        
        $this->addColumn($this->lng->txt("survey_skill_level"));
        $this->addColumn($this->lng->txt("survey_up_to_x_points"));

        $this->setRowTemplate("tpl.svy_skill_threshold_row.html", "Modules/Survey");
        
        //		$this->addMultiCommand("saveThresholds", $lng->txt("save"));
        $this->addCommandButton("saveThresholds", $lng->txt("save"));
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
    }
    
    /**
     * Determine max scales and questions
     *
     * @param
     * @return
     */
    public function determineMaxScalesAndQuestions()
    {
        include_once("./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php");
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        $ssk = new ilSurveySkill($this->object);
        $this->question_ids = $ssk->getQuestionsForSkill(
            $this->base_skill_id,
            $this->tref_id
        );
        $this->scale_sum = $ssk->determineMaxScale(
            $this->base_skill_id,
            $this->tref_id
        );
    }
    
    
    /**
     * Get levels
     *
     * @param
     * @return
     */
    public function getLevels()
    {
        include_once("./Services/Skill/classes/class.ilBasicSkill.php");
        $bs = new ilBasicSkill($this->base_skill_id);
        return $bs->getLevelData();
    }
    
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->tpl->setVariable("LEVEL", $a_set["title"]);
        $this->tpl->setVariable("LEVEL_ID", $a_set["id"]);
        
        $tr = $this->thresholds[$a_set["id"]][$this->tref_id];
        if ((int) $tr != 0) {
            $this->tpl->setVariable("THRESHOLD", (int) $tr);
        } else {
            $this->tpl->setVariable("THRESHOLD", "");
        }
    }
}
