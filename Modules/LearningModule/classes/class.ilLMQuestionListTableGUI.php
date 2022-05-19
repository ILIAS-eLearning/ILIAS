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
 * Question list table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMQuestionListTableGUI extends ilTable2GUI
{
    protected ilObjLearningModule $lm;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();

        $this->lm = $a_lm;

        $this->setId("lm_qst" . $this->lm->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("pg"));
        $this->addColumn($this->lng->txt("question"));
        $this->addColumn($this->lng->txt("cont_users_answered"));
        $this->addColumn($this->lng->txt("cont_correct_after_first"));
        $this->addColumn($this->lng->txt("cont_second"));
        $this->addColumn($this->lng->txt("cont_third_and_more"));
        $this->addColumn($this->lng->txt("cont_never"));

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, $this->parent_cmd));
        $this->setRowTemplate("tpl.lm_question_row.html", "Modules/LearningModule");
        $this->setEnableTitle(true);

        $this->getItems();
    }

    public function getItems() : void
    {
        $this->determineOffsetAndOrder();

        $questions = ilLMPageObject::queryQuestionsOfLearningModule(
            $this->lm->getId(),
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit())
        );

        if (count($questions["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $questions = ilLMPageObject::queryQuestionsOfLearningModule(
                $this->lm->getId(),
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit())
            );
        }

        $this->setMaxCount($questions["cnt"]);
        $this->setData($questions["set"]);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable(
            "PAGE_TITLE",
            ilLMObject::_lookupTitle($a_set["page_id"])
        );
        $this->tpl->setVariable(
            "QUESTION",
            assQuestion::_getQuestionText($a_set["question_id"])
        );

        $stats = ilPageQuestionProcessor::getQuestionStatistics($a_set["question_id"]);

        $this->tpl->setVariable("VAL_ANSWERED", (int) $stats["all"]);
        if ($stats["all"] == 0) {
            $this->tpl->setVariable("VAL_CORRECT_FIRST", 0);
            $this->tpl->setVariable("VAL_CORRECT_SECOND", 0);
            $this->tpl->setVariable("VAL_CORRECT_THIRD_OR_MORE", 0);
            $this->tpl->setVariable("VAL_NEVER", 0);
        } else {
            $this->tpl->setVariable("VAL_CORRECT_FIRST", $stats["first"] .
                " (" . (100 / $stats["all"] * $stats["first"]) . " %)");
            $this->tpl->setVariable("VAL_CORRECT_SECOND", $stats["second"] .
                " (" . (100 / $stats["all"] * $stats["second"]) . " %)");
            $this->tpl->setVariable("VAL_CORRECT_THIRD_AND_MORE", $stats["third_or_more"] .
                " (" . (100 / $stats["all"] * $stats["third_or_more"]) . " %)");
            $nev = $stats["all"] - $stats["first"] - $stats["second"] - $stats["third_or_more"];
            $this->tpl->setVariable("VAL_NEVER", $nev .
                " (" . (100 / $stats["all"] * $nev) . " %)");
        }
    }
}
