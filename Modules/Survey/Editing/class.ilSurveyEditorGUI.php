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

use ILIAS\Survey\Editing\EditManager;
use ILIAS\Survey\Editing\EditingGUIRequest;

/**
 * Class ilSurveyEditorGUI
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilSurveyEditorGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
 * @ilCtrl_Calls ilSurveyEditorGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
 * @ilCtrl_Calls ilSurveyEditorGUI: SurveyMatrixQuestionGUI, ilSurveyPageEditGUI
 */
class ilSurveyEditorGUI
{
    protected \ILIAS\Survey\PrintView\GUIService $print;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\DI\UIServices $ui;
    protected string $requested_pgov;
    protected EditingGUIRequest $request;
    protected EditManager $edit_manager;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilObjSurveyGUI $parent_gui;
    protected ilObjSurvey $object;
    protected array $print_options;

    public function __construct(ilObjSurveyGUI $a_parent_gui)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $this->parent_gui = $a_parent_gui;
        /** @var ilObjSurvey $survey */
        $survey = $this->parent_gui->getObject();
        $this->object = $survey;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;

        $this->ctrl->saveParameter($this, array("pgov", "pgov_pos"));

        $this->print_options = array(
            //0 => $this->lng->txt('none'),
            ilObjSurvey::PRINT_HIDE_LABELS => $this->lng->txt('svy_print_hide_labels'),
            //2 => $this->lng->txt('svy_print_label_only'),
            ilObjSurvey::PRINT_SHOW_LABELS => $this->lng->txt('svy_print_show_labels')
        );
        $this->edit_manager = $DIC->survey()
            ->internal()
            ->domain()
            ->edit();
        $this->request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        $this->requested_pgov = $this->request->getTargetPosition();
        $this->ui = $DIC->ui();
        $this->http = $DIC->http();
        $this->print = $DIC->survey()
            ->internal()
            ->gui()
            ->print();
    }

    public function setRequestedPgov(string $pgov): void
    {
        $this->requested_pgov = $pgov;
    }

    public function executeCommand(): void
    {
        $ilTabs = $this->tabs;

        $cmd = $this->ctrl->getCmd("questions");

        if ($this->requested_pgov !== "") {
            if ($cmd === "questions") {
                $this->ctrl->setCmdClass("ilSurveyPageEditGUI");
                $this->ctrl->setCmd("renderpage");
            } elseif ($cmd === "confirmRemoveQuestions") {
                // #14324
                $this->ctrl->setCmdClass("ilSurveyPageEditGUI");
                $this->ctrl->setCmd("confirmRemoveQuestions");
            }
        }

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case 'ilsurveypageeditgui':
                $this->questionsSubtabs("page");
                $pg = new ilSurveyPageEditGUI($this->object, $this);
                $this->ctrl->forwardCommand($pg);
                break;

            default:
                // question gui
                if (stripos($next_class, "questiongui") !== false) {
                    $ilTabs->clearTargets();
                    $this->ctrl->saveParameter($this, array("new_for_survey"));
                    $q_gui = SurveyQuestionGUI::_getQuestionGUI(
                        null,
                        $this->request->getQuestionId()
                    );
                    if (is_object($q_gui->object)) {
                        $ilHelp = $this->help;
                        $ilHelp->setScreenIdComponent("spl_qt" . $q_gui->object->getQuestionTypeId());
                    }
                    // $q_gui->object->setObjId($this->object->getId());
                    $q_gui->setBackUrl($this->ctrl->getLinkTarget($this, "questions"));
                    $q_gui->setQuestionTabs();
                    $this->ctrl->forwardCommand($q_gui);

                    if (!$this->request->getNewForSurvey()) {
                        // not on create
                        $this->tpl->setTitle($this->lng->txt("question") . ": " . $q_gui->object->getTitle());
                    }
                } else {
                    $cmd .= "Object";
                    $this->$cmd();
                }
                break;
        }
    }

    protected function questionsSubtabs(
        string $a_cmd
    ): void {
        $ilTabs = $this->tabs;

        if ($a_cmd === "questions" && $this->requested_pgov !== "") {
            $a_cmd = "page";
        }

        $ilTabs->addSubTab(
            "page",
            $this->lng->txt("survey_per_page_view"),
            $this->ctrl->getLinkTargetByClass("ilSurveyPageEditGUI", "renderPage")
        );

        $this->ctrl->setParameter($this, "pgov", "");
        $ilTabs->addSubTab(
            "questions",
            $this->lng->txt("survey_question_editor"),
            $this->ctrl->getLinkTarget($this, "questions")
        );
        $this->ctrl->setParameter($this, "pgov", $this->requested_pgov);

        if ($this->object->getSurveyPages()) {
            if ($a_cmd === "page") {
                $this->ctrl->setParameterByClass("ilsurveyexecutiongui", "pgov", max(1, $this->request->getPage()));
            }
            $this->ctrl->setParameterByClass("ilsurveyexecutiongui", "prvw", 1);
            $ilTabs->addSubTab(
                "preview",
                $this->lng->txt("preview"),
                $this->ctrl->getLinkTargetByClass(array("ilobjsurveygui", "ilsurveyexecutiongui"), "preview")
            );
        }

        $ilTabs->activateSubTab($a_cmd);
    }


    //
    // QUESTIONS BROWSER INCL. MULTI-ACTIONS
    //

    public function questionsObject(): void
    {
        $ilToolbar = $this->toolbar;
        $ilUser = $this->user;

        // insert new questions?
        if ($this->request->getNewId() > 0) {
            // add a question to the survey previous created in a questionpool
            $existing = $this->object->getExistingQuestions();
            if (!in_array($this->request->getNewId(), $existing)) {
                $inserted = $this->object->insertQuestion($this->request->getNewId());
                if (!$inserted) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("survey_error_insert_incomplete_question"));
                }
            }
        }
        $this->questionsSubtabs("questions");

        $hasDatasets = ilObjSurvey::_hasDatasets($this->object->getSurveyId());
        $read_only = $hasDatasets;

        // toolbar
        if (!$read_only) {
            $qtypes = array();
            foreach (ilObjSurveyQuestionPool::_getQuestiontypes() as $translation => $data) {
                $qtypes[$data["type_tag"]] = $translation;
            }

            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
            $types = new ilSelectInputGUI($this->lng->txt("create_new"), "sel_question_types");
            $types->setOptions($qtypes);
            $ilToolbar->addStickyItem($types, "");

            $button = ilSubmitButton::getInstance();
            $button->setCaption("svy_create_question");
            $button->setCommand("createQuestion");
            $ilToolbar->addStickyItem($button);

            if ($this->object->getPoolUsage()) {
                $cmd = ((int) $ilUser->getPref('svy_insert_type') === 1 ||
                    ($ilUser->getPref('svy_insert_type') ?? '') === '')
                    ? 'browseForQuestions'
                    : 'browseForQuestionblocks';

                $button = ilLinkButton::getInstance();
                $button->setCaption("browse_for_questions");
                $button->setUrl($this->ctrl->getLinkTarget($this, $cmd));
                $ilToolbar->addStickyItem($button);
            }

            $ilToolbar->addSeparator();

            $button = ilLinkButton::getInstance();
            $button->setCaption("add_heading");
            $button->setUrl($this->ctrl->getLinkTarget($this, "addHeading"));
            $ilToolbar->addInputItem($button);

            $ilToolbar->addSeparator();
            $print_view = $this->print->list($this->object->getRefId());
            $modal_elements = $print_view->getModalElements(
                $this->ctrl->getLinkTarget(
                    $this,
                    "printListViewSelection"
                )
            );
            $ilToolbar->addComponent($modal_elements->button);
            $ilToolbar->addComponent($modal_elements->modal);
        }
        $mess = "";
        if ($hasDatasets) {
            $mbox = new ilSurveyContainsDataMessageBoxGUI();
            $mess = $mbox->getHTML();
        }

        // table gui
        $table = new ilSurveyQuestionTableGUI(
            $this,
            "questions",
            $this->object,
            $read_only
        );
        $this->tpl->setContent($mess . $table->getHTML());
    }

    /**
     * Gather (and filter) selected items from table gui
     * @return array (questions, blocks, headings)
     */
    protected function gatherSelectedTableItems(
        bool $allow_blocks = true,
        bool $allow_questions = true,
        bool $allow_headings = false,
        bool $allow_questions_in_blocks = false
    ): array {
        $block_map = array();
        foreach ($this->object->getSurveyQuestions() as $item) {
            $block_map[$item["question_id"]] = $item["questionblock_id"];
        }

        $questions = $blocks = $headings = array();
        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            foreach ($ids as $key) {
                // questions
                if ($allow_questions && preg_match("/cb_(\d+)/", $key, $matches)) {
                    if (($allow_questions_in_blocks || !$block_map[$matches[1]]) &&
                        !in_array($block_map[$matches[1]], $blocks)) {
                        $questions[] = $matches[1];
                    }
                }
                // blocks
                if ($allow_blocks && preg_match("/cb_qb_(\d+)/", $key, $matches)) {
                    $blocks[] = $matches[1];
                }
                // headings
                if ($allow_headings && preg_match("/cb_tb_(\d+)/", $key, $matches)) {
                    $headings[] = $matches[1];
                }
            }
        }

        return array("questions" => $questions,
            "blocks" => $blocks,
            "headings" => $headings);
    }

    public function saveObligatoryObject(): void
    {
        $req_order = $this->request->getOrder();
        $req_block_order = $this->request->getBlockOrder();
        if (count($req_order) > 0) {
            $position = -1;
            $order = array();
            asort($req_order);
            foreach (array_keys($req_order) as $id) {
                // block items
                if (strpos($id, "qb_") === 0) {
                    $block_id = substr($id, 3);
                    $block = $req_block_order[$block_id];
                    asort($block);
                    foreach (array_keys($block) as $question_id) {
                        $position++;
                        $order[$question_id] = $position;
                    }
                } else {
                    $question_id = substr($id, 2);
                    $position++;
                    $order[$question_id] = $position;
                }
            }
            $this->object->updateOrder($order);
        }

        $obligatory = $this->request->getObligatory();
        $this->object->setObligatoryStates($obligatory);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, "questions");
    }

    public function unfoldQuestionblockObject(): void
    {
        $items = $this->gatherSelectedTableItems(true, false, false, false);
        if (count($items["blocks"])) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->object->unfoldQuestionblocks($items["blocks"]);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_unfold_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    public function moveQuestionsObject(): void
    {
        $items = $this->gatherSelectedTableItems(true, true, false, false);

        $move_questions = $items["questions"];
        foreach ($items["blocks"] as $block_id) {
            foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid) {
                $move_questions[] = $qid;
            }
        }
        if (count($move_questions) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_question_selected_for_move"), true);
            $this->ctrl->redirect($this, "questions");
        } else {
            $this->edit_manager->setMoveSurveyQuestions($this->object->getId(), $move_questions);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("select_target_position_for_move_question"));
            $this->questionsObject();
        }
    }

    public function insertQuestionsBeforeObject(): void
    {
        $this->insertQuestions(0);
    }

    public function insertQuestionsAfterObject(): void
    {
        $this->insertQuestions(1);
    }

    protected function insertQuestions(
        int $insert_mode
    ): void {
        $insert_id = null;
        $ids = $this->request->getIds();
        if (count($ids) > 0) {
            $items = $this->gatherSelectedTableItems(true, true, false, false);

            // we are using POST id for original order
            while (!$insert_id && count($ids) > 0) {
                $target = array_shift($ids);
                if (preg_match("/^cb_(\d+)$/", $target, $matches)) {
                    // questions in blocks are not allowed
                    if (in_array($matches[1], $items["questions"])) {
                        $insert_id = $matches[1];
                    }
                }
                if (!$insert_id && preg_match("/^cb_qb_(\d+)$/", $target, $matches)) {
                    $ids = $this->object->getQuestionblockQuestionIds($matches[1]);
                    if (count($ids)) {
                        if ($insert_mode === 0) {
                            $insert_id = $ids[0];
                        } elseif ($insert_mode === 1) {
                            $insert_id = $ids[count($ids) - 1];
                        }
                    }
                }
            }
        }

        if (!$insert_id) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_target_selected_for_move"), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            if ($this->edit_manager->getMoveSurveyId() !== $this->object->getId()) {
                $this->object->moveQuestions(
                    $this->edit_manager->getMoveSurveyQuestions(),
                    $insert_id,
                    $insert_mode
                );
                $this->edit_manager->clearMoveSurveyQuestions();
            }
        }

        $this->ctrl->redirect($this, "questions");
    }

    public function removeQuestionsObject(): void
    {
        $items = $this->gatherSelectedTableItems(true, true, true, true);
        if (count($items["blocks"]) + count($items["questions"]) + count($items["headings"]) > 0) {
            $this->tpl->setOnScreenMessage('question', $this->lng->txt("remove_questions"));
            $this->removeQuestionsForm($items["blocks"], $items["questions"], $items["headings"]);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_question_selected_for_removal"), true);
            $this->ctrl->redirect($this, "questions");
        }
    }

    public function removeQuestionsForm(
        array $checked_questionblocks,
        array $checked_questions,
        array $checked_headings
    ): void {
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("survey_sure_delete_questions"));
        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
        $cgui->setCancel($this->lng->txt("cancel"), "questions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmRemoveQuestions");
        $surveyquestions = $this->object->getSurveyQuestions();
        foreach ($surveyquestions as $question_id => $data) {
            if (in_array($data["question_id"], $checked_questions)) {
                $type = SurveyQuestion::_getQuestionTypeName($data["type_tag"]);

                $cgui->addItem(
                    "q_id[]",
                    $data["question_id"],
                    $type . ": " . $data["title"]
                );
            } elseif ((in_array($data["questionblock_id"], $checked_questionblocks))) {
                $type = SurveyQuestion::_getQuestionTypeName($data["type_tag"]);

                $cgui->addItem(
                    "cb[" . $data["questionblock_id"] . "]",
                    $data["questionblock_id"],
                    $data["questionblock_title"] . " - " . $type . ": " . $data["title"]
                );
            } elseif (in_array($data["question_id"], $checked_headings)) {
                $cgui->addItem(
                    "heading[" . $data["question_id"] . "]",
                    $data["question_id"],
                    $data["heading"]
                );
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmRemoveQuestionsObject(): void
    {
        $checked_questions = $this->request->getQuestionIds();
        $checked_questionblocks = $this->request->getBlockIds();
        $checked_headings = $this->request->getHeadings();

        if (count($checked_questions) || count($checked_questionblocks)) {
            $this->object->removeQuestions($checked_questions, $checked_questionblocks);
        }
        if ($checked_headings) {
            foreach ($checked_headings as $q_id) {
                $this->object->saveHeading("", $q_id);
            }
        }
        $this->object->saveCompletionStatus();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("questions_removed"), true);
        $this->ctrl->redirect($this, "questions");
    }

    public function copyQuestionsToPoolObject(): void
    {
        $items = $this->gatherSelectedTableItems(true, true, false, true);

        // gather questions from blocks
        $copy_questions = $items["questions"];
        foreach ($items["blocks"] as $block_id) {
            foreach ($this->object->getQuestionblockQuestionIds($block_id) as $qid) {
                $copy_questions[] = $qid;
            }
        }
        $copy_questions = array_unique($copy_questions);

        // only if not already in pool
        if (count($copy_questions)) {
            foreach ($copy_questions as $idx => $question_id) {
                $question = ilObjSurvey::_instanciateQuestion($question_id);
                if ($question->getOriginalId()) {
                    unset($copy_questions[$idx]);
                }
            }
        }
        if (count($copy_questions) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_question_selected_for_copy_to_pool"), true);
            $this->ctrl->redirect($this, "questions");
        } else {
            $this->questionsSubtabs("questions");

            $form = new ilPropertyFormGUI();

            $form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));

            $ids = new ilHiddenInputGUI("question_ids");
            $ids->setValue(implode(";", $copy_questions));
            $form->addItem($ids);

            $questionpools = $this->object->getAvailableQuestionpools(false, false, true, "write");
            $pools = new ilSelectInputGUI($this->lng->txt("survey_copy_select_questionpool"), "sel_spl");
            $pools->setOptions($questionpools);
            $form->addItem($pools);

            $form->addCommandButton("executeCopyQuestionsToPool", $this->lng->txt("submit"));
            $form->addCommandButton("questions", $this->lng->txt("cancel"));

            $this->tpl->setContent($form->getHTML());
        }
    }

    public function executeCopyQuestionsToPoolObject(): void
    {
        $question_ids = $this->request->getQuestionIdsFromString();
        $pool_id = ilObject::_lookupObjId($this->request->getSelectedPool());

        foreach ($question_ids as $qid) {
            // create copy (== pool "original")
            $new_question = ilObjSurvey::_instanciateQuestion($qid);
            $new_question->setId();
            $new_question->setObjId($pool_id);
            $new_question->saveToDb();

            // link "source" (survey) to copy (pool)
            SurveyQuestion::_changeOriginalId($qid, $new_question->getId(), $pool_id);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("survey_copy_to_questionpool_success"), true);
        $this->ctrl->redirect($this, "questions");
    }


    //
    // QUESTION CREATION
    //

    public function createQuestionObject(
        ilPropertyFormGUI $a_form = null,
        $sel_question_types = null,
        string $pgov_pos = null
    ): ?ilPropertyFormGUI {
        if (!$this->object->getPoolUsage()) {
            $this->executeCreateQuestionObject(null, 1, $pgov_pos);
            return null;
        }

        if (!$a_form) {
            $this->questionsSubtabs("questions");
            $form = new ilPropertyFormGUI();

            if (is_null($sel_question_types)) {
                $sel_question_types = $this->request->getSelectedQuestionTypes();
            }
            $this->ctrl->setParameter($this, "sel_question_types", $sel_question_types);
            $form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
        } else {
            $form = $a_form;
        }

        $usage = new ilRadioGroupInputGUI($this->lng->txt("survey_pool_selection"), "usage");
        $usage->setRequired(true);
        $no_pool = new ilRadioOption($this->lng->txt("survey_no_pool"), 1);
        $usage->addOption($no_pool);
        $existing_pool = new ilRadioOption($this->lng->txt("survey_existing_pool"), 3);
        $usage->addOption($existing_pool);
        $new_pool = new ilRadioOption($this->lng->txt("survey_new_pool"), 2);
        $usage->addOption($new_pool);
        $form->addItem($usage);

        if ($this->edit_manager->getPoolChoice() > 0) {
            $usage->setValue($this->edit_manager->getPoolChoice());
        } else {
            // default: no pool
            $usage->setValue(1);
        }

        $questionpools = $this->object->getAvailableQuestionpools(false, true, true, "write");
        $pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_spl");
        $pools->setOptions($questionpools);
        $existing_pool->addSubItem($pools);

        $name = new ilTextInputGUI($this->lng->txt("spl_new"), "name_spl"); // #11740
        $name->setSize(50);
        $name->setMaxLength(50);
        $new_pool->addSubItem($name);

        if ($a_form) {
            return $a_form;
        }

        $form->addCommandButton("executeCreateQuestion", $this->lng->txt("submit"));
        $form->addCommandButton("questions", $this->lng->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
        return null;
    }

    public function executeCreateQuestionObject(
        ?string $q_type = null,
        ?int $pool_usage = null,
        ?string $pgov_pos = null
    ): void {
        $this->edit_manager->setPoolChoice($this->request->getPoolUsage());

        if (is_null($q_type)) {
            $q_type = $this->request->getSelectedQuestionTypes();
        }

        $pgov = $this->requested_pgov;
        if (is_null($pgov_pos)) {
            $pgov_pos = $this->request->getTargetQuestionPosition();
        }

        if (is_null($pool_usage)) {
            $pool_usage = $this->request->getPoolUsage();
        }

        $obj_id = 0;

        // no pool
        if ($pool_usage == 1) {
            $obj_id = $this->object->getId();
        }
        // existing pool
        elseif ($pool_usage == 3 && $this->request->getSelectedPool() > 0) {
            $obj_id = ilObject::_lookupObjId($this->request->getSelectedPool());
        }
        // new pool
        elseif ($pool_usage == 2 && $this->request->getPoolName() !== "") {
            $obj_id = $this->createQuestionPool($this->request->getPoolName());
        } else {
            if (!$pool_usage) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("err_no_pool_name"), true);
            }
            $this->ctrl->setParameter($this, "sel_question_types", $q_type);
            $this->ctrl->redirect($this, "createQuestion");
        }


        // create question and redirect to question form

        $q_gui = SurveyQuestionGUI::_getQuestionGUI($q_type);
        $q_gui->object->setObjId($obj_id); // survey/pool!
        $q_gui->object->createNewQuestion();
        $q_gui_class = get_class($q_gui);

        if ($pgov !== "") {
            $this->ctrl->setParameterByClass($q_gui_class, "pgov", $pgov);
            $this->ctrl->setParameterByClass($q_gui_class, "pgov_pos", $pgov_pos);
        }

        $this->ctrl->setParameterByClass($q_gui_class, "ref_id", $this->object->getRefId());
        $this->ctrl->setParameterByClass($q_gui_class, "new_for_survey", $this->object->getRefId());
        $this->ctrl->setParameterByClass($q_gui_class, "q_id", $q_gui->object->getId());
        $this->ctrl->setParameterByClass($q_gui_class, "sel_question_types", $q_gui->getQuestionType());
        $this->ctrl->redirectByClass($q_gui_class, "editQuestion");
    }

    protected function createQuestionPool($name = "dummy"): int
    {
        $tree = $this->tree;

        $parent_ref = $tree->getParentId($this->object->getRefId());

        $qpl = new ilObjSurveyQuestionPool();
        $qpl->setType("spl");
        $qpl->setTitle($name);
        $qpl->setDescription("");
        $qpl->create();
        $qpl->createReference();
        $qpl->putInTree($parent_ref);
        $qpl->setPermissions($parent_ref);
        $qpl->setOnline(1); // must be online to be available
        $qpl->saveToDb();

        return $qpl->getId();
    }


    //
    // ADD FROM POOL
    //

    protected function setBrowseForQuestionsSubtabs(): void
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilUser = $this->user;

        if ($this->requested_pgov === "") {
            $link = $this->ctrl->getLinkTarget($this, "questions");
        } else {
            $link = $this->ctrl->getLinkTargetByClass("ilSurveyPageEditGUI", "renderpage");
        }
        $ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $link);

        // type selector
        $types = new ilSelectInputGUI($this->lng->txt("display_all_available"), "datatype");
        $types->setOptions(array(
            1 => $this->lng->txt("questions"),
            2 => $this->lng->txt("questionblocks")
        ));
        $types->setValue($ilUser->getPref('svy_insert_type'));
        $ilToolbar->addInputItem($types, true);
        $ilToolbar->addFormButton($this->lng->txt("change"), "changeDatatype");
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "changeDatatype"));
    }

    public function changeDatatypeObject(): void
    {
        $ilUser = $this->user;

        $ilUser->writePref('svy_insert_type', $this->request->getDataType());

        switch ($this->request->getDataType()) {
            case 2:
                $this->ctrl->redirect($this, 'browseForQuestionblocks');
                break;

            case 1:
            default:
                $this->ctrl->redirect($this, 'browseForQuestions');
                break;
        }
    }

    public function browseForQuestionsObject(): void
    {
        $this->setBrowseForQuestionsSubtabs();

        $table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object, true);
        $table_gui->setEditable(true);
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function filterQuestionBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object);
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, 'browseForQuestions');
    }

    public function resetfilterQuestionBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionbrowserTableGUI($this, 'browseForQuestions', $this->object);
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, 'browseForQuestions');
    }

    public function insertQuestionsObject(): void
    {
        $inserted_objects = 0;
        $page_gui = null;
        $qids = $this->request->getQuestionIds();
        if (count($qids) > 0) {
            if ($this->requested_pgov !== "") {
                $page_gui = new ilSurveyPageEditGUI($this->object, $this);
                $page_gui->determineCurrentPage();

                // as target position is predefined, insert in reverse order
                $qids = array_reverse($qids);
            }
            foreach ($qids as $question_id) {
                if ($this->requested_pgov === "") {
                    $this->object->insertQuestion($question_id);
                } else {
                    // "pgov" must be set to 1 to land here
                    // target position in page (pgov_pos) is processed there
                    $page_gui->insertNewQuestion($question_id);
                }
                $inserted_objects++;
            }
        }
        if ($inserted_objects) {
            $this->object->saveCompletionStatus();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("questions_inserted"), true);
            if ($this->requested_pgov === "") {
                $this->ctrl->redirect($this, "questions");
            } else {
                $target_page = $this->requested_pgov;
                if (substr($this->request->getTargetQuestionPosition(), -1) === "c") {
                    // see ilSurveyPageEditGUI::insertNewQuestion()
                    if ((int) $this->request->getTargetQuestionPosition()) {
                        $target_page++;
                    } else {
                        $target_page = 1;
                    }
                }
                $this->ctrl->setParameterByClass("ilSurveyPageEditGUI", "pgov", $target_page);
                $this->ctrl->redirectByClass("ilSurveyPageEditGUI", "renderpage");
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("insert_missing_question"), true);
            $this->ctrl->redirect($this, 'browseForQuestions');
        }
    }

    public function browseForQuestionblocksObject(): void
    {
        $this->setBrowseForQuestionsSubtabs();

        $table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object, true);
        $table_gui->setEditable(true);
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function filterQuestionblockBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object);
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, 'browseForQuestionblocks');
    }

    public function resetfilterQuestionblockBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionblockbrowserTableGUI($this, 'browseForQuestionblocks', $this->object);
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, 'browseForQuestionblocks');
    }

    public function insertQuestionblocksObject(): void
    {
        $inserted_objects = 0;
        $page_gui = null;
        $block_ids = $this->request->getBlockIds();
        if (count($block_ids) > 0) {
            if ($this->requested_pgov !== "") {
                $page_gui = new ilSurveyPageEditGUI($this->object, $this);
                $page_gui->determineCurrentPage();

                // as target position is predefined, insert in reverse order
                $block_ids = array_reverse($block_ids);
            }
            foreach ($block_ids as $questionblock_id) {
                if ($this->requested_pgov === "") {
                    $this->object->insertQuestionblock($questionblock_id);
                } else {
                    $page_gui->insertQuestionBlock($questionblock_id);
                }
                $inserted_objects++;
            }
        }
        if ($inserted_objects) {
            $this->object->saveCompletionStatus();
            $this->tpl->setOnScreenMessage('success', ($inserted_objects === 1) ? $this->lng->txt("questionblock_inserted") : $this->lng->txt("questionblocks_inserted"), true);
            if ($this->requested_pgov === "") {
                $this->ctrl->redirect($this, "questions");
            } else {
                $target_page = $this->requested_pgov;
                if (substr($this->request->getTargetQuestionPosition(), -1) === "c") {
                    $target_page++;
                }
                $this->ctrl->setParameterByClass("ilSurveyPageEditGUI", "pgov", $target_page);
                $this->ctrl->redirectByClass("ilSurveyPageEditGUI", "renderpage");
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("insert_missing_questionblock"), true);
            $this->ctrl->redirect($this, 'browseForQuestionblocks');
        }
    }


    //
    // BLOCKS
    //

    public function editQuestionblockObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        $block_id = $this->request->getBlockId();
        $this->ctrl->setParameter($this, "bl_id", $block_id);

        if (!$a_form) {
            $a_form = $this->initQuestionblockForm($block_id);
        }

        $this->questionsSubtabs("questions");
        $this->tpl->setContent($a_form->getHTML());
    }

    public function createQuestionblockObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        if (!$a_form) {
            // gather questions from table selected
            $items = $this->gatherSelectedTableItems(false, true, false, false);

            $qids = $this->request->getQuestionIds();
            if (count($qids) > 0) {
                $items["questions"] = $qids;
            }
            if (count($items["questions"]) < 2) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_define_questionblock_select_missing"), true);
                $this->ctrl->redirect($this, "questions");
            }

            $a_form = $this->initQuestionblockForm(null, $items["questions"]);
        }

        $this->questionsSubtabs("questions");
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function initQuestionblockForm(
        ?int $a_block_id = null,
        ?array $a_question_ids = null
    ): ilPropertyFormGUI {
        $questionblock = null;
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveDefineQuestionblock"));
        $form->setTitle($this->lng->txt("define_questionblock"));

        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);

        $toggle_blocktitle = new ilCheckboxInputGUI($this->lng->txt("survey_show_blocktitle"), "show_blocktitle");
        $toggle_blocktitle->setInfo($this->lng->txt("survey_show_blocktitle_description"));
        $form->addItem($toggle_blocktitle);

        $toggle_questiontitle = new ilCheckboxInputGUI($this->lng->txt("show_questiontext"), "show_questiontext");
        $toggle_questiontitle->setInfo($this->lng->txt("show_questiontext_description"));
        $form->addItem($toggle_questiontitle);

        if ($a_block_id) {
            $questionblock = ilObjSurvey::_getQuestionblock($a_block_id);
            $title->setValue($questionblock["title"]);
            $toggle_blocktitle->setChecked((bool) $questionblock["show_blocktitle"]);
            $toggle_questiontitle->setChecked((bool) $questionblock["show_questiontext"]);
        } else {
            $toggle_blocktitle->setChecked(true);
            $toggle_questiontitle->setChecked(true);
        }

        $compress_view = new ilCheckboxInputGUI($this->lng->txt("svy_compress_view"), "compress_view");
        $compress_view->setInfo($this->lng->txt("svy_compress_view_info"));
        $compress_view->setChecked((bool) ($questionblock["compress_view"] ?? false));
        $form->addItem($compress_view);

        $form->addCommandButton("saveDefineQuestionblock", $this->lng->txt("save"));
        $form->addCommandButton("questions", $this->lng->txt("cancel"));

        // reload?
        $qids = $this->request->getQuestionIds();
        if (!$a_question_ids && count($qids) > 0) {
            $a_question_ids = $qids;
        }

        if ($a_question_ids) {
            foreach ($a_question_ids as $q_id) {
                $hidden = new ilHiddenInputGUI("qids[]");
                $hidden->setValue($q_id);
                $form->addItem($hidden);
            }
        }

        return $form;
    }

    public function saveDefineQuestionblockObject(): void
    {
        $block_id = $this->request->getBlockId();
        $q_ids = $this->request->getQuestionIds();

        $this->ctrl->setParameter($this, "bl_id", $block_id);

        if (!$block_id && count($q_ids) === 0) {
            $this->ctrl->redirect($this, "questions");
        }

        $form = $this->initQuestionblockForm($block_id);
        if ($form->checkInput()) {
            $title = $form->getInput("title");
            $show_questiontext = $form->getInput("show_questiontext");
            $show_blocktitle = $form->getInput("show_blocktitle") ;
            $compress_view = $form->getInput("compress_view") ;
            if ($block_id) {
                $this->object->modifyQuestionblock(
                    $block_id,
                    $title,
                    $show_questiontext,
                    $show_blocktitle,
                    $compress_view
                );
            } elseif ($q_ids) {
                $this->object->createQuestionblock(
                    $title,
                    $show_questiontext,
                    $show_blocktitle,
                    $q_ids,
                    $compress_view
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, "questions");
        }

        $form->setValuesByPost();
        $this->editQuestionblockObject($form);
    }


    //
    // HEADING
    //

    protected function initHeadingForm(
        ?int $a_question_id = null
    ): ilPropertyFormGUI {
        $survey_questions = $this->object->getSurveyQuestions();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, ""));

        // heading
        $heading = new ilTextAreaInputGUI($this->lng->txt("heading"), "heading");
        $heading->setRows(10);
        $heading->setCols(80);
        $heading->setUseRte(true);
        $heading->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
        $heading->removePlugin(ilRTE::ILIAS_IMG_MANAGER_PLUGIN);
        $heading->setRTESupport($this->object->getId(), "svy", "survey");
        $heading->setRequired(true);
        $form->addItem($heading);

        $insertbefore = new ilSelectInputGUI($this->lng->txt("insert"), "insertbefore");
        $options = array();
        foreach ($survey_questions as $key => $value) {
            $options[$key] = $this->lng->txt("before") . ": \"" . $value["title"] . "\"";
        }
        $insertbefore->setOptions($options);
        $insertbefore->setRequired(true);
        $form->addItem($insertbefore);

        $form->addCommandButton("saveHeading", $this->lng->txt("save"));
        $form->addCommandButton("questions", $this->lng->txt("cancel"));

        if ($a_question_id) {
            $form->setTitle($this->lng->txt("edit_heading"));

            $heading->setValue($this->object->prepareTextareaOutput($survey_questions[$a_question_id]["heading"] ?? ""));
            $insertbefore->setValue($a_question_id);
            $insertbefore->setDisabled(true);
        } else {
            $form->setTitle($this->lng->txt("add_heading"));
        }

        return $form;
    }

    public function addHeadingObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        $q_id = $this->request->getQuestionId();
        $this->ctrl->setParameter($this, "q_id", $q_id);

        $this->questionsSubtabs("questions");

        if (!$a_form) {
            $a_form = $this->initHeadingForm($q_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    public function editHeadingObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        $q_id = $this->request->getQuestionId();
        $this->ctrl->setParameter($this, "q_id", $q_id);

        $this->questionsSubtabs("questions");

        if (!$a_form) {
            $a_form = $this->initHeadingForm($q_id);
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveHeadingObject(): void
    {
        // #15474
        $q_id = $this->request->getQuestionId();
        $this->ctrl->setParameter($this, "q_id", $q_id);

        $form = $this->initHeadingForm($q_id);
        if ($form->checkInput()) {
            $this->object->saveHeading(
                ilUtil::stripSlashes(
                    $form->getInput("heading"),
                    true,
                    ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")
                ),
                $form->getInput("insertbefore")
            );
            $this->ctrl->redirect($this, "questions");
        }

        $form->setValuesByPost();
        $this->addHeadingObject($form);
    }

    public function removeHeadingObject(): void
    {
        $q_id = $this->request->getQuestionId();
        $this->ctrl->setParameter($this, "q_id", $q_id);

        if (!$q_id) {
            $this->ctrl->redirect($this, "questions");
        }

        $this->questionsSubtabs("questions");

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("confirm_remove_heading"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmedRemoveHeading"));
        $cgui->setCancel($this->lng->txt("cancel"), "questions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedRemoveHeading");

        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmedRemoveHeadingObject(): void
    {
        $q_id = $this->request->getQuestionId();
        if (!$q_id) {
            $this->ctrl->redirect($this, "questions");
        }

        $this->object->saveHeading("", $q_id);
        $this->ctrl->redirect($this, "questions");
    }

    public function printViewObject(): void
    {
        $print_view = $this->print->page($this->object->getRefId());
        $print_view->sendPrintView();
    }

    public function printListViewSelectionObject(): void
    {
        $view = $this->print->list($this->object->getRefId());
        $view->sendForm();
    }

    public function printListViewObject(): void
    {
        $print_view = $this->print->list($this->object->getRefId());
        $print_view->sendPrintView();
    }
}
