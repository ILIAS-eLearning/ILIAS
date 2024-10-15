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

declare(strict_types=1);

namespace ILIAS\Test\Questions;

use ILIAS\Taxonomy\DomainService as TaxonomyService;
use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Component\Table\Action\Standard as TableAction;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ilTestQuestionBrowserTableGUI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data;

class QuestionsBrowserTable implements DataRetrieval
{
    public const ACTION_INSERT = 'insert';

    private ?array $records = null;

    public function __construct(
        protected readonly string $table_id,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected \ilLanguage $lng,
        protected \ilCtrl $ctrl,
        protected DataFactory $data_factory,
        protected \ilAssQuestionList $question_list,
        protected \ilObjTest $test_obj,
        protected \ilTree $tree,
        protected RequestDataCollector $testrequest,
        protected TaxonomyService $taxonomy,
        protected \Closure $getQuestionPoolLink,
        protected string $parent_title
    ) {
    }

    public function getComponent(ServerRequestInterface $request, ?array $filter): Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('list_of_questions'),
            $this->getColumns(),
            $this
        )
            ->withId($this->table_id)
            ->withActions($this->getActions())
            ->withRequest($request)
            ->withFilter($filter);
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $icon_factory = $this->ui_factory->symbol()->icon();
        $iconYes = $icon_factory->custom('assets/images/standard/icon_checked.svg', 'yes');
        $iconNo = $icon_factory->custom('assets/images/standard/icon_unchecked.svg', 'no');
        $dateFormat = $this->data_factory->dateFormat()->withTime24($this->data_factory->dateFormat()->germanShort());

        $columns = [
            'title' => $column_factory->text(
                $this->lng->txt('tst_question_title')
            )->withIsOptional(false, true),
            'description' => $column_factory->text(
                $this->lng->txt('description')
            )->withIsOptional(true, true),
            'type_tag' => $column_factory->text(
                $this->lng->txt('tst_question_type')
            )->withIsOptional(false, true),
            'points' => $column_factory->number(
                $this->lng->txt('points')
            )->withIsOptional(false, true),
            'author' => $column_factory->text(
                $this->lng->txt('author')
            )->withIsOptional(true, false),
            'lifecycle' => $column_factory->text(
                $this->lng->txt('qst_lifecycle')
            )->withIsOptional(true, false),
            'parent_title' => $column_factory->text(
                $this->lng->txt($this->parent_title)
            )->withIsOptional(false, true),
            'taxonomies' => $column_factory->text(
                $this->lng->txt('qpl_settings_subtab_taxonomies')
            )->withIsOptional(false, true),
            'feedback' => $column_factory->boolean(
                $this->lng->txt('feedback'),
                $iconYes,
                $iconNo
            )->withIsOptional(true, false),
            'hints' => $column_factory->boolean(
                $this->lng->txt('hints'),
                $iconYes,
                $iconNo
            )->withIsOptional(true, false),
            'created' => $column_factory->date(
                $this->lng->txt('created'),
                $dateFormat
            )->withIsOptional(true, false),
            'tstamp' => $column_factory->date(
                $this->lng->txt('updated'),
                $dateFormat
            )->withIsOptional(true, false)
        ];

        return array_map(static fn(Column $column): Column => $column->withIsSortable(true), $columns);
    }

    /**
     * @return array<string, TableAction>
     * @throws \ilCtrlException
     */
    public function getActions(): array
    {
        return [self::ACTION_INSERT => $this->getInsertAction()];
    }

    /**
     * @throws \ilCtrlException
     */
    private function getInsertAction(): TableAction
    {
        $url_builder = new URLBuilder($this->data_factory->uri(
            ServerRequest::getUriFromGlobals() . $this->ctrl->getLinkTargetByClass(
                ilTestQuestionBrowserTableGUI::class,
                ilTestQuestionBrowserTableGUI::CMD_INSERT_QUESTIONS
            )
        ));

        [$url_builder, $row_id_token] = $url_builder->acquireParameters(['qlist'], 'q_id');

        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('insert'),
            $url_builder,
            $row_id_token
        );
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->getViewControlledRecords($filter_data, $range, $order) as $record) {
            $question_id = $record['question_id'];

            $record['type_tag'] = $this->lng->txt($record['type_tag']);
            $record['complete'] = (bool) $record['complete'];
            $record['lifecycle'] = \ilAssQuestionLifecycle::getInstance($record['lifecycle'])->getTranslation($this->lng) ?? '';
            $record['qpl'] = call_user_func($this->getQuestionPoolLink, $record['orig_obj_fi'] ?? null);

            $record['created'] = (new \DateTimeImmutable())->setTimestamp($record['created']);
            $record['tstamp'] = (new \DateTimeImmutable())->setTimestamp($record['tstamp']);
            $record['taxonomies'] = $this->resolveTaxonomiesRowData($record['obj_fi'], $question_id);

            yield $row_builder->buildDataRow((string) $question_id, $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): int
    {
        return count($this->loadRecords($filter_data));
    }

    private function getViewControlledRecords(?array $filter_data, Range $range, Order $order): array
    {
        return $this->limitRecords(
            $this->sortRecords(
                $this->loadRecords($filter_data),
                $order
            ),
            $range
        );
    }

    private function loadRecords(?array $filter): array
    {
        $filter ??= [];
        if ($this->records !== null) {
            return $this->records;
        }

        if ($this->testrequest->raw(ilTestQuestionBrowserTableGUI::MODE_PARAMETER) === ilTestQuestionBrowserTableGUI::MODE_BROWSE_TESTS) {
            $this->question_list->setParentObjectType('tst');
            $this->question_list->setQuestionInstanceTypeFilter(\ilAssQuestionList::QUESTION_INSTANCE_TYPE_ALL);
            $this->question_list->setExcludeQuestionIdsFilter($this->test_obj->getQuestions());
        } else {
            $this->question_list->setParentObjIdsFilter($this->getQuestionParentObjIds(ilTestQuestionBrowserTableGUI::REPOSITORY_ROOT_NODE_ID));
            $this->question_list->setQuestionInstanceTypeFilter(\ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
            $this->question_list->setExcludeQuestionIdsFilter($this->test_obj->getExistingQuestions());
        }

        foreach (array_filter($filter) as $item => $value) {
            switch ($item) {
                case 'title':
                case 'description':
                case 'type':
                case 'author':
                case 'lifecycle':
                case 'parent_title':
                case 'taxonomy_title':
                case 'taxonomy_node_title':
                    if ($value !== '') {
                        $this->question_list->addFieldFilter($item, $value);
                    }
                    break;
                case 'commented':
                    if ($value !== '') {
                        $this->question_list->setCommentFilter((int) $value);
                    }
                    break;
                case 'taxonomies':
                    if ($value === '') {
                        $this->question_list->addTaxonomyFilterNoTaxonomySet(true);
                        break;
                    }

                    $tax_nodes = explode('-', $value);
                    $this->question_list->addTaxonomyFilter(
                        array_shift($tax_nodes),
                        $tax_nodes,
                        $this->test_obj->getId(),
                        $this->test_obj->getType()
                    );
                    break;
                default:
                    $this->question_list->addFieldFilter($item, $value);
            }
        }

        $this->question_list->load();
        $records = $this->filterRecordsByTaxonomyOrNodeTitle(
            $this->question_list->getQuestionDataArray(),
            $filter['taxonomy_title'] ?? '',
            $filter['taxonomy_node_title'] ?? ''
        );

        return $this->records = $records;
    }

    private function filterRecordsByTaxonomyOrNodeTitle(array $records, string $taxonomyTitle = '', string $taxonomyNodeTitle = ''): array
    {
        if (empty($taxonomyTitle) && empty($taxonomyNodeTitle)) {
            return $records;
        }

        foreach ($records as $key => $record) {
            $obj_fi = $record['obj_fi'];
            $data = $this->loadTaxonomyAssignmentData($obj_fi, $key, $this->taxonomy->getUsageOfObject($obj_fi));
            $safe = false;

            foreach ($data as $taxonomyId => $taxData) {
                $titleCheck = empty($taxonomyTitle) || is_int(mb_stripos(\ilObject::_lookupTitle($taxonomyId), $taxonomyTitle));

                if ($titleCheck) {
                    foreach ($taxData as $node) {
                        if (empty($taxonomyNodeTitle) || is_int(mb_stripos(\ilTaxonomyNode::_lookupTitle($node['node_id']), $taxonomyNodeTitle))) {
                            $safe = true;
                            break 2;
                        }
                    }
                }
            }

            if (!$safe) {
                unset($records[$key]);
            }
        }

        return $records;
    }

    private function getQuestionParentObjIds(int $repositoryRootNode): array
    {
        $parents = $this->tree->getSubTree(
            $this->tree->getNodeData($repositoryRootNode),
            true,
            ['qpl']
        );

        $parentIds = [];

        foreach ($parents as $nodeData) {
            if ((int) $nodeData['obj_id'] === $this->test_obj->getId()) {
                continue;
            }

            $parentIds[$nodeData['obj_id']] = $nodeData['obj_id'];
        }

        $parentIds = array_map('intval', array_values($parentIds));
        $available_pools = array_map('intval', array_keys(\ilObjQuestionPool::_getAvailableQuestionpools(true)));
        return array_intersect($parentIds, $available_pools);
    }

    /**
     * @throws \ilTaxonomyException
     */
    private function resolveTaxonomiesRowData(int $obj_fi, int $questionId): string
    {
        $available_taxonomy_ids = $this->taxonomy->getUsageOfObject($obj_fi);
        $data = $this->loadTaxonomyAssignmentData($obj_fi, $questionId, $available_taxonomy_ids);

        $taxonomies = [];

        foreach ($data as $taxonomyId => $taxData) {
            $taxonomies[] = \ilObject::_lookupTitle($taxonomyId);
            $taxonomies[] = $this->ui_renderer->render(
                $this->ui_factory->listing()->unordered(
                    array_map(static function ($node) {
                        return \ilTaxonomyNode::_lookupTitle($node['node_id']);
                    }, $taxData)
                )
            );
        }

        return implode('', $taxonomies);
    }

    /**
     * @throws \ilTaxonomyException
     */
    private function loadTaxonomyAssignmentData(int $parentObjId, int $questionId, array $available_taxonomy_ids): array
    {
        $taxonomyAssignmentData = [];

        foreach ($available_taxonomy_ids as $taxId) {
            $taxTree = new \ilTaxonomyTree($taxId);
            $assignments = (new \ilTaxNodeAssignment(
                'qpl',
                $parentObjId,
                'quest',
                $taxId
            ))->getAssignmentsOfItem($questionId);

            foreach ($assignments as $assData) {
                $taxId = $assData['tax_id'];
                if (!isset($taxonomyAssignmentData[$taxId])) {
                    $taxonomyAssignmentData[$taxId] = [];
                }

                $nodeId = $assData['node_id'];
                $assData['node_lft'] = $taxTree->getNodeData($nodeId)['lft'];
                $taxonomyAssignmentData[$taxId][$nodeId] = $assData;
            }
        }

        return $taxonomyAssignmentData;
    }

    private function sortRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            '',
            fn(string $index, string $key, string $value): array => [$key, $value]
        );

        usort($records, static function (array $a, array $b) use ($order_field): int {
            if (is_numeric($a[$order_field]) || is_bool($a[$order_field]) || is_array($a[$order_field])) {
                return $a[$order_field] <=> $b[$order_field];
            }

            return strcmp($a[$order_field] ?? '', $b[$order_field] ?? '');
        });

        return $order_direction === $order::DESC ? array_reverse($records) : $records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return \array_slice($records, $range->getStart(), $range->getLength());
    }
}
