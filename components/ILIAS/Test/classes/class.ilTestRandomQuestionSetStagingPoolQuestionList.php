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

/**
 * Handles a list of questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 */
class ilTestRandomQuestionSetStagingPoolQuestionList implements Iterator
{
    private int $test_obj_id = -1;
    private int $test_id = -1;
    private int $pool_id = -1;
    private array $tax_filters = [];
    private array $type_filter = [];
    private array $lifecycle_filter = [];
    private array $questions = [];

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly ilComponentRepository $component_repository
    ) {
    }

    public function setTestObjId(int $testObjId): void
    {
        $this->test_obj_id = $testObjId;
    }

    public function getTestObjId(): int
    {
        return $this->test_obj_id;
    }

    public function setTestId(int $test_id): void
    {
        $this->test_id = $test_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function setPoolId(int $pool_id): void
    {
        $this->pool_id = $pool_id;
    }

    public function getPoolId(): int
    {
        return $this->pool_id;
    }

    public function addTaxonomyFilter(int $tax_id, array $tax_nodes): void
    {
        $this->tax_filters[$tax_id] = $tax_nodes;
    }

    public function getTaxonomyFilters(): array
    {
        return $this->tax_filters;
    }

    public function getTypeFilter(): array
    {
        return $this->type_filter;
    }

    public function setTypeFilter(array $type_filter): void
    {
        $this->type_filter = $type_filter;
    }

    public function getLifecycleFilter(): array
    {
        return $this->lifecycle_filter;
    }

    public function setLifecycleFilter(array $lifecycle_filter): void
    {
        $this->lifecycle_filter = $lifecycle_filter;
    }

    public function loadQuestions()
    {
        $query = "
			SELECT		qpl_questions.question_id,
						qpl_qst_type.type_tag,
						qpl_qst_type.plugin,
						qpl_qst_type.plugin_name

			FROM		tst_rnd_cpy

			INNER JOIN	qpl_questions
			ON			qpl_questions.question_id = tst_rnd_cpy.qst_fi

			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi

			WHERE		tst_rnd_cpy.tst_fi = %s
			AND			tst_rnd_cpy.qpl_fi = %s

			{$this->getConditionalExpression()}
		";

        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->getTestId(), $this->getPoolId()]
        );

        //echo sprintf($query, $this->getTestId(), $this->getPoolId());exit;

        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);

            if (!$this->isActiveQuestionType($row)) {
                continue;
            }

            $this->questions[] = (int) $row['question_id'];
        }
    }

    private function getConditionalExpression(): string
    {
        $conditions = implode(
            ' AND ',
            array_merge(
                $this->getTaxonomyFilterExpressions(),
                $this->getTypeFilterExpressions(),
                $this->getLifecycleFilterExpressions()
            )
        );

        return $conditions !== '' ? 'AND ' . $conditions : '';
    }

    private function getTaxonomyFilterExpressions(): array
    {
        $expressions = [];
        foreach ($this->getTaxonomyFilters() as $tax_id => $tax_nodes) {
            $question_ids = [];

            if ($tax_nodes === [] || $tax_nodes === null) {
                continue;
            }

            foreach ($tax_nodes as $tax_node) {
                $tax_items = (new ilTaxNodeAssignment('tst', $this->getTestObjId(), 'quest', $tax_id))
                    ->getAssignmentsOfNode([$tax_node]);

                foreach ($tax_items as $tax_item) {
                    $question_ids[$tax_item['item_id']] = $tax_item['item_id'];
                }
            }
            $expressions[] = $this->db->in('question_id', $question_ids, false, 'integer');
        }

        return $expressions;
    }

    private function getLifecycleFilterExpressions(): array
    {
        if ($this->lifecycle_filter !== []) {
            return [
                $this->db->in('lifecycle', $this->lifecycle_filter, false, 'text')
            ];
        }

        return [];
    }

    private function getTypeFilterExpressions(): array
    {
        if ($this->type_filter !== []) {
            return [
                $this->db->in('question_type_fi', $this->type_filter, false, 'integer')
            ];
        }

        return [];
    }

    private function isActiveQuestionType(array $question_data): bool
    {
        if (!isset($question_data['plugin'])) {
            return false;
        }

        if (!$question_data['plugin']) {
            return true;
        }

        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_COMPONENT,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($question_data['plugin_name'])) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_COMPONENT,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                $question_data['plugin_name']
            )->isActive();
    }

    public function resetQuestionList()
    {
        $this->questions = [];
        $this->tax_filters = [];
        $this->type_filter = [];
        $this->pool_id = -1;
    }

    public function getQuestions(): array
    {
        return array_values($this->questions);
    }

    // =================================================================================================================

    public function rewind(): void
    {
        reset($this->questions);
    }

    public function current(): ?int
    {
        $current = current($this->questions);
        return $current !== false ? $current : null;
    }

    public function key(): ?int
    {
        return key($this->questions);
    }

    public function next(): void
    {
        next($this->questions);
    }

    public function valid(): bool
    {
        return key($this->questions) !== null;
    }

    public static function updateSourceQuestionPoolId(
        int $test_id,
        int $old_pool_id,
        int $new_pool_id
    ): void {
        global $DIC;
        $DIC['ilDB']->manipulateF(
            'UPDATE tst_rnd_cpy SET qpl_fi = %s WHERE tst_fi = %s AND qpl_fi = %s',
            ['integer', 'integer', 'integer'],
            [$new_pool_id, $test_id, $old_pool_id]
        );
    }
}
