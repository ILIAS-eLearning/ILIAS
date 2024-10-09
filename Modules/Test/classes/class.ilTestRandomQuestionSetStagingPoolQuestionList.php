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
    private $testObjId = -1;

    /**
     * @var integer
     */
    private $testId = -1;

    /**
     * @var integer
     */
    private $poolId = -1;

    /**
     * @var array
     */
    private $taxFilters = [];

    // fau: taxFilter/typeFilter - private variable
    // TODO-RND2017: rename to typesFilter (multiple types allowed)
    /**
     * @var array
     */
    private $typeFilter = [];
    // fau.

    /**
     * @var array
     */
    private $lifecycleFilter = [];

    /**
     * @var array
     */
    private $questions = [];

    public function __construct(ilDBInterface $db, ilComponentRepository $component_repository)
    {
        $this->db = $db;
        $this->component_repository = $component_repository;
    }

    public function setTestObjId($testObjId)
    {
        $this->testObjId = $testObjId;
    }

    public function getTestObjId(): int
    {
        return $this->testObjId;
    }

    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    public function getTestId(): int
    {
        return $this->testId;
    }

    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }

    public function getPoolId(): int
    {
        return $this->poolId;
    }

    public function addTaxonomyFilter($taxId, $taxNodes)
    {
        $this->taxFilters[$taxId] = $taxNodes;
    }

    public function getTaxonomyFilters(): array
    {
        return $this->taxFilters;
    }

    // fau: taxFilter/typeFilter - getter/setter
    public function getTypeFilter()
    {
        return $this->typeFilter;
    }

    public function setTypeFilter($typeFilter)
    {
        $this->typeFilter = $typeFilter;
    }
    // fau.

    /**
     * @return array
     */
    public function getLifecycleFilter(): array
    {
        return $this->lifecycleFilter;
    }

    /**
     * @param array $lifecycleFilter
     */
    public function setLifecycleFilter(array $lifecycleFilter)
    {
        $this->lifecycleFilter = $lifecycleFilter;
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
        $CONDITIONS = $this->getTaxonomyFilterExpressions();

        // fau: taxFilter/typeFilter - add the type filter expression to conditions
        $CONDITIONS = array_merge($CONDITIONS, $this->getTypeFilterExpressions());
        // fau.

        $CONDITIONS = array_merge($CONDITIONS, $this->getLifecycleFilterExpressions());

        $CONDITIONS = implode(' AND ', $CONDITIONS);

        return strlen($CONDITIONS) ? 'AND ' . $CONDITIONS : '';
    }

    private function getTaxonomyFilterExpressions(): array
    {
        $expressions = [];

        foreach ($this->getTaxonomyFilters() as $taxId => $taxNodes) {
            $questionIds = [];

            $forceBypass = true;

            foreach ($taxNodes as $taxNode) {
                $forceBypass = false;

                $taxTree = new ilTaxonomyTree($taxId);

                $taxNodeAssignment = new ilTaxNodeAssignment('tst', $this->getTestObjId(), 'quest', $taxId);

                $subNodes = $taxTree->getSubTreeIds((int) $taxNode);
                $subNodes[] = $taxNode;

                $taxItems = $taxNodeAssignment->getAssignmentsOfNode($subNodes);

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

    private function getLifecycleFilterExpressions(): array
    {
        if (count($this->lifecycleFilter)) {
            return [
                $this->db->in('lifecycle', $this->lifecycleFilter, false, 'text')
            ];
        }

        return [];
    }

    // fau: taxFilter/typeFilter - get the expressions for a type filter
    private function getTypeFilterExpressions(): array
    {
        if (count($this->typeFilter)) {
            return [
                $this->db->in('question_type_fi', $this->typeFilter, false, 'integer')
            ];
        }

        return [];
    }
    // fau;

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
        )->getPluginSlotById('qst')->hasPluginName($questionData['plugin_name'])) {
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
                $questionData['plugin_name']
            )->isActive();
    }

    public function resetQuestionList()
    {
        $this->questions = [];
        $this->taxFilters = [];
        $this->typeFilter = [];
        $this->poolId = -1;
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

    public static function updateSourceQuestionPoolId($testId, $oldPoolId, $newPoolId)
    {
        global $DIC;
        $db = $DIC['ilDB'];

        $query = "UPDATE tst_rnd_cpy SET qpl_fi = %s WHERE tst_fi = %s AND qpl_fi = %s";

        $db->manipulateF(
            $query,
            ['integer', 'integer', 'integer'],
            [$newPoolId, $testId, $oldPoolId]
        );
    }
}
