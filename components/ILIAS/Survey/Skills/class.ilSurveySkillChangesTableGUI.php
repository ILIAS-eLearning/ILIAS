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
 * TableGUI class for survey skill changes
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkillChangesTableGUI extends ilTable2GUI
{
    protected ilSurveySkill $survey_skill;
    protected ilObjSurvey $survey;
    protected array $appraisee;
    protected ilSurveySkillThresholds $thresholds;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjSurvey $a_survey,
        array $a_appraisee
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->survey = $a_survey;
        $this->appraisee = $a_appraisee;

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
    }

    public function getSkillLevelsForAppraisee(): void
    {
        $sskill = new ilSurveySkill($this->survey);

        if ($this->survey->get360Mode()) {
            $new_levels = $sskill->determineSkillLevelsForAppraisee($this->appraisee["user_id"]);
        } else {			//Svy self evaluation mode.
            $new_levels = $sskill->determineSkillLevelsForAppraisee(ilObjUser::getUserIdByLogin($this->appraisee["login"]), true);
        }

        $this->setData($new_levels);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        //var_dump($a_set);
        $this->tpl->setVariable("SKILL", $a_set["skill_title"]);
        $this->tpl->setVariable("MEAN_SUM", $a_set["mean_sum"]);
        $this->tpl->setVariable("NEW_LEVEL", $a_set["new_level"]);
    }
}
