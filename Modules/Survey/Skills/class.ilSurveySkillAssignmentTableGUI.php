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
 * TableGUI class for survey questions to skill assignment
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkillAssignmentTableGUI extends ilTable2GUI
{
    protected SkillTreeService $skill_tree_service;
    protected ilGlobalSkillTree $skill_tree;
    protected ilObjSurvey $object;
    protected ilAccessHandler $access;
    protected ilSurveySkill $skill_survey;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjSurvey $a_survey
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        $this->object = $a_survey;
        $this->skill_survey = new ilSurveySkill($a_survey);

        $this->skill_tree_service = $DIC->skills()->tree();
        $this->skill_tree = $this->skill_tree_service->getGlobalSkillTree();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getQuestions();

        $this->addColumn($this->lng->txt("question"));
        $this->addColumn($this->lng->txt("survey_skill"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.svy_skill_ass_row.html", "Modules/Survey");
    }

    public function getQuestions(): void
    {
        $survey_questions = $this->object->getSurveyQuestions();

        $table_data = [];

        if (count($survey_questions) > 0) {
            $table_data = array();
            $last_questionblock_id = $position = $block_position = 0;
            foreach ($survey_questions as $question_id => $data) {
                // it is only possible to assign  to a subset
                // of question types: single choice(2)
                $supported = false;
                if ((int) $data["questiontype_fi"] === 2) {
                    $supported = true;
                }

                $id = $data["question_id"];

                $table_data[$id] = array("id" => $id,
                    "type" => "question",
                    "supported" => $supported,
                    "heading" => $data["heading"],
                    "title" => $data["title"],
                    "description" => $data["description"],
                    "author" => $data["author"],
                    "obligatory" => (bool) $data["obligatory"]);
            }
        }
        $this->setData($table_data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "q_id", $a_set["id"]);

        if ($a_set["supported"]) {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget(
                    $this->parent_obj,
                    "assignSkillToQuestion"
                )
            );
            $this->tpl->setVariable("TXT_CMD", $lng->txt("survey_assign_competence"));
            $this->tpl->parseCurrentBlock();

            if ($s = $this->skill_survey->getSkillForQuestion($a_set["id"])) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget(
                        $this->parent_obj,
                        "removeSkillFromQuestion"
                    )
                );
                $this->tpl->setVariable("TXT_CMD", $lng->txt("survey_remove_competence"));
                $this->tpl->parseCurrentBlock();

                $this->tpl->setVariable(
                    "COMPETENCE",
                    ilBasicSkill::_lookupTitle($s["base_skill_id"], $s["tref_id"])
                );

                //var_dump($a_set);
                $path = $this->skill_tree->getSkillTreePath($s["base_skill_id"], $s["tref_id"]);
                $path_nodes = array();
                foreach ($path as $p) {
                    if ($p["child"] > 1 && $p["skill_id"] != $s["base_skill_id"]) {
                        $path_nodes[] = ilBasicSkill::_lookupTitle($p["skill_id"], $p["tref_id"]);
                    }
                }
                $this->tpl->setVariable("PATH", implode(" > ", $path_nodes));
                $this->tpl->setVariable("COMP_ID", "comp_" . $a_set["id"]);
            }
        } else {
            $this->tpl->setVariable("NOT_SUPPORTED", $lng->txt("svy_skl_comp_assignm_not_supported"));
        }

        $this->tpl->setVariable("QUESTION_TITLE", $a_set["title"]);

        $ilCtrl->setParameter($this->parent_obj, "q_id", "");
    }
}
