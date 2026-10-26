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
    private ilDBInterface $db;
    private ilComponentRepository $component_repository;

    /**
     * @var integer
     */
    private $test_obj_id = -1;

    /**
     * @var integer
     */
    private $test_id = -1;

    /**
     * @var integer
     */
    private $pool_id = -1;

    /**
     * @var array
     */
    private $tax_filters = [];

    // fau: taxFilter/typeFilter - private variable
    // TODO-RND2017: rename to typesFilter (multiple types allowed)
    /**
     * @var array
     */
    private $type_filter = [];
    // fau.

    /**
     * @var array
     */
    private $lifecycle_filter = [];

    /**
     * @var array
     */
    private $questions = [];

    public function __construct(ilDBInterface $db, ilComponentRepository $component_repository)
    {
        $this->db = $db;
        $this->component_repository = $component_repository;
    }

    public function setTestObjId($test_obj_id)
    {
        $this->test_obj_id = $test_obj_id;
    }

    public function getTestObjId(): int
    {
        return $this->test_obj_id;
    }

    public function setTestId($test_id)
    {
        $this->test_id = $test_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function setPoolId($pool_id)
    {
        $this->pool_id = $pool_id;
    }

    public function getPoolId(): int
    {
        return $this->pool_id;
    }

    public function addTaxonomyFilter($tax_id, $tax_nodes)
    {
        $tax_id = (int) $tax_id;
        if ($tax_id < 1) {
            return;
        }

        $this->tax_filters[$tax_id] = $tax_nodes;
    }

    public function getTaxonomyFilters(): array
    {
        return $this->tax_filters;
    }

    // fau: taxFilter/typeFilter - getter/setter
    public function getTypeFilter()
    {
        return $this->type_filter;
    }

    public function setTypeFilter($type_filter)
    {
        $this->type_filter = $type_filter;
    }
    // fau.

    /**
     * @return array
     */
    public function getLifecycleFilter(): array
    {
        return $this->lifecycle_filter;
    }

    /**
     * @param array $lifecycle_filter
     */
    public function setLifecycleFilter(array $lifecycle_filter)
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
        $conditions = $this->getTaxonomyFilterExpressions();

        // fau: taxFilter/typeFilter - add the type filter expression to conditions
        $conditions = array_merge($conditions, $this->getTypeFilterExpressions());
        // fau.

        $conditions = array_merge($conditions, $this->getLifecycleFilterExpressions());

        $conditions = implode(' AND ', $conditions);

        return strlen($conditions) ? 'AND ' . $conditions : '';
    }

    private function getTaxonomyFilterExpressions(): array
    {
        $expressions = [];

        foreach ($this->getTaxonomyFilters() as $tax_id => $tax_nodes) {
            $question_ids = [];

            $force_bypass = true;

            foreach ($tax_nodes as $tax_node) {
                $force_bypass = false;

                $tax_tree = new ilTaxonomyTree($tax_id);

                $tax_node_assignment = new ilTaxNodeAssignment('tst', $this->getTestObjId(), 'quest', $tax_id);

                $sub_nodes = $tax_tree->getSubTreeIds((int) $tax_node);
                $sub_nodes[] = $tax_node;

                $tax_items = $tax_node_assignment->getAssignmentsOfNode($sub_nodes);

                foreach ($tax_items as $tax_item) {
                    $question_ids[$tax_item['item_id']] = $tax_item['item_id'];
                }
            }

            if (!$force_bypass) {
                $expressions[] = $this->db->in('question_id', $question_ids, false, 'integer');
            }
        }

        return $expressions;
    }

    private function getLifecycleFilterExpressions(): array
    {
        if (count($this->lifecycle_filter)) {
            return [
                $this->db->in('lifecycle', $this->lifecycle_filter, false, 'text')
            ];
        }

        return [];
    }

    // fau: taxFilter/typeFilter - get the expressions for a type filter
    private function getTypeFilterExpressions(): array
    {
        if (count($this->type_filter)) {
            return [
                $this->db->in('question_type_fi', $this->type_filter, false, 'integer')
            ];
        }

        return [];
    }
    // fau;

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

    public static function updateSourceQuestionPoolId($test_id, $old_pool_id, $new_pool_id)
    {
        global $DIC;
        $db = $DIC['ilDB'];

        $query = "UPDATE tst_rnd_cpy SET qpl_fi = %s WHERE tst_fi = %s AND qpl_fi = %s";

        $db->manipulateF(
            $query,
            ['integer', 'integer', 'integer'],
            [$new_pool_id, $test_id, $old_pool_id]
        );
    }
}
