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
 * Class ilPCQuestion
 * Assessment Question of ilPageObject
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCQuestion extends ilPageContent
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    public php4DOMElement $q_node;
    protected static bool $initial_done = false;

    /**
     * Init page content component.
     */
    public function init(): void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->setType("pcqst");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->q_node = $a_node->first_child();		//... and this the Question
    }

    public function setQuestionReference(string $a_questionreference): void
    {
        if (is_object($this->q_node)) {
            $this->q_node->set_attribute("QRef", $a_questionreference);
        }
    }

    public function getQuestionReference(): ?string
    {
        if (is_object($this->q_node)) {
            return $this->q_node->get_attribute("QRef");
        }
        return null;
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
        $this->q_node = $this->dom->create_element("Question");
        $this->q_node = $this->node->append_child($this->q_node);
        $this->q_node->set_attribute("QRef", "");
    }

    /**
     * Copy question from pool into page
     */
    public function copyPoolQuestionIntoPage(
        string $a_q_id,
        string $a_hier_id
    ): void {
        $question = assQuestion::instantiateQuestion($a_q_id);
        $duplicate_id = $question->copyObject(0, $question->getTitle());
        $duplicate = assQuestion::instantiateQuestion($duplicate_id);
        $duplicate->setObjId(0);

        ilAssSelfAssessmentQuestionFormatter::prepareQuestionForLearningModule($duplicate);

        $this->q_node->set_attribute("QRef", "il__qst_" . $duplicate_id);
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_pcqst", "empty_question", "pc_qst");
    }

    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            "DELETE FROM page_question WHERE page_parent_type = %s " .
            " AND page_id = %s AND page_lang = %s",
            array("text", "integer", "text"),
            array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage())
        );

        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//Question');
        $q_ids = array();
        foreach ($nodes as $node) {
            $q_ref = $node->getAttribute("QRef");

            $inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
            if (!($inst_id > 0)) {
                $q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
                if ($q_id > 0) {
                    $q_ids[$q_id] = $q_id;
                }
            }
        }
        foreach ($q_ids as $qid) {
            $ilDB->manipulateF(
                "INSERT INTO page_question (page_parent_type, page_id, page_lang, question_id)" .
                " VALUES (%s,%s,%s,%s)",
                array("text", "integer", "text", "integer"),
                array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage(), $qid)
            );
        }
    }

    public static function beforePageDelete(
        ilPageObject $a_page
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            "DELETE FROM page_question WHERE page_parent_type = %s " .
            " AND page_id = %s AND page_lang = %s",
            array("text", "integer", "text"),
            array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage())
        );
    }

    public static function _getQuestionIdsForPage(
        string $a_parent_type,
        int $a_page_id,
        string $a_lang = "-"
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT * FROM page_question WHERE page_parent_type = %s " .
            " AND page_id = %s AND page_lang = %s",
            array("text", "integer", "text"),
            array($a_parent_type, $a_page_id, $a_lang)
        );
        $q_ids = array();
        while ($rec = $ilDB->fetchAssoc($res)) {
            $q_ids[] = $rec["question_id"];
        }

        return $q_ids;
    }

    public static function _getPageForQuestionId(
        int $a_q_id,
        string $a_parent_type = ""
    ): ?array {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM page_question " .
            " WHERE question_id = " . $ilDB->quote($a_q_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_parent_type == "" || $rec["page_parent_type"] == $a_parent_type) {
                return array("page_id" => $rec["page_id"], "parent_type" => $rec["page_parent_type"]);
            }
        }
        return null;
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $lng = $this->lng;

        $qhtml = "";

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            // #14154
            $q_ids = $this->getPage()->getQuestionIds();
            if (count($q_ids)) {
                foreach ($q_ids as $q_id) {
                    $q_gui = assQuestionGUI::_getQuestionGUI("", $q_id);
                    // object check due to #16557
                    if (is_object($q_gui->object) && !$q_gui->object->isComplete()) {
                        $a_output = str_replace(
                            "{{{{{Question;il__qst_" . $q_id . "}}}}}",
                            "<i>" . $lng->txt("cont_empty_question") . "</i>",
                            $a_output
                        );
                    }
                }

                // this exports the questions which is needed below
                $qhtml = $this->getQuestionJsOfPage($a_mode == "edit", $a_mode);

                $a_output = "<script>" . ilQuestionExporter::questionsJS($q_ids) . "</script>" . $a_output;
                if (!self::$initial_done) {
                    $a_output = "<script>var ScormApi=null; var questions = new Array();</script>" . $a_output;
                    self::$initial_done = true;
                }
            }
        } else {
            // set by T&A components
            $qhtml = $this->getPage()->getPageConfig()->getQuestionHTML();

            // address #19788
            if (!is_array($qhtml) || count($qhtml) == 0) {
                // #14154
                $q_ids = $this->getPage()->getQuestionIds();
                if (count($q_ids)) {
                    foreach ($q_ids as $k) {
                        $a_output = str_replace("{{{{{Question;il__qst_$k" . "}}}}}", " " . $lng->txt("copg_questions_not_supported_here"), $a_output);
                    }
                }
            }
        }

        if (is_array($qhtml)) {
            foreach ($qhtml as $k => $h) {
                $a_output = str_replace("{{{{{Question;il__qst_$k" . "}}}}}", " " . $h, $a_output);
            }
        }

        return $a_output;
    }

    /**
     * Reset initial state (for exports)
     */
    public static function resetInitialState(): void
    {
        self::$initial_done = false;
    }

    public function getJavascriptFiles(string $a_mode): array
    {
        $js_files = array();

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            $js_files[] = "./Modules/Scorm2004/scripts/questions/pure.js";
            $js_files[] = "./Modules/Scorm2004/scripts/questions/question_handling.js";
            $js_files[] = 'Modules/TestQuestionPool/js/ilAssMultipleChoice.js';
            $js_files[] = "Modules/TestQuestionPool/js/ilMatchingQuestion.js";

            foreach ($this->getPage()->getQuestionIds() as $qId) {
                $qstGui = assQuestionGUI::_getQuestionGUI('', $qId);
                $js_files = array_merge($js_files, $qstGui->getPresentationJavascripts());
            }
        }

        if (!$this->getPage()->getPageConfig()->getEnableSelfAssessmentScorm() && $a_mode != ilPageObjectGUI::PREVIEW
            && $a_mode != "offline") {
            $js_files[] = "./Services/COPage/js/ilCOPageQuestionHandler.js";
        }

        return $js_files;
    }

    public function getCssFiles(string $a_mode): array
    {
        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            return array("./Modules/Scorm2004/templates/default/question_handling.css",
                "Modules/TestQuestionPool/templates/default/test_javascript.css");
        }
        return array();
    }

    public function getOnloadCode(string $a_mode): array
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $code = array();

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            if (!$this->getPage()->getPageConfig()->getEnableSelfAssessmentScorm() && $a_mode != ilPageObjectGUI::PREVIEW
                && $a_mode != "offline") {
                $ilCtrl->setParameterByClass(strtolower(get_class($this->getPage())) . "gui", "page_id", $this->getPage()->getId());
                $url = $ilCtrl->getLinkTargetByClass(strtolower(get_class($this->getPage())) . "gui", "processAnswer", "", true, false);
                $code[] = "ilCOPageQuestionHandler.initCallback('" . $url . "');";
            }

            if ($this->getPage()->getPageConfig()->getDisableDefaultQuestionFeedback()) {
                $code[] = "ilias.questions.default_feedback = false;";
            }

            $code[] = self::getJSTextInitCode($this->getPage()->getPageConfig()->getLocalizationLanguage()) . ' il.COPagePres.updateQuestionOverviews();';
        }

        $get_stored_tries = $this->getPage()->getPageConfig()->getUseStoredQuestionTries();
        if ($get_stored_tries) {
            $q_ids = $this->getPage()->getQuestionIds();
            if (count($q_ids) > 0) {
                foreach ($q_ids as $q_id) {
                    $as = ilPageQuestionProcessor::getAnswerStatus($q_id, $ilUser->getId());
                    $code[] = "ilias.questions.initAnswer(" . $q_id . ", " . (int) $as["try"] . ", " . ($as["passed"] ? "true" : "null") . ");";
                }
            }
        }
        return $code;
    }

    /**
     * Get js txt init code
     */
    public static function getJSTextInitCode(string $a_lang): string
    {
        global $DIC;

        $lng = $DIC->language();
        $ilUser = $DIC->user();

        if ($a_lang == "") {
            $a_lang = $ilUser->getLanguage();
        }

        return
            '
			ilias.questions.txt.wrong_answers = "' . $lng->txtlng("content", "cont_wrong_answers", $a_lang) . '";
			ilias.questions.txt.wrong_answers_single = "' . $lng->txtlng("content", "cont_wrong_answers_single", $a_lang) . '";
			ilias.questions.txt.tries_remaining = "' . $lng->txtlng("content", "cont_tries_remaining", $a_lang) . '";
			ilias.questions.txt.please_try_again = "' . $lng->txtlng("content", "cont_please_try_again", $a_lang) . '";
			ilias.questions.txt.all_answers_correct = "' . $lng->txtlng("content", "cont_all_answers_correct", $a_lang) . '";
			ilias.questions.txt.enough_answers_correct = "' . $lng->txtlng("content", "cont_enough_answers_correct", $a_lang) . '";
			ilias.questions.txt.nr_of_tries_exceeded = "' . $lng->txtlng("content", "cont_nr_of_tries_exceeded", $a_lang) . '";
			ilias.questions.txt.correct_answers_shown = "' . $lng->txtlng("content", "cont_correct_answers_shown", $a_lang) . '";
			ilias.questions.txt.correct_answers_also = "' . $lng->txtlng("content", "cont_correct_answers_also", $a_lang) . '";
			ilias.questions.txt.correct_answer_also = "' . $lng->txtlng("content", "cont_correct_answer_also", $a_lang) . '";
			ilias.questions.txt.ov_all_correct = "' . $lng->txtlng("content", "cont_ov_all_correct", $a_lang) . '";
			ilias.questions.txt.ov_some_correct = "' . $lng->txtlng("content", "cont_ov_some_correct", $a_lang) . '";
			ilias.questions.txt.ov_wrong_answered = "' . $lng->txtlng("content", "cont_ov_wrong_answered", $a_lang) . '";
			ilias.questions.txt.please_select = "' . $lng->txtlng("content", "cont_please_select", $a_lang) . '";
			ilias.questions.txt.ov_preview = "' . $lng->txtlng("content", "cont_ov_preview", $a_lang) . '";
			ilias.questions.txt.submit_answers = "' . $lng->txtlng("content", "cont_submit_answers", $a_lang) . '";
			ilias.questions.refresh_lang();
			';
    }

    public function getQuestionJsOfPage(
        bool $a_no_interaction,
        string $a_mode
    ): array {
        $q_ids = $this->getPage()->getQuestionIds();
        $js = array();
        if (count($q_ids) > 0) {
            foreach ($q_ids as $q_id) {
                $q_exporter = new ilQuestionExporter($a_no_interaction);
                $image_path = "";
                if ($a_mode == "offline") {
                    if ($this->getPage()->getParentType() == "sahs") {
                        $image_path = "./objects/";
                    }
                    if ($this->getPage()->getParentType() == "lm") {
                        $image_path = "./assessment/0/" . $q_id . "/images/";
                    }
                }
                $js[$q_id] = $q_exporter->exportQuestion($q_id, $image_path, $a_mode);
            }
        }
        return $js;
    }
}
