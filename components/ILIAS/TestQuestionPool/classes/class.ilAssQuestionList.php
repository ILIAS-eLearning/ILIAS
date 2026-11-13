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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Notes\Service as NotesService;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Handles a list of questions
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/TestQuestionPool
 *
 */
class ilAssQuestionList implements ilTaxAssignedItemInfo
{
    private array $parentObjIdsFilter = [];
    private ?int $parentObjId = null;
    private string $parentObjType = 'qpl';
    private array $availableTaxonomyIds = [];
    private array $fieldFilters = [];
    private array $taxFilters = [];
    private bool $taxFiltersExcludeAnyObjectsWithTaxonomies = false;
    private array $taxParentIds = [];
    private array $taxParentTypes = [];
    private ?int $answerStatusActiveId = null;
    protected bool $join_obj_data = true;

    /**
     * answer status domain for single questions
     */
    public const QUESTION_ANSWER_STATUS_NON_ANSWERED = 'nonAnswered';
    public const QUESTION_ANSWER_STATUS_WRONG_ANSWERED = 'wrongAnswered';
    public const QUESTION_ANSWER_STATUS_CORRECT_ANSWERED = 'correctAnswered';

    /**
     * answer status filter value domain
     */
    public const ANSWER_STATUS_FILTER_ALL_NON_CORRECT = 'allNonCorrect';
    public const ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY = 'nonAnswered';
    public const ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY = 'wrongAnswered';

    private string $answerStatusFilter = '';

    public const QUESTION_INSTANCE_TYPE_ORIGINALS = 'QST_INSTANCE_TYPE_ORIGINALS';
    public const QUESTION_INSTANCE_TYPE_DUPLICATES = 'QST_INSTANCE_TYPE_DUPLICATES';
    public const QUESTION_INSTANCE_TYPE_ALL = 'QST_INSTANCE_TYPE_ALL';
    private string $questionInstanceTypeFilter = self::QUESTION_INSTANCE_TYPE_ORIGINALS;

    private array $includeQuestionIdsFilter = [];
    private array $excludeQuestionIdsFilter = [];

    public const QUESTION_COMMENTED_ONLY = '1';
    public const QUESTION_COMMENTED_EXCLUDED = '2';
    protected ?string $filter_comments = null;

    protected array $questions = [];

    private ?Order $order = null;
    private ?Range $range = null;

    private ilComponentFactory $component_factory;

    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng,
        private Refinery $refinery,
        private ilComponentRepository $component_repository,
        private ?NotesService $notes_service = null
    ) {
        global $DIC;
        $this->component_factory = $DIC['component.factory'];
    }

    public function setOrder(?Order $order = null): void
    {
        $this->order = $order;
    }

    public function setRange(?Range $range = null): void
    {
        $this->range = $range;
    }

    public function getParentObjId(): ?int
    {
        return $this->parentObjId;
    }

    public function setParentObjId(?int $parentObjId): void
    {
        $this->parentObjId = $parentObjId;
    }

    public function setParentObjectType(string $parentObjType): void
    {
        $this->parentObjType = $parentObjType;
    }

    public function setParentObjIdsFilter(array $parentObjIdsFilter): void
    {
        $this->parentObjIdsFilter = $parentObjIdsFilter;
    }

    public function setQuestionInstanceTypeFilter(?string $questionInstanceTypeFilter): void
    {
        $this->questionInstanceTypeFilter = (string) $questionInstanceTypeFilter;
    }

    public function setIncludeQuestionIdsFilter(array $questionIdsFilter): void
    {
        $this->includeQuestionIdsFilter = $questionIdsFilter;
    }

    public function setExcludeQuestionIdsFilter(array $excludeQuestionIdsFilter): void
    {
        $this->excludeQuestionIdsFilter = $excludeQuestionIdsFilter;
    }

    public function addFieldFilter(string $fieldName, mixed $fieldValue): void
    {
        $this->fieldFilters[$fieldName] = $fieldValue;
    }

    public function addTaxonomyFilter($taxId, $taxNodes, $parentObjId, $parentObjType): void
    {
        $this->taxFilters[$taxId] = $taxNodes;
        $this->taxParentIds[$taxId] = $parentObjId;
        $this->taxParentTypes[$taxId] = $parentObjType;
    }

    public function addTaxonomyFilterNoTaxonomySet(bool $flag): void
    {
        $this->taxFiltersExcludeAnyObjectsWithTaxonomies = $flag;
    }

    public function setAvailableTaxonomyIds(array $availableTaxonomyIds): void
    {
        $this->availableTaxonomyIds = $availableTaxonomyIds;
    }

    public function setAnswerStatusActiveId(?int $answerStatusActiveId): void
    {
        $this->answerStatusActiveId = $answerStatusActiveId;
    }

    public function setAnswerStatusFilter(string $answerStatusFilter): void
    {
        $this->answerStatusFilter = $answerStatusFilter;
    }

    /**
     * Set if object data table should be joined
     */
    public function setJoinObjectData(bool $a_val): void
    {
        $this->join_obj_data = $a_val;
    }

    protected function getParentObjFilterExpression(): ?string
    {
        if ($this->parentObjId) {
            return "qpl_questions.obj_fi = {$this->db->quote($this->parentObjId, ilDBConstants::T_INTEGER)}";
        }

        if (!empty($this->parentObjIdsFilter)) {
            return $this->db->in('qpl_questions.obj_fi', $this->parentObjIdsFilter, false, ilDBConstants::T_INTEGER);
        }

        return null;
    }

    private function getFieldFilterExpressions(): array
    {
        $expressions = [];

        foreach ($this->fieldFilters as $fieldName => $fieldValue) {
            switch ($fieldName) {
                case 'title':
                case 'description':
                case 'author':
                case 'lifecycle':
                    $expressions[] = $this->db->like("qpl_questions.$fieldName", ilDBConstants::T_TEXT, "%%$fieldValue%%");
                    break;
                case 'type':
                    $expressions[] = "qpl_qst_type.type_tag = {$this->db->quote($fieldValue, ilDBConstants::T_TEXT)}";
                    break;
                case 'question_id':
                    if ($fieldValue !== '' && !is_array($fieldValue)) {
                        $fieldValue = [$fieldValue];
                    }
                    $expressions[] = $this->db->in('qpl_questions.question_id', $fieldValue, false, ilDBConstants::T_INTEGER);
                    break;
                case 'parent_title':
                    if ($this->join_obj_data) {
                        $expressions[] = $this->db->like('object_data.title', ilDBConstants::T_TEXT, "%%$fieldValue%%");
                    }
                    break;
            }
        }

        return $expressions;
    }

    private function handleFeedbackJoin(string $tableJoin): string
    {
        $feedback_join = match ($this->fieldFilters['feedback'] ?? null) {
            'true' => 'INNER',
            'false' => 'LEFT',
            default => null
        };

        if (isset($feedback_join)) {
            $SQL = "$feedback_join JOIN qpl_fb_generic ON qpl_fb_generic.question_fi = qpl_questions.question_id ";
            $tableJoin .= !str_contains($tableJoin, $SQL) ? $SQL : '';
        }

        return $tableJoin;
    }

    private function handleHintJoin(string $tableJoin): string
    {
        $feedback_join = match ($this->fieldFilters['hints'] ?? null) {
            'true' => 'INNER',
            'false' => 'LEFT',
            default => null
        };

        if (isset($feedback_join)) {
            $SQL = "$feedback_join JOIN qpl_hints ON qpl_hints.qht_question_fi = qpl_questions.question_id ";
            $tableJoin .= !str_contains($tableJoin, $SQL) ? $SQL : '';
        }

        return $tableJoin;
    }

    private function getTaxonomyFilterExpressions(): array
    {
        $expressions = $this->getFilterByAssignedTaxonomyIdsExpression();

        $taxonomy_title = $this->fieldFilters['taxonomy_title'] ?? '';
        $taxonomy_node_title = $this->fieldFilters['taxonomy_node_title'] ?? '';

        if ($taxonomy_title === '' && $taxonomy_node_title === '') {
            return $expressions;
        }

        $base = 'SELECT DISTINCT item_id FROM tax_node_assignment';

        $like_taxonomy_title = $taxonomy_title !== ''
            ? "AND {$this->db->like('object_data.title', ilDBConstants::T_TEXT, "%$taxonomy_title%", false)}"
            : '';
        $like_taxonomy_node_title = $taxonomy_node_title !== ''
            ? "AND {$this->db->like('tax_node.title', ilDBConstants::T_TEXT, "%$taxonomy_node_title%", false)}"
            : '';

        $inner_join_object_data = "INNER JOIN object_data ON (object_data.obj_id = tax_node_assignment.tax_id AND object_data.type = 'tax' $like_taxonomy_title)";
        $inner_join_tax_node = "INNER JOIN tax_node ON (tax_node.tax_id = tax_node_assignment.tax_id AND tax_node.type = 'taxn' AND tax_node_assignment.node_id = tax_node.obj_id $like_taxonomy_node_title)";

        $quest_item_type = $this->db->quote('quest', ilDBConstants::T_TEXT);
        $expressions[] = "qpl_questions.question_id IN ($base $inner_join_object_data $inner_join_tax_node WHERE tax_node_assignment.item_type = $quest_item_type)";

        return $expressions;
    }

    private function getFilterByAssignedTaxonomyIdsExpression(): array
    {
        if ($this->taxFiltersExcludeAnyObjectsWithTaxonomies) {
            return ['question_id NOT IN (SELECT DISTINCT item_id FROM tax_node_assignment)'];
        }

        $expressions = [];
        foreach ($this->taxFilters as $tax_id => $tax_nodes) {
            $question_ids = [];

            if ($tax_nodes === []) {
                continue;
            }

            foreach ($tax_nodes as $tax_node) {
                $tax_items_by_tax_parent = $this->getTaxItems(
                    $this->taxParentTypes[$tax_id],
                    $this->taxParentIds[$tax_id],
                    $tax_id,
                    $tax_node
                );

                $tax_items_by_parent = $this->getTaxItems(
                    $this->parentObjType,
                    $this->parentObjId,
                    $tax_id,
                    $tax_node
                );

                $tax_items = array_merge($tax_items_by_tax_parent, $tax_items_by_parent);
                foreach ($tax_items as $tax_item) {
                    $question_ids[$tax_item['item_id']] = $tax_item['item_id'];
                }
            }

            $expressions[] = $this->db->in('question_id', $question_ids, false, ilDBConstants::T_INTEGER);
        }

        return $expressions;
    }

    protected function getTaxItems(string $parentType, int $parentObjId, int $taxId, int $taxNode): array
    {
        $taxTree = new ilTaxonomyTree($taxId);

        $taxNodeAssignment = new ilTaxNodeAssignment(
            $parentType,
            $parentObjId,
            'quest',
            $taxId
        );

        $subNodes = $taxTree->getSubTreeIds($taxNode);
        $subNodes[] = $taxNode;

        return $taxNodeAssignment->getAssignmentsOfNode($subNodes);
    }

    private function getQuestionInstanceTypeFilterExpression(): ?string
    {
        return match ($this->questionInstanceTypeFilter) {
            self::QUESTION_INSTANCE_TYPE_ORIGINALS => 'qpl_questions.original_id IS NULL',
            self::QUESTION_INSTANCE_TYPE_DUPLICATES => 'qpl_questions.original_id IS NOT NULL',
            default => null
        };
    }

    private function getQuestionIdsFilterExpressions(): array
    {
        $expressions = [];

        if (!empty($this->includeQuestionIdsFilter)) {
            $expressions[] = $this->db->in(
                'qpl_questions.question_id',
                $this->includeQuestionIdsFilter,
                false,
                ilDBConstants::T_INTEGER
            );
        }

        if (!empty($this->excludeQuestionIdsFilter)) {
            $IN = $this->db->in(
                'qpl_questions.question_id',
                $this->excludeQuestionIdsFilter,
                true,
                ilDBConstants::T_INTEGER
            );

            $expressions[] = $IN === ' 1=2 ' ? ' 1=1 ' : $IN; // required for ILIAS < 5.0
        }

        return $expressions;
    }

    private function getAnswerStatusFilterExpressions(): array
    {
        return match ($this->answerStatusFilter) {
            self::ANSWER_STATUS_FILTER_ALL_NON_CORRECT => ['
                (tst_test_result.question_fi IS NULL OR tst_test_result.points < qpl_questions.points)
            '],
            self::ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY => ['tst_test_result.question_fi IS NULL'],
            self::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY => [
                'tst_test_result.question_fi IS NOT NULL',
                'tst_test_result.points < qpl_questions.points'
            ],
            default => [],
        };
    }

    private function getQuestionTypeJoinExpression(): string
    {
        return '
			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi
		';
    }

    private function getObjectDataJoinExpression(): string
    {
        return '
            INNER JOIN	object_data
            ON			object_data.obj_id = qpl_questions.obj_fi
		';
    }

    private function getTestQuestionJoinExpression(): string
    {
        return '
			INNER JOIN tst_test_question tstquest
			ON tstquest.question_fi = qpl_questions.question_id
		';
    }

    private function getTestResultJoinExpression(): string
    {
        return "
            LEFT JOIN	tst_test_result
			ON			tst_test_result.question_fi = qpl_questions.question_id
			AND			tst_test_result.active_fi = {$this->db->quote($this->answerStatusActiveId, ilDBConstants::T_INTEGER)}
		";
    }

    private function usesTestQuestionJoin(): bool
    {
        return $this->parentObjType === 'tst'
            && $this->questionInstanceTypeFilter === self::QUESTION_INSTANCE_TYPE_ALL;
    }

    private function getTableJoinExpression(): string
    {
        $table_join = $this->getQuestionTypeJoinExpression();

        if ($this->join_obj_data) {
            $table_join .= $this->getObjectDataJoinExpression();
        }

        if ($this->usesTestQuestionJoin()) {
            $table_join .= $this->getTestQuestionJoinExpression();
        }

        $table_join = $this->handleFeedbackJoin($table_join);
        $table_join = $this->handleHintJoin($table_join);

        if ($this->answerStatusActiveId) {
            $table_join .= $this->getTestResultJoinExpression();
        }

        return $table_join;
    }

    private function getEnrichmentJoinExpression(): string
    {
        $table_join = $this->getQuestionTypeJoinExpression();

        if ($this->join_obj_data) {
            $table_join .= $this->getObjectDataJoinExpression();
        }

        if ($this->answerStatusActiveId) {
            $table_join .= $this->getTestResultJoinExpression();
        }

        return $table_join;
    }

    /**
     * The row-selecting (paging) query must join qpl_qst_type and, if enabled,
     * object_data unconditionally, mirroring getEnrichmentJoinExpression(). Both
     * are INNER JOINs there, so a question_id that is selected for a page here but
     * does not satisfy those same INNER JOINs would silently be dropped later on
     * when the enriched row data is fetched, shrinking the page below its LIMIT
     * and leaving the dropped question unreachable on any page.
     */
    private function getRowSelectingJoinExpression(): string
    {
        $table_join = $this->getQuestionTypeJoinExpression();

        if ($this->join_obj_data) {
            $table_join .= $this->getObjectDataJoinExpression();
        }

        if ($this->usesTestQuestionJoin()) {
            $table_join .= $this->getTestQuestionJoinExpression();
        }

        $table_join = $this->handleFeedbackJoin($table_join);
        $table_join = $this->handleHintJoin($table_join);

        if ($this->pagingNeedsTestResultJoin()) {
            $table_join .= $this->getTestResultJoinExpression();
        }

        return $table_join;
    }

    private function pagingNeedsTestResultJoin(): bool
    {
        if ($this->answerStatusActiveId === null) {
            return false;
        }

        if ($this->answerStatusFilter !== '') {
            return true;
        }

        return in_array($this->resolveOrder()['field'], ['reached_points', 'question_answer_status'], true);
    }

    private function getConditionalFilterExpression(): string
    {
        $conditions = [];

        if ($this->getQuestionInstanceTypeFilterExpression() !== null) {
            $conditions[] = $this->getQuestionInstanceTypeFilterExpression();
        }

        if ($this->getParentObjFilterExpression() !== null) {
            $conditions[] = $this->getParentObjFilterExpression();
        }

        $conditions = array_merge(
            $conditions,
            $this->getQuestionIdsFilterExpressions(),
            $this->getFieldFilterExpressions(),
            $this->getTaxonomyFilterExpressions(),
            $this->getAnswerStatusFilterExpressions()
        );

        $conditions = implode(' AND ', $conditions);
        return $conditions !== '' ? "AND $conditions" : '';
    }

    /**
     * @return list<string>
     */
    private function getBaseSelectFieldsExpression(): array
    {
        $select_fields = [
            'qpl_questions.*',
            'qpl_qst_type.type_tag',
            'qpl_qst_type.plugin',
            'qpl_qst_type.plugin_name',
            'qpl_questions.points max_points'
        ];

        if ($this->join_obj_data) {
            $select_fields[] = 'object_data.title parent_title';
        }

        if ($this->answerStatusActiveId) {
            $select_fields[] = 'tst_test_result.points reached_points';
            $select_fields[] = "{$this->questionAnswerStatusExpression()} question_answer_status";
        }

        return $select_fields;
    }

    /**
     * @param list<string> $flags
     * @return list<string>
     */
    private function getFlagSelectFieldsExpression(array $flags): array
    {
        $fields = [];
        foreach ($flags as $flag) {
            $fields[] = match ($flag) {
                'feedback' => $this->generateFeedbackSubquery(),
                'hints' => $this->generateHintSubquery(),
                'taxonomies' => $this->generateTaxonomySubquery(),
                default => throw new ilTestQuestionPoolException('Unsupported question list flag.'),
            };
        }

        return $fields;
    }

    private function getSelectFieldsExpression(): string
    {
        $select_fields = array_merge(
            $this->getBaseSelectFieldsExpression(),
            $this->getFlagSelectFieldsExpression(['feedback', 'hints', 'taxonomies'])
        );

        return 'SELECT DISTINCT ' . implode(', ', $select_fields);
    }

    private function questionAnswerStatusExpression(): string
    {
        return "CASE
					WHEN tst_test_result.points IS NULL THEN '" . self::QUESTION_ANSWER_STATUS_NON_ANSWERED . "'
					WHEN tst_test_result.points < qpl_questions.points THEN '" . self::QUESTION_ANSWER_STATUS_WRONG_ANSWERED . "'
					ELSE '" . self::QUESTION_ANSWER_STATUS_CORRECT_ANSWERED . "'
				END";
    }

    private function feedbackFlagExpression(): string
    {
        $cases = [];
        $tables = ['qpl_fb_generic', 'qpl_fb_specific'];

        foreach ($tables as $table) {
            $subquery = "SELECT 1 FROM $table WHERE $table.question_fi = qpl_questions.question_id AND $table.feedback <> ''";
            $cases[] = "WHEN EXISTS ($subquery) THEN TRUE";
        }

        $page_object_table = 'page_object';
        foreach ($tables as $table) {
            $subquery = sprintf(
                "SELECT 1 FROM $table JOIN $page_object_table ON $page_object_table.page_id = $table.feedback_id WHERE $page_object_table.parent_type IN ('%s', '%s') AND $page_object_table.is_empty <> 1 AND $table.question_fi = qpl_questions.question_id",
                \ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK,
                \ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK,
            );
            $cases[] = "WHEN EXISTS ($subquery) THEN TRUE";
        }

        return 'CASE ' . implode(' ', $cases) . ' ELSE FALSE END';
    }

    private function generateFeedbackSubquery(): string
    {
        return $this->feedbackFlagExpression() . ' AS feedback';
    }

    private function hintFlagExpression(): string
    {
        $hint_subquery = 'SELECT 1 FROM qpl_hints WHERE qpl_hints.qht_question_fi = qpl_questions.question_id';
        return "CASE WHEN EXISTS ($hint_subquery) THEN TRUE ELSE FALSE END";
    }

    private function generateHintSubquery(): string
    {
        return "{$this->hintFlagExpression()} AS hints";
    }

    private function taxonomyFlagExpression(): string
    {
        $assignment_exists = "WHEN EXISTS (SELECT 1 FROM tax_node_assignment WHERE tax_node_assignment.component = '%s' AND tax_node_assignment.item_type = 'quest' AND tax_node_assignment.item_id = qpl_questions.question_id) THEN TRUE";

        return sprintf(
            'CASE ' . $assignment_exists . ' ' . $assignment_exists . ' ELSE FALSE END',
            'qpl',
            'tst'
        );
    }

    private function generateTaxonomySubquery(): string
    {
        return $this->taxonomyFlagExpression() . ' AS taxonomies';
    }

    protected function buildBasicQuery(): string
    {
        return "{$this->getSelectFieldsExpression()} FROM qpl_questions {$this->getTableJoinExpression()} WHERE qpl_questions.tstamp > 0";
    }

    /**
     * @return list<string>
     */
    private function activeHavingFlags(): array
    {
        $flags = [];
        foreach (['feedback', 'hints'] as $flag) {
            $field_value = $this->fieldFilters[$flag] ?? null;
            if (!is_string($field_value)) {
                continue;
            }

            $field_value = strtoupper($field_value);
            if (in_array($field_value, ['TRUE', 'FALSE'], true)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }

    private function getHavingFilterExpression(): string
    {
        $expressions = [];
        foreach ($this->activeHavingFlags() as $flag) {
            $expressions[] = "{$flag} IS " . strtoupper((string) $this->fieldFilters[$flag]);
        }

        $having = implode(' AND ', $expressions);
        return $having !== '' ? "HAVING $having" : '';
    }

    /**
     * @return list<string>
     */
    private function getRequiredFlagsForPaging(): array
    {
        $flags = $this->activeHavingFlags();
        $order_field = $this->resolveOrder()['field'];
        if (
            in_array($order_field, ['feedback', 'hints', 'taxonomies'], true)
            && !in_array($order_field, $flags, true)
        ) {
            $flags[] = $order_field;
        }

        return $flags;
    }

    private function resolveOrderSourceExpression(string $field): string
    {
        $question_columns = [
            'question_id',
            'question_type_fi',
            'obj_fi',
            'title',
            'description',
            'author',
            'owner',
            'question_text',
            'points',
            'nr_of_tries',
            'complete',
            'created',
            'original_id',
            'tstamp',
            'external_id',
            'add_cont_edit_mode',
            'lifecycle',
        ];
        if (in_array($field, $question_columns, true)) {
            return "qpl_questions.{$field}";
        }

        return match ($field) {
            'type_tag' => 'qpl_qst_type.type_tag',
            'plugin' => 'qpl_qst_type.plugin',
            'plugin_name' => 'qpl_qst_type.plugin_name',
            'max_points' => 'qpl_questions.points',
            'parent_title' => 'object_data.title',
            'reached_points' => 'tst_test_result.points',
            'question_answer_status' => $this->questionAnswerStatusExpression(),
            'feedback' => $this->feedbackFlagExpression(),
            'hints' => $this->hintFlagExpression(),
            'taxonomies' => $this->taxonomyFlagExpression(),
            default => throw new ilTestQuestionPoolException('Unsupported question list order field.'),
        };
    }

    /**
     * @return array{field: string, expression: string, direction: string}
     */
    private function resolveOrder(): array
    {
        if ($this->order === null) {
            return [
                'field' => 'question_id',
                'expression' => 'qpl_questions.question_id',
                'direction' => Order::ASC,
            ];
        }

        [$order_field, $order_direction] = $this->order->join(
            '',
            static fn(string $index, string $key, string $value): array => [$key, $value]
        );

        $order_direction = strtoupper($order_direction);
        if (!in_array($order_direction, [Order::ASC, Order::DESC], true)) {
            $order_direction = Order::ASC;
        }

        return [
            'field' => $order_field,
            'expression' => $this->resolveOrderSourceExpression($order_field),
            'direction' => $order_direction,
        ];
    }

    private function buildOrderQueryExpression(): string
    {
        if ($this->order === null) {
            return '';
        }

        $order = $this->resolveOrder();
        return " ORDER BY {$order['expression']} {$order['direction']}";
    }

    private function buildLimitQueryExpression(): string
    {
        $range = $this->range;
        if ($range === null) {
            return '';
        }

        $limit = max($range->getLength(), 0);
        $offset = max($range->getStart(), 0);

        return " LIMIT $limit OFFSET $offset";
    }

    private function buildPagingSubquery(): string
    {
        $order = $this->resolveOrder();
        $flags = $this->getRequiredFlagsForPaging();
        $select = [
            'qpl_questions.question_id',
            "{$order['expression']} AS sort_key",
        ];
        if (!in_array($order['field'], $this->activeHavingFlags(), true)) {
            $flags = array_values(
                array_filter(
                    $flags,
                    static fn(string $flag): bool => $flag !== $order['field']
                )
            );
        }
        $select = array_merge($select, $this->getFlagSelectFieldsExpression($flags));

        $query = 'SELECT DISTINCT ' . implode(', ', $select)
            . ' FROM qpl_questions ' . $this->getRowSelectingJoinExpression()
            . ' WHERE qpl_questions.tstamp > 0 ' . $this->getConditionalFilterExpression()
            . ' ' . $this->getHavingFilterExpression()
            . " ORDER BY sort_key {$order['direction']}, qpl_questions.question_id ASC";

        return trim($query);
    }

    private function buildEnrichedQuery(): string
    {
        $order = $this->resolveOrder();
        $paging = $this->buildPagingSubquery() . $this->buildLimitQueryExpression();

        return implode(PHP_EOL, [
            $this->getSelectFieldsExpression() . ', paged.sort_key, paged.question_id',
            'FROM qpl_questions ' . $this->getEnrichmentJoinExpression(),
            'INNER JOIN (',
            $paging,
            ') paged ON paged.question_id = qpl_questions.question_id',
            "ORDER BY paged.sort_key {$order['direction']}, paged.question_id ASC",
        ]);
    }

    private function buildQuery(): string
    {
        if ($this->range !== null) {
            return $this->buildEnrichedQuery();
        }

        return implode(
            PHP_EOL,
            array_filter([
                $this->buildBasicQuery(),
                $this->getConditionalFilterExpression(),
                $this->getHavingFilterExpression(),
                $this->buildOrderQueryExpression(),
                $this->buildLimitQueryExpression(),
            ])
        );
    }

    public function load(): void
    {
        $this->checkFilters();

        $tags_trafo = $this->refinery->encode()->htmlSpecialCharsAsEntities();

        $res = $this->db->query($this->buildQuery());
        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);

            if (!$this->isActiveQuestionType($row)) {
                continue;
            }

            $row['title'] = $tags_trafo->transform($row['title'] ?? '&nbsp;');
            $row['description'] = $tags_trafo->transform($row['description'] ?? '');
            $row['author'] = $tags_trafo->transform($row['author']);
            $row['taxonomies'] = $this->loadTaxonomyAssignmentData($row['obj_fi'], $row['question_id']);
            $row['ttype'] = $this->getQuestionTypeTranslation($row);
            $row['feedback'] = $row['feedback'] === 1;
            $row['hints'] = $row['hints'] === 1;
            $row['comments'] = $this->getNumberOfCommentsForQuestion($row['question_id']);
            unset($row['sort_key']);

            if (
                $this->filter_comments === self::QUESTION_COMMENTED_ONLY && $row['comments'] === 0
                || $this->filter_comments === self::QUESTION_COMMENTED_EXCLUDED && $row['comments'] > 0
            ) {
                continue;
            }

            $this->questions[$row['question_id']] = $row;
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $this->checkFilters();

        $count = 'COUNT(*)';
        if ($this->activeHavingFlags() !== []) {
            $query = "SELECT $count FROM ({$this->buildPagingSubquery()}) t";
        } else {
            $query = "SELECT $count FROM qpl_questions {$this->getTableJoinExpression()} WHERE qpl_questions.tstamp > 0 {$this->getConditionalFilterExpression()}";
        }

        return (int) ($this->db->query($query)->fetch()[$count] ?? 0);
    }

    protected function getNumberOfCommentsForQuestion(int $question_id): int
    {
        if ($this->notes_service === null) {
            return 0;
        }
        $notes_context = $this->notes_service->data()->context(
            $this->getParentObjId(),
            $question_id,
            'quest'
        );
        return $this->notes_service->domain()->getNrOfCommentsForContext($notes_context);
    }

    public function setCommentFilter(?int $commented = null): void
    {
        $this->filter_comments = $commented;
    }

    private function loadTaxonomyAssignmentData(
        int $parent_obj_id,
        int $question_id
    ): array {
        $tax_assignment_data = [];
        foreach ($this->availableTaxonomyIds as $tax_id) {
            $tax_tree = new ilTaxonomyTree($tax_id);

            $tax_assignment = new ilTaxNodeAssignment('qpl', $parent_obj_id, 'quest', $tax_id);
            $assignments = $tax_assignment->getAssignmentsOfItem($question_id);

            foreach ($assignments as $ass_data) {
                if (!isset($tax_assignment_data[$ass_data['tax_id']])) {
                    $tax_assignment_data[$ass_data['tax_id']] = [];
                }

                $ass_data['node_lft'] = $tax_tree->getNodeData($ass_data['node_id']);

                $tax_assignment_data[$ass_data['tax_id']][$ass_data['node_id']] = $ass_data;
            }
        }

        return $tax_assignment_data;
    }

    protected function isActiveQuestionType(array $questionData): bool
    {
        if (!isset($questionData['plugin'])) {
            return false;
        }

        if (!$questionData['plugin']) {
            return true;
        }

        if (
            !isset($questionData['plugin_name'])
            || !$this->component_repository->getComponentByTypeAndName(
                ilComponentInfo::TYPE_COMPONENT,
                'TestQuestionPool'
            )->getPluginSlotById('qst')->hasPluginName($questionData['plugin_name'])
        ) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(ilComponentInfo::TYPE_COMPONENT, 'TestQuestionPool')
            ->getPluginSlotById('qst')
            ->getPluginByName($questionData['plugin_name'])
            ->isActive();
    }

    private function getQuestionTypeTranslation(array $question_data): string
    {
        if (!($question_data['plugin'] ?? false)) {
            return $this->lng->txt($question_data['type_tag']);
        }

        foreach ($this->component_factory->getActivePluginsInSlot('qst') as $plugin) {
            if ($plugin->getQuestionType() === $question_data['type_tag']) {
                return $plugin->getQuestionTypeTranslation();
            }
        }

        return $this->lng->txt($question_data['type_tag']);
    }

    public function getDataArrayForQuestionId(int $questionId)
    {
        return $this->questions[$questionId];
    }

    public function getQuestionDataArray(): array
    {
        return $this->questions;
    }

    public function isInList(int $questionId): bool
    {
        return isset($this->questions[$questionId]);
    }

    /**
     * Get title of an assigned item
     *
     * (is used from ilObjTaxonomyGUI when item sorting is activated)
     *
     * @param string $a_comp_id ('qpl' in our context)
     * @param string $a_item_type ('quest' in our context)
     * @param integer $a_item_id (questionId in our context)
     */
    public function getTitle(string $a_comp_id, string $a_item_type, int $a_item_id): string
    {
        if ($a_comp_id !== 'qpl' || $a_item_type !== 'quest' || !$a_item_id) {
            return '';
        }

        return $this->questions[$a_item_id]['title'] ?? '';
    }

    private function checkFilters(): void
    {
        if ($this->answerStatusFilter !== '' && !$this->answerStatusActiveId) {
            throw new ilTestQuestionPoolException(
                'No active id given! You cannot use the answer status filter without giving an active id.'
            );
        }
    }
}
