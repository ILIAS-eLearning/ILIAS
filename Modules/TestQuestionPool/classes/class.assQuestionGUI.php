<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once 'Modules/Test/classes/class.ilTestExpressPage.php';
require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
require_once 'Modules/Test/classes/class.ilTestExpressPage.php';
require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';

/**
* Basic GUI class for assessment questions
*
* The assQuestionGUI class encapsulates basic GUI functions for assessment questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
* @ingroup		ModulesTestQuestionPool
*/
abstract class assQuestionGUI
{
    const FORM_MODE_EDIT = 'edit';
    const FORM_MODE_ADJUST = 'adjust';
    
    const FORM_ENCODING_URLENCODE = 'application/x-www-form-urlencoded';
    const FORM_ENCODING_MULTIPART = 'multipart/form-data';
    
    const SESSION_PREVIEW_DATA_BASE_INDEX = 'ilAssQuestionPreviewAnswers';
    
    /**
    * Question object
    *
    * A reference to the matching question object
    *
    * @var assQuestion
    */
    public $object;

    /** @var ilGlobalTemplateInterface */
    public $tpl;
    public $lng;
    public $error;
    public $errormessage;
    
    /**
     * sequence number in test
     */
    public $sequence_no;
    /**
     * question count in test
     */
    public $question_count;
    
    private $taxonomyIds = array();
    
    private $targetGuiClass = null;

    private $questionActionCmd = 'handleQuestionAction';

    /**
     * @var ilQuestionHeaderBlockBuilder
     */
    private $questionHeaderBlockBuilder;

    /**
     * @var ilTestQuestionNavigationGUI
     */
    private $navigationGUI;

    const PRESENTATION_CONTEXT_TEST = 'pContextTest';
    const PRESENTATION_CONTEXT_RESULTS = 'pContextResults';

    /**
     * @var string
     */
    private $presentationContext = null;

    const RENDER_PURPOSE_PLAYBACK = 'renderPurposePlayback';
    const RENDER_PURPOSE_DEMOPLAY = 'renderPurposeDemoplay';
    const RENDER_PURPOSE_PREVIEW = 'renderPurposePreview';
    const RENDER_PURPOSE_PRINT_PDF = 'renderPurposePrintPdf';
    const RENDER_PURPOSE_INPUT_VALUE = 'renderPurposeInputValue';
    
    /**
     * @var string
     */
    private $renderPurpose = self::RENDER_PURPOSE_PLAYBACK;

    const EDIT_CONTEXT_AUTHORING = 'authoring';
    const EDIT_CONTEXT_ADJUSTMENT = 'adjustment';
    
    /**
     * @var string
     */
    private $editContext = self::EDIT_CONTEXT_AUTHORING;
    
    // hey: prevPassSolutions - flag to indicate that a previous answer is shown
    /**
     * @var bool
     */
    private $previousSolutionPrefilled = false;
    // hey.
    
    /**
     * @var \ilPropertyFormGUI
     */
    protected $editForm;

    /**
     * Prefer the intermediate solution for solution output
     * @var bool
     */
    protected $use_intermediate_solution = false;

    /**
    * assQuestionGUI constructor
    */
    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = &$lng;
        $this->tpl = &$tpl;
        $this->ctrl = &$ilCtrl;
        $this->ctrl->saveParameter($this, "q_id");
        $this->ctrl->saveParameter($this, "prev_qid");
        $this->ctrl->saveParameter($this, "calling_test");
        $this->ctrl->saveParameter($this, "calling_consumer");
        $this->ctrl->saveParameter($this, "consumer_context");
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'test_express_mode');
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'calling_consumer');
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'consumer_context');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'test_express_mode');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'calling_consumer');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'consumer_context');

        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $this->errormessage = $this->lng->txt("fill_out_all_required_fields");
        
        $this->selfassessmenteditingmode = false;
        $this->new_id_listeners = array();
        $this->new_id_listener_cnt = 0;
        
        $this->navigationGUI = null;
    }
    
    /**
     * this method can be overwritten per question type
     *
     * @return bool
     */
    public function hasInlineFeedback()
    {
        return false;
    }
    
    public function addHeaderAction()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
/*
        $DIC->ui()->mainTemplate()->setVariable(
            "HEAD_ACTION",
            $this->getHeaderAction()
        );
        
        $notesUrl = $this->ctrl->getLinkTargetByClass(
            array("ilcommonactiondispatchergui", "ilnotegui"),
            "",
            "",
            true,
            false
        );
        
        ilNoteGUI::initJavascript($notesUrl, IL_NOTE_PUBLIC, $DIC->ui()->mainTemplate());
        
        $redrawActionsUrl = $DIC->ctrl()->getLinkTarget($this, 'redrawHeaderAction', '', true);
        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Object.setRedrawAHUrl('$redrawActionsUrl');");
*/
    }
    
    public function redrawHeaderAction()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        echo $this->getHeaderAction() . $DIC->ui()->mainTemplate()->getOnLoadCodeForAsynch();
        exit;
    }
    
    public function getHeaderAction()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        /* @var ilObjectDataCache $ilObjDataCache */
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $parentObjType = $ilObjDataCache->lookupType($this->object->getObjId());
        
        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $DIC->access(),
            $parentObjType,
            $_GET["ref_id"],
            $this->object->getObjId()
        );
        
        $dispatcher->setSubObject("quest", $this->object->getId());
        
        $ha = $dispatcher->initHeaderAction();
        $ha->enableComments(true, false);
        
        return $ha->getHeaderAction($DIC->ui()->mainTemplate());
    }
    
    public function getNotesHTML()
    {
        $notesGUI = new ilNoteGUI($this->object->getObjId(), $this->object->getId(), 'quest');
        $notesGUI->enablePublicNotes(true);
        $notesGUI->enablePublicNotesDeletion(true);
        
        return $notesGUI->getNotesHTML();
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        $ilHelp->setScreenIdComponent('qpl');

        $cmd = $this->ctrl->getCmd("editQuestion");
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $form = $this->buildEditForm();

                require_once 'Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $form_prop_dispatch->setItem($form->getItemByPostVar(ilUtil::stripSlashes($_GET['postvar'])));
                return $this->ctrl->forwardCommand($form_prop_dispatch);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }
        return $ret;
    }

    public function getCommand($cmd)
    {
        return $cmd;
    }

    /**
    * needed for page editor compliance
    */
    public function getType()
    {
        return $this->getQuestionType();
    }

    /**
     * @return string
     */
    public function getPresentationContext()
    {
        return $this->presentationContext;
    }

    /**
     * @param string $presentationContext
     */
    public function setPresentationContext($presentationContext)
    {
        $this->presentationContext = $presentationContext;
    }
    
    public function isTestPresentationContext()
    {
        return $this->getPresentationContext() == self::PRESENTATION_CONTEXT_TEST;
    }

    // hey: previousPassSolutions - setter/getter for Previous Solution Prefilled flag
    /**
     * @return boolean
     */
    public function isPreviousSolutionPrefilled()
    {
        return $this->previousSolutionPrefilled;
    }
    
    /**
     * @param boolean $previousSolutionPrefilled
     */
    public function setPreviousSolutionPrefilled($previousSolutionPrefilled)
    {
        $this->previousSolutionPrefilled = $previousSolutionPrefilled;
    }
    // hey.

    /**
     * @return string
     */
    public function getRenderPurpose()
    {
        return $this->renderPurpose;
    }

    /**
     * @param string $renderPurpose
     */
    public function setRenderPurpose($renderPurpose)
    {
        $this->renderPurpose = $renderPurpose;
    }
    
    public function isRenderPurposePrintPdf()
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PRINT_PDF;
    }
    
    public function isRenderPurposePreview()
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PREVIEW;
    }
    
    public function isRenderPurposeInputValue()
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_INPUT_VALUE;
    }
    
    public function isRenderPurposePlayback()
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PLAYBACK;
    }
    
    public function isRenderPurposeDemoplay()
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_DEMOPLAY;
    }
    
    public function renderPurposeSupportsFormHtml()
    {
        if ($this->isRenderPurposePrintPdf()) {
            return false;
        }
        
        if ($this->isRenderPurposeInputValue()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return string
     */
    public function getEditContext()
    {
        return $this->editContext;
    }
    
    /**
     * @param string $editContext
     */
    public function setEditContext($editContext)
    {
        $this->editContext = $editContext;
    }
    
    /**
     * @param bool $isAuthoringEditContext
     */
    public function isAuthoringEditContext()
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_AUTHORING;
    }
    
    /**
     * @param bool $isAdjustmentEditContext
     */
    public function isAdjustmentEditContext()
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_ADJUSTMENT;
    }
    
    public function setAdjustmentEditContext()
    {
        return $this->setEditContext(self::EDIT_CONTEXT_ADJUSTMENT);
    }
    
    /**
     * @return ilTestQuestionNavigationGUI
     */
    public function getNavigationGUI()
    {
        return $this->navigationGUI;
    }

    /**
     * @param ilTestQuestionNavigationGUI $navigationGUI
     */
    public function setNavigationGUI($navigationGUI)
    {
        $this->navigationGUI = $navigationGUI;
    }
    
    public function setTaxonomyIds($taxonomyIds)
    {
        $this->taxonomyIds = $taxonomyIds;
    }
    
    public function getTaxonomyIds()
    {
        return $this->taxonomyIds;
    }
    
    public function setTargetGui($linkTargetGui)
    {
        $this->setTargetGuiClass(get_class($linkTargetGui));
    }
    
    public function setTargetGuiClass($targetGuiClass)
    {
        $this->targetGuiClass = $targetGuiClass;
    }
    
    public function getTargetGuiClass()
    {
        return $this->targetGuiClass;
    }

    /**
     * @param \ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder
     */
    public function setQuestionHeaderBlockBuilder($questionHeaderBlockBuilder)
    {
        $this->questionHeaderBlockBuilder = $questionHeaderBlockBuilder;
    }

    // fau: testNav - get the question header block bulder (for tweaking)
    /**
     * @return \ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder
     */
    public function getQuestionHeaderBlockBuilder()
    {
        return $this->questionHeaderBlockBuilder;
    }
    // fau.

    public function setQuestionActionCmd($questionActionCmd)
    {
        $this->questionActionCmd = $questionActionCmd;

        if (is_object($this->object)) {
            $this->object->questionActionCmd = $questionActionCmd;
        }
    }

    public function getQuestionActionCmd()
    {
        return $this->questionActionCmd;
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * @return integer A positive value, if one of the required fields wasn't set, else 0
     */
    protected function writePostData($always = false)
    {
    }

    /**
    * output assessment
    */
    public function assessment()
    {
        /**
         * @var $tpl ilGlobalTemplate
         */
        global $DIC;
        $tpl = $DIC['tpl'];

        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionCumulatedStatisticsTableGUI.php';
        $stats_table = new ilQuestionCumulatedStatisticsTableGUI($this, 'assessment', '', $this->object);

        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionUsagesTableGUI.php';
        $usage_table = new ilQuestionUsagesTableGUI($this, 'assessment', '', $this->object);

        $tpl->setContent(implode('<br />', array(
            $stats_table->getHTML(),
            $usage_table->getHTML()
        )));
    }

    /**
     * Creates a question gui representation and returns the alias to the question gui
     * note: please do not use $this inside this method to allow static calls
     *
     * @param string $question_type The question type as it is used in the language database
     * @param integer $question_id The database ID of an existing question to load it into assQuestionGUI
     *
     * @return assQuestionGUI The alias to the question object
     */
    public static function _getQuestionGUI($question_type, $question_id = -1)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        
        if ((!$question_type) and ($question_id > 0)) {
            $question_type = assQuestion::getQuestionTypeFromDb($question_id);
        }
        
        if (strlen($question_type) == 0) {
            return null;
        }

        assQuestion::_includeClass($question_type, 1);

        $question_type_gui = assQuestion::getGuiClassNameByQuestionType($question_type);
        $question = new $question_type_gui();

        $feedbackObjectClassname = assQuestion::getFeedbackClassNameByQuestionType($question_type);
        $question->object->feedbackOBJ = new $feedbackObjectClassname($question->object, $ilCtrl, $ilDB, $lng);
        
        if ($question_id > 0) {
            $question->object->loadFromDb($question_id);
        }
        
        return $question;
    }

    /**
     * @deprecated
     */
    public static function _getGUIClassNameForId($a_q_id)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $q_type = assQuestion::getQuestionTypeFromDb($a_q_id);
        $class_name = assQuestionGUI::_getClassNameForQType($q_type);
        return $class_name;
    }

    /**
     * @deprecated
     */
    public static function _getClassNameForQType($q_type)
    {
        return $q_type . "GUI";
    }

    /**
    * Creates a question gui representation
    *
    * Creates a question gui representation and returns the alias to the question gui
    *
    * @param string $question_type The question type as it is used in the language database
    * @param integer $question_id The database ID of an existing question to load it into assQuestionGUI
    * @return object The alias to the question object
    * @access public
     *
     * @deprecated: WTF is this? GUIobject::question should be a GUIobject !? WTF is a question alias !?
    */
    public function &createQuestionGUI($question_type, $question_id = -1)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
        $this->question = &assQuestionGUI::_getQuestionGUI($question_type, $question_id);
    }
    
    public function populateJavascriptFilesRequiredForWorkForm(ilGlobalTemplateInterface $tpl)
    {
        foreach ($this->getPresentationJavascripts() as $jsFile) {
            $tpl->addJavaScript($jsFile);
        }
    }
    
    public function getPresentationJavascripts()
    {
        return array();
    }

    /**
    * get question template
    */
    public function getQuestionTemplate()
    {
        // @todo Björn: Maybe this has to be changed for PHP 7/ILIAS 5.2.x (ilObjTestGUI::executeCommand, switch -> default case -> $this->prepareOutput(); already added a template to the CONTENT variable wrapped in a block named content)
        if (!$this->tpl->blockExists('content')) {
            $this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", "Modules/TestQuestionPool");
        }
        // @todo Björn: Maybe this has to be changed for PHP 7/ILIAS 5.2.x (ilObjTestGUI::executeCommand, switch -> default case -> $this->prepareOutput(); already added a template to the STATUSLINE variable wrapped in a block named statusline)
        if (!$this->tpl->blockExists('statusline')) {
            $this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
        }
        // @todo Björn: Maybe this has to be changed for PHP 7/ILIAS 5.2.x because ass[XYZ]QuestionGUI::editQuestion is called multiple times
        if (!$this->tpl->blockExists('adm_content')) {
            $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_question.html", "Modules/TestQuestionPool");
        }
    }
    
    /**
     * @param $form
     */
    protected function renderEditForm($form)
    {
        $this->getQuestionTemplate();
        $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
    }

    /**
    * Returns the ILIAS Page around a question
    *
    * @return string The ILIAS page content
    * @access public
    */
    public function getILIASPage($html = "")
    {
        include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
        $page_gui = new ilAssQuestionPageGUI($this->object->getId());
        $page_gui->setQuestionHTML(array($this->object->getId() => $html));
        $presentation = $page_gui->presentation();
        $presentation = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $presentation);
        return $presentation;
    }

    /**
    * output question page
    */
    public function outQuestionPage($a_temp_var, $a_postponed = false, $active_id = "", $html = "", $inlineFeedbackEnabled = false)
    {
        // hey: prevPassSolutions - add the "use previous answer"
        // hey: prevPassSolutions - refactored identifiers
        if ($this->object->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            // hey
            ilUtil::sendInfo($this->getPreviousSolutionProvidedMessage());
            $html .= $this->getPreviousSolutionConfirmationCheckboxHtml();
        } elseif // (!) --> if ($this->object->getTestQuestionConfig()->isUnchangedAnswerPossible())
        // hey.
// fau: testNav - add the "use unchanged answer checkbox"
        // hey: prevPassSolutions - refactored identifiers
        ($this->object->getTestPresentationConfig()->isUnchangedAnswerPossible()) {
            // hey.
            $html .= $this->getUseUnchangedAnswerCheckboxHtml();
        }
        // fau.

        $this->lng->loadLanguageModule("content");

        // fau: testNav - add question buttons below question, add actions menu
        include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
        $page_gui = new ilAssQuestionPageGUI($this->object->getId());
        $page_gui->setOutputMode("presentation");
        $page_gui->setTemplateTargetVar($a_temp_var);

        if ($this->getNavigationGUI()) {
            $html .= $this->getNavigationGUI()->getHTML();
            $page_gui->setQuestionActionsHTML($this->getNavigationGUI()->getActionsHTML());
        }
        // fau.

        if (strlen($html)) {
            if ($inlineFeedbackEnabled && $this->hasInlineFeedback()) {
                $html = $this->buildFocusAnchorHtml() . $html;
            }
            
            $page_gui->setQuestionHTML(array($this->object->getId() => $html));
        }

        // fau: testNav - fill the header with subtitle blocks for question info an actions
        $page_gui->setPresentationTitle($this->questionHeaderBlockBuilder->getPresentationTitle());
        $page_gui->setQuestionInfoHTML($this->questionHeaderBlockBuilder->getQuestionInfoHTML());
        // fau.

        return $page_gui->presentation();
    }
    
    // fau: testNav - get the html of the "use unchanged answer checkbox"
    protected function getUseUnchangedAnswerCheckboxHtml()
    {
        // hey: prevPassSolutions - use abstracted template to share with other purposes of this kind
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->object->getTestPresentationConfig()->getUseUnchangedAnswerLabel());
        // hey.
        return $tpl->get();
    }
    // fau.

    // hey: prevPassSolutions - build prev solution message / build "use previous answer checkbox" html
    protected function getPreviousSolutionProvidedMessage()
    {
        return $this->lng->txt('use_previous_solution_advice');
    }
    
    protected function getPreviousSolutionConfirmationCheckboxHtml()
    {
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        // hey: prevPassSolutions - use abtract template
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->lng->txt('use_previous_solution'));
        // hey.
        return $tpl->get();
    }
    // hey.
    
    /**
    * cancel action
    */
    public function cancel()
    {
        if ($_GET["calling_test"]) {
            $_GET["ref_id"] = $_GET["calling_test"];
            ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["calling_test"]);
        } elseif ($_GET["test_ref_id"]) {
            $_GET["ref_id"] = $_GET["test_ref_id"];
            ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["test_ref_id"]);
        } else {
            if ($_GET["q_id"] > 0) {
                $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
                $this->ctrl->redirectByClass("ilAssQuestionPageGUI", "edit");
            } else {
                $this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
            }
        }
    }

    /**
     * @param string $return_to
     * @param string $return_to_feedback  ilAssQuestionFeedbackEditingGUI
     */
    public function originalSyncForm($return_to = "", $return_to_feedback = '')
    {
        if (strlen($return_to)) {
            $this->ctrl->setParameter($this, "return_to", $return_to);
        } elseif ($_REQUEST['return_to']) {
            $this->ctrl->setParameter($this, "return_to", $_REQUEST['return_to']);
        }
        if (strlen($return_to_feedback)) {
            $this->ctrl->setParameter($this, 'return_to_fb', 'true');
        }

        $this->ctrl->saveParameter($this, 'test_express_mode');
        
        $template = new ilTemplate("tpl.il_as_qpl_sync_original.html", true, true, "Modules/TestQuestionPool");
        $template->setVariable("BUTTON_YES", $this->lng->txt("yes"));
        $template->setVariable("BUTTON_NO", $this->lng->txt("no"));
        $template->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
        $template->setVariable("TEXT_SYNC", $this->lng->txt("confirm_sync_questions"));
        $this->tpl->setVariable("ADM_CONTENT", $template->get());
    }
    
    public function sync()
    {
        $original_id = $this->object->original_id;
        if ($original_id) {
            $this->object->syncWithOriginal();
        }
        if (strlen($_GET["return_to"])) {
            $this->ctrl->redirect($this, $_GET["return_to"]);
        }
        if (strlen($_REQUEST["return_to_fb"])) {
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', 'showFeedbackForm');
        } else {
            if (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer']) {
                $ref_id = (int) $_GET['calling_consumer'];
                $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
                if ($consumer instanceof ilQuestionEditingFormConsumer) {
                    ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($_GET['consumer_context']));
                }
                require_once 'Services/Link/classes/class.ilLink.php';
                ilUtil::redirect(ilLink::_getLink($ref_id));
            }
            $_GET["ref_id"] = $_GET["calling_test"];
            
            if ($_REQUEST['test_express_mode']) {
                ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
            } else {
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["calling_test"]);
            }
        }
    }

    public function cancelSync()
    {
        if (strlen($_GET["return_to"])) {
            $this->ctrl->redirect($this, $_GET["return_to"]);
        }
        if (strlen($_REQUEST['return_to_fb'])) {
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', 'showFeedbackForm');
        } else {
            if (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer']) {
                $ref_id = (int) $_GET['calling_consumer'];
                $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
                if ($consumer instanceof ilQuestionEditingFormConsumer) {
                    ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($_GET['consumer_context']));
                }
                require_once 'Services/Link/classes/class.ilLink.php';
                ilUtil::redirect(ilLink::_getLink($ref_id));
            }
            $_GET["ref_id"] = $_GET["calling_test"];

            if ($_REQUEST['test_express_mode']) {
                ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
            } else {
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["calling_test"]);
            }
        }
    }
    
    /**
    * save question
    */
    public function saveEdit()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $result = $this->writePostData();
        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            $originalexists = $this->object->_questionExists($this->object->original_id);
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            if ($_GET["calling_test"] && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                $this->ctrl->redirect($this, "originalSyncForm");
            } elseif ($_GET["calling_test"]) {
                $_GET["ref_id"] = $_GET["calling_test"];
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["calling_test"]);
                return;
            } elseif ($_GET["test_ref_id"]) {
                global $DIC;
                $tree = $DIC['tree'];
                $ilDB = $DIC['ilDB'];
                $ilPluginAdmin = $DIC['ilPluginAdmin'];
                
                include_once("./Modules/Test/classes/class.ilObjTest.php");
                $_GET["ref_id"] = $_GET["test_ref_id"];
                $test = new ilObjTest($_GET["test_ref_id"], true);
                
                require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
                $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                $test->insertQuestion($testQuestionSetConfigFactory->getQuestionSetConfig(), $this->object->getId());
                
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $_GET["test_ref_id"]);
            } else {
                $this->ctrl->setParameter($this, "q_id", $this->object->getId());
                $this->editQuestion();
                if (strcmp($_SESSION["info"], "") != 0) {
                    ilUtil::sendSuccess($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
                } else {
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), false);
                }
                $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $this->object->getId());
                $this->ctrl->redirectByClass("ilAssQuestionPageGUI", "edit");
            }
        }
    }

    /**
    * save question
    */
    public function save()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $old_id = $_GET["q_id"];
        $result = $this->writePostData();

        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            $originalexists = $this->object->_questionExistsInPool($this->object->original_id);


            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            if (($_GET["calling_test"] || (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer'])) && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->setParameter($this, 'return_to', 'editQuestion');
                $this->ctrl->redirect($this, "originalSyncForm");
                return;
            } elseif ($_GET["calling_test"]) {
                require_once 'Modules/Test/classes/class.ilObjTest.php';
                $test = new ilObjTest($_GET["calling_test"]);
                if (!assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId())) {
                    global $DIC;
                    $tree = $DIC['tree'];
                    $ilDB = $DIC['ilDB'];
                    $ilPluginAdmin = $DIC['ilPluginAdmin'];
                    
                    include_once("./Modules/Test/classes/class.ilObjTest.php");
                    $_GET["ref_id"] = $_GET["calling_test"];
                    $test = new ilObjTest($_GET["calling_test"], true);
                    
                    require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
                    $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                    $new_id = $test->insertQuestion(
                        $testQuestionSetConfigFactory->getQuestionSetConfig(),
                        $this->object->getId()
                    );

                    if (isset($_REQUEST['prev_qid'])) {
                        $test->moveQuestionAfter($this->object->getId() + 1, $_REQUEST['prev_qid']);
                    }

                    $this->ctrl->setParameter($this, 'q_id', $new_id);
                    $this->ctrl->setParameter($this, 'calling_test', $_GET['calling_test']);
                    #$this->ctrl->setParameter($this, 'test_ref_id', false);
                }
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, 'editQuestion');
            } else {
                $this->callNewIdListeners($this->object->getId());

                if ($this->object->getId() != $old_id) {
                    // first save
                    $this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
                    $this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

                    //global $___test_express_mode;
                    /**
                     * in express mode, so add question to test directly
                     */
                    if ($_REQUEST['prev_qid']) {
                        // @todo: bheyser/mbecker wtf? ..... thx@jposselt ....
                        // mbecker: Possible fix: Just instantiate the obj?
                        include_once("./Modules/Test/classes/class.ilObjTest.php");
                        $test = new ilObjTest($_GET["ref_id"], true);
                        $test->moveQuestionAfter($_REQUEST['prev_qid'], $this->object->getId());
                    }
                    if ( /*$___test_express_mode || */ $_REQUEST['express_mode']) {
                        global $DIC;
                        $tree = $DIC['tree'];
                        $ilDB = $DIC['ilDB'];
                        $ilPluginAdmin = $DIC['ilPluginAdmin'];

                        include_once("./Modules/Test/classes/class.ilObjTest.php");
                        $test = new ilObjTest($_GET["ref_id"], true);

                        require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
                        $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                        $test->insertQuestion(
                            $testQuestionSetConfigFactory->getQuestionSetConfig(),
                            $this->object->getId()
                        );
                        
                        require_once 'Modules/Test/classes/class.ilTestExpressPage.php';
                        $_REQUEST['q_id'] = $this->object->getId();
                        ilUtil::redirect(ilTestExpressPage::getReturnToPageLink());
                    }

                    $this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
                }
                if (strcmp($_SESSION["info"], "") != 0) {
                    ilUtil::sendSuccess($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), true);
                } else {
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                }
                $this->ctrl->redirect($this, 'editQuestion');
            }
        }
    }

    /**
    * save question
    */
    public function saveReturn()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $old_id = $_GET["q_id"];
        $result = $this->writePostData();
        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            $originalexists = $this->object->_questionExistsInPool($this->object->original_id);

            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            if (($_GET["calling_test"] || (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer'])) && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->setParameter($this, 'test_express_mode', $_REQUEST['test_express_mode']);
                $this->ctrl->redirect($this, "originalSyncForm");
                return;
            } elseif ($_GET["calling_test"]) {
                require_once 'Modules/Test/classes/class.ilObjTest.php';
                $test = new ilObjTest($_GET["calling_test"]);
                #var_dump(assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId()));
                $q_id = $this->object->getId();
                if (!assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId())) {
                    global $DIC;
                    $tree = $DIC['tree'];
                    $ilDB = $DIC['ilDB'];
                    $ilPluginAdmin = $DIC['ilPluginAdmin'];
                    
                    include_once("./Modules/Test/classes/class.ilObjTest.php");
                    $_GET["ref_id"] = $_GET["calling_test"];
                    $test = new ilObjTest($_GET["calling_test"], true);
                    
                    require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
                    $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                    $new_id = $test->insertQuestion(
                        $testQuestionSetConfigFactory->getQuestionSetConfig(),
                        $this->object->getId()
                    );
                    
                    $q_id = $new_id;
                    if (isset($_REQUEST['prev_qid'])) {
                        $test->moveQuestionAfter($this->object->getId() + 1, $_REQUEST['prev_qid']);
                    }

                    $this->ctrl->setParameter($this, 'q_id', $new_id);
                    $this->ctrl->setParameter($this, 'calling_test', $_GET['calling_test']);
                    #$this->ctrl->setParameter($this, 'test_ref_id', false);
                }
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
            } else {
                if ($this->object->getId() != $old_id) {
                    $this->callNewIdListeners($this->object->getId());
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                    $this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
                }
                if (strcmp($_SESSION["info"], "") != 0) {
                    ilUtil::sendSuccess($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), true);
                } else {
                    ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                }
                $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
            }
        }
    }

    /**
    * apply changes
    */
    public function apply()
    {
        $this->writePostData();
        $this->object->saveToDb();
        $this->ctrl->setParameter($this, "q_id", $this->object->getId());
        $this->editQuestion();
    }
    
    /**
    * get context path in content object tree
    *
    * @param	int		$a_endnode_id		id of endnode
    * @param	int		$a_startnode_id		id of startnode
    */
    public function getContextPath($cont_obj, $a_endnode_id, $a_startnode_id = 1)
    {
        $path = "";

        $tmpPath = $cont_obj->getLMTree()->getPathFull($a_endnode_id, $a_startnode_id);

        // count -1, to exclude the learning module itself
        for ($i = 1; $i < (count($tmpPath) - 1); $i++) {
            if ($path != "") {
                $path .= " > ";
            }

            $path .= $tmpPath[$i]["title"];
        }

        return $path;
    }

    public function setSequenceNumber($nr)
    {
        $this->sequence_no = $nr;
    }
    
    public function getSequenceNumber()
    {
        return $this->sequence_no;
    }
    
    public function setQuestionCount($a_question_count)
    {
        $this->question_count = $a_question_count;
    }
    
    public function getQuestionCount()
    {
        return $this->question_count;
    }
    
    public function getErrorMessage()
    {
        return $this->errormessage;
    }
    
    public function setErrorMessage($errormessage)
    {
        $this->errormessage = $errormessage;
    }

    public function addErrorMessage($errormessage)
    {
        $this->errormessage .= ((strlen($this->errormessage)) ? "<br />" : "") . $errormessage;
    }
    
    public function outAdditionalOutput()
    {
    }

    /**
    * Returns the question type string
    *
    * Returns the question type string
    *
    * @result string The question type string
    * @access public
    */
    public function getQuestionType()
    {
        return $this->object->getQuestionType();
    }
    
    /**
    * Returns a HTML value attribute
    *
    * @param mixed $a_value A given text or value
    * @result string The value as HTML value attribute
    * @access public
    */
    public function getAsValueAttribute($a_value)
    {
        $result = "";
        if (strlen($a_value)) {
            $result = " value=\"$a_value\" ";
        }
        return $result;
    }

    // scorm2004-start
    /**
    * Add a listener that is notified with the new question ID, when
    * a new question is saved
    */
    public function addNewIdListener(&$a_object, $a_method, $a_parameters = "")
    {
        $cnt = $this->new_id_listener_cnt;
        $this->new_id_listeners[$cnt]["object"] = &$a_object;
        $this->new_id_listeners[$cnt]["method"] = $a_method;
        $this->new_id_listeners[$cnt]["parameters"] = $a_parameters;
        $this->new_id_listener_cnt++;
    }

    /**
    * Call the new id listeners
    */
    public function callNewIdListeners($a_new_id)
    {
        for ($i = 0; $i < $this->new_id_listener_cnt; $i++) {
            $this->new_id_listeners[$i]["parameters"]["new_id"] = $a_new_id;
            $object = &$this->new_id_listeners[$i]["object"];
            $method = $this->new_id_listeners[$i]["method"];
            $parameters = $this->new_id_listeners[$i]["parameters"];
            //var_dump($object);
            //var_dump($method);
            //var_dump($parameters);

            $object->$method($parameters);
        }
    }
    
    /**
    * Add the command buttons of a question properties form
    */
    public function addQuestionFormCommandButtons($form)
    {
        //if (!$this->object->getSelfAssessmentEditingMode() && !$_GET["calling_test"]) $form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        }
        $form->addCommandButton("save", $this->lng->txt("save"));
    }
    
    /**
    * Add basic question form properties:
    * assessment: title, author, description, question, working time
    *
    * @return	int	Default Nr of Tries
    */
    public function addBasicQuestionFormProperties($form)
    {
        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setMaxLength(100);
        $title->setValue($this->object->getTitle());
        $title->setRequired(true);
        $form->addItem($title);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // author
            $author = new ilTextInputGUI($this->lng->txt("author"), "author");
            $author->setValue($this->object->getAuthor());
            $author->setRequired(true);
            $form->addItem($author);
    
            // description
            $description = new ilTextInputGUI($this->lng->txt("description"), "comment");
            $description->setValue($this->object->getComment());
            $description->setRequired(false);
            $form->addItem($description);
        } else {
            // author as hidden field
            $hi = new ilHiddenInputGUI("author");
            $author = ilUtil::prepareFormOutput($this->object->getAuthor());
            if (trim($author) == "") {
                $author = "-";
            }
            $hi->setValue($author);
            $form->addItem($hi);
        }
        
        // lifecycle
        $lifecycle = new ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'lifecycle');
        $lifecycle->setOptions($this->object->getLifecycle()->getSelectOptions($this->lng));
        $lifecycle->setValue($this->object->getLifecycle()->getIdentifier());
        $form->addItem($lifecycle);

        // questiontext
        $question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
        $question->setValue($this->object->getQuestion());
        $question->setRequired(true);
        $question->setRows(10);
        $question->setCols(80);
        
        if (!$this->object->getSelfAssessmentEditingMode()) {
            if ($this->object->getAdditionalContentEditingMode() != assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT) {
                $question->setUseRte(true);
                include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
                $question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $question->addPlugin("latex");
                $question->addButton("latex");
                $question->addButton("pastelatex");
                $question->setRTESupport($this->object->getId(), "qpl", "assessment");
            }
        } else {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
            $question->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $question->setUseTagsForRteOnly(false);
        }
        $form->addItem($question);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // duration
            $duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
            $duration->setShowHours(true);
            $duration->setShowMinutes(true);
            $duration->setShowSeconds(true);
            $ewt = $this->object->getEstimatedWorkingTime();
            $duration->setHours($ewt["h"]);
            $duration->setMinutes($ewt["m"]);
            $duration->setSeconds($ewt["s"]);
            $duration->setRequired(false);
            $form->addItem($duration);
        } else {
            // number of tries
            if (strlen($this->object->getNrOfTries())) {
                $nr_tries = $this->object->getNrOfTries();
            } else {
                $nr_tries = $this->object->getDefaultNrOfTries();
            }
            if ($nr_tries < 1) {
                $nr_tries = "";
            }
            
            $ni = new ilNumberInputGUI($this->lng->txt("qst_nr_of_tries"), "nr_of_tries");
            $ni->setValue($nr_tries);
            $ni->setMinValue(0);
            $ni->setSize(5);
            $ni->setMaxLength(5);
            $form->addItem($ni);
        }
    }
    
    protected function saveTaxonomyAssignments()
    {
        if (count($this->getTaxonomyIds())) {
            require_once 'Services/Taxonomy/classes/class.ilTaxAssignInputGUI.php';
            
            foreach ($this->getTaxonomyIds() as $taxonomyId) {
                $postvar = "tax_node_assign_$taxonomyId";
                
                $tax_node_assign = new ilTaxAssignInputGUI($taxonomyId, true, '', $postvar);
                // TODO: determine tst/qpl when tax assigns become maintainable within tests
                $tax_node_assign->saveInput("qpl", $this->object->getObjId(), "quest", $this->object->getId());
            }
        }
    }
    
    protected function populateTaxonomyFormSection(ilPropertyFormGUI $form)
    {
        if (count($this->getTaxonomyIds())) {
            // this is needed by ilTaxSelectInputGUI in some cases
            require_once 'Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php';
            ilOverlayGUI::initJavaScript();

            $sectHeader = new ilFormSectionHeaderGUI();
            $sectHeader->setTitle($this->lng->txt('qpl_qst_edit_form_taxonomy_section'));
            $form->addItem($sectHeader);

            require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

            foreach ($this->getTaxonomyIds() as $taxonomyId) {
                $taxonomy = new ilObjTaxonomy($taxonomyId);
                $label = sprintf($this->lng->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle());
                $postvar = "tax_node_assign_$taxonomyId";

                $taxSelect = new ilTaxSelectInputGUI($taxonomy->getId(), $postvar, true);
                $taxSelect->setTitle($label);

                require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
                $taxNodeAssignments = new ilTaxNodeAssignment(ilObject::_lookupType($this->object->getObjId()), $this->object->getObjId(), 'quest', $taxonomyId);
                $assignedNodes = $taxNodeAssignments->getAssignmentsOfItem($this->object->getId());

                $taxSelect->setValue(array_map(function ($assignedNode) {
                    return $assignedNode['node_id'];
                }, $assignedNodes));
                $form->addItem($taxSelect);
            }
        }
    }
    
    /**
    * Returns the answer generic feedback depending on the results of the question
    *
    * @deprecated Use getGenericFeedbackOutput instead.
    * @param integer $active_id Active ID of the user
    * @param integer $pass Active pass
    * @return string HTML Code with the answer specific feedback
    * @access public
    */
    public function getAnswerFeedbackOutput($active_id, $pass)
    {
        return $this->getGenericFeedbackOutput($active_id, $pass);
    }

    /**
     * Returns the answer specific feedback for the question

     *
     * @param integer $active_id Active ID of the user
     * @param integer $pass Active pass
     * @return string HTML Code with the answer specific feedback
     * @access public
     */
    public function getGenericFeedbackOutput($active_id, $pass)
    {
        $output = "";
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        $manual_feedback = ilObjTest::getManualFeedback($active_id, $this->object->getId(), $pass);
        if (strlen($manual_feedback)) {
            return $manual_feedback;
        }
        $correct_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true);
        $incorrect_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false);
        if (strlen($correct_feedback . $incorrect_feedback)) {
            $reached_points = $this->object->calculateReachedPoints($active_id, $pass);
            $max_points = $this->object->getMaximumPoints();
            if ($reached_points == $max_points) {
                $output = $correct_feedback;
            } else {
                $output = $incorrect_feedback;
            }
        }
        return $this->object->prepareTextareaOutput($output, true);
    }

    public function getGenericFeedbackOutputForCorrectSolution()
    {
        return $this->object->prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true),
            true
        );
    }

    public function getGenericFeedbackOutputForIncorrectSolution()
    {
        return $this->object->prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false),
            true
        );
    }

    /**
     * Returns the answer specific feedback for the question
     *
     * This method should be overwritten by the actual question.
     *
     * @todo Mark this method abstract!
     * @param array $userSolution ($userSolution[<value1>] = <value2>)
     * @return string HTML Code with the answer specific feedback
     * @access public
     */
    abstract public function getSpecificFeedbackOutput($userSolution);
    
    public function outQuestionType()
    {
        $count = $this->object->isInUse();
        
        if ($this->object->_questionExistsInPool($this->object->getId()) && $count) {
            global $DIC;
            $rbacsystem = $DIC['rbacsystem'];
            if ($rbacsystem->checkAccess("write", $_GET["ref_id"])) {
                ilUtil::sendInfo(sprintf($this->lng->txt("qpl_question_is_in_use"), $count));
            }
        }
        
        return assQuestion::_getQuestionTypeName($this->object->getQuestionType());
    }
    
    public function showSuggestedSolution()
    {
        $this->suggestedsolution();
    }

    /**
    * Allows to add suggested solutions for questions
    *
    * @access public
    */
    public function suggestedsolution()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $save = (is_array($_POST["cmd"]) && array_key_exists("suggestedsolution", $_POST["cmd"])) ? true : false;

        if ($save && $_POST["deleteSuggestedSolution"] == 1) {
            $this->object->deleteSuggestedSolutions();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "suggestedsolution");
        }

        $output = "";
        $solution_array = $this->object->getSuggestedSolution(0);
        $options = array(
            "lm" => $this->lng->txt("obj_lm"),
            "st" => $this->lng->txt("obj_st"),
            "pg" => $this->lng->txt("obj_pg"),
            "git" => $this->lng->txt("glossary_term"),
            "file" => $this->lng->txt("fileDownload"),
            "text" => $this->lng->txt("solutionText")
        );

        if ((strcmp($_POST["solutiontype"], "file") == 0) && (strcmp($solution_array["type"], "file") != 0)) {
            $solution_array = array(
                "type" => "file"
            );
        } elseif ((strcmp($_POST["solutiontype"], "text") == 0) && (strcmp($solution_array["type"], "text") != 0)) {
            $oldOutputMode = $this->getRenderPurpose();
            $this->setRenderPurpose(self::RENDER_PURPOSE_INPUT_VALUE);
            
            $solution_array = array(
                "type" => "text",
                "value" => $this->getSolutionOutput(0, null, false, false, true, false, true)
            );
            $this->setRenderPurpose($oldsaveSuggestedSolutionOutputMode);
        }
        if ($save && strlen($_POST["filename"])) {
            $solution_array["value"]["filename"] = $_POST["filename"];
        }
        if ($save && strlen($_POST["solutiontext"])) {
            $solution_array["value"] = $_POST["solutiontext"];
        }
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        if (count($solution_array)) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt("solution_hint"));
            $form->setMultipart(true);
            $form->setTableWidth("100%");
            $form->setId("suggestedsolutiondisplay");

            // suggested solution output
            include_once "./Modules/TestQuestionPool/classes/class.ilSolutionTitleInputGUI.php";
            $title = new ilSolutionTitleInputGUI($this->lng->txt("showSuggestedSolution"), "solutiontype");
            $template = new ilTemplate("tpl.il_as_qpl_suggested_solution_input_presentation.html", true, true, "Modules/TestQuestionPool");
            if (strlen($solution_array["internal_link"])) {
                $href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
                $template->setCurrentBlock("preview");
                $template->setVariable("TEXT_SOLUTION", $this->lng->txt("suggested_solution"));
                $template->setVariable("VALUE_SOLUTION", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("view") . "</a> ");
                $template->parseCurrentBlock();
            } elseif ((strcmp($solution_array["type"], "file") == 0) && (is_array($solution_array["value"]))) {
                $href = $this->object->getSuggestedSolutionPathWeb() . $solution_array["value"]["name"];
                $template->setCurrentBlock("preview");
                $template->setVariable("TEXT_SOLUTION", $this->lng->txt("suggested_solution"));
                $template->setVariable("VALUE_SOLUTION", " <a href=\"$href\" target=\"content\">" . ilUtil::prepareFormOutput((strlen($solution_array["value"]["filename"])) ? $solution_array["value"]["filename"] : $solution_array["value"]["name"]) . "</a> ");
                $template->parseCurrentBlock();
            }
            $template->setVariable("TEXT_TYPE", $this->lng->txt("type"));
            $template->setVariable("VALUE_TYPE", $options[$solution_array["type"]]);
            $title->setHtml($template->get());
            $deletesolution = new ilCheckboxInputGUI("", "deleteSuggestedSolution");
            $deletesolution->setOptionTitle($this->lng->txt("deleteSuggestedSolution"));
            $title->addSubItem($deletesolution);
            $form->addItem($title);

            if (strcmp($solution_array["type"], "file") == 0) {
                // file
                $file = new ilFileInputGUI($this->lng->txt("fileDownload"), "file");
                $file->setRequired(true);
                $file->enableFileNameSelection("filename");
                //$file->setSuffixes(array("doc","xls","png","jpg","gif","pdf"));
                if ($_FILES["file"]["tmp_name"] && $file->checkInput()) {
                    if (!file_exists($this->object->getSuggestedSolutionPath())) {
                        ilUtil::makeDirParents($this->object->getSuggestedSolutionPath());
                    }
                    
                    $res = ilUtil::moveUploadedFile($_FILES["file"]["tmp_name"], $_FILES["file"]["name"], $this->object->getSuggestedSolutionPath() . $_FILES["file"]["name"]);
                    if ($res) {
                        ilUtil::renameExecutables($this->object->getSuggestedSolutionPath());
                        
                        // remove an old file download
                        if (is_array($solution_array["value"])) {
                            @unlink($this->object->getSuggestedSolutionPath() . $solution_array["value"]["name"]);
                        }
                        $file->setValue($_FILES["file"]["name"]);
                        $this->object->saveSuggestedSolution("file", "", 0, array("name" => $_FILES["file"]["name"], "type" => $_FILES["file"]["type"], "size" => $_FILES["file"]["size"], "filename" => $_POST["filename"]));
                        $originalexists = $this->object->_questionExistsInPool($this->object->original_id);
                        if (($_GET["calling_test"] || (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer'])) && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                            return $this->originalSyncForm("suggestedsolution");
                        } else {
                            ilUtil::sendSuccess($this->lng->txt("suggested_solution_added_successfully"), true);
                            $this->ctrl->redirect($this, "suggestedsolution");
                        }
                    } else {
                        // BH: $res as info string? wtf? it holds a bool or something else!!?
                        ilUtil::sendInfo($res);
                    }
                } else {
                    if (is_array($solution_array["value"])) {
                        $file->setValue($solution_array["value"]["name"]);
                        $file->setFilename((strlen($solution_array["value"]["filename"])) ? $solution_array["value"]["filename"] : $solution_array["value"]["name"]);
                    }
                }
                $form->addItem($file);
                $hidden = new ilHiddenInputGUI("solutiontype");
                $hidden->setValue("file");
                $form->addItem($hidden);
            } elseif (strcmp($solution_array["type"], "text") == 0) {
                $solutionContent = $solution_array['value'];
                $solutionContent = $this->object->fixSvgToPng($solutionContent);
                $solutionContent = $this->object->fixUnavailableSkinImageSources($solutionContent);
                $question = new ilTextAreaInputGUI($this->lng->txt("solutionText"), "solutiontext");
                $question->setValue($this->object->prepareTextareaOutput($solutionContent));
                $question->setRequired(true);
                $question->setRows(10);
                $question->setCols(80);
                $question->setUseRte(true);
                $question->addPlugin("latex");
                $question->addButton("latex");
                $question->setRTESupport($this->object->getId(), "qpl", "assessment");
                $hidden = new ilHiddenInputGUI("solutiontype");
                $hidden->setValue("text");
                $form->addItem($hidden);
                $form->addItem($question);
            }
            if ($ilAccess->checkAccess("write", "", $_GET['ref_id'])) {
                $form->addCommandButton('showSuggestedSolution', $this->lng->txt('cancel'));
                $form->addCommandButton('suggestedsolution', $this->lng->txt('save'));
            }
            
            if ($save) {
                if ($form->checkInput()) {
                    switch ($solution_array["type"]) {
                        case "file":
                            $this->object->saveSuggestedSolution("file", "", 0, array(
                                "name" => $solution_array["value"]["name"],
                                "type" => $solution_array["value"]["type"],
                                "size" => $solution_array["value"]["size"],
                                "filename" => $_POST["filename"]
                            ));
                            break;
                        case "text":
                            $this->object->saveSuggestedSolution("text", "", 0, $solution_array["value"]);
                            break;
                    }
                    $originalexists = $this->object->_questionExistsInPool($this->object->original_id);
                    if (($_GET["calling_test"] || (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer'])) && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                        return $this->originalSyncForm("suggestedsolution");
                    } else {
                        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                        $this->ctrl->redirect($this, "suggestedsolution");
                    }
                }
            }
            
            $output = $form->getHTML();
        }
        
        $savechange = (strcmp($this->ctrl->getCmd(), "saveSuggestedSolution") == 0) ? true : false;

        $changeoutput = "";
        if ($ilAccess->checkAccess("write", "", $_GET['ref_id'])) {
            $formchange = new ilPropertyFormGUI();
            $formchange->setFormAction($this->ctrl->getFormAction($this));
            $formchange->setTitle((count($solution_array)) ? $this->lng->txt("changeSuggestedSolution") : $this->lng->txt("addSuggestedSolution"));
            $formchange->setMultipart(false);
            $formchange->setTableWidth("100%");
            $formchange->setId("suggestedsolution");

            $solutiontype = new ilRadioGroupInputGUI($this->lng->txt("suggestedSolutionType"), "solutiontype");
            foreach ($options as $opt_value => $opt_caption) {
                $solutiontype->addOption(new ilRadioOption($opt_caption, $opt_value));
            }
            if (count($solution_array)) {
                $solutiontype->setValue($solution_array["type"]);
            }
            $solutiontype->setRequired(true);
            $formchange->addItem($solutiontype);

            $formchange->addCommandButton("saveSuggestedSolution", $this->lng->txt("select"));

            if ($savechange) {
                $formchange->checkInput();
            }
            $changeoutput = $formchange->getHTML();
        }
        
        $this->tpl->setVariable("ADM_CONTENT", $changeoutput . $output);
    }
    
    public function outSolutionExplorer()
    {
        global $DIC;
        $tree = $DIC['tree'];

        include_once("./Modules/TestQuestionPool/classes/class.ilSolutionExplorer.php");
        $type = $_GET["link_new_type"];
        $search = $_GET["search_link_type"];
        $this->ctrl->setParameter($this, "link_new_type", $type);
        $this->ctrl->setParameter($this, "search_link_type", $search);
        $this->ctrl->saveParameter($this, array("subquestion_index", "link_new_type", "search_link_type"));

        ilUtil::sendInfo($this->lng->txt("select_object_to_link"));

        $parent_ref_id = $tree->getParentId($_GET["ref_id"]);
        $exp = new ilSolutionExplorer($this->ctrl->getLinkTarget($this, 'suggestedsolution'), get_class($this));
        $exp->setExpand($_GET['expand_sol'] ? $_GET['expand_sol'] : $parent_ref_id);
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'outSolutionExplorer'));
        $exp->setTargetGet("ref_id");
        $exp->setRefId($_GET["ref_id"]);
        $exp->addFilter($type);
        $exp->setSelectableType($type);
        if (isset($_GET['expandCurrentPath']) && $_GET['expandCurrentPath']) {
            $exp->expandPathByRefId($parent_ref_id);
        }

        // build html-output
        $exp->setOutput(0);

        $template = new ilTemplate("tpl.il_as_qpl_explorer.html", true, true, "Modules/TestQuestionPool");
        $template->setVariable("EXPLORER_TREE", $exp->getOutput());
        $template->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));
        $template->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "suggestedsolution"));
        $this->tpl->setVariable("ADM_CONTENT", $template->get());
    }
    
    public function saveSuggestedSolution()
    {
        global $DIC;
        $tree = $DIC['tree'];

        include_once("./Modules/TestQuestionPool/classes/class.ilSolutionExplorer.php");
        switch ($_POST["solutiontype"]) {
            case "lm":
                $type = "lm";
                $search = "lm";
                break;
            case "git":
                $type = "glo";
                $search = "glo";
                break;
            case "st":
                $type = "lm";
                $search = "st";
                break;
            case "pg":
                $type = "lm";
                $search = "pg";
                break;
            case "file":
            case "text":
                return $this->suggestedsolution();
                break;
            default:
                return $this->suggestedsolution();
                break;
        }
        if (isset($_POST['solutiontype'])) {
            $this->ctrl->setParameter($this, 'expandCurrentPath', 1);
        }
        $this->ctrl->setParameter($this, "link_new_type", $type);
        $this->ctrl->setParameter($this, "search_link_type", $search);
        $this->ctrl->redirect($this, "outSolutionExplorer");
    }

    public function cancelExplorer()
    {
        $this->ctrl->redirect($this, "suggestedsolution");
    }
    
    public function outPageSelector()
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionInternalLinkSelectionTableGUI.php';
        require_once 'Modules/LearningModule/classes/class.ilLMPageObject.php';
        require_once 'Modules/LearningModule/classes/class.ilObjContentObjectGUI.php';

        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $cont_obj_gui = new ilObjContentObjectGUI('', $_GET['source_id'], true);
        $cont_obj = $cont_obj_gui->object;
        $pages = ilLMPageObject::getPageList($cont_obj->getId());
        $shownpages = array();
        $tree = $cont_obj->getLMTree();
        $chapters = $tree->getSubtree($tree->getNodeData($tree->getRootId()));

        $rows = array();

        foreach ($chapters as $chapter) {
            $chapterpages = $tree->getChildsByType($chapter['obj_id'], 'pg');
            foreach ($chapterpages as $page) {
                if ($page['type'] == $_GET['search_link_type']) {
                    array_push($shownpages, $page['obj_id']);

                    if ($tree->isInTree($page['obj_id'])) {
                        $path_str = $this->getContextPath($cont_obj, $page['obj_id']);
                    } else {
                        $path_str = '---';
                    }

                    $this->ctrl->setParameter($this, $page['type'], $page['obj_id']);
                    $rows[] = array(
                        'title' => $page['title'],
                        'description' => ilUtil::prepareFormOutput($path_str),
                        'text_add' => $this->lng->txt('add'),
                        'href_add' => $this->ctrl->getLinkTarget($this, 'add' . strtoupper($page['type']))
                    );
                }
            }
        }
        foreach ($pages as $page) {
            if (!in_array($page['obj_id'], $shownpages)) {
                $this->ctrl->setParameter($this, $page['type'], $page['obj_id']);
                $rows[] = array(
                    'title' => $page['title'],
                    'description' => '---',
                    'text_add' => $this->lng->txt('add'),
                    'href_add' => $this->ctrl->getLinkTarget($this, 'add' . strtoupper($page['type']))
                );
            }
        }

        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionInternalLinkSelectionTableGUI.php';
        $table = new ilQuestionInternalLinkSelectionTableGUI($this, 'cancelExplorer', __METHOD__);
        $table->setTitle($this->lng->txt('obj_' . ilUtil::stripSlashes($_GET['search_link_type'])));
        $table->setData($rows);

        $this->tpl->setContent($table->getHTML());
    }

    public function outChapterSelector()
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionInternalLinkSelectionTableGUI.php';
        require_once 'Modules/LearningModule/classes/class.ilObjContentObjectGUI.php';

        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $cont_obj_gui = new ilObjContentObjectGUI('', $_GET['source_id'], true);
        $cont_obj = $cont_obj_gui->object;
        $ctree = $cont_obj->getLMTree();
        $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));

        $rows = array();

        foreach ($nodes as $node) {
            if ($node['type'] == $_GET['search_link_type']) {
                $this->ctrl->setParameter($this, $node['type'], $node['obj_id']);
                $rows[] = array(
                    'title' => $node['title'],
                    'description' => '',
                    'text_add' => $this->lng->txt('add'),
                    'href_add' => $this->ctrl->getLinkTarget($this, 'add' . strtoupper($node['type']))
                );
            }
        }

        $table = new ilQuestionInternalLinkSelectionTableGUI($this, 'cancelExplorer', __METHOD__);
        $table->setTitle($this->lng->txt('obj_' . ilUtil::stripSlashes($_GET['search_link_type'])));
        $table->setData($rows);

        $this->tpl->setContent($table->getHTML());
    }

    public function outGlossarySelector()
    {
        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $glossary = new ilObjGlossary($_GET['source_id'], true);
        $terms = $glossary->getTermList();

        $rows = array();

        foreach ($terms as $term) {
            $this->ctrl->setParameter($this, 'git', $term['id']);
            $rows[] = array(
                'title' => $term['term'],
                'description' => '',
                'text_add' => $this->lng->txt('add'),
                'href_add' => $this->ctrl->getLinkTarget($this, 'addGIT')
            );
        }

        $table = new ilQuestionInternalLinkSelectionTableGUI($this, 'cancelExplorer', __METHOD__);
        $table->setTitle($this->lng->txt('glossary_term'));
        $table->setData($rows);

        $this->tpl->setContent($table->getHTML());
    }
    
    public function linkChilds()
    {
        $this->ctrl->saveParameter($this, array("subquestion_index", "link_new_type", "search_link_type"));
        switch ($_GET["search_link_type"]) {
            case "pg":
                return $this->outPageSelector();
                break;
            case "st":
                return $this->outChapterSelector();
                break;
            case "glo":
                return $this->outGlossarySelector();
                break;
            case "lm":
                $subquestion_index = ($_GET["subquestion_index"] > 0) ? $_GET["subquestion_index"] : 0;
                $this->object->saveSuggestedSolution("lm", "il__lm_" . $_GET["source_id"], $subquestion_index);
                ilUtil::sendSuccess($this->lng->txt("suggested_solution_added_successfully"), true);
                $this->ctrl->redirect($this, "suggestedsolution");
                break;
        }
    }

    public function addPG()
    {
        $subquestion_index = 0;
        if (strlen($_GET["subquestion_index"]) && $_GET["subquestion_index"] >= 0) {
            $subquestion_index = $_GET["subquestion_index"];
        }
        $this->object->saveSuggestedSolution("pg", "il__pg_" . $_GET["pg"], $subquestion_index);
        ilUtil::sendSuccess($this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addST()
    {
        $subquestion_index = 0;
        if (strlen($_GET["subquestion_index"]) && $_GET["subquestion_index"] >= 0) {
            $subquestion_index = $_GET["subquestion_index"];
        }
        $this->object->saveSuggestedSolution("st", "il__st_" . $_GET["st"], $subquestion_index);
        ilUtil::sendSuccess($this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addGIT()
    {
        $subquestion_index = 0;
        if (strlen($_GET["subquestion_index"]) && $_GET["subquestion_index"] >= 0) {
            $subquestion_index = $_GET["subquestion_index"];
        }
        $this->object->saveSuggestedSolution("git", "il__git_" . $_GET["git"], $subquestion_index);
        ilUtil::sendSuccess($this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function isSaveCommand()
    {
        return in_array($this->ctrl->getCmd(), array('save', 'saveEdit', 'saveReturn'));
    }
        
    /**
     * extracts values of all constants of given class with given prefix as array
     * can be used to get all possible commands in case of these commands are defined as constants
     *
     * @param string $guiClassName
     * @param string $cmdConstantNameBegin
     * @return array
     */
    public static function getCommandsFromClassConstants($guiClassName, $cmdConstantNameBegin = 'CMD_')
    {
        $reflectionClass = new ReflectionClass($guiClassName);
        
        $commands = null;
        
        if ($reflectionClass instanceof ReflectionClass) {
            $commands = array();
        
            foreach ($reflectionClass->getConstants() as $constName => $constValue) {
                if (substr($constName, 0, strlen($cmdConstantNameBegin)) == $cmdConstantNameBegin) {
                    $commands[] = $constValue;
                }
            }
        }
        
        return $commands;
    }
    
    public function setQuestionTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();

        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $q_type = $this->object->getQuestionType();

        if (strlen($q_type)) {
            $classname = $q_type . "GUI";
            $this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
            $this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
        }

        if ($_GET["q_id"]) {
            $this->addTab_Question($ilTabs);
        }

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($ilTabs);

        // add tab for question's suggested solution within common class assQuestionGUI
        $this->addTab_SuggestedSolution($ilTabs, $classname);

        // Assessment of questions sub menu entry
        if ($_GET["q_id"]) {
            $ilTabs->addTarget(
                "statistics",
                $this->ctrl->getLinkTargetByClass($classname, "assessment"),
                array("assessment"),
                $classname,
                ""
            );
        }

        $this->addBackTab($ilTabs);
    }
    
    public function addTab_SuggestedSolution(ilTabsGUI $tabs, $classname)
    {
        if ($_GET["q_id"]) {
            $tabs->addTarget(
                "suggested_solution",
                $this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
                array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel",
                    "addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
                ),
                $classname,
                ""
            );
        }
    }
    
    final public function getEditQuestionTabCommands()
    {
        return array_merge($this->getBasicEditQuestionTabCommands(), $this->getAdditionalEditQuestionCommands());
    }
    
    protected function getBasicEditQuestionTabCommands()
    {
        return array('editQuestion', 'save', 'saveEdit', 'originalSyncForm');
    }
    
    protected function getAdditionalEditQuestionCommands()
    {
        return array();
    }
    
    /**
     * adds the feedback tab to ilTabsGUI
     *
     * @global ilCtrl $ilCtrl
     * @param ilTabsGUI $tabs
     */
    protected function addTab_QuestionFeedback(ilTabsGUI $tabs)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
        $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionFeedbackEditingGUI');
        
        $tabLink = $ilCtrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
        
        $tabs->addTarget('tst_feedback', $tabLink, $tabCommands, $ilCtrl->getCmdClass(), '');
    }

    /**
     * @param ilTabsGUI $tabs
     */
    protected function addTab_Units(ilTabsGUI $tabs)
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $tabs->addTarget('units', $ilCtrl->getLinkTargetByClass('ilLocalUnitConfigurationGUI', ''), '', 'illocalunitconfigurationgui');
    }
    
    /**
     * adds the hints tab to ilTabsGUI
     *
     * @global ilCtrl $ilCtrl
     * @param ilTabsGUI $tabs
     */
    protected function addTab_QuestionHints(ilTabsGUI $tabs)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';

        switch ($ilCtrl->getCmdClass()) {
            case 'ilassquestionhintsgui':
                
                $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionHintsGUI');
                break;

            case 'ilassquestionhintgui':
                
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
                $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionHintGUI');
                break;
            
            default:
                
                $tabCommands = array();
        }

        $tabLink = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
        
        $tabs->addTarget('tst_question_hints_tab', $tabLink, $tabCommands, $ilCtrl->getCmdClass(), '');
    }
    
    protected function addTab_Question(ilTabsGUI $tabsGUI)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php';

        $tabsGUI->addTarget(
            'edit_question',
            $this->ctrl->getLinkTargetByClass(
                array('ilrepositorygui','ilobjquestionpoolgui', get_class($this)),
                'editQuestion'),'editQuestion','','',false
        );
   }

    abstract public function getSolutionOutput(
        $active_id,
        $pass = null,
        $graphicalOutput = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    );

    /**
     * Question type specific support of intermediate solution output
     * The function getSolutionOutput respects getUseIntermediateSolution()
     * @return bool
     */
    public function supportsIntermediateSolutionOutput()
    {
        return false;
    }

    /**
     * Check if the question has an intermediate solution
     * @param int $activeId
     * @param int $passIndex
     * @return bool
     */
    public function hasIntermediateSolution($activeId, $passIndex)
    {
        $result = $this->object->lookupForExistingSolutions($activeId, $passIndex);
        return ($result['intermediate']);
    }

    /**
     * Set to use the intermediate solution for solution output
     * @var bool $use
     */
    public function setUseIntermediateSolution($use)
    {
        $this->use_intermediate_solution = (bool) $use;
    }

    /**
     * Get if intermediate solution should be used for solution output
     * @return bool
     */
    public function getUseIntermediateSolution()
    {
        return (bool) $this->use_intermediate_solution;
    }

    protected function hasCorrectSolution($activeId, $passIndex)
    {
        $reachedPoints = $this->object->getAdjustedReachedPoints($activeId, $passIndex, true);
        $maximumPoints = $this->object->getMaximumPoints();
        
        return $reachedPoints == $maximumPoints;
    }
    
    public function isAutosaveable()
    {
        return $this->object->isAutosaveable();
    }

    protected function writeQuestionGenericPostData()
    {
        $this->object->setTitle($_POST["title"]);
        $this->object->setAuthor($_POST["author"]);
        $this->object->setComment($_POST["comment"]);
        if ($this->object->getSelfAssessmentEditingMode()) {
            $this->object->setNrOfTries($_POST['nr_of_tries']);
        }
        
        try {
            $lifecycle = ilAssQuestionLifecycle::getInstance($_POST['lifecycle']);
            $this->object->setLifecycle($lifecycle);
        } catch (ilTestQuestionPoolInvalidArgumentException $e) {
        }
        
        $this->object->setQuestion(ilUtil::stripOnlySlashes($_POST['question'])); // ?
        $this->object->setEstimatedWorkingTime(
            $_POST["Estimated"]["hh"],
            $_POST["Estimated"]["mm"],
            $_POST["Estimated"]["ss"]
        );
    }

    abstract public function getPreview($show_question_only = false, $showInlineFeedback = false);

    /**
     * @param string		$formaction
     * @param integer		$active_id
     * @param integer|null 	$pass
     * @param bool 			$is_question_postponed
     * @param bool 			$user_post_solutions
     * @param bool 			$show_specific_inline_feedback
     */
    final public function outQuestionForTest(
        $formaction,
        $active_id,
        // hey: prevPassSolutions - pass will be always available from now on
        $pass,
        // hey.
        $is_question_postponed = false,
        $user_post_solutions = false,
        $show_specific_inline_feedback = false
    ) {
        $formaction = $this->completeTestOutputFormAction($formaction, $active_id, $pass);
        
        $test_output = $this->getTestOutput(
            $active_id,
            $pass,
            $is_question_postponed,
            $user_post_solutions,
            $show_specific_inline_feedback
        );
        
        $this->magicAfterTestOutput();

        $this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
        $this->tpl->setVariable("FORMACTION", $formaction);
        $this->tpl->setVariable("ENCTYPE", 'enctype="' . $this->getFormEncodingType() . '"');
        $this->tpl->setVariable("FORM_TIMESTAMP", time());
    }
    
    // hey: prevPassSolutions - $pass will be passed always from now on
    protected function completeTestOutputFormAction($formAction, $active_id, $pass)
    // hey.
    {
        return $formAction;
    }
    
    public function magicAfterTestOutput()
    {
        return;
    }
    
    abstract public function getTestOutput(
        $active_id,
        $pass,
        $is_question_postponed,
        $user_post_solutions,
        $show_specific_inline_feedback
    );

    public function getFormEncodingType()
    {
        return self::FORM_ENCODING_URLENCODE;
    }

    /**
     * @param ilTabsGUI $ilTabs
     */
    protected function addBackTab(ilTabsGUI $ilTabs)
    {
        $ilTabs->setBackTarget(
            $this->lng->txt('backtocallingpage'),
            $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW)
        );
    }

    /**
     * @var ilAssQuestionPreviewSession
     */
    private $previewSession;

    /**
     * @param \ilAssQuestionPreviewSession $previewSession
     */
    public function setPreviewSession($previewSession)
    {
        $this->previewSession = $previewSession;
    }

    /**
     * @return \ilAssQuestionPreviewSession
     */
    public function getPreviewSession()
    {
        return $this->previewSession;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function buildBasicEditFormObject()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setId($this->getType());
        $form->setTitle($this->outQuestionType());

        $form->setTableWidth('100%');

        $form->setMultipart(true);
        
        return $form;
    }

    public function showHints()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
    }

    /**
     *
     */
    protected function buildEditForm()
    {
        $errors = $this->editQuestion(true); // TODO bheyser: editQuestion should be added to the abstract base class with a unified signature
        return $this->editForm;
    }
    
    /**
     * @return string
     */
    public function buildFocusAnchorHtml()
    {
        return '<div id="focus"></div>';
    }
    
    public function isAnswerFreuqencyStatisticSupported()
    {
        return true;
    }
    
    public function getSubQuestionsIndex()
    {
        return array(0);
    }
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex)
    {
        return array();
    }
    
    /**
     * @param $parentGui
     * @param $parentCmd
     * @param $relevantAnswers
     * @param $questionIndex
     * @return ilAnswerFrequencyStatisticTableGUI
     */
    public function getAnswerFrequencyTableGUI($parentGui, $parentCmd, $relevantAnswers, $questionIndex)
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilAnswerFrequencyStatisticTableGUI.php';
        
        $table = new ilAnswerFrequencyStatisticTableGUI($parentGui, $parentCmd, $this->object);
        $table->setQuestionIndex($questionIndex);
        $table->setData($this->getAnswersFrequency($relevantAnswers, $questionIndex));
        $table->initColumns();
        
        return $table;
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form)
    {
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
    }
}
