<?php declare(strict_types=0);

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
 
use ILIAS\UI\Component\Listing\Workflow\Step;
use ILIAS\UI\Component\Listing\Workflow\Factory as Workflow;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * class ilobjcourseobjectivesgui
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCourseObjectivesGUI
{
    public const MODE_UNDEFINED = 0;
    public const MODE_CREATE = 1;
    public const MODE_UPDATE = 2;

    protected const STEP_SETTINGS = 1;
    protected const STEP_MATERIAL_ASSIGNMENT = 2;
    protected const STEP_INITIAL_TEST_ASSIGNMENT = 3;
    protected const STEP_INITIAL_TEST_LIMIT = 4;
    protected const STEP_FINAL_TEST_ASSIGNMENT = 5;
    protected const STEP_FINAL_TEST_LIMIT = 6;

    protected ilObjCourse $course_obj;
    protected ?ilCourseObjective $objective = null;
    protected ?ilCourseObjective $objectives_obj = null;
    protected ?ilCourseObjectiveMaterials $objectives_lm_obj = null;
    protected ?ilCourseObjectiveQuestion $objectives_qst_obj = null;
    protected ?ilCourseObjectiveQuestion $questions = null;
    protected int $course_id;
    protected ilLOSettings $settings;
    protected int $test_type = 0;
    protected ?ilPropertyFormGUI $form = null;

    private ilLogger $logger;
    protected ilDBInterface $db;
    protected ilCtrlInterface $ctrl;
    protected ilErrorHandling $ilErr;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTree $tree;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilHelpGUI $help;
    protected ilObjectDataCache $objectDataCache;
    protected Workflow $workflow;
    protected UIRenderer $renderer;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct(int $a_course_id)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        $this->ctrl->saveParameter($this, array("ref_id"));

        $this->logger = $DIC->logger()->crs();
        $this->ilErr = $DIC['ilErr'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC->help();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->workflow = $DIC->ui()->factory()->listing()->workflow();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->course_id = $a_course_id;
        $this->__initCourseObject();
        $this->settings = ilLOSettings::getInstanceByObjId($this->course_obj->getId());
    }

    public function executeCommand() : void
    {
        $this->tabs->setTabActive('crs_objectives');

        $cmd = $this->ctrl->getCmd();

        if (!$cmd = $this->ctrl->getCmd()) {
            $cmd = "list";
        }

        $this->$cmd();
    }

    protected function initObjectiveIdFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('objective_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'objective_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function initObjectiveIdsFromPost() : array
    {
        if ($this->http->wrapper()->post()->has('objective')) {
            return $this->http->wrapper()->post()->retrieve(
                'objective',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initTestTypeFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('tt')) {
            return $this->http->wrapper()->query()->retrieve(
                'tt',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function getSettings() : ilLOSettings
    {
        return $this->settings;
    }

    protected function listObjectives() : void
    {
        ilSession::set('objective_mode', self::MODE_UNDEFINED);
        if (!$this->access->checkAccess("write", '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilErr->MESSAGE);
        }
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.crs_objectives.html', 'Modules/Course');
        $this->toolbar->addButton(
            $this->lng->txt('crs_add_objective'),
            $this->ctrl->getLinkTarget($this, "'create")
        );

        $table = new ilCourseObjectivesTableGUI($this, $this->course_obj);
        $table->setTitle($this->lng->txt('crs_objectives'), '', $this->lng->txt('crs_objectives'));
        $table->parse(ilCourseObjective::_getObjectiveIds($this->course_obj->getId(), false));

        $this->tpl->setVariable('OBJECTIVES_TABLE', $table->getHTML());
    }

    protected function questionOverview() : void
    {
        $this->tabs->setSubTabActive('crs_objective_overview_question_assignment');

        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }

        $table = new ilCourseObjectiveQuestionsTableGUI($this, $this->course_obj);
        $table->setTitle(
            $this->lng->txt('crs_objectives_edit_question_assignments'),
            '',
            $this->lng->txt('crs_objectives')
        );
        $table->parse(ilCourseObjective::_getObjectiveIds($this->course_obj->getId(), false));
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveQuestionOverview() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }

        $post_self_limit = [];
        if ($this->http->wrapper()->post()->has('self')) {
            $post_self_limit = $this->http->wrapper()->post()->retrieve(
                'self',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->float()
                )
            );
        }
        $post_final_limit = [];
        if ($this->http->wrapper()->post()->has('final')) {
            $post_final_limit = $this->http->wrapper()->post()->retrieve(
                'final',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->float()
                )
            );
        }

        foreach ($post_self_limit as $objective_id => $limit) {
            $qst = new ilCourseObjectiveQuestion($objective_id);
            $max_points = $qst->getSelfAssessmentPoints();

            if ($limit < 0 || $limit > $max_points) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_objective_limit_err'));
                $this->questionOverview();
                return;
            }
        }
        foreach ($post_final_limit as $objective_id => $limit) {
            $qst = new ilCourseObjectiveQuestion($objective_id);
            $max_points = $qst->getFinalTestPoints();

            if ($limit < 0 || $limit > $max_points) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_objective_limit_err'));
                $this->questionOverview();
                return;
            }
        }

        foreach ($post_self_limit as $objective_id => $limit) {
            ilCourseObjectiveQuestion::_updateTestLimits(
                $objective_id,
                ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT,
                $limit
            );
        }

        foreach ($post_final_limit as $objective_id => $limit) {
            ilCourseObjectiveQuestion::_updateTestLimits(
                $objective_id,
                ilCourseObjectiveQuestion::TYPE_FINAL_TEST,
                $limit
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->questionOverview();
    }

    protected function __initCourseObject() : void
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        if (!$this->course_obj = ilObjectFactory::getInstanceByRefId($this->course_id, false)) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new RuntimeException('Course objectives GUI initialized without valid course instance');
        }
    }

    public function __initObjectivesObject(int $a_id = 0) : ilCourseObjective
    {
        return $this->objectives_obj = new ilCourseObjective($this->course_obj, $a_id);
    }

    public function __initLMObject($a_objective_id = 0) : ilCourseObjectiveMaterials
    {
        return $this->objectives_lm_obj = new ilCourseObjectiveMaterials($a_objective_id);
    }

    public function __initQuestionObject($a_objective_id = 0) : ilCourseObjectiveQuestion
    {
        $this->objectives_qst_obj = new ilCourseObjectiveQuestion($a_objective_id);
        return $this->objectives_qst_obj;
    }

    // end-patch lok

    public function setSubTabs(string $a_active = "") : void
    {
        if ($a_active != "") {
            $this->help->setScreenIdComponent("crs");
            $this->help->setScreenId("crs_objective");
            $this->help->setSubScreenId($a_active);
        }
    }

    public function create(?ilPropertyFormGUI $form = null) : void
    {
        $this->setSubTabs("create_obj");
        ilSession::set('objective_mode', self::MODE_CREATE);

        $this->ctrl->saveParameter($this, 'objective_id');

        if (!$this->objective instanceof ilCourseObjective) {
            $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());
        }
        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_SETTINGS);
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormTitle('create');
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function edit(?ilPropertyFormGUI $form = null) : void
    {
        ilSession::set('objective_mode', self::MODE_UPDATE);
        $this->setSubTabs("edit_obj");
        $this->ctrl->setParameter($this, 'objective_id', $this->initObjectiveIdFromQuery());

        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }
        if (!$this->objective instanceof ilCourseObjective) {
            $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());
        }

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_SETTINGS);
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormTitle('create');
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function save() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());
        $form = $this->initFormTitle('create');
        if ($form->checkInput()) {
            $this->objective->setTitle($form->getInput('title'));
            $this->objective->setDescription($form->getInput('description'));
            $this->objective->setPasses(0);

            if (!$this->initObjectiveIdFromQuery()) {
                $objective_id = $this->objective->add();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_added_objective'), true);
            } else {
                $this->objective->update();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objective_modified'), true);
                $objective_id = $this->initObjectiveIdFromQuery();
            }
        } elseif ($this->initObjectiveIdFromQuery()) {
            $form->setValuesByPost();
            $this->edit($form);
            return;
        } else {
            $form->setValuesByPost();
            $this->create($form);
            return;
        }
        if (ilSession::get('objective_mode') != self::MODE_CREATE) {
            $this->ctrl->returnToParent($this);
        }
        $this->ctrl->setParameter($this, 'objective_id', $objective_id);
        $this->ctrl->redirect($this, 'materialAssignment');
    }

    protected function materialAssignment() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("materials");
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());

        $table = new ilCourseObjectiveMaterialAssignmentTableGUI(
            $this,
            $this->course_obj,
            $this->initObjectiveIdFromQuery()
        );
        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_materials'),
            '',
            $this->lng->txt('crs_objectives')
        );
        $table->parse(ilCourseObjectiveMaterials::_getAssignableMaterials($this->course_obj->getRefId()));
        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_MATERIAL_ASSIGNMENT);
        $this->tpl->setContent($table->getHTML());
    }

    protected function updateMaterialAssignment() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initLMObject($this->initObjectiveIdFromQuery());
        $this->objectives_lm_obj->deleteAll();

        $materials = [];
        if ($this->http->wrapper()->post()->has('materials')) {
            $materials = $this->http->wrapper()->post()->retrieve(
                'materials',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        foreach ($materials as $node_id) {
            $obj_id = $this->objectDataCache->lookupObjId((int) $node_id);
            $type = $this->objectDataCache->lookupType($obj_id);

            $this->objectives_lm_obj->setLMRefId($node_id);
            $this->objectives_lm_obj->setLMObjId($obj_id);
            $this->objectives_lm_obj->setType($type);
            $this->objectives_lm_obj->add();
        }
        $chapters = [];
        if ($this->http->wrapper()->post()->has('chapters')) {
            $chapters = $this->http->wrapper()->post()->retrieve(
                'chapters',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        foreach ($chapters as $chapter) {
            list($ref_id, $chapter_id) = explode('_', $chapter);

            $this->objectives_lm_obj->setLMRefId($ref_id);
            $this->objectives_lm_obj->setLMObjId($chapter_id);
            $this->objectives_lm_obj->setType(ilLMObject::_lookupType($chapter_id));
            $this->objectives_lm_obj->add();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_assigned_lm'));
        if (ilSession::get('objective_mode') != self::MODE_CREATE) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_assigned_lm'), true);
            $this->ctrl->returnToParent($this);
        }
        if ($this->getSettings()->worksWithInitialTest()) {
            $this->selfAssessmentAssignment();
        } else {
            $this->finalTestAssignment();
        }
    }

    protected function selfAssessmentAssignment() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("self_ass_assign");

        $this->ctrl->saveParameter($this, 'objective_id');

        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());

        // begin-patch lok
        $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_INITIAL);
        $this->test_type = ilLOSettings::TYPE_TEST_INITIAL;
        if ($this->isRandomTestType(ilLOSettings::TYPE_TEST_INITIAL)) {
            $this->showRandomTestAssignment();
            return;
        }
        // end-patch lok
        $table = new ilCourseObjectiveQuestionAssignmentTableGUI(
            $this,
            $this->course_obj,
            $this->initObjectiveIdFromQuery(),
            ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT
        );
        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_self'),
            '',
            $this->lng->txt('crs_objective')
        );
        $table->parse(ilCourseObjectiveQuestion::_getAssignableTests($this->course_obj->getRefId()));

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_INITIAL_TEST_ASSIGNMENT);
        $this->tpl->setContent($table->getHTML());
    }

    protected function updateSelfAssessmentAssignment() : void
    {
        $checked_questions = [];
        if ($this->http->wrapper()->post()->has('questions')) {
            $checked_questions = $this->http->wrapper()->post()->retrieve(
                'questions',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }
        $this->__initQuestionObject($this->initObjectiveIdFromQuery());

        // Delete unchecked
        foreach ($this->objectives_qst_obj->getSelfAssessmentQuestions() as $question) {
            $id = $question['ref_id'] . '_' . $question['question_id'];
            if (!in_array($id, $checked_questions)) {
                $this->objectives_qst_obj->delete($question['qst_ass_id']);
            }
        }
        // Add checked
        foreach ($checked_questions as $question_id) {
            list($test_ref_id, $qst_id) = explode('_', $question_id);
            $test_obj_id = $this->objectDataCache->lookupObjId((int) $test_ref_id);

            if ($this->objectives_qst_obj->isSelfAssessmentQuestion($qst_id)) {
                continue;
            }
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT);
            $this->objectives_qst_obj->setTestRefId($test_ref_id);
            $this->objectives_qst_obj->setTestObjId($test_obj_id);
            $this->objectives_qst_obj->setQuestionId($qst_id);
            $this->objectives_qst_obj->add();
        }

        // TODO: not nice
        $this->questions = new ilCourseObjectiveQuestion($this->initObjectiveIdFromQuery());

        if ($checked_questions) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_assigned_lm'));
            $this->selfAssessmentLimits();
        } else {
            switch (ilSession::get('objective_mode')) {
                case self::MODE_CREATE:
                    $this->finalTestAssignment();
                    return;

                case self::MODE_UPDATE:
                    $this->selfAssessmentAssignment();
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_assigned_lm'));
            }
        }
    }

    protected function selfAssessmentLimits() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("self_ass_limits");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_INITIAL_TEST_LIMIT);

        $this->initFormLimits('selfAssessment');
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function updateSelfAssessmentLimits() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());

        $limit = 0;
        if ($this->http->wrapper()->post()->has('limit')) {
            $limit = $this->http->wrapper()->post()->retrieve(
                'limit',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($limit < 1 || $limit > 100) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_objective_err_limit'));
            $this->selfAssessmentLimits();
            return;
        }

        foreach ($this->objectives_qst_obj->getSelfAssessmentTests() as $test) {
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT);
            $this->objectives_qst_obj->setTestSuggestedLimit($limit);
            $this->objectives_qst_obj->updateTest($test['test_objective_id']);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->returnToParent($this);
    }

    protected function finalTestAssignment() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->setSubTabs("final_test_assign");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());

        // begin-patch lok
        $this->ctrl->setParameter($this, 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
        $this->test_type = ilLOSettings::TYPE_TEST_QUALIFIED;
        if ($this->isRandomTestType(ilLOSettings::TYPE_TEST_QUALIFIED)) {
            $this->showRandomTestAssignment();
            return;
        }
        // end-patch lok

        $table = new ilCourseObjectiveQuestionAssignmentTableGUI(
            $this,
            $this->course_obj,
            $this->initObjectiveIdFromQuery(),
            ilCourseObjectiveQuestion::TYPE_FINAL_TEST
        );

        $table->setTitle(
            $this->lng->txt('crs_objective_wiz_final'),
            '',
            $this->lng->txt('crs_objective')
        );
        $table->parse(ilCourseObjectiveQuestion::_getAssignableTests($this->course_obj->getRefId()));
        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_FINAL_TEST_ASSIGNMENT);
        $this->tpl->setContent($table->getHTML());
    }

    protected function isRandomTestType(int $a_tst_type = 0) : bool
    {
        if ($a_tst_type === 0) {
            $a_tst_type = $this->test_type;
        }

        $tst_ref_id = $this->getSettings()->getTestByType($a_tst_type);
        if ($tst_ref_id === 0) {
            return false;
        }
        return ilObjTest::_lookupRandomTest(ilObject::_lookupObjId($tst_ref_id));
    }

    protected function showRandomTestAssignment(ilPropertyFormGUI $form = null) : void
    {
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->ctrl->setParameter($this, 'tt', $this->initTestTypeFromQuery());
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());
        $this->test_type = $this->initTestTypeFromQuery();
        $this->setSubTabs("rand_test_assign");

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormRandom();
        }

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_FINAL_TEST_ASSIGNMENT);
        $this->tpl->setContent($form->getHTML());
    }

    protected function initFormRandom() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($this->test_type == ilLOSettings::TYPE_TEST_INITIAL) {
            $form->setTitle($this->lng->txt('crs_loc_form_random_limits_it'));
        } else {
            $form->setTitle($this->lng->txt('crs_loc_form_random_limits_qt'));
        }

        $form->addCommandButton('saveRandom', $this->lng->txt('save'));

        $options = new ilRadioGroupInputGUI($this->lng->txt('crs_loc_rand_assign_qpl'), 'type');
        $options->setValue('1');
        $options->setRequired(true);

        $ass_qpl = new ilRadioOption($this->lng->txt('crs_loc_rand_assign_qpl'), '1');
        $options->addOption($ass_qpl);

        $qpl = new ilSelectInputGUI($this->lng->txt('crs_loc_rand_qpl'), 'qpl');
        $qpl->setRequired(true);
        $qpl->setMulti(true, false);
        $qpl->setOptions($this->getRandomTestQplOptions());

        $sequences = ilLORandomTestQuestionPools::lookupSequencesByType(
            $this->course_obj->getId(),
            $this->initObjectiveIdFromQuery(),
            ilObject::_lookupObjId($this->getSettings()->getTestByType($this->test_type)),
            $this->test_type
        );

        $qpl->setValue($sequences[0]);
        $qpl->setMultiValues($sequences);
        $ass_qpl->addSubItem($qpl);

        // points
        $per = new ilNumberInputGUI($this->lng->txt('crs_loc_perc'), 'per');
        $per->setValue(
            (string) ilLORandomTestQuestionPools::lookupLimit(
                $this->course_obj->getId(),
                $this->initObjectiveIdFromQuery(),
                $this->test_type
            )
        );
        $per->setSize(3);
        $per->setMinValue(1);
        $per->setMaxValue(100);
        $per->setRequired(true);
        $ass_qpl->addSubItem($per);
        $form->addItem($options);
        return $form;
    }

    protected function getRandomTestQplOptions() : array
    {
        $tst = null;
        $tst_ref_id = $this->getSettings()->getTestByType($this->test_type);
        if ($tst_ref_id) {
            $tst = ilObjectFactory::getInstanceByRefId($tst_ref_id, false);
        }
        if (!$tst instanceof ilObjTest) {
            return array();
        }
        $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $tst,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $this->db,
                $tst
            )
        );

        $list->loadDefinitions();
        $translater = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['DIC']['ilDB']);
        $translater->loadLabels($list);

        $options[0] = $this->lng->txt('select_one');
        foreach ($list as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $title = $definition->getPoolTitle();
            // fau: taxFilter/typeFilter - get title for extended filter conditions
            $filterTitle = array();
            $filterTitle[] = $translater->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter());
            $filterTitle[] = $translater->getTypeFilterLabel($definition->getTypeFilter());
            if (!empty($filterTitle)) {
                $title .= ' -> ' . implode(' / ', $filterTitle);
            }
            $options[$definition->getId()] = $title;
        }
        return $options;
    }

    protected function saveRandom() : void
    {
        $this->ctrl->saveParameter($this, 'objective_id');
        $this->ctrl->setParameter($this, 'tt', $this->initTestTypeFromQuery());
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());
        $this->test_type = $this->initTestTypeFromQuery();

        $form = $this->initFormRandom();
        if ($form->checkInput()) {
            ilLORandomTestQuestionPools::deleteForObjectiveAndTestType(
                $this->course_obj->getId(),
                $this->initObjectiveIdFromQuery(),
                $this->test_type
            );

            $qst = $this->__initQuestionObject($this->initObjectiveIdFromQuery());
            $qst->deleteByTestType(
                ($this->test_type == ilLOSettings::TYPE_TEST_INITIAL) ?
                    ilCourseObjectiveQuestion::TYPE_SELF_ASSESSMENT :
                    ilCourseObjectiveQuestion::TYPE_FINAL_TEST
            );
            $ref_id = $this->getSettings()->getTestByType($this->test_type);
            foreach (array_unique((array) $form->getInput('qpl')) as $qpl_id) {
                $rnd = new ilLORandomTestQuestionPools(
                    $this->course_obj->getId(),
                    $this->initObjectiveIdFromQuery(),
                    $this->test_type,
                    $qpl_id
                );
                $rnd->setLimit($form->getInput('per'));
                $rnd->setTestId(ilObject::_lookupObjId($ref_id));
                $rnd->create();
            }
        } else {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->showRandomTestAssignment($form);
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        if ($this->test_type == ilLOSettings::TYPE_TEST_QUALIFIED) {
            $this->ctrl->returnToParent($this);
        } else {
            $this->ctrl->redirect($this, 'finalTestAssignment');
        }
    }

    protected function updateFinalTestAssignment() : void
    {
        $checked_questions = [];
        if ($this->http->wrapper()->post()->has('questions')) {
            $checked_questions = $this->http->wrapper()->post()->retrieve(
                'questions',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());

        // Delete unchecked
        foreach ($this->objectives_qst_obj->getFinalTestQuestions() as $question) {
            $id = $question['ref_id'] . '_' . $question['question_id'];
            if (!in_array($id, $checked_questions)) {
                $this->objectives_qst_obj->delete($question['qst_ass_id']);
            }
        }
        // Add checked
        foreach ($checked_questions as $question_id) {
            list($test_ref_id, $qst_id) = explode('_', $question_id);
            $test_obj_id = $this->objectDataCache->lookupObjId((int) $test_ref_id);

            if ($this->objectives_qst_obj->isFinalTestQuestion($qst_id)) {
                continue;
            }

            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_FINAL_TEST);
            $this->objectives_qst_obj->setTestRefId($test_ref_id);
            $this->objectives_qst_obj->setTestObjId($test_obj_id);
            $this->objectives_qst_obj->setQuestionId($qst_id);
            $this->objectives_qst_obj->add();
        }

        // TODO: not nice
        $this->questions = new ilCourseObjectiveQuestion($this->initObjectiveIdFromQuery());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_assigned_lm'));
        $this->finalTestLimits();
    }

    /**
     * @todo get rid of this form
     */
    protected function finalTestLimits() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->setSubTabs("final_test_limits");

        $this->ctrl->saveParameter($this, 'objective_id');
        $this->objective = new ilCourseObjective($this->course_obj, $this->initObjectiveIdFromQuery());

        $this->__initQuestionObject($this->initObjectiveIdFromQuery());
        $this->initWizard(self::STEP_FINAL_TEST_LIMIT);

        $this->initFormLimits('final');
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function updateFinalTestLimits() : void
    {
        if (!$this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->WARNING);
        }
        if (!$this->initObjectiveIdFromQuery()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_no_objective_selected'), true);
            $this->ctrl->redirect($this, 'listObjectives');
        }
        $this->__initQuestionObject($this->initObjectiveIdFromQuery());

        $limit = 0;
        if ($this->http->wrapper()->post()->has('limit')) {
            $limit = $this->http->wrapper()->post()->retrieve(
                'limit',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($limit < 1 || $limit > 100) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_objective_err_limit'));
            $this->finalTestLimits();
            return;
        }

        foreach ($this->objectives_qst_obj->getFinalTests() as $test) {
            $this->objectives_qst_obj->setTestStatus(ilCourseObjectiveQuestion::TYPE_FINAL_TEST);
            $this->objectives_qst_obj->setTestSuggestedLimit($limit);
            $this->objectives_qst_obj->updateTest($test['test_objective_id']);
        }

        if (ilSession::get('objective_mode') != self::MODE_CREATE) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_added_objective'), true);
        }
        $this->ctrl->returnToParent($this);
    }

    protected function initFormLimits(string $a_mode) : ilPropertyFormGUI
    {
        if (!is_object($this->form)) {
            $this->form = new ilPropertyFormGUI();
        }
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTableWidth('100%');
        //$this->form->setTitleIcon(ilUtil::getImagePath('icon_lobj.svg'),$this->lng->txt('crs_objective'));

        $tests = [];
        $max_points = 0;
        switch ($a_mode) {
            case 'selfAssessment':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_self_limit'));
                $this->form->addCommandButton('updateSelfAssessmentLimits', $this->lng->txt('crs_wiz_next'));

                $tests = $this->objectives_qst_obj->getSelfAssessmentTests();
                $max_points = $this->objectives_qst_obj->getSelfAssessmentPoints();

                break;

            case 'final':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_final_limit'));
                $this->form->addCommandButton('updateFinalTestLimits', $this->lng->txt('crs_wiz_next'));

                $tests = $this->objectives_qst_obj->getFinalTests();
                $max_points = $this->objectives_qst_obj->getFinalTestPoints();

                break;
        }

        $over = new ilCustomInputGUI($this->lng->txt('crs_objective_qst_summary'), '');

        $tpl = new ilTemplate('tpl.crs_objective_qst_summary.html', true, true, 'Modules/Course');

        $limit = 0;

        foreach ($tests as $test) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($test, true));

            $limit = $test['limit'];

            foreach ($this->objectives_qst_obj->getQuestionsOfTest($test['obj_id']) as $question) {
                $tpl->setCurrentBlock('qst');
                $tpl->setVariable('QST_TITLE', $question['title']);
                if (strlen($question['description'])) {
                    $tpl->setVariable('QST_DESCRIPTION', $question['description']);
                }
                $tpl->setVariable('QST_POINTS', $question['points'] . ' ' .
                    $this->lng->txt('crs_objective_points'));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('tst');
            $tpl->setVariable('TST_TITLE', ilObject::_lookupTitle($test['obj_id']));
            if ($desc = ilObject::_lookupDescription($test['obj_id'])) {
                $tpl->setVariable('TST_DESC', $desc);
            }
            $tpl->setVariable('TST_TYPE_IMG', ilObject::_getIcon($test['obj_id'], 'tiny', 'tst'));
            $tpl->setVariable('TST_ALT_IMG', $this->lng->txt('obj_tst'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('TXT_ALL_POINTS', $this->lng->txt('crs_objective_all_points'));
        $tpl->setVariable('TXT_POINTS', $this->lng->txt('crs_objective_points'));
        $tpl->setVariable('POINTS', $max_points);

        $over->setHtml($tpl->get());
        $this->form->addItem($over);

        // points
        $req = new ilNumberInputGUI($this->lng->txt('crs_loc_perc'), 'limit');
        $req->setValue($limit);
        $req->setSize(3);
        $req->setMinValue(1);
        $req->setMaxValue(100);
        $req->setRequired(true);
        switch ($a_mode) {
            case 'selfAssessment':
                $req->setInfo($this->lng->txt('crs_obj_initial_req_info'));
                break;

            case 'final':
                $req->setInfo($this->lng->txt('crs_obj_final_req_info'));
                break;
        }
        $this->form->addItem($req);
        return $this->form;
    }

    protected function initFormTitle(string $a_mode) : ilPropertyFormGUI
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        //$this->form->setTitleIcon(ilUtil::getImagePath('icon_lobj.svg'),$this->lng->txt('crs_objective'));

        switch ($a_mode) {
            case 'create':
                $this->form->setTitle($this->lng->txt('crs_objective_wiz_title'));
                $this->form->addCommandButton('save', $this->lng->txt('crs_wiz_next'));
                // begin-patch lok
                #$this->form->addCommandButton('listObjectives',$this->lng->txt('cancel'));
                // end-patch lok
                break;

            case 'update':
                break;
        }

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->objective->getTitle());
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(70);
        $this->form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $desc->setValue($this->objective->getDescription());
        $desc->setCols(40);
        $desc->setRows(5);
        $this->form->addItem($desc);
        return $this->form;
    }

    protected function initWizard(int $active_step) : void
    {
        $steps = [];
        $step_positions = [];

        // 1 Settings
        $title = $this->lng->txt('crs_objective_wiz_title');
        $link = $this->ctrl->getLinkTarget($this, 'edit');

        $steps[] = $this->workflow->step($title, "", $link);
        $step_positions[self::STEP_SETTINGS] = count($steps) - 1;

        // 2 Material
        $title = $this->lng->txt('crs_objective_wiz_materials');
        $link = $this->ctrl->getLinkTarget($this, 'materialAssignment');
        $steps[] = $this->workflow->step($title, "", $link);
        $step_positions[self::STEP_MATERIAL_ASSIGNMENT] = count($steps) - 1;

        if ($this->getSettings()->worksWithInitialTest() && !$this->getSettings()->hasSeparateInitialTests()) {
            // 3 initial
            $title = $this->lng->txt('crs_objective_wiz_self');
            $link = $this->getSettings()->worksWithInitialTest()
                ? $this->ctrl->getLinkTarget($this, 'selfAssessmentAssignment')
                : null;

            $steps[] = $this->workflow->step($title, "", $link)
                                      ->withAvailability($link == null ? Step::NOT_AVAILABLE : Step::AVAILABLE);
            $step_positions[self::STEP_INITIAL_TEST_ASSIGNMENT] = count($steps) - 1;

            if (!$this->isRandomTestType(ilLOSettings::TYPE_TEST_INITIAL)) {
                // 4 initial limit
                $title = $this->lng->txt('crs_objective_wiz_self_limit');
                $link = count($this->objectives_qst_obj->getSelfAssessmentQuestions())
                && $this->getSettings()->worksWithInitialTest()
                    ? $this->ctrl->getLinkTarget($this, 'selfAssessmentLimits')
                    : null;
                $steps[] = $this->workflow->step($title, "", $link)
                                          ->withAvailability($link == null ? Step::NOT_AVAILABLE : Step::AVAILABLE);
                $step_positions[self::STEP_INITIAL_TEST_LIMIT] = count($steps) - 1;
            }
        }

        if (!$this->getSettings()->hasSeparateQualifiedTests()) {
            // 5 final
            $title = $this->lng->txt('crs_objective_wiz_final');
            $link = $this->ctrl->getLinkTarget($this, 'finalTestAssignment');
            $steps[] = $this->workflow->step($title, "", $link);
            $step_positions[self::STEP_FINAL_TEST_ASSIGNMENT] = count($steps) - 1;

            if (!$this->isRandomTestType(ilLOSettings::TYPE_TEST_QUALIFIED)) {
                // 6 final limit
                $title = $this->lng->txt('crs_objective_wiz_final_limit');
                $link = count($this->objectives_qst_obj->getFinalTestQuestions())
                    ? $this->ctrl->getLinkTarget($this, 'finalTestLimits')
                    : null;
                $steps[] = $this->workflow->step($title, "", $link)
                                          ->withAvailability($link == null ? Step::NOT_AVAILABLE : Step::AVAILABLE);
                $step_positions[self::STEP_FINAL_TEST_LIMIT] = count($steps) - 1;
            }
        }

        $list = $this->workflow->linear(
            $this->lng->txt('crs_checklist_objective'),
            $steps
        );
        if (!empty($step_positions[$active_step])) {
            $list = $list->withActive($step_positions[$active_step]);
        }
        $this->tpl->setRightContent($this->renderer->render($list));
    }
}
