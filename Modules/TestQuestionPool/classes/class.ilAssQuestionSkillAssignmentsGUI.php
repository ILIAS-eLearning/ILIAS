<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionSkillAssignmentsTableGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilSkillSelectorGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilToolbarGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionSkillAssignmentPropertyFormGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssLacLegendGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilConfirmationGUI
 */
class ilAssQuestionSkillAssignmentsGUI
{
    const CMD_SHOW_SKILL_QUEST_ASSIGNS = 'showSkillQuestionAssignments';
    const CMD_SHOW_SKILL_SELECT = 'showSkillSelection';
    const CMD_UPDATE_SKILL_QUEST_ASSIGNS = 'updateSkillQuestionAssignments';
    const CMD_SHOW_SKILL_QUEST_ASSIGN_PROPERTIES_FORM = 'showSkillQuestionAssignmentPropertiesForm';
    const CMD_SAVE_SKILL_QUEST_ASSIGN_PROPERTIES_FORM = 'saveSkillQuestionAssignmentPropertiesForm';
    const CMD_SAVE_SKILL_POINTS = 'saveSkillPoints';
    const CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION = 'showSyncOriginalConfirmation';
    const CMD_SYNC_ORIGINAL = 'syncOriginal';
    
    const PARAM_SKILL_SELECTION = 'skill_ids';
    
    /**
     * @var ilCtrl
     */
    private $ctrl;
    
    /**
     * @var ilAccessHandler
     */
    private $access;

    /**
     * @var ilTemplate
     */
    private $tpl;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilAssQuestionList
     */
    private $questionList;

    /**
     * @var integer
     */
    private $questionContainerId;

    /**
     * @var bool
     */
    private $assignmentEditingEnabled;

    /**
     * @var array
     */
    private $questionOrderSequence;

    /**
     * @var string
     */
    private $assignmentConfigurationHintMessage;

    /**
     * @param ilCtrl $ctrl
     * @param ilAccessHandler $access
     * @param ilTemplate $tpl
     * @param ilLanguage $lng
     * @param ilDBInterface $db
     */
    public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilTemplate $tpl, ilLanguage $lng, ilDBInterface $db)
    {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getQuestionOrderSequence()
    {
        return $this->questionOrderSequence;
    }

    /**
     * @return string
     */
    public function getAssignmentConfigurationHintMessage()
    {
        return $this->assignmentConfigurationHintMessage;
    }

    /**
     * @param string $assignmentConfigurationHintMessage
     */
    public function setAssignmentConfigurationHintMessage($assignmentConfigurationHintMessage)
    {
        $this->assignmentConfigurationHintMessage = $assignmentConfigurationHintMessage;
    }

    /**
     * @param array $questionOrderSequence
     */
    public function setQuestionOrderSequence($questionOrderSequence)
    {
        $this->questionOrderSequence = $questionOrderSequence;
    }

    /**
     * @return ilAssQuestionList
     */
    public function getQuestionList()
    {
        return $this->questionList;
    }

    /**
     * @param ilAssQuestionList $questionList
     */
    public function setQuestionList($questionList)
    {
        $this->questionList = $questionList;
    }

    /**
     * @return int
     */
    public function getQuestionContainerId()
    {
        return $this->questionContainerId;
    }

    /**
     * @param int $questionContainerId
     */
    public function setQuestionContainerId($questionContainerId)
    {
        $this->questionContainerId = $questionContainerId;
    }

    /**
     * @return bool
     */
    public function isAssignmentEditingEnabled()
    {
        return $this->assignmentEditingEnabled;
    }

    /**
     * @param bool $assignmentEditingEnabled
     */
    public function setAssignmentEditingEnabled($assignmentEditingEnabled)
    {
        $this->assignmentEditingEnabled = $assignmentEditingEnabled;
    }

    public function executeCommand()
    {
        $nextClass = $this->ctrl->getNextClass();
        
        $command = $this->ctrl->getCmd(self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
        
        if ($this->isAvoidManipulationRedirectRequired($command)) {
            $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
        }
        
        switch ($nextClass) {
            case strtolower(__CLASS__):
            case '':
                
                $command .= 'Cmd';
                $this->$command();
                break;
                
            default:
                
                throw new ilTestQuestionPoolException('unsupported next class in ctrl flow');
        }
    }
    
    private function isAvoidManipulationRedirectRequired($command)
    {
        if ($this->isAssignmentEditingEnabled()) {
            return false;
        }
        
        switch ($command) {
            case self::CMD_SAVE_SKILL_QUEST_ASSIGN_PROPERTIES_FORM:
            case self::CMD_UPDATE_SKILL_QUEST_ASSIGNS:
                
                return true;
        }
        
        return false;
    }

    private function saveSkillPointsCmd()
    {
        $success = true;

        if (is_array($_POST['skill_points'])) {
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';

            for ($i = 0; $i < 2; $i++) {
                foreach ($_POST['skill_points'] as $assignmentKey => $skillPoints) {
                    $assignmentKey = explode(':', $assignmentKey);
                    $skillBaseId = (int) $assignmentKey[0];
                    $skillTrefId = (int) $assignmentKey[1];
                    $questionId = (int) $assignmentKey[2];

                    if ($this->isTestQuestion($questionId)) {
                        $assignment = new ilAssQuestionSkillAssignment($this->db);

                        if ($i == 0) {
                            if (!$assignment->isValidSkillPoint($skillPoints)) {
                                $success = false;
                                break 2;
                            }
                            continue;
                        }

                        $assignment->setParentObjId($this->getQuestionContainerId());
                        $assignment->setQuestionId($questionId);
                        $assignment->setSkillBaseId($skillBaseId);
                        $assignment->setSkillTrefId($skillTrefId);

                        if ($assignment->dbRecordExists()) {
                            $assignment->loadFromDb();

                            if (!$assignment->hasEvalModeBySolution()) {
                                $assignment->setSkillPoints((int) $skillPoints);
                                $assignment->saveToDb();
                            }
                        }
                    }
                }
            }
        }

        if ($success) {
            ilUtil::sendSuccess($this->lng->txt('tst_msg_skl_qst_assign_points_saved'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
        } else {
            ilUtil::sendFailure($this->lng->txt('tst_msg_skl_qst_assign_points_not_saved'));
            $this->showSkillQuestionAssignmentsCmd(true);
        }
    }

    private function updateSkillQuestionAssignmentsCmd()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        
        $questionId = (int) $_GET['question_id'];

        if ($this->isTestQuestion($questionId)) {
            $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
            $assignmentList->setParentObjId($this->getQuestionContainerId());
            $assignmentList->loadFromDb();

            $handledSkills = array();
            
            //$skillIds = (array)$_POST['skill_ids'];
            $sgui = $this->buildSkillSelectorExplorerGUI(array());
            $skillIds = $sgui->getSelectedSkills();
            
            foreach ($skillIds as $skillId) {
                $skill = explode(':', $skillId);
                $skillBaseId = (int) $skill[0];
                $skillTrefId = (int) $skill[1];
                
                if ($skillBaseId) {
                    if (!$assignmentList->isAssignedToQuestionId($skillBaseId, $skillTrefId, $questionId)) {
                        $assignment = new ilAssQuestionSkillAssignment($this->db);

                        $assignment->setParentObjId($this->getQuestionContainerId());
                        $assignment->setQuestionId($questionId);
                        $assignment->setSkillBaseId($skillBaseId);
                        $assignment->setSkillTrefId($skillTrefId);

                        $assignment->setSkillPoints(ilAssQuestionSkillAssignment::DEFAULT_COMPETENCE_POINTS);
                        $assignment->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
                        $assignment->saveToDb();
                    }
                    
                    $handledSkills[$skillId] = $skill;
                }
            }
            
            foreach ($assignmentList->getAssignmentsByQuestionId($questionId) as $assignment) {
                if (isset($handledSkills["{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}"])) {
                    continue;
                }

                $assignment->deleteFromDb();
            }

            ilUtil::sendSuccess($this->lng->txt('qpl_qst_skl_assigns_updated'), true);
            
            if ($this->isSyncOriginalPossibleAndAllowed($questionId)) {
                $this->keepAssignmentParameters();
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION);
            }
        }
        
        $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
    }

    private function showSkillSelectionCmd()
    {
        $this->ctrl->saveParameter($this, 'question_id');
        $questionId = (int) $_GET['question_id'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getQuestionContainerId());
        $assignmentList->loadFromDb();

        $skillSelectorExplorerGUI = $this->buildSkillSelectorExplorerGUI(
            $assignmentList->getAssignmentsByQuestionId($questionId)
        );

        if (!$skillSelectorExplorerGUI->handleCommand()) {
            $tpl = new ilTemplate('tpl.qpl_qst_skl_assign_selection.html', false, false, 'Modules/TestQuestionPool');

            $tpl->setVariable('SKILL_SELECTOR_HEADER', $this->getSkillSelectorHeader($questionId));
            
            $skillSelectorToolbarGUI = $this->buildSkillSelectorToolbarGUI();

            $skillSelectorToolbarGUI->setOpenFormTag(true);
            $skillSelectorToolbarGUI->setCloseFormTag(false);
            $skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), " ");
            $tpl->setVariable('SKILL_SELECTOR_TOOLBAR_TOP', $this->ctrl->getHTML($skillSelectorToolbarGUI));
            
            $tpl->setVariable('SKILL_SELECTOR_EXPLORER', $this->ctrl->getHTML($skillSelectorExplorerGUI));

            $skillSelectorToolbarGUI->setOpenFormTag(false);
            $skillSelectorToolbarGUI->setCloseFormTag(true);
            $skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), " ");
            $tpl->setVariable('SKILL_SELECTOR_TOOLBAR_BOTTOM', $this->ctrl->getHTML($skillSelectorToolbarGUI));
            
            $this->tpl->setContent($tpl->get());
        }
    }
    
    private function showSkillQuestionAssignmentPropertiesFormCmd(
        assQuestionGUI $questionGUI = null,
        ilAssQuestionSkillAssignment $assignment = null,
        ilPropertyFormGUI $form = null
    ) {
        $this->handleAssignmentConfigurationHintMessage();

        $this->keepAssignmentParameters();
        
        if ($questionGUI === null) {
            require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
            $questionGUI = assQuestionGUI::_getQuestionGUI('', (int) $_GET['question_id']);
        }

        if ($assignment === null) {
            $assignment = $this->buildQuestionSkillAssignment(
                (int) $_GET['question_id'],
                (int) $_GET['skill_base_id'],
                (int) $_GET['skill_tref_id']
            );
        }
        
        if ($form === null) {
            $form = $this->buildSkillQuestionAssignmentPropertiesForm($questionGUI->object, $assignment);
        }

        $questionPageHTML = $this->buildQuestionPage($questionGUI);

        $lacLegendHTML = $this->buildLacLegendHTML($questionGUI->object, $assignment);

        $this->tpl->setContent($this->ctrl->getHTML($form) . '<br />' . $questionPageHTML . $lacLegendHTML);
    }
    
    private function saveSkillQuestionAssignmentPropertiesFormCmd()
    {
        $questionId = (int) $_GET['question_id'];
        
        if ($this->isTestQuestion($questionId)) {
            require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
            $questionGUI = assQuestionGUI::_getQuestionGUI('', $questionId);
    
            $assignment = $this->buildQuestionSkillAssignment(
                (int) $_GET['question_id'],
                (int) $_GET['skill_base_id'],
                (int) $_GET['skill_tref_id']
            );

            $this->keepAssignmentParameters();
            $form = $this->buildSkillQuestionAssignmentPropertiesForm($questionGUI->object, $assignment);
            if (!$form->checkInput()) {
                $form->setValuesByPost();
                $this->showSkillQuestionAssignmentPropertiesFormCmd($questionGUI, $assignment, $form);
                return;
            }
            $form->setValuesByPost();

            if ($form->getItemByPostVar('eval_mode')) {
                $assignment->setEvalMode($form->getItemByPostVar('eval_mode')->getValue());
            } else {
                $assignment->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
            }

            if ($assignment->hasEvalModeBySolution()) {
                $solCmpExprInput = $form->getItemByPostVar('solution_compare_expressions');
                
                if (!$this->checkSolutionCompareExpressionInput($solCmpExprInput, $questionGUI->object)) {
                    ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
                    $this->showSkillQuestionAssignmentPropertiesFormCmd($questionGUI, $assignment, $form);
                    return;
                }

                $assignment->initSolutionComparisonExpressionList();
                $assignment->getSolutionComparisonExpressionList()->reset();
                
                foreach ($solCmpExprInput->getValues() as $expression) {
                    $assignment->getSolutionComparisonExpressionList()->add($expression);
                }
            } else {
                $assignment->setSkillPoints($form->getItemByPostVar('q_res_skill_points')->getValue());
            }
            
            $assignment->saveToDb();
            
            ilUtil::sendSuccess($this->lng->txt('qpl_qst_skl_assign_properties_modified'), true);

            if ($this->isSyncOriginalPossibleAndAllowed($questionId)) {
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION);
            }
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
    }
    
    private function buildSkillQuestionAssignmentPropertiesForm(assQuestion $question, ilAssQuestionSkillAssignment $assignment)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssQuestionSkillAssignmentPropertyFormGUI.php';
        $form = new ilAssQuestionSkillAssignmentPropertyFormGUI($this->ctrl, $this->lng, $this);

        $form->setQuestion($question);
        $form->setAssignment($assignment);
        $form->setManipulationEnabled($this->isAssignmentEditingEnabled());

        $form->build();
        
        return $form;
    }

    private function showSkillQuestionAssignmentsCmd($loadSkillPointsFromRequest = false)
    {
        $this->handleAssignmentConfigurationHintMessage();
        
        $table = $this->buildTableGUI();
        $table->loadSkillPointsFromRequest($loadSkillPointsFromRequest);

        $assignmentList = $this->buildSkillQuestionAssignmentList();
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();
        $table->setSkillQuestionAssignmentList($assignmentList);
        $table->setData($this->orderQuestionData($this->questionList->getQuestionDataArray()));

        $this->tpl->setContent($this->ctrl->getHTML($table));
    }

    private function isSyncOriginalPossibleAndAllowed($questionId)
    {
        $questionData = $this->questionList->getDataArrayForQuestionId($questionId);

        if (!$questionData['original_id']) {
            return false;
        }

        require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
        $parentObjId = assQuestion::lookupParentObjId($questionData['original_id']);

        if (!$this->doesObjectTypeMatch($parentObjId)) {
            return false;
        }

        foreach (ilObject::_getAllReferences($parentObjId) as $parentRefId) {
            if ($this->access->checkAccess('write', '', $parentRefId)) {
                return true;
            }
        }

        return false;
    }

    private function showSyncOriginalConfirmationCmd()
    {
        $questionId = (int) $_GET['question_id'];

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();

        $confirmation->setHeaderText($this->lng->txt('qpl_sync_quest_skl_assigns_confirmation'));

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->addHiddenItem('question_id', $questionId);
        $confirmation->setConfirm($this->lng->txt('yes'), self::CMD_SYNC_ORIGINAL);
        $confirmation->setCancel($this->lng->txt('no'), self::CMD_SHOW_SKILL_QUEST_ASSIGNS);

        $this->tpl->setContent($this->ctrl->getHTML($confirmation));
    }

    private function syncOriginalCmd()
    {
        $questionId = (int) $_POST['question_id'];

        if ($this->isTestQuestion($questionId) && $this->isSyncOriginalPossibleAndAllowed($questionId)) {
            $question = assQuestion::_instantiateQuestion($questionId);

            $question->syncSkillAssignments(
                $question->getObjId(),
                $question->getId(),
                $question->lookupParentObjId($question->getOriginalId()),
                $question->getOriginalId()
            );

            ilUtil::sendSuccess($this->lng->txt('qpl_qst_skl_assign_synced_to_orig'), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
    }

    private function buildTableGUI()
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilAssQuestionSkillAssignmentsTableGUI.php';
        $table = new ilAssQuestionSkillAssignmentsTableGUI($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS, $this->ctrl, $this->lng);
        $table->setManipulationsEnabled($this->isAssignmentEditingEnabled());
        $table->init();

        return $table;
    }

    private function buildSkillQuestionAssignmentList()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getQuestionContainerId());

        return $assignmentList;
    }

    /**
     * @return ilSkillSelectorGUI
     */
    private function buildSkillSelectorExplorerGUI($assignments)
    {
        require_once 'Services/Skill/classes/class.ilSkillSelectorGUI.php';

        $skillSelectorExplorerGUI = new ilSkillSelectorGUI(
            $this,
            self::CMD_SHOW_SKILL_SELECT,
            $this,
            self::CMD_UPDATE_SKILL_QUEST_ASSIGNS,
            self::PARAM_SKILL_SELECTION
        );

        $skillSelectorExplorerGUI->setSelectMode(self::PARAM_SKILL_SELECTION, true);
        //$skillSelectorExplorerGUI->setNodeOnclickEnabled(false);
        
        // parameter name for skill selection is actually taken from value passed to constructor,
        // but passing a non empty name to setSelectMode is neccessary to keep input fields enabled

        foreach ($assignments as $assignment) {
            $id = "{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}";
            //$skillSelectorExplorerGUI->setNodeSelected($id);
            $skillSelectorExplorerGUI->setSkillSelected($id);
        }

        return $skillSelectorExplorerGUI;
    }

    /**
     * @return ilToolbarGUI
     */
    private function buildSkillSelectorToolbarGUI()
    {
        require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

        $skillSelectorToolbarGUI = new ilToolbarGUI();

        $skillSelectorToolbarGUI->setFormAction($this->ctrl->getFormAction($this));
        $skillSelectorToolbarGUI->addFormButton($this->lng->txt('qpl_save_skill_assigns_update'), self::CMD_UPDATE_SKILL_QUEST_ASSIGNS);
        $skillSelectorToolbarGUI->addFormButton($this->lng->txt('qpl_cancel_skill_assigns_update'), self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
        
        return $skillSelectorToolbarGUI;
    }

    private function buildQuestionPage(assQuestionGUI $questionGUI)
    {
        $this->tpl->addCss('Services/COPage/css/content.css');

        include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
        $pageGUI = new ilAssQuestionPageGUI($questionGUI->object->getId());

        $pageGUI->setOutputMode("presentation");
        $pageGUI->setRenderPageContainer(true);

        $pageGUI->setPresentationTitle($questionGUI->object->getTitle());

        $questionGUI->object->setShuffle(false); // dirty, but works ^^
        $questionHTML = $questionGUI->getSolutionOutput(0, 0, false, false, true, false, true, false, true);
        $pageGUI->setQuestionHTML(array($questionGUI->object->getId() => $questionHTML));

        $pageHTML = $pageGUI->presentation();
        $pageHTML = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $pageHTML);

        return $pageHTML;
    }

    /**
     * @return ilAssQuestionSkillAssignment
     */
    private function buildQuestionSkillAssignment($questionId, $skillBaseId, $skillTrefId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';
        
        $assignment = new ilAssQuestionSkillAssignment($this->db);
        
        $assignment->setParentObjId($this->getQuestionContainerId());
        $assignment->setQuestionId($questionId);
        $assignment->setSkillBaseId($skillBaseId);
        $assignment->setSkillTrefId($skillTrefId);
        
        $assignment->loadFromDb();
        $assignment->loadAdditionalSkillData();
        
        return $assignment;
    }

    private function isTestQuestion($questionId)
    {
        return $this->questionList->isInList($questionId);
    }

    private function checkSolutionCompareExpressionInput(ilLogicalAnswerComparisonExpressionInputGUI $input, assQuestion $question)
    {
        $errors = array();

        foreach ($input->getValues() as $expression) {
            $result = $this->validateSolutionCompareExpression($expression, $question);

            if ($result !== true) {
                $errors[] = "{$this->lng->txt('ass_lac_expression')} {$expression->getOrderIndex()}: {$result}";
            }
        }

        if (count($errors)) {
            $alert = $this->lng->txt('ass_lac_validation_error');
            $alert .= '<br />' . implode('<br />', $errors);
            $input->setAlert($alert);
            return false;
        }

        return true;
    }

    private function validateSolutionCompareExpression(ilAssQuestionSolutionComparisonExpression $expression, iQuestionCondition $question)
    {
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacConditionParser.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacQuestionProvider.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacCompositeValidator.php';

        try {
            $conditionParser = new ilAssLacConditionParser();
            $conditionComposite = $conditionParser->parse($expression->getExpression());
            $questionProvider = new ilAssLacQuestionProvider();
            $questionProvider->setQuestion($question);
            $conditionValidator = new ilAssLacCompositeValidator($questionProvider);

            $conditionValidator->validate($conditionComposite);
        } catch (ilAssLacException $e) {
            if ($e instanceof ilAssLacFormAlertProvider) {
                return $e->getFormAlert($this->lng);
            }
            
            throw $e;
        }

        return true;
    }

    private function keepAssignmentParameters()
    {
        $this->ctrl->saveParameter($this, 'question_id');
        $this->ctrl->saveParameter($this, 'skill_base_id');
        $this->ctrl->saveParameter($this, 'skill_tref_id');
    }
    
    private function orderQuestionData($questionData)
    {
        $orderedQuestionsData = array();

        if ($this->getQuestionOrderSequence()) {
            foreach ($this->getQuestionOrderSequence() as $questionId) {
                $orderedQuestionsData[$questionId] = $questionData[$questionId];
            }
            
            return $orderedQuestionsData;
        }

        foreach ($questionData as $questionId => $data) {
            $orderedQuestionsData[$questionId] = $data['title'];
        }

        $orderedQuestionsData = $this->sortAlphabetically($orderedQuestionsData);
        
        foreach ($orderedQuestionsData as $questionId => $questionTitle) {
            $orderedQuestionsData[$questionId] = $questionData[$questionId];
        }
        
        return $orderedQuestionsData;
    }
    
    private function handleAssignmentConfigurationHintMessage()
    {
        if ($this->getAssignmentConfigurationHintMessage()) {
            ilUtil::sendInfo($this->getAssignmentConfigurationHintMessage());
        }
    }

    private function getSkillSelectorHeader($questionId)
    {
        $questionData = $this->questionList->getDataArrayForQuestionId($questionId);
        
        return sprintf($this->lng->txt('qpl_qst_skl_selection_for_question_header'), $questionData['title']);
    }
    
    private function sortAlphabetically($array)
    {
        $flags = SORT_REGULAR;

        if (defined('SORT_NATURAL')) {
            $flags = SORT_NATURAL;
        } elseif (defined('SORT_STRING')) {
            $flags = SORT_STRING;
        }
        
        if (defined('SORT_FLAG_CASE')) {
            $flags = $flags | SORT_FLAG_CASE;
        }
        
        asort($array, $flags);
        
        return $array;
    }

    protected function doesObjectTypeMatch($objectId)
    {
        return ilObject::_lookupType($objectId) == 'qpl';
    }

    /**
     * @param assQuestionGUI $questionGUI
     * @param ilAssQuestionSkillAssignment $assignment
     * @return string
     * @throws ilCtrlException
     */
    private function buildLacLegendHTML(assQuestion $questionOBJ, ilAssQuestionSkillAssignment $assignment)
    {
        // #19192
        if ($questionOBJ instanceof iQuestionCondition && $this->isAssignmentEditingEnabled()) {
            require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/class.ilAssLacLegendGUI.php';
            $legend = new ilAssLacLegendGUI($this->lng, $this->tpl);
            $legend->setQuestionOBJ($questionOBJ);
            $legend->setInitialVisibilityEnabled($assignment->hasEvalModeBySolution());
            $lacLegendHTML = $this->ctrl->getHTML($legend);
            return $lacLegendHTML;
        }
        
        return '';
    }
}
