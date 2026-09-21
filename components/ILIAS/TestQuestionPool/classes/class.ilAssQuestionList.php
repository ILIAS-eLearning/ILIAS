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
    private const QUESTION_TABLE_NAME = 'qpl_questions';
    private const QUESTION_TYPE_TABLE_NAME = 'qpl_qst_type';
    private const OBJECT_DATA_TABLE_NAME = 'object_data';
    private const TEST_QUESTION_TABLE_NAME = 'tst_test_question';
    private const TEST_RESULT_TABLE_NAME = 'tst_test_result';
    private const FEEDBACK_GENERIC_TABLE_NAME = 'qpl_fb_generic';
    private const FEEDBACK_SPECIFIC_TABLE_NAME = 'qpl_fb_specific';
    private const HINTS_TABLE_NAME = 'qpl_hints';
    private const PAGE_OBJECT_TABLE_NAME = 'page_object';
    private const TAX_NODE_ASSIGNMENT_TABLE_NAME = 'tax_node_assignment';
    private const TAX_NODE_TABLE_NAME = 'tax_node';

    private const COMPUTED_FIELD_FEEDBACK = 'feedback';
    private const COMPUTED_FIELD_HINTS = 'hints';
    private const COMPUTED_FIELD_TAXONOMIES = 'taxonomies';
    private const ALL_COMPUTED_FIELDS = [
        self::COMPUTED_FIELD_FEEDBACK,
        self::COMPUTED_FIELD_HINTS,
        self::COMPUTED_FIELD_TAXONOMIES,
    ];

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

    private function getParentObjFilterExpression(): ?string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        if ($this->parentObjId !== null) {
            $quoted = $this->db->quote($this->parentObjId, ilDBConstants::T_INTEGER);
            return "{$questions_table}.obj_fi = {$quoted}";
        }

        if (!empty($this->parentObjIdsFilter)) {
            $field = "{$questions_table}.obj_fi";
            return $this->db->in($field, $this->parentObjIdsFilter, false, ilDBConstants::T_INTEGER);
        }

        return null;
    }

    private function getFieldFilterExpressions(): array
    {
        $expressions = [];
        $questions_table = self::QUESTION_TABLE_NAME;
        $qst_type_table = self::QUESTION_TYPE_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;

        foreach ($this->fieldFilters as $fieldName => $fieldValue) {
            switch ($fieldName) {
                case 'title':
                case 'description':
                case 'author':
                case 'lifecycle':
                    $field = "{$questions_table}.{$fieldName}";
                    $expressions[] = $this->db->like($field, ilDBConstants::T_TEXT, "%%{$fieldValue}%%");
                    break;
                case 'type':
                    $quoted = $this->db->quote($fieldValue, ilDBConstants::T_TEXT);
                    $expressions[] = "{$qst_type_table}.type_tag = {$quoted}";
                    break;
                case 'question_id':
                    if ($fieldValue !== '' && !is_array($fieldValue)) {
                        $fieldValue = [$fieldValue];
                    }
                    $field = "{$questions_table}.question_id";
                    $expressions[] = $this->db->in($field, $fieldValue, false, ilDBConstants::T_INTEGER);
                    break;
                case 'parent_title':
                    if ($this->join_obj_data) {
                        $field = "{$object_data_table}.title";
                        $expressions[] = $this->db->like($field, ilDBConstants::T_TEXT, "%%{$fieldValue}%%");
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
            $fb_table = self::FEEDBACK_GENERIC_TABLE_NAME;
            $q_table = self::QUESTION_TABLE_NAME;
            $SQL = "{$feedback_join} JOIN {$fb_table} ON {$fb_table}.question_fi = {$q_table}.question_id ";
            $tableJoin .= !str_contains($tableJoin, $SQL) ? $SQL : '';
        }

        return $tableJoin;
    }

    private function handleHintJoin(string $tableJoin): string
    {
        $hint_join = match ($this->fieldFilters['hints'] ?? null) {
            'true' => 'INNER',
            'false' => 'LEFT',
            default => null
        };

        if (isset($hint_join)) {
            $hints_table = self::HINTS_TABLE_NAME;
            $q_table = self::QUESTION_TABLE_NAME;
            $SQL = "{$hint_join} JOIN {$hints_table} ON {$hints_table}.qht_question_fi = {$q_table}.question_id ";
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

        $tax_assignment_table = self::TAX_NODE_ASSIGNMENT_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;
        $tax_node_table = self::TAX_NODE_TABLE_NAME;
        $questions_table = self::QUESTION_TABLE_NAME;

        $base = "SELECT DISTINCT item_id FROM {$tax_assignment_table}";

        $object_data_title_field = "{$object_data_table}.title";
        $like_taxonomy_title = $taxonomy_title !== ''
            ? "AND " . $this->db->like($object_data_title_field, ilDBConstants::T_TEXT, "%{$taxonomy_title}%", false)
            : '';

        $tax_node_title_field = "{$tax_node_table}.title";
        $like_taxonomy_node_title = $taxonomy_node_title !== ''
            ? "AND " . $this->db->like($tax_node_title_field, ilDBConstants::T_TEXT, "%{$taxonomy_node_title}%", false)
            : '';

        $inner_join_object_data = "INNER JOIN {$object_data_table} ON ({$object_data_table}.obj_id = {$tax_assignment_table}.tax_id AND {$object_data_table}.type = 'tax' {$like_taxonomy_title})";
        $inner_join_tax_node = "INNER JOIN {$tax_node_table} ON ({$tax_node_table}.tax_id = {$tax_assignment_table}.tax_id AND {$tax_node_table}.type = 'taxn' AND {$tax_assignment_table}.node_id = {$tax_node_table}.obj_id {$like_taxonomy_node_title})";

        $expressions[] = "{$questions_table}.question_id IN ({$base} {$inner_join_object_data} {$inner_join_tax_node})";

        return $expressions;
    }

    private function getFilterByAssignedTaxonomyIdsExpression(): array
    {
        $tax_assignment_table = self::TAX_NODE_ASSIGNMENT_TABLE_NAME;
        if ($this->taxFiltersExcludeAnyObjectsWithTaxonomies) {
            $subquery = "SELECT DISTINCT item_id FROM {$tax_assignment_table}";
            return ["question_id NOT IN ({$subquery})"];
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
        $questions_table = self::QUESTION_TABLE_NAME;
        return match ($this->questionInstanceTypeFilter) {
            self::QUESTION_INSTANCE_TYPE_ORIGINALS => "{$questions_table}.original_id IS NULL",
            self::QUESTION_INSTANCE_TYPE_DUPLICATES => "{$questions_table}.original_id IS NOT NULL",
            default => null
        };
    }

    private function getQuestionIdsFilterExpressions(): array
    {
        $expressions = [];
        $questions_table = self::QUESTION_TABLE_NAME;

        if (!empty($this->includeQuestionIdsFilter)) {
            $field = "{$questions_table}.question_id";
            $expressions[] = $this->db->in(
                $field,
                $this->includeQuestionIdsFilter,
                false,
                ilDBConstants::T_INTEGER
            );
        }

        if (!empty($this->excludeQuestionIdsFilter)) {
            $field = "{$questions_table}.question_id";
            $IN = $this->db->in(
                $field,
                $this->excludeQuestionIdsFilter,
                true,
                ilDBConstants::T_INTEGER
            );

            $expressions[] = $IN === ' 1=2 ' ? ' 1=1 ' : $IN;
        }

        return $expressions;
    }

    private function getAnswerStatusFilterExpressions(): array
    {
        $test_result_table = self::TEST_RESULT_TABLE_NAME;
        $questions_table = self::QUESTION_TABLE_NAME;
        return match ($this->answerStatusFilter) {
            self::ANSWER_STATUS_FILTER_ALL_NON_CORRECT => ["
                ({$test_result_table}.question_fi IS NULL OR {$test_result_table}.points < {$questions_table}.points)
            "],
            self::ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY => ["{$test_result_table}.question_fi IS NULL"],
            self::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY => [
                "{$test_result_table}.question_fi IS NOT NULL",
                "{$test_result_table}.points < {$questions_table}.points"
            ],
            default => [],
        };
    }

    private function getTableJoinExpression(): string
    {
        $qst_type_table = self::QUESTION_TYPE_TABLE_NAME;
        $questions_table = self::QUESTION_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;
        $test_question_table = self::TEST_QUESTION_TABLE_NAME;
        $test_result_table = self::TEST_RESULT_TABLE_NAME;

        $tableJoin = "
			INNER JOIN	{$qst_type_table}
			ON			{$qst_type_table}.question_type_id = {$questions_table}.question_type_fi
		";

        if ($this->join_obj_data) {
            $tableJoin .= "
				INNER JOIN	{$object_data_table}
				ON			{$object_data_table}.obj_id = {$questions_table}.obj_fi
			";
        }

        if (
            $this->parentObjType === 'tst'
            && $this->questionInstanceTypeFilter === self::QUESTION_INSTANCE_TYPE_ALL
        ) {
            $tableJoin .= "INNER JOIN {$test_question_table} tstquest ON tstquest.question_fi = {$questions_table}.question_id";
        }

        $tableJoin = $this->handleFeedbackJoin($tableJoin);
        $tableJoin = $this->handleHintJoin($tableJoin);

        if ($this->answerStatusActiveId !== null) {
            $quoted = $this->db->quote($this->answerStatusActiveId, ilDBConstants::T_INTEGER);
            $tableJoin .= "
				LEFT JOIN	{$test_result_table}
				ON			{$test_result_table}.question_fi = {$questions_table}.question_id
				AND			{$test_result_table}.active_fi = {$quoted}
			";
        }

        return $tableJoin;
    }

    private function getConditionalFilterExpression(): string
    {
        $conditions = [];

        $instance_type_filter = $this->getQuestionInstanceTypeFilterExpression();
        if ($instance_type_filter !== null) {
            $conditions[] = $instance_type_filter;
        }

        $parent_obj_filter = $this->getParentObjFilterExpression();
        if ($parent_obj_filter !== null) {
            $conditions[] = $parent_obj_filter;
        }

        $conditions = array_merge(
            $conditions,
            $this->getQuestionIdsFilterExpressions(),
            $this->getFieldFilterExpressions(),
            $this->getTaxonomyFilterExpressions(),
            $this->getAnswerStatusFilterExpressions()
        );

        $merged = implode(' AND ', $conditions);
        return $merged !== '' ? "AND {$merged}" : '';
    }

    private function getSelectFieldsExpression(array $computed_fields = []): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $qst_type_table = self::QUESTION_TYPE_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;
        $test_result_table = self::TEST_RESULT_TABLE_NAME;

        $select_fields = [
            "{$questions_table}.*",
            "{$qst_type_table}.type_tag",
            "{$qst_type_table}.plugin",
            "{$qst_type_table}.plugin_name",
            "{$questions_table}.points max_points"
        ];

        if ($this->join_obj_data) {
            $select_fields[] = "{$object_data_table}.title parent_title";
        }

        if ($this->answerStatusActiveId !== null) {
            $select_fields[] = "{$test_result_table}.points reached_points";
            $select_fields[] = "CASE
					WHEN {$test_result_table}.points IS NULL THEN '" . self::QUESTION_ANSWER_STATUS_NON_ANSWERED . "'
					WHEN {$test_result_table}.points < {$questions_table}.points THEN '" . self::QUESTION_ANSWER_STATUS_WRONG_ANSWERED . "'
					ELSE '" . self::QUESTION_ANSWER_STATUS_CORRECT_ANSWERED . "'
				END question_answer_status
			";
        }

        foreach ($this->computedSubqueriesForFields($computed_fields) as $subquery) {
            $select_fields[] = $subquery;
        }

        $merged = implode(', ', $select_fields);
        return "SELECT DISTINCT {$merged}";
    }

    private function getComputedFieldsExpression(array $computed_fields): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $fields = ["{$questions_table}.question_id"];
        foreach ($this->computedSubqueriesForFields($computed_fields) as $subquery) {
            $fields[] = $subquery;
        }
        $merged = implode(', ', $fields);
        return "SELECT DISTINCT {$merged}";
    }

    private function computedSubqueriesForFields(array $computed_fields): array
    {
        $subqueries = [];
        if (in_array(self::COMPUTED_FIELD_FEEDBACK, $computed_fields, true)) {
            $subqueries[] = $this->generateFeedbackSubquery();
        }
        if (in_array(self::COMPUTED_FIELD_HINTS, $computed_fields, true)) {
            $subqueries[] = $this->generateHintSubquery();
        }
        if (in_array(self::COMPUTED_FIELD_TAXONOMIES, $computed_fields, true)) {
            $subqueries[] = $this->generateTaxonomySubquery();
        }
        return $subqueries;
    }

    private function generateFeedbackSubquery(): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $fb_generic_table = self::FEEDBACK_GENERIC_TABLE_NAME;
        $fb_specific_table = self::FEEDBACK_SPECIFIC_TABLE_NAME;
        $page_object_table = self::PAGE_OBJECT_TABLE_NAME;

        $cases = [];
        $tables = [$fb_generic_table, $fb_specific_table];

        foreach ($tables as $table) {
            $subquery = "SELECT 1 FROM {$table} WHERE {$table}.question_fi = {$questions_table}.question_id AND {$table}.feedback <> ''";
            $cases[] = "WHEN EXISTS ({$subquery}) THEN TRUE";
        }

        foreach ($tables as $table) {
            $subquery = sprintf(
                "SELECT 1 FROM {$table} JOIN {$page_object_table} ON {$page_object_table}.page_id = {$table}.feedback_id WHERE {$page_object_table}.parent_type IN ('%s', '%s') AND {$page_object_table}.is_empty <> 1 AND {$table}.question_fi = {$questions_table}.question_id",
                \ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK,
                \ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK,
            );
            $cases[] = "WHEN EXISTS ({$subquery}) THEN TRUE";
        }

        $feedback_case_subquery = implode(' ', $cases);
        return "CASE {$feedback_case_subquery} ELSE FALSE END AS feedback";
    }

    private function generateHintSubquery(): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $hints_table = self::HINTS_TABLE_NAME;
        $hint_subquery = "SELECT 1 FROM {$hints_table} WHERE {$hints_table}.qht_question_fi = {$questions_table}.question_id";
        return "CASE WHEN EXISTS ({$hint_subquery}) THEN TRUE ELSE FALSE END AS hints";
    }

    private function generateTaxonomySubquery(): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $tax_assignment_table = self::TAX_NODE_ASSIGNMENT_TABLE_NAME;
        $tax_subquery = "SELECT 1 FROM {$tax_assignment_table} WHERE {$tax_assignment_table}.item_id = {$questions_table}.question_id AND {$tax_assignment_table}.item_type = 'quest'";
        return "CASE WHEN EXISTS ({$tax_subquery}) THEN TRUE ELSE FALSE END AS taxonomies";
    }

    private function buildBasicQuery(array $computed_fields = []): string
    {
        $select = $this->getSelectFieldsExpression($computed_fields);
        $joins = $this->getTableJoinExpression();
        $questions_table = self::QUESTION_TABLE_NAME;
        return "{$select} FROM {$questions_table} {$joins} WHERE {$questions_table}.tstamp > 0";
    }

    private function getHavingFilterExpression(): string
    {
        $expressions = [];

        foreach ($this->fieldFilters as $fieldName => $fieldValue) {
            if ($fieldName === self::COMPUTED_FIELD_FEEDBACK) {
                $upper = strtoupper((string) $fieldValue);
                if (in_array($upper, ['TRUE', 'FALSE'], true)) {
                    $expressions[] = "feedback IS {$upper}";
                }
                continue;
            }

            if ($fieldName === self::COMPUTED_FIELD_HINTS) {
                $upper = strtoupper((string) $fieldValue);
                if (in_array($upper, ['TRUE', 'FALSE'], true)) {
                    $expressions[] = "hints IS {$upper}";
                }
            }
        }

        $having = implode(' AND ', $expressions);
        return $having !== '' ? "HAVING {$having}" : '';
    }

    private function buildOrderQueryExpression(): string
    {
        $order = $this->order;
        if ($order === null) {
            return '';
        }

        [$order_field, $order_direction] = $order->join(
            '',
            static fn(string $index, string $key, string $value): array => [$key, $value]
        );

        $upper_direction = strtoupper($order_direction);
        if (!in_array($upper_direction, [Order::ASC, Order::DESC], true)) {
            $upper_direction = Order::ASC;
        }

        $quoted_order_field = $this->qualifyAndBacktickField($order_field);

        return " ORDER BY {$quoted_order_field} {$upper_direction}";
    }

    private function qualifyAndBacktickField(string $field): string
    {
        $qualified = $this->qualifyField($field);
        $segments = explode('.', $qualified);
        $quoted = array_map(
            static fn(string $segment): string => "`{$segment}`",
            $segments
        );
        return implode('.', $quoted);
    }

    private function qualifyField(string $field): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $qst_type_table = self::QUESTION_TYPE_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;
        return match ($field) {
            'title', 'description', 'author', 'lifecycle', 'points',
            'created', 'tstamp', 'complete', 'question_id', 'original_id' => "{$questions_table}.{$field}",
            'max_points' => "{$questions_table}.points",
            'type_tag' => "{$qst_type_table}.type_tag",
            'parent_title' => "{$object_data_table}.title",
            default => $field,
        };
    }

    private function buildLimitQueryExpression(): string
    {
        $range = $this->range;
        if ($range === null) {
            return '';
        }

        $limit = max($range->getLength(), 0);
        $offset = max($range->getStart(), 0);

        return " LIMIT {$limit} OFFSET {$offset}";
    }

    private function buildQuery(): string
    {
        $required_computed = $this->requiredComputedFields();
        return implode(PHP_EOL, array_filter([
            $this->buildBasicQuery($required_computed),
            $this->getConditionalFilterExpression(),
            $this->getHavingFilterExpression(),
            $this->buildOrderQueryExpression(),
            $this->buildLimitQueryExpression(),
        ]));
    }

    private function requiredComputedFields(): array
    {
        $fields = [];

        if (isset($this->fieldFilters[self::COMPUTED_FIELD_FEEDBACK])) {
            $fields[] = self::COMPUTED_FIELD_FEEDBACK;
        }
        if (isset($this->fieldFilters[self::COMPUTED_FIELD_HINTS])) {
            $fields[] = self::COMPUTED_FIELD_HINTS;
        }

        if ($this->order !== null) {
            [$order_field] = $this->order->join(
                '',
                static fn(string $index, string $key, string $value): array => [$key, $value]
            );
            if (in_array($order_field, self::ALL_COMPUTED_FIELDS, true) && !in_array($order_field, $fields, true)) {
                $fields[] = $order_field;
            }
        }

        return $fields;
    }

    private function buildEnrichmentQuery(array $question_ids, array $computed_fields): string
    {
        $questions_table = self::QUESTION_TABLE_NAME;
        $in = $this->db->in("{$questions_table}.question_id", $question_ids, false, ilDBConstants::T_INTEGER);
        $select_fields = $this->getComputedFieldsExpression($computed_fields);
        $joins = $this->getBaseTableJoinExpression();
        return "{$select_fields} FROM {$questions_table} {$joins} WHERE {$questions_table}.tstamp > 0 AND {$in}";
    }

    private function getBaseTableJoinExpression(): string
    {
        $qst_type_table = self::QUESTION_TYPE_TABLE_NAME;
        $questions_table = self::QUESTION_TABLE_NAME;
        $object_data_table = self::OBJECT_DATA_TABLE_NAME;
        $test_question_table = self::TEST_QUESTION_TABLE_NAME;
        $test_result_table = self::TEST_RESULT_TABLE_NAME;

        $table_join = "
			INNER JOIN	{$qst_type_table}
			ON			{$qst_type_table}.question_type_id = {$questions_table}.question_type_fi
		";

        if ($this->join_obj_data) {
            $table_join .= "
				INNER JOIN	{$object_data_table}
				ON			{$object_data_table}.obj_id = {$questions_table}.obj_fi
			";
        }

        if (
            $this->parentObjType === 'tst'
            && $this->questionInstanceTypeFilter === self::QUESTION_INSTANCE_TYPE_ALL
        ) {
            $table_join .= "INNER JOIN {$test_question_table} tstquest ON tstquest.question_fi = {$questions_table}.question_id";
        }

        if ($this->answerStatusActiveId !== null) {
            $quoted = $this->db->quote($this->answerStatusActiveId, ilDBConstants::T_INTEGER);
            $table_join .= "
				LEFT JOIN	{$test_result_table}
				ON			{$test_result_table}.question_fi = {$questions_table}.question_id
				AND			{$test_result_table}.active_fi = {$quoted}
			";
        }

        return $table_join;
    }

    public function load(): void
    {
        $this->checkFilters();

        $tags_trafo = $this->refinery->encode()->htmlSpecialCharsAsEntities();
        $required_computed = $this->requiredComputedFields();
        $enrichment_fields = array_diff(self::ALL_COMPUTED_FIELDS, $required_computed);

        $res = $this->db->query($this->buildQuery());
        $rows_by_id = [];
        $ordered_ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $qid = (int) $row['question_id'];
            $rows_by_id[$qid] = $row;
            $ordered_ids[] = $qid;
        }

        if ($enrichment_fields !== [] && $ordered_ids !== []) {
            $enrichment_res = $this->db->query($this->buildEnrichmentQuery($ordered_ids, $enrichment_fields));
            while ($enrichment_row = $this->db->fetchAssoc($enrichment_res)) {
                $eid = (int) $enrichment_row['question_id'];
                if (isset($rows_by_id[$eid])) {
                    foreach ($enrichment_fields as $field) {
                        $rows_by_id[$eid][$field] = $enrichment_row[$field];
                    }
                }
            }
        }

        foreach ($ordered_ids as $question_id) {
            $row = $rows_by_id[$question_id];
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

        $questions_table = self::QUESTION_TABLE_NAME;
        $count = 'COUNT(*)';
        $joins = $this->getTableJoinExpression();
        $filters = $this->getConditionalFilterExpression();
        $query = "SELECT {$count} FROM {$questions_table} {$joins} WHERE {$questions_table}.tstamp > 0 {$filters}";

        $result = $this->db->query($query);
        $fetch = $this->db->fetchAssoc($result);
        return (int) ($fetch[$count] ?? 0);
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

    private function isActiveQuestionType(array $questionData): bool
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
