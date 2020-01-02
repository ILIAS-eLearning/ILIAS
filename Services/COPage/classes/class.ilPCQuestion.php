<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCQuestion
*
* Assessment Question of ilPageObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCQuestion extends ilPageContent
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    public $dom;
    public $q_node;			// node of Paragraph element
    
    protected static $initial_done; // [bool]
    
    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->setType("pcqst");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->q_node = $a_node->first_child();		//... and this the Question
    }

    /**
    * Set Question Reference.
    *
    * @param	string	$a_questionreference	Question Reference
    */
    public function setQuestionReference($a_questionreference)
    {
        if (is_object($this->q_node)) {
            $this->q_node->set_attribute("QRef", $a_questionreference);
        }
    }

    /**
    * Get Question Reference.
    *
    * @return	string	Question Reference
    */
    public function getQuestionReference()
    {
        if (is_object($this->q_node)) {
            return $this->q_node->get_attribute("QRef", $a_questionreference);
        }
        return false;
    }

    /**
    * Create Question Element
    */
    public function create(&$a_pg_obj, $a_hier_id)
    {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
        $this->q_node = $this->dom->create_element("Question");
        $this->q_node = $this->node->append_child($this->q_node);
        $this->q_node->set_attribute("QRef", "");
    }
    
    /**
     * Copy question from pool into page
     *
     * @param
     * @return
     */
    public function copyPoolQuestionIntoPage($a_q_id, $a_hier_id)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $question = assQuestion::_instanciateQuestion($a_q_id);
        $duplicate_id = $question->copyObject(0, $question->getTitle());
        $duplicate = assQuestion::_instanciateQuestion($duplicate_id);
        $duplicate->setObjId(0);
        
        /* PATCH-BEGIN: moved cleanup code to central place ilAssSelfAssessmentQuestionFormatter */
        /*
        // we remove everything not supported by the non-tiny self
        // assessment question editor
        $q = $duplicate->getQuestion();

        // we try to save all latex tags
        $try = true;
        $ls = '<span class="latex">';
        $le = '</span>';
        while ($try)
        {
            // search position of start tag
            $pos1 = strpos($q, $ls);
            if (is_int($pos1))
            {
                $pos2 = strpos($q, $le, $pos1);
                if (is_int($pos2))
                {
                    // both found: replace end tag
                    $q = substr($q, 0, $pos2)."[/tex]".substr($q, $pos2+7);
                    $q = substr($q, 0, $pos1)."[tex]".substr($q, $pos1+20);
                }
                else
                {
                    $try = false;
                }
            }
            else
            {
                $try = false;
            }
        }

        $tags = assQuestionGUI::getSelfAssessmentTags();
        $tstr = "";
        foreach ($tags as $t)
        {
            $tstr.="<".$t.">";
        }
        $q = ilUtil::secureString($q, true, $tstr);
        // self assessment uses nl2br, not p
        $duplicate->setQuestion($q);

        $duplicate->saveQuestionDataToDb();
        */

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
        ilAssSelfAssessmentQuestionFormatter::prepareQuestionForLearningModule($duplicate);
        
        /* PATCH-END: moved cleanup code to central place ilAssSelfAssessmentQuestionFormatter */
        
        $this->q_node->set_attribute("QRef", "il__qst_" . $duplicate_id);
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_pcqst", "empty_question", "pc_qst");
    }

    /**
     * After page has been updated (or created)
     *
     * @param object page object
     * @param DOMDocument $a_domdoc dom document
     * @param string xml
     * @param bool true on creation, otherwise false
     */
    public static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        include_once("./Services/Link/classes/class.ilInternalLink.php");
        
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
    
    /**
     * Before page is being deleted
     *
     * @param object page object
     */
    public static function beforePageDelete($a_page)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulateF(
            "DELETE FROM page_question WHERE page_parent_type = %s " .
            " AND page_id = %s AND page_lang = %s",
            array("text", "integer", "text"),
            array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage())
        );
    }
    
    /**
     * Get all questions of a page
     */
    public static function _getQuestionIdsForPage($a_parent_type, $a_page_id, $a_lang = "-")
    {
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

    /**
     * Get page for question id
     *
     * @param
     * @return array
     */
    public static function _getPageForQuestionId($a_q_id, $a_parent_type = "")
    {
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
        return false;
    }

    /**
     * Modify page content after xsl
     *
     * @param string $a_output
     * @return string
     */
    public function modifyPageContentPostXsl($a_output, $a_mode)
    {
        $lng = $this->lng;

        $qhtml = "";

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            // #14154
            $q_ids = $this->getPage()->getQuestionIds();
            if (sizeof($q_ids)) {
                include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
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
                $qhtml = $this->getQuestionJsOfPage(($a_mode == "edit") ? true : false, $a_mode);
                                                            
                require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
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
                if (sizeof($q_ids)) {
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
    public static function resetInitialState()
    {
        self::$initial_done = false;
    }

    /**
     * Get Javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        $js_files = array();

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            $js_files[] = "./Modules/Scorm2004/scripts/questions/pure.js";
            $js_files[] = "./Modules/Scorm2004/scripts/questions/question_handling.js";
            $js_files[] = "Modules/TestQuestionPool/js/ilMatchingQuestion.js";
            $js_files[] = "Modules/TestQuestionPool/js/ilMultipleChoiceQuestion.js";
            
            global $DIC;
            if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
                $js_files[] = 'libs/bower/bower_components/jqueryui-touch-punch/jquery.ui.touch-punch.min.js';
            }
        }

        if (!$this->getPage()->getPageConfig()->getEnableSelfAssessmentScorm() && $a_mode != IL_PAGE_PREVIEW
            && $a_mode != "offline") {
            $js_files[] = "./Services/COPage/js/ilCOPageQuestionHandler.js";
        }

        return $js_files;
    }

    /**
     * Get css files
     */
    public function getCssFiles($a_mode)
    {
        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            return array("./Modules/Scorm2004/templates/default/question_handling.css",
                "Modules/TestQuestionPool/templates/default/test_javascript.css");
        }
        return array();
    }

    /**
     * Get on load code
     */
    public function getOnloadCode($a_mode)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $code = array();

        if ($this->getPage()->getPageConfig()->getEnableSelfAssessment()) {
            if (!$this->getPage()->getPageConfig()->getEnableSelfAssessmentScorm() && $a_mode != IL_PAGE_PREVIEW
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
                    include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
                    $as = ilPageQuestionProcessor::getAnswerStatus($q_id, $ilUser->getId());
                    $code[] = "ilias.questions.initAnswer(" . $q_id . ", " . (int) $as["try"] . ", " . ($as["passed"] ? "true" : "null") . ");";
                }
            }
        }
        return $code;
    }

    /**
     * Get js txt init code
     *
     * @param
     * @return
     */
    public static function getJSTextInitCode($a_lang)
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

    /**
     * Get question js
     */
    public function getQuestionJsOfPage($a_no_interaction, $a_mode)
    {
        require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
        $q_ids = $this->getPage()->getQuestionIds();
        $js = array();
        if (count($q_ids) > 0) {
            foreach ($q_ids as $q_id) {
                $q_exporter = new ilQuestionExporter($a_no_interaction);
                $image_path = null;
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
