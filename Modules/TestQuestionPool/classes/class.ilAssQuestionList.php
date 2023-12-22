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

use ILIAS\Notes\Service as NotesService;

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
    private array $forcedQuestionIds = [];
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

    private $answerStatusFilter = null;

    public const QUESTION_INSTANCE_TYPE_ORIGINALS = 'QST_INSTANCE_TYPE_ORIGINALS';
    public const QUESTION_INSTANCE_TYPE_DUPLICATES = 'QST_INSTANCE_TYPE_DUPLICATES';
    public const QUESTION_INSTANCE_TYPE_ALL = 'QST_INSTANCE_TYPE_ALL';
    private string $questionInstanceTypeFilter = self::QUESTION_INSTANCE_TYPE_ORIGINALS;

    private $includeQuestionIdsFilter = null;
    private $excludeQuestionIdsFilter = null;

    public const QUESTION_COMPLETION_STATUS_COMPLETE = 'complete';
    public const QUESTION_COMPLETION_STATUS_INCOMPLETE = 'incomplete';
    public const QUESTION_COMPLETION_STATUS_BOTH = 'complete/incomplete';
    private string $questionCompletionStatusFilter = self::QUESTION_COMPLETION_STATUS_BOTH;

    public const QUESTION_COMMENTED_ONLY = '1';
    public const QUESTION_COMMENTED_EXCLUDED = '2';
    protected ?string $filter_comments = null;

    protected array $questions = [];

    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng,
        private ilComponentRepository $component_repository,
        private NotesService $notes_service
    ) {
    }

    public function getParentObjId(): ?int
    {
        return $this->parentObjId;
    }

    public function setParentObjId($parentObjId): void
    {
        $this->parentObjId = $parentObjId;
    }

    public function getParentObjectType(): string
    {
        return $this->parentObjType;
    }

    public function setParentObjectType($parentObjType): void
    {
        $this->parentObjType = $parentObjType;
    }

    public function getParentObjIdsFilter(): array
    {
        return $this->parentObjIdsFilter;
    }

    /**
     * @param array $parentObjIdsFilter
     */
    public function setParentObjIdsFilter($parentObjIdsFilter): void
    {
        $this->parentObjIdsFilter = $parentObjIdsFilter;
    }

    public function setQuestionInstanceTypeFilter($questionInstanceTypeFilter): void
    {
        $this->questionInstanceTypeFilter = $questionInstanceTypeFilter;
    }

    public function getQuestionInstanceTypeFilter()
    {
        return $this->questionInstanceTypeFilter;
    }

    public function setIncludeQuestionIdsFilter($questionIdsFilter): void
    {
        $this->includeQuestionIdsFilter = $questionIdsFilter;
    }

    public function getIncludeQuestionIdsFilter()
    {
        return $this->includeQuestionIdsFilter;
    }

    public function getExcludeQuestionIdsFilter()
    {
        return $this->excludeQuestionIdsFilter;
    }

    public function setExcludeQuestionIdsFilter($excludeQuestionIdsFilter): void
    {
        $this->excludeQuestionIdsFilter = $excludeQuestionIdsFilter;
    }

    public function getQuestionCompletionStatusFilter(): string
    {
        return $this->questionCompletionStatusFilter;
    }

    public function setQuestionCompletionStatusFilter($questionCompletionStatusFilter): void
    {
        $this->questionCompletionStatusFilter = $questionCompletionStatusFilter;
    }

    public function addFieldFilter($fieldName, $fieldValue): void
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

    public function setAvailableTaxonomyIds($availableTaxonomyIds): void
    {
        $this->availableTaxonomyIds = $availableTaxonomyIds;
    }

    public function getAvailableTaxonomyIds(): array
    {
        return $this->availableTaxonomyIds;
    }

    public function setAnswerStatusActiveId($answerStatusActiveId): void
    {
        $this->answerStatusActiveId = $answerStatusActiveId;
    }

    public function getAnswerStatusActiveId(): ?int
    {
        return $this->answerStatusActiveId;
    }

    public function setAnswerStatusFilter($answerStatusFilter): void
    {
        $this->answerStatusFilter = $answerStatusFilter;
    }

    public function getAnswerStatusFilter(): ?string
    {
        return $this->answerStatusFilter;
    }

    /**
     * Set if object data table should be joined
     *
     * @param bool $a_val join object_data
     */
    public function setJoinObjectData($a_val): void
    {
        $this->join_obj_data = $a_val;
    }

    /**
     * Get if object data table should be joined
     *
     * @return bool join object_data
     */
    public function getJoinObjectData(): bool
    {
        return $this->join_obj_data;
    }

    /**
     * @param array $forcedQuestionIds
     */
    public function setForcedQuestionIds($forcedQuestionIds): void
    {
        $this->forcedQuestionIds = $forcedQuestionIds;
    }

    /**
     * @return array
     */
    public function getForcedQuestionIds(): array
    {
        return $this->forcedQuestionIds;
    }

    private function getParentObjFilterExpression(): ?string
    {
        if ($this->getParentObjId()) {
            return 'qpl_questions.obj_fi = ' . $this->db->quote($this->getParentObjId(), 'integer');
        }

        if (count($this->getParentObjIdsFilter())) {
            return $this->db->in('qpl_questions.obj_fi', $this->getParentObjIdsFilter(), false, 'integer');
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

                    $expressions[] = $this->db->like('qpl_questions.' . $fieldName, 'text', "%%$fieldValue%%");
                    break;

                case 'type':

                    $expressions[] = "qpl_qst_type.type_tag = {$this->db->quote($fieldValue, 'text')}";
                    break;

                case 'question_id':
                    if ($fieldValue != "" && !is_array($fieldValue)) {
                        $fieldValue = array($fieldValue);
                    }
                    $expressions[] = $this->db->in("qpl_questions.question_id", $fieldValue, false, "integer");
                    break;

                case 'parent_title':
                    if ($this->join_obj_data) {
                        $expressions[] = $this->db->like('object_data.title', 'text', "%%$fieldValue%%");
                    }
                    break;
            }
        }

        return $expressions;
    }

    private function getTaxonomyFilterExpressions(): array
    {
        $expressions = [];
        if($this->taxFiltersExcludeAnyObjectsWithTaxonomies) {
            $expressions[] = 'question_id NOT IN (SELECT DISTINCT item_id FROM tax_node_assignment)';
            return $expressions;
        }

        foreach ($this->taxFilters as $taxId => $taxNodes) {
            $questionIds = [];

            $forceBypass = true;

            foreach ($taxNodes as $taxNode) {
                $forceBypass = false;

                $taxItemsByTaxParent = $this->getTaxItems(
                    $this->taxParentTypes[$taxId],
                    $this->taxParentIds[$taxId],
                    $taxId,
                    $taxNode
                );

                $taxItemsByParent = $this->getTaxItems(
                    $this->parentObjType,
                    $this->parentObjId,
                    $taxId,
                    $taxNode
                );

                $taxItems = array_merge($taxItemsByTaxParent, $taxItemsByParent);
                foreach ($taxItems as $taxItem) {
                    $questionIds[$taxItem['item_id']] = $taxItem['item_id'];
                }
            }

            if (!$forceBypass) {
                $expressions[] = $this->db->in('question_id', $questionIds, false, 'integer');
            }
        }

        return $expressions;
    }

    /**
     * @param string $parentType
     * @param int $parentObjId
     * @param int $taxId
     * @param int $taxNode
     * @return array
     */
    protected function getTaxItems($parentType, $parentObjId, $taxId, $taxNode): array
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
        switch ($this->getQuestionInstanceTypeFilter()) {
            case self::QUESTION_INSTANCE_TYPE_ORIGINALS:
                return 'qpl_questions.original_id IS NULL';
            case self::QUESTION_INSTANCE_TYPE_DUPLICATES:
                return 'qpl_questions.original_id IS NOT NULL';
            case self::QUESTION_INSTANCE_TYPE_ALL:
            default:
                return null;
        }

        return null;
    }

    private function getQuestionIdsFilterExpressions(): array
    {
        $expressions = [];

        if (is_array($this->getIncludeQuestionIdsFilter())) {
            $expressions[] = $this->db->in(
                'qpl_questions.question_id',
                $this->getIncludeQuestionIdsFilter(),
                false,
                'integer'
            );
        }

        if (is_array($this->getExcludeQuestionIdsFilter())) {
            $IN = $this->db->in(
                'qpl_questions.question_id',
                $this->getExcludeQuestionIdsFilter(),
                true,
                'integer'
            );

            if ($IN == ' 1=2 ') {
                $IN = ' 1=1 ';
            } // required for ILIAS < 5.0

            $expressions[] = $IN;
        }

        return $expressions;
    }

    private function getParentObjectIdFilterExpression(): ?string
    {
        if ($this->parentObjId) {
            return "qpl_questions.obj_fi = {$this->db->quote($this->parentObjId, 'integer')}";
        }

        return null;
    }

    private function getAnswerStatusFilterExpressions(): array
    {
        $expressions = [];

        switch ($this->getAnswerStatusFilter()) {
            case self::ANSWER_STATUS_FILTER_ALL_NON_CORRECT:

                $expressions[] = '
					(tst_test_result.question_fi IS NULL OR tst_test_result.points < qpl_questions.points)
				';
                break;

            case self::ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY:

                $expressions[] = 'tst_test_result.question_fi IS NULL';
                break;

            case self::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY:

                $expressions[] = 'tst_test_result.question_fi IS NOT NULL';
                $expressions[] = 'tst_test_result.points < qpl_questions.points';
                break;
        }

        return $expressions;
    }

    private function getTableJoinExpression(): string
    {
        $tableJoin = "
			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi
		";

        if ($this->join_obj_data) {
            $tableJoin .= "
				INNER JOIN	object_data
				ON			object_data.obj_id = qpl_questions.obj_fi
			";
        }

        if ($this->getParentObjectType() === 'tst'
            && $this->getQuestionInstanceTypeFilter() === self::QUESTION_INSTANCE_TYPE_ALL) {
            $tableJoin .= "
            						INNER JOIN	tst_test_question tstquest
			ON			tstquest.question_fi = qpl_questions.question_id
			";
        }

        if ($this->getAnswerStatusActiveId()) {
            $tableJoin .= "
				LEFT JOIN	tst_test_result
				ON			tst_test_result.question_fi = qpl_questions.question_id
				AND			tst_test_result.active_fi = {$this->db->quote($this->getAnswerStatusActiveId(), 'integer')}
			";
        }

        return $tableJoin;
    }

    private function getConditionalFilterExpression(): string
    {
        $CONDITIONS = [];

        if ($this->getQuestionInstanceTypeFilterExpression() !== null) {
            $CONDITIONS[] = $this->getQuestionInstanceTypeFilterExpression();
        }

        if ($this->getParentObjFilterExpression() !== null) {
            $CONDITIONS[] = $this->getParentObjFilterExpression();
        }

        if ($this->getParentObjectIdFilterExpression() !== null) {
            $CONDITIONS[] = $this->getParentObjectIdFilterExpression();
        }

        $CONDITIONS = array_merge(
            $CONDITIONS,
            $this->getQuestionIdsFilterExpressions(),
            $this->getFieldFilterExpressions(),
            $this->getTaxonomyFilterExpressions(),
            $this->getAnswerStatusFilterExpressions()
        );

        $CONDITIONS = implode(' AND ', $CONDITIONS);

        return strlen($CONDITIONS) ? 'AND ' . $CONDITIONS : '';
    }

    private function getSelectFieldsExpression(): string
    {
        $selectFields = array(
            'qpl_questions.*',
            'qpl_qst_type.type_tag',
            'qpl_qst_type.plugin',
            'qpl_qst_type.plugin_name',
            'qpl_questions.points max_points'
        );

        if ($this->join_obj_data) {
            $selectFields[] = 'object_data.title parent_title';
        }

        if ($this->getAnswerStatusActiveId()) {
            $selectFields[] = 'tst_test_result.points reached_points';
            $selectFields[] = "CASE
					WHEN tst_test_result.points IS NULL THEN '" . self::QUESTION_ANSWER_STATUS_NON_ANSWERED . "'
					WHEN tst_test_result.points < qpl_questions.points THEN '" . self::QUESTION_ANSWER_STATUS_WRONG_ANSWERED . "'
					ELSE '" . self::QUESTION_ANSWER_STATUS_CORRECT_ANSWERED . "'
				END question_answer_status
			";
        }

        $selectFields = implode(",\n\t\t\t\t", $selectFields);

        return "
			SELECT		{$selectFields}
		";
    }

    private function buildBasicQuery(): string
    {
        return "
			{$this->getSelectFieldsExpression()}

			FROM		qpl_questions

			{$this->getTableJoinExpression()}

			WHERE		qpl_questions.tstamp > 0
		";
    }

    private function buildQuery(): string
    {
        $query = $this->buildBasicQuery() . "
			{$this->getConditionalFilterExpression()}
		";

        if (count($this->getForcedQuestionIds())) {
            $query .= "
				UNION {$this->buildBasicQuery()}
				AND	{$this->db->in('qpl_questions.question_id', $this->getForcedQuestionIds(), false, 'integer')}
			";
        }

        return $query;
    }

    public function load(): void
    {
        $this->checkFilters();

        $query = $this->buildQuery();
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);

            if (!$this->isActiveQuestionType($row)) {
                continue;
            }

            $row['taxonomies'] = $this->loadTaxonomyAssignmentData($row['obj_fi'], $row['question_id']);
            $row['ttype'] = $this->lng->txt($row['type_tag']);
            $row['feedback'] = $this->hasGenericFeedback((int)$row['question_id']);
            $row['hints'] = $this->hasHints((int)$row['question_id']);
            $row['comments'] = $this->getNumberOfCommentsForQuestion($row['question_id']);

            if(
                ($this->filter_comments === self::QUESTION_COMMENTED_ONLY && $row['comments'] === 0) ||
                ($this->filter_comments === self::QUESTION_COMMENTED_EXCLUDED && $row['comments'] > 0)
            ) {
                continue;
            }

            $this->questions[ $row['question_id'] ] = $row;
        }
    }

    protected function getNumberOfCommentsForQuestion(int $question_id): int
    {
        $notes_context = $this->notes_service->data()->context(
            $this->getParentObjId(),
            $question_id,
            'quest'
        );
        return $this->notes_service->domain()->getNrOfCommentsForContext($notes_context);
    }

    public function setCommentFilter(int $commented = null)
    {
        $this->filter_comments = $commented;
    }

    protected function hasGenericFeedback(int $question_id): bool
    {
        $res = $this->db->queryF(
            "SELECT * FROM qpl_fb_generic WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );
        return $this->db->numRows($res) > 0;
    }

    protected function hasHints(int $question_id): bool
    {
        $questionHintList = ilAssQuestionHintList::getListByQuestionId($question_id);
        return iterator_count($questionHintList) > 0;
    }

    private function loadTaxonomyAssignmentData($parentObjId, $questionId): array
    {
        $taxAssignmentData = [];

        foreach ($this->getAvailableTaxonomyIds() as $taxId) {
            $taxTree = new ilTaxonomyTree($taxId);

            $taxAssignment = new ilTaxNodeAssignment('qpl', $parentObjId, 'quest', $taxId);

            $assignments = $taxAssignment->getAssignmentsOfItem($questionId);

            foreach ($assignments as $assData) {
                if (!isset($taxAssignmentData[ $assData['tax_id'] ])) {
                    $taxAssignmentData[ $assData['tax_id'] ] = [];
                }

                $nodeData = $taxTree->getNodeData($assData['node_id']);

                $assData['node_lft'] = $nodeData['lft'];

                $taxAssignmentData[ $assData['tax_id'] ][ $assData['node_id'] ] = $assData;
            }
        }

        return $taxAssignmentData;
    }

    private function isActiveQuestionType(array $questionData): bool
    {
        if (!isset($questionData['plugin'])) {
            return false;
        }

        if (!$questionData['plugin']) {
            return true;
        }

        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName((string) $questionData['plugin_name'])) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_MODULES,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                (string) $questionData['plugin_name']
            )->isActive();
    }

    public function getDataArrayForQuestionId($questionId)
    {
        return $this->questions[$questionId];
    }

    public function getQuestionDataArray(): array
    {
        return $this->questions;
    }

    public function isInList($questionId): bool
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
        if ($a_comp_id != 'qpl' || $a_item_type != 'quest' || !$a_item_id) {
            return '';
        }

        if (!isset($this->questions[$a_item_id])) {
            return '';
        }

        return $this->questions[$a_item_id]['title'];
    }

    private function checkFilters(): void
    {
        if ($this->getAnswerStatusFilter() !== null && !$this->getAnswerStatusActiveId()) {
            throw new ilTestQuestionPoolException(
                'No active id given! You cannot use the answer status filter without giving an active id.'
            );
        }
    }
}
