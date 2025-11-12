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

declare(strict_types=0);

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\URLBuilder;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class ilLOTestAssignmentTableGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOTestAssignmentTableGUI
{
    public const int TYPE_MULTIPLE_ASSIGNMENTS = 1;
    public const int TYPE_SINGLE_ASSIGNMENTS = 2;
    public const string TABLE_COL_TITLE = 'title';
    public const string TABLE_COL_COURSE_OBJECTIVES = 'objective';
    public const string TABLE_COL_SELECTION_OF_TEST_QUESTIONS = 'ttype';
    public const string TABLE_COL_QESTIONS = 'qstqpl';
    protected const string ALL_OBJECTS = "ALL_OBJECTS";
    protected const string TABLE_ID = 'lotstasstbl';
    protected const string ROW_ID = 'row_ids';
    protected const string TABLE_ACTION_ID = 'table_action';
    protected const string TABLE_ACTION_CONFIRM_DELETE_TESTS = 'confirmDeleteTests';
    protected const string TABLE_ACTION_CONFIRM_DELETE_TEST = 'confirmDeleteTest';
    protected const string ACTION_CONFIRM_DELETE_TEST = "delete_selected_test";
    protected const string ACTION_CONFIRM_DELETE_TESTS = "delete_selected_tests";
    protected const string LNG_TABLE_COL_TITLE = 'title';
    protected const string LNG_TABLE_COL_COURSE_OBJECTIVES = 'crs_objectives';
    protected const string LNG_TABLE_COL_SELECTION_OF_TEST_QUESTIONS = 'crs_loc_tbl_tst_type';
    protected const string LNG_TABLE_COL_QESTIONS = 'crs_loc_tbl_tst_qst_qpl';
    protected const string LNG_TABLE_ACTION_CONFIRM_DELETE_TESTS = 'crs_loc_delete_assignment';
    protected const string LNG_TABLE_ACTION_CONFIRM_DELETE_TEST = 'crs_loc_delete_assignment';

    protected URLBuilder $url_builder;
    protected ilDataFactory $data_factory;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilDataTable $table;
    protected ilLOSettings $settings;

    public function __construct(
        protected int $assignment_type,
        protected int $test_type,
        protected int $container_id,
        protected ilLanguage $lng,
        protected ilUIServices $ui_services,
        protected ilHTTPServices $http_services,
        protected ilLOTestAssignmentTableDataRetrieval $data_retrieval,
        protected ilRefineryFactory $refinery,
        protected ilCtrl $ctrl,
        protected object $parent_object
    ) {
        $this->data_factory = new ilDataFactory();
        $this->settings = ilLOSettings::getInstanceByObjId($container_id);
    }

    protected function getColumns(): array
    {
        $columns = [];
        $columns[self::TABLE_COL_TITLE] = $this->ui_services->factory()->table()->column()->link(
            $this->lng->txt(self::LNG_TABLE_COL_TITLE)
        )->withIsSortable(true);
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $columns[self::TABLE_COL_COURSE_OBJECTIVES] = $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_COURSE_OBJECTIVES)
            )->withIsSortable(false);
        }
        $columns[self::TABLE_COL_SELECTION_OF_TEST_QUESTIONS] = $this->ui_services->factory()->table()->column()->text(
            $this->lng->txt(self::LNG_TABLE_COL_SELECTION_OF_TEST_QUESTIONS)
        )->withIsSortable(false);
        $columns[self::TABLE_COL_QESTIONS] = $this->ui_services->factory()->table()->column()->text(
            $this->lng->txt(self::LNG_TABLE_COL_QESTIONS)
        )->withIsSortable(false);
        return $columns;
    }

    protected function getActions(): array
    {
        $this->url_builder = new URLBuilder($this->data_factory->uri($this->http_services->request()->getUri()->__toString()));
        list($this->url_builder, $this->action_parameter_token, $this->row_id_token) =
            $this->url_builder->acquireParameters(
                ['datatable', self::TABLE_ID],
                self::TABLE_ACTION_ID,
                self::ROW_ID
            );
        $actions = [];
        if ($this->getAssignmentType() === self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $actions[self::TABLE_ACTION_CONFIRM_DELETE_TESTS] = $this->ui_services->factory()->table()->action()->multi(
                $this->lng->txt(self::LNG_TABLE_ACTION_CONFIRM_DELETE_TESTS),
                $this->url_builder->withParameter($this->action_parameter_token, self::TABLE_ACTION_CONFIRM_DELETE_TESTS),
                $this->row_id_token,
            )->withAsync();
        }
        if ($this->getAssignmentType() !== self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $actions[self::TABLE_ACTION_CONFIRM_DELETE_TESTS] = $this->ui_services->factory()->table()->action()->multi(
                $this->lng->txt(self::LNG_TABLE_ACTION_CONFIRM_DELETE_TEST),
                $this->url_builder->withParameter($this->action_parameter_token, self::TABLE_ACTION_CONFIRM_DELETE_TEST),
                $this->row_id_token,
            )->withAsync();
        }
        return $actions;
    }

    protected function getTitleLangVar(): string
    {
        switch ($this->test_type) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                if (
                    $this->getAssignmentType() === self::TYPE_SINGLE_ASSIGNMENTS &&
                    $this->getSettings()->isInitialTestQualifying()
                ) {
                    return 'crs_loc_settings_tbl_its_q_all';
                }
                if (
                    $this->getAssignmentType() === self::TYPE_SINGLE_ASSIGNMENTS &&
                    !$this->getSettings()->isInitialTestQualifying()
                ) {
                    return 'crs_loc_settings_tbl_its_nq_all';
                }
                if ($this->getSettings()->isInitialTestQualifying()) {
                    return 'crs_loc_settings_tbl_it_q';
                }
                if (!$this->getSettings()->isInitialTestQualifying()) {
                    return 'crs_loc_settings_tbl_it_nq';
                }
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                if ($this->getAssignmentType() === self::TYPE_SINGLE_ASSIGNMENTS) {
                    return 'crs_loc_settings_tbl_qts_all';
                }
                if ($this->getAssignmentType() !== self::TYPE_SINGLE_ASSIGNMENTS) {
                    return 'crs_loc_settings_tbl_qt';
                }
                break;
        }
        return 'lng_title_missing';
    }

    protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $this->table = $this->ui_services->factory()->table()->data(
            $this->data_retrieval,
            $this->lng->txt($this->getTitleLangVar()),
            $this->getColumns()
        )
            ->withId(self::TABLE_ID)
            ->withActions($this->getActions())
            ->withRequest($this->http_services->request());
    }

    protected function allIds(): array
    {
        return $this->data_retrieval->allIds();
    }

    protected function readIdsFromQuery(): array
    {
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        return is_null($tokens)
            ? []
            : (is_array($tokens) ? $tokens : [$tokens]);
    }

    #[NoReturn] protected function showDeleteModal(
        array $id_map,
        string $url_action
    ): void {
        $items = [];
        foreach ($id_map as $id => $obj_id) {
            $items[] = $this->ui_services->factory()->modal()->interruptiveItem()->standard(
                $id,
                ilObject::_lookupTitle($obj_id),
            );
        }
        echo($this->ui_services->renderer()->renderAsync([
            $this->ui_services->factory()->modal()->interruptive(
                $this->lng->txt('crs_loc_delete_assignment'),
                $this->lng->txt('crs_loc_confirm_delete_tst'),
                (string) $this->url_builder
                    ->withParameter(
                        $this->action_parameter_token,
                        $url_action
                    )->withParameter(
                        $this->row_id_token,
                        array_keys($id_map)
                    )->buildURI()
            )->withAffectedItems($items)
        ]));
        exit();
    }

    protected function deleteTests(array $ids): void
    {
        foreach ($ids as $assign_id) {
            $assignment = new ilLOTestAssignment($assign_id);
            $assignment->delete();

            // finally delete start object assignment
            $start = new ilContainerStartObjects(
                $this->getParentObject()->getRefId(),
                $this->getParentObject()->getId()
            );
            $start->deleteItem($assignment->getTestRefId());

            // ... and assigned questions
            ilCourseObjectiveQuestion::deleteTest($assignment->getTestRefId());
        }
        $this->ctrl->redirectByClass('ilLOEditorGUI', 'testOverview');
    }

    protected function deleteTest(array $ids): void
    {
        $settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        foreach ($ids as $tst_id) {
            switch ($this->getTestType()) {
                case ilLOSettings::TYPE_TEST_INITIAL:
                    $settings->setInitialTest(0);
                    break;

                case ilLOSettings::TYPE_TEST_QUALIFIED:
                    $settings->setQualifiedTest(0);
                    break;
            }
            $settings->update();

            // finally delete start object assignment
            $start = new ilContainerStartObjects(
                $this->getParentObject()->getRefId(),
                $this->getParentObject()->getId()
            );
            $start->deleteItem($tst_id);

            // ... and assigned questions
            ilCourseObjectiveQuestion::deleteTest($tst_id);
        }
        $this->ctrl->redirectByClass('ilLOEditorGUI', 'testOverview');
    }

    public function getHTML(): string
    {
        $this->initTable();
        return $this->ui_services->renderer()->render([$this->table]);
    }

    public function getAssignmentType(): int
    {
        return $this->assignment_type;
    }

    public function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    public function getParentObject(): object
    {
        return $this->parent_object;
    }

    public function getTestType(): int
    {
        return $this->test_type;
    }

    public function handleCommands(): void
    {
        $this->initTable();
        if (!$this->http_services->wrapper()->query()->has($this->action_parameter_token->getName())) {
            return;
        }
        $action = $this->http_services->wrapper()->query()->retrieve(
            $this->action_parameter_token->getName(),
            $this->refinery->to()->string()
        );
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        $all_entries = ($tokens[0] ?? "") === self::ALL_OBJECTS;
        $ids = [];
        if ($all_entries) {
            $ids = $this->allIds();
        }
        if (!$all_entries) {
            $ids = $this->readIdsFromQuery();
        }
        if (is_null($ids[0]) || count($ids) === 0) {
            return;
        }
        switch ($action) {
            case self::TABLE_ACTION_CONFIRM_DELETE_TEST:
                $id_map = [];
                foreach ($ids as $id) {
                    $id_map[$id] = ilObject::_lookupObjId($id);
                    ;
                }
                $this->showDeleteModal($id_map, self::ACTION_CONFIRM_DELETE_TEST);
                break;
            case self::TABLE_ACTION_CONFIRM_DELETE_TESTS:
                $id_map = [];
                foreach ($ids as $id) {
                    $assignment = new ilLOTestAssignment($id);
                    $id_map[$id] = ilObject::_lookupObjId($assignment->getTestRefId());
                }
                $this->showDeleteModal($id_map, self::ACTION_CONFIRM_DELETE_TESTS);
                break;
            case self::ACTION_CONFIRM_DELETE_TEST:
                $this->deleteTest($ids);
                break;
            case self::ACTION_CONFIRM_DELETE_TESTS:
                $this->deleteTests($ids);
                break;
        }
    }
}
