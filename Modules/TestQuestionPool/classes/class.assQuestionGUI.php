<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Notes\Note;

require_once './Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
require_once './Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
require_once './Services/Taxonomy/classes/class.ilTaxAssignInputGUI.php';

require_once './Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php';

require_once './Services/Link/classes/class.ilLink.php';

require_once './Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';

require_once './Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';

require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/Test/classes/class.ilTestExpressPage.php';
require_once './Modules/Test/classes/class.ilTestExpressPage.php';
require_once './Modules/Test/classes/class.ilObjAssessmentFolder.php';
require_once './Modules/Test/classes/class.ilObjTest.php';
require_once './Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';

require_once './Modules/LearningModule/classes/class.ilLMPageObject.php';
require_once './Modules/LearningModule/classes/class.ilObjContentObjectGUI.php';

require_once './Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilSolutionTitleInputGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilSolutionExplorer.php';
require_once './Modules/TestQuestionPool/classes/tables/class.ilQuestionInternalLinkSelectionTableGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php';
require_once './Modules/TestQuestionPool/classes/tables/class.ilAnswerFrequencyStatisticTableGUI.php';
require_once './Modules/TestQuestionPool/classes/tables/class.ilQuestionCumulatedStatisticsTableGUI.php';
require_once './Modules/TestQuestionPool/classes/tables/class.ilQuestionUsagesTableGUI.php';
require_once './Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';


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
    private $ui;
    private ilObjectDataCache $ilObjDataCache;
    private ilHelpGUI $ilHelp;
    private ilAccessHandler $access;
    private ilObjUser $ilUser;
    private ilTabsGUI $ilTabs;
    private ilRbacSystem $rbacsystem;
    protected \ILIAS\Notes\GUIService $notes_gui;

    protected ilCtrl $ctrl;
    private array $new_id_listeners = array();
    private int $new_id_listener_cnt = 0;

    /** @var ilAssQuestionPreviewSession  */
    private $previewSession;

    public assQuestion $object;
    public ilGlobalPageTemplate $tpl;
    public ilLanguage $lng;

    public $error;
    public string $errormessage;
    
    /** sequence number in test */
    public int $sequence_no;

    /** question count in test */
    public int $question_count;
    
    private $taxonomyIds = array();
    
    private $targetGuiClass = null;

    private string $questionActionCmd = 'handleQuestionAction';

    private ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder;

    private ilTestQuestionNavigationGUI $navigationGUI;

    const PRESENTATION_CONTEXT_TEST = 'pContextTest';
    const PRESENTATION_CONTEXT_RESULTS = 'pContextResults';

    private ?string $presentationContext = null;

    const RENDER_PURPOSE_PLAYBACK = 'renderPurposePlayback';
    const RENDER_PURPOSE_DEMOPLAY = 'renderPurposeDemoplay';
    const RENDER_PURPOSE_PREVIEW = 'renderPurposePreview';
    const RENDER_PURPOSE_PRINT_PDF = 'renderPurposePrintPdf';
    const RENDER_PURPOSE_INPUT_VALUE = 'renderPurposeInputValue';
    
    private string $renderPurpose = self::RENDER_PURPOSE_PLAYBACK;

    const EDIT_CONTEXT_AUTHORING = 'authoring';
    const EDIT_CONTEXT_ADJUSTMENT = 'adjustment';
    
    private string $editContext = self::EDIT_CONTEXT_AUTHORING;
    
    private bool $previousSolutionPrefilled = false;

    protected ilPropertyFormGUI $editForm;
    protected \ILIAS\TestQuestionPool\InternalRequestService $request;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->ui = $DIC->ui();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->access = $DIC->access();
        $this->ilHelp = $DIC['ilHelp'];
        $this->ilUser = $DIC['ilUser'];
        $this->ilTabs = $DIC['ilTabs'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->request = $DIC->testQuestionPool()->internal()->request();

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

        $this->errormessage = $this->lng->txt("fill_out_all_required_fields");
        $this->notes_gui = $DIC->notes()->gui();
    }
    
    public function hasInlineFeedback() : bool
    {
        return false;
    }
    
    public function addHeaderAction() : void
    {
        $this->ui->mainTemplate()->setVariable(
            "HEAD_ACTION",
            $this->getHeaderAction()
        );

        $this->notes_gui->initJavascript();

        $redrawActionsUrl = $this->ctrl->getLinkTarget($this, 'redrawHeaderAction', '', true);
        $this->ui->mainTemplate()->addOnLoadCode("il.Object.setRedrawAHUrl('$redrawActionsUrl');");
    }
    
    public function redrawHeaderAction() : void
    {
        echo $this->getHeaderAction() . $this->ui->mainTemplate()->getOnLoadCodeForAsynch();
        exit;
    }
    
    public function getHeaderAction() : string
    {
        $parentObjType = $this->ilObjDataCache->lookupType($this->object->getObjId());
        
        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $this->access,
            $parentObjType,
            $this->request->getRefId(),
            $this->object->getObjId()
        );
        
        $dispatcher->setSubObject("quest", $this->object->getId());
        
        $ha = $dispatcher->initHeaderAction();
        $ha->enableComments(true, false);
        
        return $ha->getHeaderAction($this->ui->mainTemplate());
    }
    
    public function getNotesHTML() : string
    {
        $notesGUI = new ilNoteGUI($this->object->getObjId(), $this->object->getId(), 'quest');
        $notesGUI->enablePublicNotes(true);
        $notesGUI->enablePublicNotesDeletion(true);
        
        return $notesGUI->getCommentsHTML();
    }

    public function executeCommand()
    {
        $this->ilHelp->setScreenIdComponent('qpl');

        $cmd = $this->ctrl->getCmd("editQuestion");
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $form = $this->buildEditForm();

                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $form_prop_dispatch->setItem($form->getItemByPostVar(ilUtil::stripSlashes($this->request->raw('postvar'))));
                return $this->ctrl->forwardCommand($form_prop_dispatch);

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

    /** needed for page editor compliance */
    public function getType() : string
    {
        return $this->getQuestionType();
    }

    public function getPresentationContext() : string
    {
        return $this->presentationContext;
    }

    public function setPresentationContext(string $presentationContext) : void
    {
        $this->presentationContext = $presentationContext;
    }
    
    public function isTestPresentationContext() : bool
    {
        return $this->getPresentationContext() == self::PRESENTATION_CONTEXT_TEST;
    }

    // hey: previousPassSolutions - setter/getter for Previous Solution Prefilled flag
    public function isPreviousSolutionPrefilled() : bool
    {
        return $this->previousSolutionPrefilled;
    }

    public function setPreviousSolutionPrefilled(bool $previousSolutionPrefilled) : void
    {
        $this->previousSolutionPrefilled = $previousSolutionPrefilled;
    }
    // hey.

    public function getRenderPurpose() : string
    {
        return $this->renderPurpose;
    }

    public function setRenderPurpose(string $renderPurpose) : void
    {
        $this->renderPurpose = $renderPurpose;
    }
    
    public function isRenderPurposePrintPdf() : bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PRINT_PDF;
    }
    
    public function isRenderPurposePreview() : bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PREVIEW;
    }
    
    public function isRenderPurposeInputValue() : bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_INPUT_VALUE;
    }
    
    public function isRenderPurposePlayback() : bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PLAYBACK;
    }
    
    public function isRenderPurposeDemoplay() : bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_DEMOPLAY;
    }
    
    public function renderPurposeSupportsFormHtml() : bool
    {
        if ($this->isRenderPurposePrintPdf()) {
            return false;
        }
        
        if ($this->isRenderPurposeInputValue()) {
            return false;
        }
        
        return true;
    }
    
    public function getEditContext() : string
    {
        return $this->editContext;
    }
    
    public function setEditContext(string $editContext) : void
    {
        $this->editContext = $editContext;
    }
    
    public function isAuthoringEditContext() : bool
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_AUTHORING;
    }
    
    public function isAdjustmentEditContext() : bool
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_ADJUSTMENT;
    }
    
    public function setAdjustmentEditContext() : void
    {
        $this->setEditContext(self::EDIT_CONTEXT_ADJUSTMENT);
    }
    
    public function getNavigationGUI() : ilTestQuestionNavigationGUI
    {
        return $this->navigationGUI;
    }

    public function setNavigationGUI(ilTestQuestionNavigationGUI $navigationGUI) : void
    {
        $this->navigationGUI = $navigationGUI;
    }
    
    public function setTaxonomyIds(array $taxonomyIds) : void
    {
        $this->taxonomyIds = $taxonomyIds;
    }
    
    public function getTaxonomyIds() : array
    {
        return $this->taxonomyIds;
    }
    
    public function setTargetGui($linkTargetGui) : void
    {
        $this->setTargetGuiClass(get_class($linkTargetGui));
    }
    
    public function setTargetGuiClass($targetGuiClass) : void
    {
        $this->targetGuiClass = $targetGuiClass;
    }
    
    public function getTargetGuiClass() : string
    {
        return $this->targetGuiClass;
    }

    public function setQuestionHeaderBlockBuilder(\ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder) : void
    {
        $this->questionHeaderBlockBuilder = $questionHeaderBlockBuilder;
    }

    // fau: testNav - get the question header block bulder (for tweaking)
    public function getQuestionHeaderBlockBuilder() : \ilQuestionHeaderBlockBuilder
    {
        return $this->questionHeaderBlockBuilder;
    }
    // fau.

    public function setQuestionActionCmd(string $questionActionCmd) : void
    {
        $this->questionActionCmd = $questionActionCmd;

        if (is_object($this->object)) {
            $this->object->questionActionCmd = $questionActionCmd;
        }
    }

    public function getQuestionActionCmd() : string
    {
        return $this->questionActionCmd;
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * @return integer A positive value, if one of the required fields wasn't set, else 0
     */
    protected function writePostData(bool $always = false) : int
    {
        return 0;
    }

    public function assessment()
    {
        $stats_table = new ilQuestionCumulatedStatisticsTableGUI($this, 'assessment', '', $this->object);


        $usage_table = new ilQuestionUsagesTableGUI($this, 'assessment', '', $this->object);

        $this->tpl->setContent(implode('<br />', array(
            $stats_table->getHTML(),
            $usage_table->getHTML()
        )));
    }

    /**
     * Creates a question gui representation and returns the alias to the question gui
     */
    public static function _getQuestionGUI(string $question_type = '', int $question_id = -1) : assQuestionGUI
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        if (($question_type === '') && ($question_id > 0)) {
            $question_type = assQuestion::getQuestionTypeFromDb($question_id);
        }
        
        if ($question_type === '') {
            throw new ilTestQuestionPoolInvalidArgumentException('No question type given or determined by question_id');
        }

        assQuestion::_includeClass($question_type, 1);

        $question_type_gui = $question_type . 'GUI';
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
    public static function _getGUIClassNameForId($a_q_id) : string
    {
        $q_type = assQuestion::getQuestionTypeFromDb($a_q_id);
        $class_name = assQuestionGUI::_getClassNameForQType($q_type);
        return $class_name;
    }

    /**
     * @deprecated
     */
    public static function _getClassNameForQType($q_type) : string
    {
        return $q_type . "GUI";
    }

    public function populateJavascriptFilesRequiredForWorkForm(ilGlobalTemplateInterface $tpl) : void
    {
        foreach ($this->getPresentationJavascripts() as $jsFile) {
            $tpl->addJavaScript($jsFile);
        }
    }
    
    public function getPresentationJavascripts() : array
    {
        return array();
    }

    public function getQuestionTemplate() : void
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
    
    protected function renderEditForm(ilPropertyFormGUI $form) : void
    {
        $this->getQuestionTemplate();
        $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
    }

    /**
     * Returns the ILIAS Page around a question
     */
    public function getILIASPage(string $html = "") : string
    {
        $page_gui = new ilAssQuestionPageGUI($this->object->getId());
        $page_gui->setQuestionHTML(array($this->object->getId() => $html));
        $presentation = $page_gui->presentation();
        $presentation = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $presentation);
        return $presentation;
    }

    public function outQuestionPage($a_temp_var, $a_postponed = false, $active_id = "", $html = "", $inlineFeedbackEnabled = false) : string
    {
        if ($this->object->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            // hey
            $this->tpl->setOnScreenMessage('info', $this->getPreviousSolutionProvidedMessage());
            $html .= $this->getPreviousSolutionConfirmationCheckboxHtml();
        } elseif ($this->object->getTestPresentationConfig()->isUnchangedAnswerPossible()) {
            $html .= $this->getUseUnchangedAnswerCheckboxHtml();
        }

        $this->lng->loadLanguageModule("content");

        $page_gui = new ilAssQuestionPageGUI($this->object->getId());
        $page_gui->setOutputMode("presentation");
        $page_gui->setTemplateTargetVar($a_temp_var);

        if ($this->getNavigationGUI()) {
            $html .= $this->getNavigationGUI()->getHTML();
            $page_gui->setQuestionActionsHTML($this->getNavigationGUI()->getActionsHTML());
        }

        if (strlen($html)) {
            if ($inlineFeedbackEnabled && $this->hasInlineFeedback()) {
                $html = $this->buildFocusAnchorHtml() . $html;
            }
            
            $page_gui->setQuestionHTML(array($this->object->getId() => $html));
        }

        $page_gui->setPresentationTitle($this->questionHeaderBlockBuilder->getPresentationTitle());
        $page_gui->setQuestionInfoHTML($this->questionHeaderBlockBuilder->getQuestionInfoHTML());

        return $page_gui->presentation();
    }

    protected function getUseUnchangedAnswerCheckboxHtml() : string
    {
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->object->getTestPresentationConfig()->getUseUnchangedAnswerLabel());
        return $tpl->get();
    }

    protected function getPreviousSolutionProvidedMessage() : string
    {
        return $this->lng->txt('use_previous_solution_advice');
    }
    
    protected function getPreviousSolutionConfirmationCheckboxHtml() : string
    {
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->lng->txt('use_previous_solution'));
        return $tpl->get();
    }

    public function cancel() : void
    {
        if ($this->request->raw("calling_test")) {
            $_GET["ref_id"] = $this->request->raw("calling_test");
            ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
        } elseif ($this->request->raw("test_ref_id")) {
            $_GET["ref_id"] = $this->request->raw("test_ref_id");
            ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("test_ref_id"));
        } else {
            if ($this->request->raw("q_id") > 0) {
                $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $this->request->getQuestionId());
                $this->ctrl->redirectByClass("ilAssQuestionPageGUI", "edit");
            } else {
                $this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
            }
        }
    }

    public function originalSyncForm(string $return_to = "", string $return_to_feedback = '') : void
    {
        if (strlen($return_to)) {
            $this->ctrl->setParameter($this, "return_to", $return_to);
        } elseif ($this->request->raw('return_to')) {
            $this->ctrl->setParameter($this, "return_to", $this->request->raw('return_to'));
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
    
    public function sync() : void
    {
        $original_id = $this->object->original_id;
        if ($original_id) {
            $this->object->syncWithOriginal();
        }
        if (strlen($this->request->raw("return_to"))) {
            $this->ctrl->redirect($this, $this->request->raw("return_to"));
        }
        if (strlen($this->request->raw("return_to_fb"))) {
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', 'showFeedbackForm');
        } else {
            if ($this->request->isset('calling_consumer') && (int) $this->request->raw('calling_consumer')) {
                $ref_id = (int) $this->request->raw('calling_consumer');
                $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
                if ($consumer instanceof ilQuestionEditingFormConsumer) {
                    ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($this->request->raw('consumer_context')));
                }

                ilUtil::redirect(ilLink::_getLink($ref_id));
            }
            $_GET["ref_id"] = $this->request->raw("calling_test");
            
            if ($this->request->raw('test_express_mode')) {
                ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
            } else {
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
            }
        }
    }

    public function cancelSync() : void
    {
        if (strlen($this->request->raw("return_to"))) {
            $this->ctrl->redirect($this, $this->request->raw("return_to"));
        }
        if (strlen($this->request->raw('return_to_fb'))) {
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', 'showFeedbackForm');
        } else {
            if ($this->request->isset('calling_consumer') && (int) $this->request->raw('calling_consumer')) {
                $ref_id = (int) $this->request->raw('calling_consumer');
                $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
                if ($consumer instanceof ilQuestionEditingFormConsumer) {
                    ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($this->request->raw('consumer_context')));
                }
                ilUtil::redirect(ilLink::_getLink($ref_id));
            }
            $_GET["ref_id"] = $this->request->raw("calling_test");

            if ($this->request->raw('test_express_mode')) {
                ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
            } else {
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
            }
        }
    }
    
    public function saveEdit()
    {
        $ilUser = $this->ilUser;
        $result = $this->writePostData();
        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            $originalexists = $this->object->_questionExists($this->object->getOriginalId());

            if ($this->request->raw("calling_test") && $originalexists && assQuestion::_isWriteable($this->object->getOriginalId(), $ilUser->getId())) {
                $this->ctrl->redirect($this, "originalSyncForm");
            } elseif ($this->request->raw("calling_test")) {
                $_GET["ref_id"] = $this->request->raw("calling_test");
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
                return;
            } elseif ($this->request->raw("test_ref_id")) {
                global $DIC;
                $tree = $DIC['tree'];
                $ilDB = $DIC['ilDB'];
                $ilPluginAdmin = $DIC['ilPluginAdmin'];
                // TODO: Courier Antipattern!
                $_GET["ref_id"] = $this->request->raw("test_ref_id");
                $test = new ilObjTest($this->request->raw("test_ref_id"), true);

                $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                $test->insertQuestion($testQuestionSetConfigFactory->getQuestionSetConfig(), $this->object->getId());
                
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("test_ref_id"));
            } else {
                $this->ctrl->setParameter($this, "q_id", $this->object->getId());
                $this->editQuestion();
                if (ilSession::get("info") != null) {
                    $this->tpl->setOnScreenMessage('success', ilSession::get("info") . "<br />" . $this->lng->txt("msg_obj_modified"), false);
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), false);
                }
                $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $this->object->getId());
                $this->ctrl->redirectByClass("ilAssQuestionPageGUI", "edit");
            }
        }
    }

    public function save() : void
    {
        $ilUser = $this->ilUser;
        $old_id = $this->request->raw("q_id");
        $result = $this->writePostData();

        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            if ($this->object->getOriginalId() == null) {
                $originalexists = false;
            } else {
                $originalexists = $this->object->_questionExistsInPool($this->object->getOriginalId());
            }

            if (($this->request->raw("calling_test") ||
                    ($this->request->isset('calling_consumer')
                        && (int) $this->request->raw('calling_consumer')))
                && $originalexists && assQuestion::_isWriteable($this->object->getOriginalId(), $ilUser->getId())) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->setParameter($this, 'return_to', 'editQuestion');
                $this->ctrl->redirect($this, "originalSyncForm");
                return;
            } elseif ($this->request->raw("calling_test")) {
                $test = new ilObjTest($this->request->raw("calling_test"));
                if (!assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId())) {
                    global $DIC;
                    $tree = $DIC['tree'];
                    $ilDB = $DIC['ilDB'];
                    $ilPluginAdmin = $DIC['ilPluginAdmin'];
                    // TODO: Courier Antipattern!
                    $_GET["ref_id"] = $this->request->raw("calling_test");
                    $test = new ilObjTest($this->request->raw("calling_test"), true);
                    $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                    $new_id = $test->insertQuestion(
                        $testQuestionSetConfigFactory->getQuestionSetConfig(),
                        $this->object->getId()
                    );

                    if ($this->request->isset('prev_qid')) {
                        $test->moveQuestionAfter($this->object->getId() + 1, $this->request->raw('prev_qid'));
                    }

                    $this->ctrl->setParameter($this, 'q_id', $new_id);
                    $this->ctrl->setParameter($this, 'calling_test', $this->request->raw("calling_test"));
                    #$this->ctrl->setParameter($this, 'test_ref_id', false);
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, 'editQuestion');
            } else {
                $this->callNewIdListeners($this->object->getId());

                if ($this->object->getId() != $old_id) {
                    // first save
                    $this->ctrl->setParameterByClass($this->request->raw("cmdClass"), "q_id", $this->object->getId());
                    $this->ctrl->setParameterByClass($this->request->raw("cmdClass"), "sel_question_types", $this->request->raw("sel_question_types"));
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

                    //global $___test_express_mode;
                    /**
                     * in express mode, so add question to test directly
                     */
                    if ($this->request->raw('prev_qid')) {
                        // @todo: bheyser/mbecker wtf? ..... thx@jposselt ....
                        $test = new ilObjTest($this->request->getRefId(), true);
                        $test->moveQuestionAfter($this->request->raw('prev_qid'), $this->object->getId());
                    }
                    if ( /*$___test_express_mode || */ $this->request->raw('express_mode')) {
                        global $DIC;
                        $tree = $DIC['tree'];
                        $ilDB = $DIC['ilDB'];
                        $ilPluginAdmin = $DIC['ilPluginAdmin'];
                        // TODO: Courier Antipattern!
                        $test = new ilObjTest($this->request->getRefId(), true);
                        $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);
                        $test->insertQuestion(
                            $testQuestionSetConfigFactory->getQuestionSetConfig(),
                            $this->object->getId()
                        );
                        $_REQUEST['q_id'] = $this->object->getId();
                        ilUtil::redirect(ilTestExpressPage::getReturnToPageLink());
                    }

                    $this->ctrl->redirectByClass($this->request->raw("cmdClass"), "editQuestion");
                }
                if (ilSession::get("info") != null) {
                    $this->tpl->setOnScreenMessage('success', ilSession::get("info") . "<br />" . $this->lng->txt("msg_obj_modified"), true);
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                }
                $this->ctrl->redirect($this, 'editQuestion');
            }
        }
    }

    public function saveReturn() : void
    {
        $ilUser = $this->ilUser;
        $old_id = $this->request->getQuestionId();
        $result = $this->writePostData();
        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            if ($this->object->getOriginalId() == null) {
                $originalexists = false;
            } else {
                $originalexists = $this->object->_questionExistsInPool($this->object->getOriginalId());
            }
            if (($this->request->raw("calling_test") || ($this->request->isset('calling_consumer')
                        && (int) $this->request->raw('calling_consumer')))
                && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->setParameter($this, 'test_express_mode', $this->request->raw('test_express_mode'));
                $this->ctrl->redirect($this, "originalSyncForm");
                return;
            } elseif ($this->request->raw("calling_test")) {
                $test = new ilObjTest($this->request->raw("calling_test"));
                #var_dump(assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId()));
                $q_id = $this->object->getId();
                if (!assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId())) {
                    global $DIC;
                    $tree = $DIC['tree'];
                    $ilDB = $DIC['ilDB'];
                    $ilPluginAdmin = $DIC['ilPluginAdmin'];
                    // TODO: Courier Antipattern!
                    $_GET["ref_id"] = $this->request->raw("calling_test");
                    $test = new ilObjTest($this->request->raw("calling_test"), true);

                    $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $test);

                    $new_id = $test->insertQuestion(
                        $testQuestionSetConfigFactory->getQuestionSetConfig(),
                        $this->object->getId()
                    );

                    $q_id = $new_id;
                    if ($this->request->isset('prev_qid')) {
                        $test->moveQuestionAfter($this->object->getId() + 1, $this->request->raw('prev_qid'));
                    }

                    $this->ctrl->setParameter($this, 'q_id', $new_id);
                    $this->ctrl->setParameter($this, 'calling_test', $this->request->raw("calling_test"));
                    #$this->ctrl->setParameter($this, 'test_ref_id', false);
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                if ( /*$___test_express_mode || */
                $this->request->raw('test_express_mode')
                ) {
                    ilUtil::redirect(ilTestExpressPage::getReturnToPageLink($q_id));
                } else {
                    ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
                }
            } else {
                if ($this->object->getId() != $old_id) {
                    $this->callNewIdListeners($this->object->getId());
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                    $this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
                }
                if (ilSession::get("info") != null) {
                    $this->tpl->setOnScreenMessage('success', ilSession::get("info") . "<br />" . $this->lng->txt("msg_obj_modified"), true);
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                }
                $this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
            }
        }
    }

    public function apply() : void
    {
        $this->writePostData();
        $this->object->saveToDb();
        $this->ctrl->setParameter($this, "q_id", $this->object->getId());
        $this->editQuestion();
    }
    
    /**
     * get context path in content object tree
     */
    public function getContextPath($cont_obj, int $a_endnode_id, int $a_startnode_id = 1) : string
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

    public function setSequenceNumber(int $nr) : void
    {
        $this->sequence_no = $nr;
    }
    
    public function getSequenceNumber() : int
    {
        return $this->sequence_no;
    }
    
    public function setQuestionCount(int $a_question_count) : void
    {
        $this->question_count = $a_question_count;
    }
    
    public function getQuestionCount() : int
    {
        return $this->question_count;
    }
    
    public function getErrorMessage() : string
    {
        return $this->errormessage;
    }
    
    public function setErrorMessage(string $errormessage) : void
    {
        $this->errormessage = $errormessage;
    }

    public function addErrorMessage(string $errormessage) : void
    {
        $this->errormessage .= ((strlen($this->errormessage)) ? "<br />" : "") . $errormessage;
    }

    /** Why are you here? Some magic for plugins? */
    public function outAdditionalOutput()
    {
    }

    public function getQuestionType() : string
    {
        return $this->object->getQuestionType();
    }
    
    public function getAsValueAttribute(string $a_value) : string
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
    public function addNewIdListener($a_object, string $a_method, string $a_parameters = "") : void
    {
        $cnt = $this->new_id_listener_cnt;
        $this->new_id_listeners[$cnt]["object"] = &$a_object;
        $this->new_id_listeners[$cnt]["method"] = $a_method;
        $this->new_id_listeners[$cnt]["parameters"] = $a_parameters;
        $this->new_id_listener_cnt++;
    }

    public function callNewIdListeners(int $a_new_id) : void
    {
        for ($i = 0; $i < $this->new_id_listener_cnt; $i++) {
            $this->new_id_listeners[$i]["parameters"]["new_id"] = $a_new_id;
            $object = &$this->new_id_listeners[$i]["object"];
            $method = $this->new_id_listeners[$i]["method"];
            $parameters = $this->new_id_listeners[$i]["parameters"];
            $object->$method($parameters);
        }
    }
    
    public function addQuestionFormCommandButtons(ilPropertyFormGUI $form) : void
    {
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        }
        $form->addCommandButton("save", $this->lng->txt("save"));
    }
    
    /**
    * Add basic question form properties:
    * assessment: title, author, description, question, working time
    * @return	int	Default Nr of Tries
    */
    public function addBasicQuestionFormProperties(ilPropertyFormGUI $form) : int
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
            $author = ilLegacyFormElementsUtil::prepareFormOutput($this->object->getAuthor());
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
                $question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $question->addPlugin("latex");
                $question->addButton("latex");
                $question->addButton("pastelatex");
                $question->setRTESupport($this->object->getId(), "qpl", "assessment");
            }
        } else {
            $question->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $question->setUseTagsForRteOnly(false);
        }
        $form->addItem($question);
        $nr_tries = 0;
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
        return  (int) $nr_tries;
    }
    
    protected function saveTaxonomyAssignments() : void
    {
        if (count($this->getTaxonomyIds())) {
            foreach ($this->getTaxonomyIds() as $taxonomyId) {
                $postvar = "tax_node_assign_$taxonomyId";
                
                $tax_node_assign = new ilTaxAssignInputGUI($taxonomyId, true, '', $postvar);
                // TODO: determine tst/qpl when tax assigns become maintainable within tests
                $tax_node_assign->saveInput("qpl", $this->object->getObjId(), "quest", $this->object->getId());
            }
        }
    }
    
    protected function populateTaxonomyFormSection(ilPropertyFormGUI $form) : void
    {
        if (count($this->getTaxonomyIds())) {
            // this is needed by ilTaxSelectInputGUI in some cases
            ilOverlayGUI::initJavaScript();

            $sectHeader = new ilFormSectionHeaderGUI();
            $sectHeader->setTitle($this->lng->txt('qpl_qst_edit_form_taxonomy_section'));
            $form->addItem($sectHeader);

            foreach ($this->getTaxonomyIds() as $taxonomyId) {
                $taxonomy = new ilObjTaxonomy($taxonomyId);
                $label = sprintf($this->lng->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle());
                $postvar = "tax_node_assign_$taxonomyId";

                $taxSelect = new ilTaxSelectInputGUI($taxonomy->getId(), $postvar, true);
                $taxSelect->setTitle($label);


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
     * @param   int|null  $pass      Active pass
     */
    public function getGenericFeedbackOutput(int $active_id, $pass) : string
    {
        $output = "";
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

    public function getGenericFeedbackOutputForCorrectSolution() : string
    {
        return $this->object->prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true),
            true
        );
    }

    public function getGenericFeedbackOutputForIncorrectSolution() : string
    {
        return $this->object->prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false),
            true
        );
    }

    /**
     * Returns the answer specific feedback for the question
     * @param array $userSolution ($userSolution[<value1>] = <value2>)
     */
    abstract public function getSpecificFeedbackOutput(array $userSolution) : string;

    public function outQuestionType() : string
    {
        $count = $this->object->usageNumber();
        
        if ($this->object->_questionExistsInPool($this->object->getId()) && $count) {
            global $DIC;
            $rbacsystem = $DIC['rbacsystem'];
            if ($rbacsystem->checkAccess("write", $this->request->getRefId())) {
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("qpl_question_is_in_use"), $count));
            }
        }
        
        return assQuestion::_getQuestionTypeName($this->object->getQuestionType());
    }

    public function suggestedsolution() : void
    {
        $ilUser = $this->ilUser;
        $ilAccess = $this->access;

        $save = (is_array($_POST["cmd"]) && array_key_exists("suggestedsolution", $_POST["cmd"])) ? true : false;

        if ($save && $_POST["deleteSuggestedSolution"] == 1) {
            $this->object->deleteSuggestedSolutions();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
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
            $oldsaveSuggestedSolutionOutputMode = $this->getRenderPurpose();
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
        if (count($solution_array)) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt("solution_hint"));
            $form->setMultipart(true);
            $form->setTableWidth("100%");
            $form->setId("suggestedsolutiondisplay");

            // suggested solution output
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
                $template->setVariable("VALUE_SOLUTION", " <a href=\"$href\" target=\"content\">" . ilLegacyFormElementsUtil::prepareFormOutput(
                    (strlen(
                        $solution_array["value"]["filename"]
                    )) ? $solution_array["value"]["filename"] : $solution_array["value"]["name"]
                ) . "</a> ");
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
                        ilFileUtils::makeDirParents($this->object->getSuggestedSolutionPath());
                    }
                    
                    $res = ilFileUtils::moveUploadedFile(
                        $_FILES["file"]["tmp_name"],
                        $_FILES["file"]["name"],
                        $this->object->getSuggestedSolutionPath() . $_FILES["file"]["name"]
                    );
                    if ($res) {
                        ilFileUtils::renameExecutables($this->object->getSuggestedSolutionPath());
                        
                        // remove an old file download
                        if (is_array($solution_array["value"])) {
                            @unlink($this->object->getSuggestedSolutionPath() . $solution_array["value"]["name"]);
                        }
                        $file->setValue($_FILES["file"]["name"]);
                        $this->object->saveSuggestedSolution("file", "", 0, array("name" => $_FILES["file"]["name"], "type" => $_FILES["file"]["type"], "size" => $_FILES["file"]["size"], "filename" => $_POST["filename"]));
                        $originalexists = $this->object->_questionExistsInPool($this->object->original_id);
                        if (($this->request->raw("calling_test") || ($this->request->isset('calling_consumer')
                                    && (int) $this->request->raw('calling_consumer'))) && $originalexists
                            && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                            $this->originalSyncForm("suggestedsolution");
                            return;
                        } else {
                            $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
                            $this->ctrl->redirect($this, "suggestedsolution");
                        }
                    } else {
                        // BH: $res as info string? wtf? it holds a bool or something else!!?
                        $this->tpl->setOnScreenMessage('info', $res);
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
            if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
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
                    if (($this->request->raw("calling_test") || ($this->request->isset('calling_consumer')
                                && (int) $this->request->raw('calling_consumer'))) && $originalexists
                        && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                        $this->originalSyncForm("suggestedsolution");
                        return;
                    } else {
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                        $this->ctrl->redirect($this, "suggestedsolution");
                    }
                }
            }
            
            $output = $form->getHTML();
        }
        
        $savechange = (strcmp($this->ctrl->getCmd(), "saveSuggestedSolution") == 0) ? true : false;

        $changeoutput = "";
        if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
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
    
    public function outSolutionExplorer() : void
    {
        global $DIC;
        $tree = $DIC['tree'];

        $type = $this->request->raw("link_new_type");
        $search = $this->request->raw("search_link_type");
        $this->ctrl->setParameter($this, "link_new_type", $type);
        $this->ctrl->setParameter($this, "search_link_type", $search);
        $this->ctrl->saveParameter($this, array("subquestion_index", "link_new_type", "search_link_type"));

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("select_object_to_link"));

        $parent_ref_id = $tree->getParentId($this->request->getRefId());
        $exp = new ilSolutionExplorer($this->ctrl->getLinkTarget($this, 'suggestedsolution'), get_class($this));
        $exp->setExpand($this->request->raw('expand_sol') ? $this->request->raw('expand_sol') : $parent_ref_id);
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'outSolutionExplorer'));
        $exp->setTargetGet("ref_id");
        $exp->setRefId($this->request->getRefId());
        $exp->addFilter($type);
        $exp->setSelectableType($type);
        if ($this->request->isset('expandCurrentPath') && $this->request->raw('expandCurrentPath')) {
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
    
    public function saveSuggestedSolution() : void
    {
        global $DIC;
        $tree = $DIC['tree'];

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
            default:
                $this->suggestedsolution();
                return;
        }
        if (isset($_POST['solutiontype'])) {
            $this->ctrl->setParameter($this, 'expandCurrentPath', 1);
        }
        $this->ctrl->setParameter($this, "link_new_type", $type);
        $this->ctrl->setParameter($this, "search_link_type", $search);
        $this->ctrl->redirect($this, "outSolutionExplorer");
    }

    public function cancelExplorer() : void
    {
        $this->ctrl->redirect($this, "suggestedsolution");
    }
    
    public function outPageSelector() : void
    {
        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $cont_obj_gui = new ilObjContentObjectGUI('', $this->request->raw('source_id'), true);
        $cont_obj = $cont_obj_gui->getObject();
        $pages = ilLMPageObject::getPageList($cont_obj->getId());
        $shownpages = array();
        $tree = $cont_obj->getLMTree();
        $chapters = $tree->getSubtree($tree->getNodeData($tree->getRootId()));

        $rows = array();

        foreach ($chapters as $chapter) {
            $chapterpages = $tree->getChildsByType($chapter['obj_id'], 'pg');
            foreach ($chapterpages as $page) {
                if ($page['type'] == $this->request->raw('search_link_type')) {
                    array_push($shownpages, $page['obj_id']);

                    if ($tree->isInTree($page['obj_id'])) {
                        $path_str = $this->getContextPath($cont_obj, $page['obj_id']);
                    } else {
                        $path_str = '---';
                    }

                    $this->ctrl->setParameter($this, $page['type'], $page['obj_id']);
                    $rows[] = array(
                        'title' => $page['title'],
                        'description' => ilLegacyFormElementsUtil::prepareFormOutput($path_str),
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

        $table = new ilQuestionInternalLinkSelectionTableGUI($this, 'cancelExplorer', __METHOD__);
        $table->setTitle($this->lng->txt('obj_' . ilUtil::stripSlashes($this->request->raw('search_link_type'))));
        $table->setData($rows);

        $this->tpl->setContent($table->getHTML());
    }

    public function outChapterSelector() : void
    {
        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $cont_obj_gui = new ilObjContentObjectGUI('', $this->request->raw('source_id'), true);
        $cont_obj = $cont_obj_gui->getObject();
        $ctree = $cont_obj->getLMTree();
        $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));

        $rows = array();

        foreach ($nodes as $node) {
            if ($node['type'] == $this->request->raw('search_link_type')) {
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
        $table->setTitle($this->lng->txt('obj_' . ilUtil::stripSlashes($this->request->raw('search_link_type'))));
        $table->setData($rows);

        $this->tpl->setContent($table->getHTML());
    }

    public function outGlossarySelector() : void
    {
        $this->ctrl->setParameter($this, 'q_id', $this->object->getId());

        $glossary = new ilObjGlossary($this->request->raw('source_id'), true);
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
    
    public function linkChilds() : void
    {
        $this->ctrl->saveParameter($this, array("subquestion_index", "link_new_type", "search_link_type"));
        switch ($this->request->raw("search_link_type")) {
            case "pg":
                $this->outPageSelector();
                break;
            case "st":
                $this->outChapterSelector();
                break;
            case "glo":
                $this->outGlossarySelector();
                break;
            case "lm":
                $subquestion_index = ($this->request->raw("subquestion_index") > 0) ? $this->request->raw("subquestion_index") : 0;
                $this->object->saveSuggestedSolution("lm", "il__lm_" . $this->request->raw("source_id"), $subquestion_index);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
                $this->ctrl->redirect($this, "suggestedsolution");
                break;
        }
    }

    public function addPG() : void
    {
        $subquestion_index = 0;
        if (strlen($this->request->raw("subquestion_index")) && $this->request->raw("subquestion_index") >= 0) {
            $subquestion_index = $this->request->raw("subquestion_index");
        }
        $this->object->saveSuggestedSolution("pg", "il__pg_" . $this->request->raw("pg"), $subquestion_index);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addST() : void
    {
        $subquestion_index = 0;
        if (strlen($this->request->raw("subquestion_index")) && $this->request->raw("subquestion_index") >= 0) {
            $subquestion_index = $this->request->raw("subquestion_index");
        }
        $this->object->saveSuggestedSolution("st", "il__st_" . $this->request->raw("st"), $subquestion_index);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addGIT() : void
    {
        $subquestion_index = 0;
        if (strlen($this->request->raw("subquestion_index")) && $this->request->raw("subquestion_index") >= 0) {
            $subquestion_index = $this->request->raw("subquestion_index");
        }
        $this->object->saveSuggestedSolution("git", "il__git_" . $this->request->raw("git"), $subquestion_index);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function isSaveCommand() : bool
    {
        return in_array($this->ctrl->getCmd(), array('save', 'saveEdit', 'saveReturn'));
    }
        
    public static function getCommandsFromClassConstants(
        string $guiClassName,
        string $cmdConstantNameBegin = 'CMD_'
    ) : array {
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
    
    public function setQuestionTabs() : void
    {
        $this->ilTabs->clearTargets();

        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $this->request->getQuestionId());
        $q_type = $this->object->getQuestionType();

        if (strlen($q_type)) {
            $classname = $q_type . "GUI";
            $this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
            $this->ctrl->setParameterByClass(strtolower($classname), "q_id", $this->request->getQuestionId());
        }

        if ($this->request->getQuestionId()) {
            if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
                // edit page
                $this->ilTabs->addTarget(
                    "edit_page",
                    $this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
                    array("edit", "insert", "exec_pg"),
                    "",
                    "",
                    false
                );
            }

            $this->addTab_QuestionPreview($this->ilTabs);
        }
        if ($this->rbacsystem->checkAccess('write', $this->request->getRefId())) {
            $url = "";
            if ($classname) {
                $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
            }
            // edit question properties
            $this->ilTabs->addTarget(
                "edit_question",
                $url,
                $this->getEditQuestionTabCommands(),
                $classname,
                "",
                false
            );
        }

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($this->ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($this->ilTabs);

        // add tab for question's suggested solution within common class assQuestionGUI
        $this->addTab_SuggestedSolution($this->ilTabs, $classname);

        // Assessment of questions sub menu entry
        if ($this->request->getQuestionId()) {
            $this->ilTabs->addTarget(
                "statistics",
                $this->ctrl->getLinkTargetByClass($classname, "assessment"),
                array("assessment"),
                $classname,
                ""
            );
        }

        $this->addBackTab($this->ilTabs);
    }
    
    public function addTab_SuggestedSolution(ilTabsGUI $tabs, string $classname) : void
    {
        if ($this->request->getQuestionId()) {
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
    
    final public function getEditQuestionTabCommands() : array
    {
        return array_merge($this->getBasicEditQuestionTabCommands(), $this->getAdditionalEditQuestionCommands());
    }
    
    protected function getBasicEditQuestionTabCommands() : array
    {
        return array('editQuestion', 'save', 'saveEdit', 'originalSyncForm');
    }
    
    protected function getAdditionalEditQuestionCommands() : array
    {
        return array();
    }
    
    protected function addTab_QuestionFeedback(ilTabsGUI $tabs) : void
    {
        $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionFeedbackEditingGUI');
        
        $tabLink = $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
        
        $tabs->addTarget('tst_feedback', $tabLink, $tabCommands, $this->ctrl->getCmdClass(), '');
    }

    protected function addTab_Units(ilTabsGUI $tabs) : void
    {
        $tabs->addTarget('units', $this->ctrl->getLinkTargetByClass('ilLocalUnitConfigurationGUI', ''), '', 'illocalunitconfigurationgui');
    }
    
    protected function addTab_QuestionHints(ilTabsGUI $tabs) : void
    {
        switch ($this->ctrl->getCmdClass()) {
            case 'ilassquestionhintsgui':
                $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionHintsGUI');
                break;

            case 'ilassquestionhintgui':
                $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionHintGUI');
                break;
            
            default:
                
                $tabCommands = array();
        }

        $tabLink = $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
        
        $tabs->addTarget('tst_question_hints_tab', $tabLink, $tabCommands, $this->ctrl->getCmdClass(), '');
    }
    
    protected function addTab_QuestionPreview(ilTabsGUI $tabsGUI) : void
    {
        $tabsGUI->addTarget(
            ilAssQuestionPreviewGUI::TAB_ID_QUESTION_PREVIEW,
            $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW),
            array(),
            array('ilAssQuestionPreviewGUI')
        );
    }

    // TODO: OWN "PASS" IN THE REFACTORING getSolutionOutput
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
    ) : string;
    
    protected function hasCorrectSolution($activeId, $passIndex) : bool
    {
        $reachedPoints = $this->object->getAdjustedReachedPoints($activeId, $passIndex, true);
        $maximumPoints = $this->object->getMaximumPoints();
        
        return $reachedPoints == $maximumPoints;
    }
    
    public function isAutosaveable() : bool
    {
        return $this->object->isAutosaveable();
    }

    protected function writeQuestionGenericPostData() : void
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

    // TODO: OWN "PASS" IN THE REFACTORING getPreview
    abstract public function getPreview($show_question_only = false, $showInlineFeedback = false);

    final public function outQuestionForTest(
        string $formaction,
        int $active_id,
        ?int $pass,
        bool $is_question_postponed = false,
        bool $user_post_solutions = false,
        bool $show_specific_inline_feedback = false
    ) : void {
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
        $this->tpl->setVariable("FORM_TIMESTAMP", (string) time());
    }
    
    // hey: prevPassSolutions - $pass will be passed always from now on
    protected function completeTestOutputFormAction($formAction, $active_id, $pass)
    // hey.
    {
        return $formAction;
    }
    
    public function magicAfterTestOutput() : void
    {
        return;
    }

    // TODO: OWN "PASS" IN THE REFACTORING getPreview
    abstract public function getTestOutput(
        $active_id,
        $pass,
        $is_question_postponed,
        $user_post_solutions,
        $show_specific_inline_feedback
    );

    public function getFormEncodingType() : string
    {
        return self::FORM_ENCODING_URLENCODE;
    }

    protected function addBackTab(ilTabsGUI $ilTabs) : void
    {
        if (($this->request->raw("calling_test") > 0) || ($this->request->raw("test_ref_id") > 0)) {
            $ref_id = $this->request->raw("calling_test");
            if (strlen($ref_id) == 0) {
                $ref_id = $this->request->raw("test_ref_id");
            }

            if (!$this->request->raw('test_express_mode') && (!isset($GLOBALS['___test_express_mode']) || !$GLOBALS['___test_express_mode'])) {
                $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
            } else {
                $link = ilTestExpressPage::getReturnToPageLink();
                $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), $link);
            }
        } elseif ($this->request->isset('calling_consumer') && (int) $this->request->raw('calling_consumer')) {
            $ref_id = (int) $this->request->raw('calling_consumer');
            $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
            if ($consumer instanceof ilQuestionEditingFormConsumer) {
                $ilTabs->setBackTarget($consumer->getQuestionEditingFormBackTargetLabel(), $consumer->getQuestionEditingFormBackTarget($this->request->raw('consumer_context')));
            } else {
                $ilTabs->setBackTarget($this->lng->txt("qpl"), ilLink::_getLink($ref_id));
            }
        } else {
            $ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
        }
    }

    public function setPreviewSession(ilAssQuestionPreviewSession $previewSession) : void
    {
        $this->previewSession = $previewSession;
    }

    /**
     * @return ilAssQuestionPreviewSession|null
     */
    public function getPreviewSession() : ?ilAssQuestionPreviewSession
    {
        return $this->previewSession;
    }

    protected function buildBasicEditFormObject() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setId($this->getType());
        $form->setTitle($this->outQuestionType());
        $form->setTableWidth('100%');
        $form->setMultipart(true);
        return $form;
    }

    public function showHints() : void
    {
        $this->ctrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
    }

    protected function buildEditForm() : ilPropertyFormGUI
    {
        $this->editQuestion(true); // TODO bheyser: editQuestion should be added to the abstract base class with a unified signature
        return $this->editForm;
    }
    
    public function buildFocusAnchorHtml() : string
    {
        return '<div id="focus"></div>';
    }
    
    public function isAnswerFrequencyStatisticSupported() : bool
    {
        return true;
    }
    
    public function getSubQuestionsIndex() : array
    {
        return array(0);
    }
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex) : array
    {
        return array();
    }
    
    public function getAnswerFrequencyTableGUI($parentGui, $parentCmd, $relevantAnswers, $questionIndex) : ilAnswerFrequencyStatisticTableGUI
    {
        $table = new ilAnswerFrequencyStatisticTableGUI($parentGui, $parentCmd, get_class($this->object));
        $table->setQuestionIndex($questionIndex);
        $table->setData($this->getAnswersFrequency($relevantAnswers, $questionIndex));
        $table->initColumns();
        return $table;
    }

    public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form) : void
    {
    }
    
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form) : void
    {
    }

    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form) : void
    {
    }

    /**
     * Prepares a string for a text area output where latex code may be in it
     * If the text is HTML-free, CHR(13) will be converted to a line break
     *
     * @param string $txt_output String which should be prepared for output
     * @access public
     *
     */
    public static function prepareTextareaOutput($txt_output, $prepare_for_latex_output = false, $omitNl2BrWhenTextArea = false)
    {
        $result = $txt_output;
        $is_html = false;
        if (strlen(strip_tags($result)) < strlen($result)) {
            $is_html = true;
        }

        // removed: did not work with magic_quotes_gpc = On
        if (!$is_html) {
            if (!$omitNl2BrWhenTextArea) {
                // if the string does not contain HTML code, replace the newlines with HTML line breaks
                $result = preg_replace("/[\n]/", "<br />", $result);
            }
        } else {
            // patch for problems with the <pre> tags in tinyMCE
            if (preg_match_all("/(\<pre>.*?\<\/pre>)/ims", $result, $matches)) {
                foreach ($matches[0] as $found) {
                    $replacement = "";
                    if (strpos("\n", $found) === false) {
                        $replacement = "\n";
                    }
                    $removed = preg_replace("/\<br\s*?\/>/ims", $replacement, $found);
                    $result = str_replace($found, $removed, $result);
                }
            }
        }

        // since server side mathjax rendering does include svg-xml structures that indeed have linebreaks,
        // do latex conversion AFTER replacing linebreaks with <br>. <svg> tag MUST NOT contain any <br> tags.
        if ($prepare_for_latex_output) {
            include_once './Services/MathJax/classes/class.ilMathJax.php';
            $result = ilMathJax::getInstance()->insertLatexImages($result, "\<span class\=\"latex\">", "\<\/span>");
            $result = ilMathJax::getInstance()->insertLatexImages($result, "\[tex\]", "\[\/tex\]");
        }

        if ($prepare_for_latex_output) {
            // replace special characters to prevent problems with the ILIAS template system
            // eg. if someone uses {1} as an answer, nothing will be shown without the replacement
            $result = str_replace("{", "&#123;", $result);
            $result = str_replace("}", "&#125;", $result);
            $result = str_replace("\\", "&#92;", $result);
        }

        return $result;
    }
}
