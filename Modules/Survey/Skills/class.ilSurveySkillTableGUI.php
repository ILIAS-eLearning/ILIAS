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

use ILIAS\Skill\Service\SkillTreeService;

/**
 * TableGUI class for skill list in survey
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkillTableGUI extends ilTable2GUI
{
    protected ilSurveySkillThresholds $skill_thres;
    protected SkillTreeService $skill_tree_service;
    protected ilGlobalSkillTree $skill_tree;
    protected ilObjSurvey $survey;
    /** @var array<int, array<int, int>> */
    protected array $thresholds;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjSurvey $a_survey
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->survey = $a_survey;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getSkills();
        $this->setTitle($lng->txt("survey_competences"));

        $this->skill_tree_service = $DIC->skills()->tree();
        $this->skill_tree = $this->skill_tree_service->getGlobalSkillTree();

        $this->skill_thres = new ilSurveySkillThresholds($a_survey);
        $this->thresholds = $this->skill_thres->getThresholds();

        $this->addColumn($this->lng->txt("survey_skill"));
        $this->addColumn($this->lng->txt("survey_skill_nr_q"));
        $this->addColumn($this->lng->txt("survey_skill_max_scale_points"));
        $this->addColumn($this->lng->txt("survey_up_to_x_points"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.svy_skill_row.html", "Modules/Survey");
    }

    public function getSkills(): void
    {
        $sskill = new ilSurveySkill($this->survey);
        $opts = $sskill->getAllAssignedSkillsAsOptions();
        $data = array();
        foreach ($opts as $k => $o) {
            $v = explode(":", $k);

            $question_ids = $sskill->getQuestionsForSkill($v[0], $v[1]);
            $scale_sum = $sskill->determineMaxScale($v[0], $v[1]);

            $data[] = array("title" => ilBasicSkill::_lookupTitle($v[0], $v[1]),
                "base_skill" => $v[0],
                "tref_id" => $v[1],
                "nr_of_q" => count($question_ids),
                "scale_sum" => $scale_sum
                );
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "sk_id", $a_set["base_skill"]);
        $ilCtrl->setParameter($this->parent_obj, "tref_id", $a_set["tref_id"]);

        $this->tpl->setVariable(
            "COMPETENCE",
            ilBasicSkill::_lookupTitle($a_set["base_skill"], $a_set["tref_id"])
        );
        $path = $this->skill_tree->getSkillTreePath($a_set["base_skill"], $a_set["tref_id"]);
        $path_nodes = array();
        foreach ($path as $p) {
            if ($p["child"] > 1 && $p["skill_id"] != $a_set["base_skill"]) {
                $path_nodes[] = ilBasicSkill::_lookupTitle($p["skill_id"], $p["tref_id"]);
            }
        }
        $this->tpl->setVariable("PATH", implode(" > ", $path_nodes));



        $this->tpl->setVariable("NR_OF_QUESTIONS", $a_set["nr_of_q"]);
        $this->tpl->setVariable("MAX_SCALE_POINTS", $a_set["scale_sum"]);
        $this->tpl->setVariable("CMD", $ilCtrl->getLinkTarget($this->parent_obj, "listSkillThresholds"));
        $this->tpl->setVariable("ACTION", $lng->txt("edit"));

        $bs = new ilBasicSkill($a_set["base_skill"]);
        $ld = $bs->getLevelData();
        foreach ($ld as $l) {
            $this->tpl->setCurrentBlock("points");
            $this->tpl->setVariable("LEV", $l["title"]);

            $tr = $this->thresholds[$l["id"]][$a_set["tref_id"]] ?? 0;
            if ((int) $tr !== 0) {
                $this->tpl->setVariable("THRESHOLD", (int) $tr);
            } else {
                $this->tpl->setVariable("THRESHOLD", "");
            }
            $this->tpl->parseCurrentBlock();
        }
    }
}
