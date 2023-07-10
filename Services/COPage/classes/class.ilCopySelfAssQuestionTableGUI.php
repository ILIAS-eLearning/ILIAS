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
 * Table to select self assessment questions for copying into learning resources
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCopySelfAssQuestionTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected int $pool_ref_id;
    protected int $pool_obj_id;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_pool_ref_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();

        $this->setId("cont_qpl");
        $this->pool_ref_id = $a_pool_ref_id;
        $this->pool_obj_id = ilObject::_lookupObjId($a_pool_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle(ilObject::_lookupTitle($this->pool_obj_id));

        $this->setFormName('sa_quest_browser');

        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("cont_question_type"), 'ttype', '');
        $this->addColumn($this->lng->txt("actions"), '', '');


        $this->setRowTemplate("tpl.copy_sa_quest_row.html", "Services/COPage");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->initFilter();

        $this->getQuestions();
    }

    public function getQuestions(): void
    {
        global $DIC;

        $access = $this->access;

        $all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
        $all_ids = array();
        foreach ($all_types as $k => $v) {
            $all_ids[] = $v["question_type_id"];
        }

        $questions = array();
        if ($access->checkAccess("read", "", $this->pool_ref_id)) {
            $questionList = new ilAssQuestionList(
                $DIC->database(),
                $DIC->language(),
                $DIC["component.repository"]
            );
            $questionList->setParentObjId($this->pool_obj_id);
            $questionList->load();

            $data = $questionList->getQuestionDataArray();

            $questions = array();
            foreach ($data as $d) {
                // list only self assessment question types
                if (in_array($d["question_type_fi"], $all_ids)) {
                    $questions[] = $d;
                }
            }
        }
        $this->setData($questions);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        // action: copy
        $ctrl->setParameter($this->parent_obj, "q_id", $a_set["question_id"]);
        $ctrl->setParameter($this->parent_obj, "subCmd", "copyQuestion");
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable(
            "HREF_CMD",
            $ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd)
        );
        $this->tpl->setVariable(
            "TXT_CMD",
            $lng->txt("cont_copy_question_into_page")
        );
        $this->tpl->parseCurrentBlock();
        $ctrl->setParameter($this->parent_obj, "subCmd", "listPoolQuestions");

        // properties
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable(
            "TYPE",
            assQuestion::_getQuestionTypeName($a_set["type_tag"])
        );
    }
}
