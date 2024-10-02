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

use ILIAS\TA\Questions\assQuestionSuggestedSolution;
use ILIAS\TA\Questions\assQuestionSuggestedSolutionsDatabaseRepository;

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
    public const FORM_MODE_EDIT = 'edit';
    public const FORM_MODE_ADJUST = 'adjust';

    public const FORM_ENCODING_URLENCODE = 'application/x-www-form-urlencoded';
    public const FORM_ENCODING_MULTIPART = 'multipart/form-data';

    protected const SUGGESTED_SOLUTION_COMMANDS_CANCEL = 'cancelSuggestedSolution';
    protected const SUGGESTED_SOLUTION_COMMANDS_SAVE = 'saveSuggestedSolution';
    protected const SUGGESTED_SOLUTION_COMMANDS_DEFAULT = 'suggestedsolution';

    public const CORRECTNESS_NOT_OK = 0;
    public const CORRECTNESS_MOSTLY_OK = 1;
    public const CORRECTNESS_OK = 2;

    protected const HAS_SPECIAL_QUESTION_COMMANDS = false;

    /**
     * sk - 12.05.2023: This const is also used in ilKprimChoiceWizardInputGUI.
     * Don't ask, but I didn't find an easy fix without undoing two more
     * question types.
     */
    public const ALLOWED_PLAIN_TEXT_TAGS = "<em>, <strong>";

    private const RETURN_AFTER_EXISTING_WITH_ORIGINAL_SAVE = -1;
    private const RETURN_AFTER_EXISTING_SAVE = 0;

    public const SESSION_PREVIEW_DATA_BASE_INDEX = 'ilAssQuestionPreviewAnswers';
    private $ui;
    private ilObjectDataCache $ilObjDataCache;
    private ilHelpGUI $ilHelp;
    private ilAccessHandler $access;
    private ilObjUser $ilUser;
    private ilTabsGUI $ilTabs;
    private ilRbacSystem $rbacsystem;

    private ilTree $tree;
    private ilDBInterface $db;
    protected ilLogger $logger;
    private ilComponentRepository $component_repository;
    protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;

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

    private $taxonomyIds = [];

    private $targetGuiClass = null;

    private string $questionActionCmd = 'handleQuestionAction';

    private ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder;

    private ?ilTestQuestionNavigationGUI $navigationGUI = null;

    public const PRESENTATION_CONTEXT_TEST = 'pContextTest';
    public const PRESENTATION_CONTEXT_RESULTS = 'pContextResults';

    private ?string $presentationContext = null;

    public const RENDER_PURPOSE_PLAYBACK = 'renderPurposePlayback';
    public const RENDER_PURPOSE_DEMOPLAY = 'renderPurposeDemoplay';
    public const RENDER_PURPOSE_PREVIEW = 'renderPurposePreview';
    public const RENDER_PURPOSE_PRINT_PDF = 'renderPurposePrintPdf';
    public const RENDER_PURPOSE_INPUT_VALUE = 'renderPurposeInputValue';

    private string $renderPurpose = self::RENDER_PURPOSE_PLAYBACK;

    public const EDIT_CONTEXT_AUTHORING = 'authoring';
    public const EDIT_CONTEXT_ADJUSTMENT = 'adjustment';

    private string $editContext = self::EDIT_CONTEXT_AUTHORING;

    private bool $previousSolutionPrefilled = false;

    protected ilPropertyFormGUI $editForm;
    protected \ILIAS\TestQuestionPool\InternalRequestService $request;
    protected bool $parent_type_is_lm = false;

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
        $this->tree = $DIC['tree'];
        $this->db = $DIC->database();
        $this->logger = $DIC['ilLog'];
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->component_repository = $DIC['component.repository'];
        $this->ctrl->saveParameter($this, "q_id");
        $this->ctrl->saveParameter($this, "prev_qid");
        $this->ctrl->saveParameter($this, "calling_test");
        $this->ctrl->saveParameter($this, "consumer_context");
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'test_express_mode');
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'consumer_context');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'test_express_mode');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'consumer_context');

        $this->errormessage = $this->lng->txt("fill_out_all_required_fields");
        $this->notes_gui = $DIC->notes()->gui();
    }

    public function hasInlineFeedback(): bool
    {
        return false;
    }

    public function addHeaderAction(): void
    {
    }

    public function redrawHeaderAction(): void
    {
        echo $this->getHeaderAction() . $this->ui->mainTemplate()->getOnLoadCodeForAsynch();
        exit;
    }

    public function getHeaderAction(): string
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

    public function geCommentsPanelHTML(): string
    {
        $comment_gui = new ilCommentGUI($this->object->getObjId(), $this->object->getId(), 'quest');
        return $comment_gui->getListHTML();
    }

    public function executeCommand()
    {
        $this->ilHelp->setScreenIdComponent('qpl');

        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $form = $this->buildEditForm();
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $form_prop_dispatch->setItem($form->getItemByPostVar(ilUtil::stripSlashes($this->request->raw('postvar'))));
                $this->ctrl->forwardCommand($form_prop_dispatch);
                break;
            default:
                $cmd = $this->ctrl->getCmd('editQuestion');
                switch ($cmd) {
                    case self::SUGGESTED_SOLUTION_COMMANDS_CANCEL:
                    case self::SUGGESTED_SOLUTION_COMMANDS_SAVE:
                    case self::SUGGESTED_SOLUTION_COMMANDS_DEFAULT:
                        $this->suggestedsolution();
                        break;
                    case 'saveSuggestedSolutionType':
                    case 'saveContentsSuggestedSolution':
                    case 'deleteSuggestedSolution':
                    case 'linkChilds':
                    case 'cancelExplorer':
                    case 'outSolutionExplorer':
                    case 'addST':
                    case 'addPG':
                    case 'addGIT':
                        $this->$cmd();
                        break;
                    case 'save':
                    case 'saveReturn':
                    case 'editQuestion':
                        $this->addSaveOnEnterOnLoadCode();
                        $this->$cmd();
                        break;
                    default:
                        if (method_exists($this, $cmd)) {
                            $this->$cmd();
                            return;
                        }
                        if ($this->hasSpecialQuestionCommands() === true) {
                            $this->callSpecialQuestionCommands($cmd);
                        }
                }
        }
    }

    protected function hasSpecialQuestionCommands(): bool
    {
        return static::HAS_SPECIAL_QUESTION_COMMANDS;
    }

    /** needed for page editor compliance */
    public function getType(): string
    {
        return $this->getQuestionType();
    }

    public function getPresentationContext(): ?string
    {
        return $this->presentationContext;
    }

    public function setPresentationContext(string $presentationContext): void
    {
        $this->presentationContext = $presentationContext;
    }

    public function isTestPresentationContext(): bool
    {
        return $this->getPresentationContext() == self::PRESENTATION_CONTEXT_TEST;
    }

    // hey: previousPassSolutions - setter/getter for Previous Solution Prefilled flag
    public function isPreviousSolutionPrefilled(): bool
    {
        return $this->previousSolutionPrefilled;
    }

    public function setPreviousSolutionPrefilled(bool $previousSolutionPrefilled): void
    {
        $this->previousSolutionPrefilled = $previousSolutionPrefilled;
    }
    // hey.

    public function getRenderPurpose(): string
    {
        return $this->renderPurpose;
    }

    public function setRenderPurpose(string $renderPurpose): void
    {
        $this->renderPurpose = $renderPurpose;
    }

    public function isRenderPurposePrintPdf(): bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PRINT_PDF;
    }

    public function isRenderPurposePreview(): bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PREVIEW;
    }

    public function isRenderPurposeInputValue(): bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_INPUT_VALUE;
    }

    public function isRenderPurposePlayback(): bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_PLAYBACK;
    }

    public function isRenderPurposeDemoplay(): bool
    {
        return $this->getRenderPurpose() == self::RENDER_PURPOSE_DEMOPLAY;
    }

    public function renderPurposeSupportsFormHtml(): bool
    {
        if ($this->isRenderPurposePrintPdf()) {
            return false;
        }

        if ($this->isRenderPurposeInputValue()) {
            return false;
        }

        return true;
    }

    public function getEditContext(): string
    {
        return $this->editContext;
    }

    public function setEditContext(string $editContext): void
    {
        $this->editContext = $editContext;
    }

    public function isAuthoringEditContext(): bool
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_AUTHORING;
    }

    public function isAdjustmentEditContext(): bool
    {
        return $this->getEditContext() == self::EDIT_CONTEXT_ADJUSTMENT;
    }

    public function setAdjustmentEditContext(): void
    {
        $this->setEditContext(self::EDIT_CONTEXT_ADJUSTMENT);
    }

    public function getNavigationGUI(): ?ilTestQuestionNavigationGUI
    {
        return $this->navigationGUI;
    }

    public function setNavigationGUI(?ilTestQuestionNavigationGUI $navigationGUI): void
    {
        $this->navigationGUI = $navigationGUI;
    }

    public function setTaxonomyIds(array $taxonomyIds): void
    {
        $this->taxonomyIds = $taxonomyIds;
    }

    public function getTaxonomyIds(): array
    {
        return $this->taxonomyIds;
    }

    public function setTargetGui($linkTargetGui): void
    {
        $this->setTargetGuiClass(get_class($linkTargetGui));
    }

    public function setTargetGuiClass($targetGuiClass): void
    {
        $this->targetGuiClass = $targetGuiClass;
    }

    public function getTargetGuiClass(): ?string
    {
        return $this->targetGuiClass;
    }

    public function setQuestionHeaderBlockBuilder(\ilQuestionHeaderBlockBuilder $questionHeaderBlockBuilder): void
    {
        $this->questionHeaderBlockBuilder = $questionHeaderBlockBuilder;
    }

    // fau: testNav - get the question header block bulder (for tweaking)
    public function getQuestionHeaderBlockBuilder(): \ilQuestionHeaderBlockBuilder
    {
        return $this->questionHeaderBlockBuilder;
    }
    // fau.

    public function setQuestionActionCmd(string $questionActionCmd): void
    {
        $this->questionActionCmd = $questionActionCmd;

        if (is_object($this->object)) {
            $this->object->questionActionCmd = $questionActionCmd;
        }
    }

    public function getQuestionActionCmd(): string
    {
        return $this->questionActionCmd;
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * @return integer A positive value, if one of the required fields wasn't set, else 0
     */
    protected function writePostData(bool $always = false): int
    {
        return 0;
    }

    public function assessment(): void
    {
        $stats_table = new ilQuestionCumulatedStatisticsTableGUI($this, 'assessment', '', $this->object, $this->questioninfo);
        $usage_table = new ilQuestionUsagesTableGUI($this, 'assessment', '', $this->object);

        $this->tpl->setContent(implode('<br />', array(
            $stats_table->getHTML(),
            $usage_table->getHTML()
        )));
    }

    /**
     * Creates a question gui representation and returns the alias to the question gui
     */
    public static function _getQuestionGUI(string $question_type = '', int $question_id = -1): ?assQuestionGUI
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        if (($question_type === '') && ($question_id > 0)) {
            $question_type = $DIC->testQuestionPool()->questionInfo()->getQuestionType($question_id);
        }

        if ($question_type === '') {
            return null;
        }

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
    public static function _getGUIClassNameForId($a_q_id): string
    {
        global $DIC;
        $q_type = $DIC->testQuestionPool()->questionInfo()->getQuestionType($a_q_id);
        $class_name = assQuestionGUI::_getClassNameForQType($q_type);
        return $class_name;
    }

    /**
     * @deprecated
     */
    public static function _getClassNameForQType($q_type): string
    {
        return $q_type . "GUI";
    }

    public function populateJavascriptFilesRequiredForWorkForm(ilGlobalTemplateInterface $tpl): void
    {
        foreach ($this->getPresentationJavascripts() as $jsFile) {
            $tpl->addJavaScript($jsFile);
        }
    }

    public function getPresentationJavascripts(): array
    {
        return array();
    }

    public function getQuestionTemplate(): void
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

    protected function renderEditForm(ilPropertyFormGUI $form): void
    {
        $this->getQuestionTemplate();
        $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
    }

    /**
     * Returns the ILIAS Page around a question
     */
    public function getILIASPage(string $html = ""): string
    {
        $page_gui = new ilAssQuestionPageGUI($this->object->getId());
        $page_gui->setQuestionHTML(
            [$this->object->getId() => $html]
        );
        $presentation = $page_gui->presentation();
        $presentation = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $presentation);
        return $presentation;
    }

    public function outQuestionPage($a_temp_var, $a_postponed = false, $active_id = "", $html = "", $inlineFeedbackEnabled = false): string
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
            $page_gui->setQuestionHTML(array($this->object->getId() => $html));
        }

        $page_gui->setPresentationTitle($this->questionHeaderBlockBuilder->getPresentationTitle());
        $page_gui->setQuestionInfoHTML($this->questionHeaderBlockBuilder->getQuestionInfoHTML());

        return $page_gui->presentation();
    }

    protected function getUseUnchangedAnswerCheckboxHtml(): string
    {
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->object->getTestPresentationConfig()->getUseUnchangedAnswerLabel());
        return $tpl->get();
    }

    protected function getPreviousSolutionProvidedMessage(): string
    {
        return $this->lng->txt('use_previous_solution_advice');
    }

    protected function getPreviousSolutionConfirmationCheckboxHtml(): string
    {
        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->lng->txt('use_previous_solution'));
        return $tpl->get();
    }

    public function cancel(): void
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

    public function originalSyncForm(string $return_to = "", string $return_to_feedback = ''): void
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

    public function sync(): void
    {
        $original_id = $this->object->getOriginalId();
        if ($original_id !== null) {
            $this->object->syncWithOriginal();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        }
        if ($this->request->raw("return_to") !== null) {
            $this->ctrl->redirect($this, $this->request->raw("return_to"));
        }
        if ($this->request->raw("return_to_fb") !== null) {
            $this->ctrl->redirectByClass(ilAssQuestionFeedbackEditingGUI::class, 'showFeedbackForm');
        }

        if ($this->request->raw('test_express_mode')) {
            $this->ctrl->redirectToURL(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
        }

        $this->ctrl->redirectByClass(ilAssQuestionPreviewGUI::class, ilAssQuestionPreviewGUI::CMD_SHOW);
    }

    public function cancelSync(): void
    {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        if ($this->request->raw("return_to") !== '' && $this->request->raw("return_to") !== null) {
            $this->ctrl->redirect($this, $this->request->raw("return_to"));
        }
        if ($this->request->raw('return_to_fb') !== '' && $this->request->raw('return_to_fb') !== null) {
            $this->ctrl->redirectByClass(ilAssQuestionFeedbackEditingGUI::class, 'showFeedbackForm');
        }
        if ($this->request->raw('test_express_mode')) {
            $this->ctrl->redirectToURL(ilTestExpressPage::getReturnToPageLink($this->object->getId()));
        }
        $this->ctrl->redirectByClass(ilAssQuestionPreviewGUI::class, ilAssQuestionPreviewGUI::CMD_SHOW);
    }

    public function saveEdit(): void
    {
        $ilUser = $this->ilUser;
        $result = $this->writePostData();
        if ($result == 0) {
            $ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
            $this->object->saveToDb();
            $originalexists = $this->questioninfo->questionExists($this->object->getOriginalId());

            if ($this->request->raw("calling_test") && $originalexists && assQuestion::_isWriteable($this->object->getOriginalId(), $ilUser->getId())) {
                $this->ctrl->redirect($this, "originalSyncForm");
            } elseif ($this->request->raw("calling_test")) {
                $_GET["ref_id"] = $this->request->raw("calling_test");
                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("calling_test"));
                return;
            } elseif ($this->request->raw("test_ref_id")) {
                // TODO: Courier Antipattern!
                $_GET["ref_id"] = $this->request->raw("test_ref_id");
                $test = new ilObjTest($this->request->raw("test_ref_id"), true);

                $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory(
                    $this->tree,
                    $this->db,
                    $this->lng,
                    $this->logger,
                    $this->component_repository,
                    $test,
                    $this->questioninfo
                );

                $test->insertQuestion($testQuestionSetConfigFactory->getQuestionSetConfig(), $this->object->getId());

                ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=" . $this->request->raw("test_ref_id"));
            } else {
                $this->ctrl->setParameter($this, "q_id", $this->object->getId());
                $this->editQuestion();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), false);
                $this->ctrl->setParameterByClass(ilAssQuestionPageGUI::class, "q_id", $this->object->getId());
                $this->ctrl->redirectByClass(ilAssQuestionPageGUI::class, "edit");
            }
        }
    }

    public function save(): void
    {
        $this->ilTabs->setTabActive('edit_question');
        $result = $this->writePostData();

        if ($result !== 0) {
            return;
        }

        $old_id = $this->request->int('q_id');

        $this->ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
        $this->ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
        $this->object->saveToDb();

        if ($this->object->getId() !== $old_id) {
            $this->callNewIdListeners($this->object->getId());
        }

        if ($this->request->int('calling_test') !== 0) {
            if (($q_id = $this->saveQuestionToTest()) === self::RETURN_AFTER_EXISTING_WITH_ORIGINAL_SAVE) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->setParameter($this, 'return_to', 'editQuestion');
                $this->ctrl->redirect($this, "originalSyncForm");
            }

            $this->ctrl->setParameter(
                $this,
                'q_id',
                $q_id === self::RETURN_AFTER_EXISTING_SAVE ? $this->object->getId() : $q_id
            );
            $this->ctrl->setParameter($this, 'ref_id', $this->request->raw('calling_test'));
            $this->ctrl->setParameter($this, 'calling_test', $this->request->raw('calling_test'));
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, 'editQuestion');
    }

    public function saveReturn(): void
    {
        $this->ilTabs->setTabActive('edit_question');
        $result = $this->writePostData();
        if ($result !== 0) {
            return;
        }

        $old_id = $this->request->getQuestionId();

        $this->ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
        $this->ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
        $this->object->saveToDb();

        if ($this->object->getId() !== $old_id) {
            $this->callNewIdListeners($this->object->getId());
        }

        if ($this->request->int('calling_test') !== 0
            && $this->saveQuestionToTest() === self::RETURN_AFTER_EXISTING_WITH_ORIGINAL_SAVE) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->setParameter($this, 'test_express_mode', $this->request->raw('test_express_mode'));
            $this->ctrl->redirect($this, "originalSyncForm");
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
    }

    private function saveQuestionToTest(): int
    {
        $originalexists = !is_null($this->object->getOriginalId())
                && $this->questioninfo->questionExistsInPool($this->object->getOriginalId());

        if ($originalexists
            && assQuestion::_isWriteable($this->object->getOriginalId(), $this->ilUser->getId())) {
            return self::RETURN_AFTER_EXISTING_WITH_ORIGINAL_SAVE;
        }

        $test = new ilObjTest($this->request->raw("calling_test"), true);
        if (assQuestion::_questionExistsInTest($this->object->getId(), $test->getTestId())) {
            return self::RETURN_AFTER_EXISTING_SAVE;
        }

        $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $test,
            $this->questioninfo
        );

        $new_q_id = $this->object->getId();
        if ($test->getRefId() !== $this->request->int('ref_id')) {
            $new_q_id = $this->object->duplicate(true, $this->object->getTitle(), $this->object->getAuthor(), $this->object->getOwner(), $test->getId());
        }

        $test->insertQuestion(
            $testQuestionSetConfigFactory->getQuestionSetConfig(),
            $new_q_id,
            true
        );

        if ($this->request->isset('prev_qid')) {
            $test->moveQuestionAfter($new_q_id, $this->request->raw('prev_qid'));
        }

        $this->ctrl->setParameter($this, 'calling_test', $this->request->raw("calling_test"));
        return $new_q_id;
    }

    public function apply(): void
    {
        $this->writePostData();
        $this->object->saveToDb();
        $this->ctrl->setParameter($this, "q_id", $this->object->getId());
        $this->editQuestion();
    }

    /**
     * get context path in content object tree
     */
    public function getContextPath($cont_obj, int $a_endnode_id, int $a_startnode_id = 1): string
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

    public function setSequenceNumber(int $nr): void
    {
        $this->sequence_no = $nr;
    }

    public function getSequenceNumber(): int
    {
        return $this->sequence_no;
    }

    public function setQuestionCount(int $a_question_count): void
    {
        $this->question_count = $a_question_count;
    }

    public function getQuestionCount(): int
    {
        return $this->question_count;
    }

    public function getErrorMessage(): string
    {
        return $this->errormessage;
    }

    public function setErrorMessage(string $errormessage): void
    {
        $this->errormessage = $errormessage;
    }

    public function addErrorMessage(string $errormessage): void
    {
        $this->errormessage .= ((strlen($this->errormessage)) ? "<br />" : "") . $errormessage;
    }

    /** Why are you here? Some magic for plugins? */
    public function outAdditionalOutput(): void
    {
    }

    public function getQuestionType(): string
    {
        return $this->object->getQuestionType();
    }

    public function getAsValueAttribute(string $a_value): string
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
    public function addNewIdListener($a_object, string $a_method, string $a_parameters = ""): void
    {
        $cnt = $this->new_id_listener_cnt;
        $this->new_id_listeners[$cnt]["object"] = &$a_object;
        $this->new_id_listeners[$cnt]["method"] = $a_method;
        $this->new_id_listeners[$cnt]["parameters"] = $a_parameters;
        $this->new_id_listener_cnt++;
    }

    public function callNewIdListeners(int $a_new_id): void
    {
        for ($i = 0; $i < $this->new_id_listener_cnt; $i++) {
            $this->new_id_listeners[$i]["parameters"]["new_id"] = $a_new_id;
            $object = &$this->new_id_listeners[$i]["object"];
            $method = $this->new_id_listeners[$i]["method"];
            $parameters = $this->new_id_listeners[$i]["parameters"];
            $object->$method($parameters);
        }
    }

    public function addQuestionFormCommandButtons(ilPropertyFormGUI $form): void
    {
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        }
        $form->addCommandButton("save", $this->lng->txt("save"));
    }

    public function addBasicQuestionFormProperties(ilPropertyFormGUI $form): void
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
            $author->setMaxLength(512);
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
            if ($this->object->getAdditionalContentEditingMode() != assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE) {
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
        $this->addNumberOfTriesToFormIfNecessary($form);
    }

    protected function addNumberOfTriesToFormIfNecessary(ilPropertyFormGUI $form)
    {
        if (!$this->object->getSelfAssessmentEditingMode()) {
            return;
        }

        $nr_tries = $this->object->getNrOfTries() ?? $this->object->getDefaultNrOfTries();

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

    protected function saveTaxonomyAssignments(): void
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

    protected function populateTaxonomyFormSection(ilPropertyFormGUI $form): void
    {
        if ($this->getTaxonomyIds() !== []) {
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
    public function getGenericFeedbackOutput(int $active_id, ?int $pass): string
    {
        $output = '';
        $manual_feedback = ilObjTest::getManualFeedback($active_id, $this->object->getId(), $pass);
        if ($manual_feedback !== '') {
            return $manual_feedback;
        }

        $correct_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true);
        $incorrect_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false);
        if ($correct_feedback . $incorrect_feedback !== '') {
            $output = $this->genericFeedbackOutputBuilder($correct_feedback, $incorrect_feedback, $active_id, $pass);
        }

        if ($this->object->isAdditionalContentEditingModePageObject()) {
            return $output;
        }
        return ilLegacyFormElementsUtil::prepareTextareaOutput($output, true);
    }

    protected function genericFeedbackOutputBuilder(
        string $feedback_correct,
        string $feedback_incorrect,
        int $active_id,
        ?int $pass
    ): string {
        if ($pass === null) {
            return '';
        }
        $reached_points = $this->object->calculateReachedPoints($active_id, $pass);
        $max_points = $this->object->getMaximumPoints();
        if ($reached_points == $max_points) {
            return $feedback_correct;
        }

        return $feedback_incorrect;
    }

    public function getGenericFeedbackOutputForCorrectSolution(): string
    {
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true),
            true
        );
    }

    public function getGenericFeedbackOutputForIncorrectSolution(): string
    {
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false),
            true
        );
    }

    /**
     * Returns the answer specific feedback for the question
     * @param array $userSolution ($userSolution[<value1>] = <value2>)
     */
    abstract public function getSpecificFeedbackOutput(array $userSolution): string;

    public function outQuestionType(): string
    {
        $count = $this->questioninfo->usageNumber($this->object->getId());

        if ($this->questioninfo->questionExistsInPool($this->object->getId()) && $count) {
            if ($this->rbacsystem->checkAccess("write", $this->request->getRefId())) {
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("qpl_question_is_in_use"), $count));
            }
        }

        return $this->questioninfo->getQuestionTypeName($this->object->getId());
    }

    protected function getTypeOptions(): array
    {
        foreach (assQuestionSuggestedSolution::TYPES as $k => $v) {
            $options[$k] = $this->lng->txt($v);
        }
        return $options;
    }

    public function suggestedsolution(): void
    {
        $ilUser = $this->ilUser;
        $ilAccess = $this->access;

        $cmd = $this->request->raw('cmd');
        $save = is_array($cmd) && array_key_exists('saveSuggestedSolution', $cmd);
        if ($save && $this->request->int('deleteSuggestedSolution') === 1) {
            $this->object->deleteSuggestedSolutions();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "suggestedsolution");
        }

        $output = "";

        $solution = $this->object->getSuggestedSolution(0);
        $options = $this->getTypeOptions();

        $solution_type = $this->request->raw('solutiontype');
        if (is_string($solution_type) && strcmp($solution_type, "file") == 0
            && (!$solution || $solution->getType() !== assQuestionSuggestedSolution::TYPE_FILE)
        ) {
            $solution = $this->getSuggestedSolutionsRepo()->create(
                $this->object->getId(),
                assQuestionSuggestedSolution::TYPE_FILE
            );
        }

        $solution_filename = $this->request->raw('filename');
        if ($save &&
            is_string($solution_filename) &&
            strlen($solution_filename)) {
            $solution = $solution->withTitle($solution_filename);
        }

        if ($solution) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt("solution_hint"));
            $form->setMultipart(true);
            $form->setTableWidth("100%");
            $form->setId("suggestedsolutiondisplay");

            $title = new ilSolutionTitleInputGUI($this->lng->txt("showSuggestedSolution"), "solutiontype");
            $template = new ilTemplate("tpl.il_as_qpl_suggested_solution_input_presentation.html", true, true, "Modules/TestQuestionPool");

            if ($solution->isOfTypeLink()) {
                $href = assQuestion::_getInternalLinkHref($solution->getInternalLink());
                $template->setCurrentBlock("preview");
                $template->setVariable("TEXT_SOLUTION", $this->lng->txt("suggested_solution"));
                $template->setVariable("VALUE_SOLUTION", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("view") . "</a> ");
                $template->parseCurrentBlock();
            } elseif (
                $solution->isOfTypeFile()
                && $solution->getFilename()
            ) {
                $href = $this->object->getSuggestedSolutionPathWeb() . $solution->getFilename();
                $link = " <a href=\"$href\" target=\"content\">"
                    . ilLegacyFormElementsUtil::prepareFormOutput($solution->getTitle())
                    . "</a> ";
                $template->setCurrentBlock("preview");
                $template->setVariable("TEXT_SOLUTION", $this->lng->txt("suggested_solution"));
                $template->setVariable("VALUE_SOLUTION", $link);
                $template->parseCurrentBlock();
            }

            $template->setVariable("TEXT_TYPE", $this->lng->txt("type"));
            $template->setVariable("VALUE_TYPE", $options[$solution->getType()]);

            $title->setHtml($template->get());
            $deletesolution = new ilCheckboxInputGUI("", "deleteSuggestedSolution");
            $deletesolution->setOptionTitle($this->lng->txt("deleteSuggestedSolution"));
            $title->addSubItem($deletesolution);
            $form->addItem($title);

            if ($solution->isOfTypeFile()) {
                $file = new ilFileInputGUI($this->lng->txt("fileDownload"), "file");
                $file->setRequired(true);
                $file->enableFileNameSelection("filename");

                //$file->setSuffixes(array("doc","xls","png","jpg","gif","pdf"));
                if ($_FILES && $_FILES["file"]["tmp_name"] && $file->checkInput()) {
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
                        if ($solution->getFilename()) {
                            @unlink($this->object->getSuggestedSolutionPath() . $solution->getFilename());
                        }

                        $file->setValue($_FILES["file"]["name"]);
                        $solution = $solution
                            ->withFilename($_FILES["file"]["name"])
                            ->withMime($_FILES["file"]["type"])
                            ->withSize($_FILES["file"]["size"])
                            ->withTitle($_POST["filename"]);

                        $this->getSuggestedSolutionsRepo()->update([$solution]);

                        $originalexists = $this->object->getOriginalId() &&
                            $this->questioninfo->questionExistsInPool($this->object->getOriginalId());
                        if ($this->request->raw("calling_test") && $originalexists
                            && assQuestion::_isWriteable($this->object->getOriginalId(), $ilUser->getId())) {
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
                    if ($solution->getFilename()) {
                        $file->setValue($solution->getFilename());
                        $file->setFilename($solution->getTitle());
                    }
                }
                $form->addItem($file);
                $hidden = new ilHiddenInputGUI("solutiontype");
                $hidden->setValue("file");
                $form->addItem($hidden);
            }
            if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
                $form->addCommandButton('cancelSuggestedSolution', $this->lng->txt('cancel'));
                $form->addCommandButton('saveSuggestedSolution', $this->lng->txt('save'));
            }

            if ($save) {
                if ($form->checkInput()) {
                    if ($solution->isOfTypeFile()) {
                        $solution = $solution->withTitle($_POST["filename"]);
                    }

                    if (!$solution->isOfTypeLink()) {
                        $this->getSuggestedSolutionsRepo()->update([$solution]);
                    }

                    $originalexists = !is_null($this->object->getOriginalId()) &&
                        $this->questioninfo->questionExistsInPool($this->object->getOriginalId());
                    if ($this->request->raw("calling_test") && $originalexists
                        && assQuestion::_isWriteable($this->object->getOriginalId(), $ilUser->getId())) {
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

        $savechange = $this->ctrl->getCmd() === "saveSuggestedSolutionType";

        $changeoutput = "";
        if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
            $formchange = new ilPropertyFormGUI();
            $formchange->setFormAction($this->ctrl->getFormAction($this));

            $title = $solution ? $this->lng->txt("changeSuggestedSolution") : $this->lng->txt("addSuggestedSolution");
            $formchange->setTitle($title);
            $formchange->setMultipart(false);
            $formchange->setTableWidth("100%");
            $formchange->setId("suggestedsolution");

            $solutiontype = new ilRadioGroupInputGUI($this->lng->txt("suggestedSolutionType"), "solutiontype");
            foreach ($options as $opt_value => $opt_caption) {
                $solutiontype->addOption(new ilRadioOption($opt_caption, $opt_value));
            }
            if ($solution) {
                $solutiontype->setValue($solution->getType());
            }
            $solutiontype->setRequired(true);
            $formchange->addItem($solutiontype);

            $formchange->addCommandButton("saveSuggestedSolutionType", $this->lng->txt("select"));

            if ($savechange) {
                $formchange->checkInput();
            }
            $changeoutput = $formchange->getHTML();
        }

        $this->tpl->setVariable("ADM_CONTENT", $changeoutput . $output);
    }

    public function outSolutionExplorer(): void
    {
        $type = $this->request->raw("link_new_type");
        $search = $this->request->raw("search_link_type");
        $this->ctrl->setParameter($this, "link_new_type", $type);
        $this->ctrl->setParameter($this, "search_link_type", $search);
        $this->ctrl->saveParameter($this, array("subquestion_index", "link_new_type", "search_link_type"));

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("select_object_to_link"));

        $parent_ref_id = $this->tree->getParentId($this->request->getRefId());
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

    public function saveSuggestedSolutionType(): void
    {
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

    public function cancelExplorer(): void
    {
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function outPageSelector(): void
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

    public function outChapterSelector(): void
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

    public function outGlossarySelector(): void
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

    protected function createSuggestedSolutionLinkingTo(string $type, string $target)
    {
        $repo = $this->getSuggestedSolutionsRepo();
        $question_id = $this->object->getId();
        $subquestion_index = ($this->request->raw("subquestion_index") > 0) ? $this->request->raw("subquestion_index") : 0;

        $solution = $repo->create($question_id, $type)
            ->withSubquestionIndex($subquestion_index)
            ->withInternalLink($target);

        $repo->update([$solution]);
    }

    public function linkChilds(): void
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
                $target = "il__lm_" . $this->request->raw("source_id");
                $this->createSuggestedSolutionLinkingTo('lm', $target);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
                $this->ctrl->redirect($this, "suggestedsolution");
                break;
        }
    }

    public function addPG(): void
    {
        $target = "il__pg_" . $this->request->raw("pg");
        $this->createSuggestedSolutionLinkingTo('pg', $target);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addST(): void
    {
        $target = "il__st_" . $this->request->raw("st");
        $this->createSuggestedSolutionLinkingTo('st', $target);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function addGIT(): void
    {
        $target = "il__git_" . $this->request->raw("git");
        $this->createSuggestedSolutionLinkingTo('git', $target);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("suggested_solution_added_successfully"), true);
        $this->ctrl->redirect($this, "suggestedsolution");
    }

    public function isSaveCommand(): bool
    {
        return in_array($this->ctrl->getCmd(), array('save', 'saveEdit', 'saveReturn'));
    }

    public static function getCommandsFromClassConstants(
        string $guiClassName,
        string $cmdConstantNameBegin = 'CMD_'
    ): array {
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

    public function setQuestionTabs(): void
    {
        $this->ilTabs->clearTargets();

        $this->setDefaultTabs($this->ilTabs);
        $this->setQuestionSpecificTabs($this->ilTabs);
        $this->addBackTab($this->ilTabs);
    }

    protected function setDefaultTabs(ilTabsGUI $ilTabs): void
    {
        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $this->request->getQuestionId());
        $q_type = $this->object->getQuestionType();

        if (strlen($q_type)) {
            $classname = $q_type . "GUI";
            $this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
            $this->ctrl->setParameterByClass(strtolower($classname), "q_id", $this->request->getQuestionId());
        }

        if ($this->request->isset("q_id")) {
            $this->addTab_Question($ilTabs);
        }

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($ilTabs);

        // add tab for question's suggested solution within common class assQuestionGUI
        $this->addTab_SuggestedSolution($ilTabs, $classname);

        $this->addBackTab($ilTabs);
    }

    protected function setQuestionSpecificTabs(ilTabsGUI $ilTabs): void
    {
    }

    public function addTab_SuggestedSolution(ilTabsGUI $tabs, string $classname): void
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

    final public function getEditQuestionTabCommands(): array
    {
        return array_merge($this->getBasicEditQuestionTabCommands(), $this->getAdditionalEditQuestionCommands());
    }

    protected function getBasicEditQuestionTabCommands(): array
    {
        return array('editQuestion', 'save', 'saveEdit', 'originalSyncForm');
    }

    protected function getAdditionalEditQuestionCommands(): array
    {
        return array();
    }

    protected function addTab_QuestionFeedback(ilTabsGUI $tabs): void
    {
        $tabCommands = self::getCommandsFromClassConstants('ilAssQuestionFeedbackEditingGUI');

        $tabLink = $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);

        $tabs->addTarget('feedback', $tabLink, $tabCommands, $this->ctrl->getCmdClass(), '');
    }

    protected function addTab_QuestionHints(ilTabsGUI $tabs): void
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

    protected function addTab_Question(ilTabsGUI $tabsGUI): void
    {
        $tabsGUI->addTarget(
            'edit_question',
            $this->ctrl->getLinkTargetByClass(
                array('ilrepositorygui','ilobjquestionpoolgui', get_class($this)),
                'editQuestion'
            ),
            'editQuestion',
            '',
            '',
            false
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
    ): string;

    protected function hasCorrectSolution($activeId, $passIndex): bool
    {
        $reachedPoints = $this->object->getAdjustedReachedPoints((int) $activeId, (int) $passIndex, true);
        $maximumPoints = $this->object->getMaximumPoints();

        return $reachedPoints == $maximumPoints;
    }

    public function isAutosaveable(): bool
    {
        return $this->object instanceof ilAssQuestionAutosaveable;
    }

    protected function writeQuestionGenericPostData(): void
    {
        $this->object->setTitle($_POST["title"]);
        $this->object->setAuthor($_POST["author"]);
        $this->object->setComment($_POST["comment"] ?? '');
        if ($this->object->getSelfAssessmentEditingMode()) {
            $this->object->setNrOfTries((int) ($_POST['nr_of_tries'] ?? 0));
        }

        try {
            $lifecycle = ilAssQuestionLifecycle::getInstance($_POST['lifecycle']);
            $this->object->setLifecycle($lifecycle);
        } catch (ilTestQuestionPoolInvalidArgumentException $e) {
        }

        $this->object->setQuestion(ilUtil::stripOnlySlashes($_POST['question']));
    }

    // TODO: OWN "PASS" IN THE REFACTORING getPreview
    abstract public function getPreview($show_question_only = false, $showInlineFeedback = false);

    final public function outQuestionForTest(
        string $formaction,
        int $active_id,
        ?int $pass,
        bool $is_question_postponed = false,
        $user_post_solutions = false,
        bool $show_specific_inline_feedback = false
    ): void {
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

    public function magicAfterTestOutput(): void
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

    public function getFormEncodingType(): string
    {
        return self::FORM_ENCODING_URLENCODE;
    }

    protected function addBackTab(ilTabsGUI $ilTabs): void
    {
        $this->ctrl->saveParameterByClass(ilAssQuestionPreviewGUI::class, 'prev_qid');
        $ilTabs->setBackTarget(
            $this->lng->txt('backtocallingpage'),
            $this->ctrl->getLinkTargetByClass(ilAssQuestionPreviewGUI::class, ilAssQuestionPreviewGUI::CMD_SHOW)
        );
    }

    public function setPreviewSession(ilAssQuestionPreviewSession $previewSession): void
    {
        $this->previewSession = $previewSession;
    }

    /**
     * @return ilAssQuestionPreviewSession|null
     */
    public function getPreviewSession(): ?ilAssQuestionPreviewSession
    {
        return $this->previewSession;
    }

    protected function buildBasicEditFormObject(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setId($this->getType());
        $form->setTitle($this->outQuestionType());
        $form->setTableWidth('100%');
        $form->setMultipart(true);
        return $form;
    }

    public function showHints(): void
    {
        $this->ctrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
    }

    protected function escapeTemplatePlaceholders(string $text): string
    {
        return str_replace(['{','}'], ['&#123;','&#125;'], $text);
    }

    protected function buildEditForm(): ilPropertyFormGUI
    {
        $this->editQuestion(true); // TODO bheyser: editQuestion should be added to the abstract base class with a unified signature
        return $this->editForm;
    }

    public function buildFocusAnchorHtml(): string
    {
        return '<div id="focus"></div>';
    }

    public function isAnswerFrequencyStatisticSupported(): bool
    {
        return true;
    }

    public function getSubQuestionsIndex(): array
    {
        return array(0);
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
    {
        return array();
    }

    public function getAnswerFrequencyTableGUI($parentGui, $parentCmd, $relevantAnswers, $questionIndex): ilAnswerFrequencyStatisticTableGUI
    {
        $table = new ilAnswerFrequencyStatisticTableGUI($parentGui, $parentCmd, $this->object);
        $table->setQuestionIndex($questionIndex);
        $table->setData($this->getAnswersFrequency($relevantAnswers, $questionIndex));
        $table->initColumns();
        return $table;
    }

    public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form): void
    {
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
    }

    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
    }


    protected function generateCorrectnessIconsForCorrectness(int $correctness): string
    {
        switch ($correctness) {
            case self::CORRECTNESS_NOT_OK:
                $icon_name = 'standard/icon_not_ok.svg';
                $label = $this->lng->txt("answer_is_wrong");
                break;
            case self::CORRECTNESS_MOSTLY_OK:
                $icon_name = 'standard/icon_ok.svg';
                $label = $this->lng->txt("answer_is_not_correct_but_positive");
                break;
            case self::CORRECTNESS_OK:
                $icon_name = 'standard/icon_ok.svg';
                $label = $this->lng->txt("answer_is_right");
                break;
            default:
                return '';
        }
        $path = ilUtil::getImagePath($icon_name);
        $icon = $this->ui->factory()->symbol()->icon()->custom(
            $path,
            $label
        );
        return $this->ui->renderer()->render($icon);
    }

    /**
     * Prepares a string for a text area output where latex code may be in it
     * If the text is HTML-free, CHR(13) will be converted to a line break
     *
     * @param string $txt_output String which should be prepared for output
     * @access public
     *
     */
    public static function prepareTextareaOutput(
        ?string $txt_output,
        bool $prepare_for_latex_output = false,
        bool $omitNl2BrWhenTextArea = false
    ): string {
        if ($txt_output === null || $txt_output === '') {
            return '';
        }

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

    protected ?assQuestionSuggestedSolutionsDatabaseRepository $suggestedsolution_repo = null;
    protected function getSuggestedSolutionsRepo(): assQuestionSuggestedSolutionsDatabaseRepository
    {
        if (is_null($this->suggestedsolution_repo)) {
            $dic = ilQuestionPoolDIC::dic();
            $this->suggestedsolution_repo = $dic['question.repo.suggestedsolutions'];
        }
        return $this->suggestedsolution_repo;
    }

    /**
     * sk - 12.05.2023: This is one more of those that we need, but don't want.
     * @deprecated
     */
    protected function cleanupAnswerText(array $answer_text, bool $is_rte): array
    {
        if (!is_array($answer_text)) {
            return [];
        }

        if ($is_rte) {
            return ilArrayUtil::stripSlashesRecursive(
                $answer_text,
                false,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );
        }

        return ilArrayUtil::stripSlashesRecursive(
            $answer_text,
            true,
            self::ALLOWED_PLAIN_TEXT_TAGS
        );
    }

    public function isInLearningModuleContext(): bool
    {
        return $this->parent_type_is_lm;
    }
    public function setInLearningModuleContext(bool $flag): void
    {
        $this->parent_type_is_lm = $flag;
    }

    protected function addSaveOnEnterOnLoadCode(): void
    {
        $this->tpl->addOnloadCode("
            let form = document.querySelector('#ilContentContainer form');
            let button = form.querySelector('input[name=\"cmd[save]\"]');
            if (form && button) {
                form.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter'
                        && e.target.type !== 'textarea'
                        && e.target.type !== 'submit'
                        && e.target.type !== 'file'
                    ) {
                        e.preventDefault();
                        form.requestSubmit(button);
                    }
                })
            }
        ");
    }
}
