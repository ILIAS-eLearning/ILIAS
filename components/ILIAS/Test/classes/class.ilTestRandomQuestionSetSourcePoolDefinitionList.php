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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionList implements Iterator
{
    protected ilDBInterface $db;
    protected ilObjTest $test_obj;
    private array $source_pool_definitions = [];
    private ilTestRandomQuestionSetSourcePoolDefinitionFactory $source_pool_definition_factory;
    protected array $lost_pools = [];
    protected array $trashed_pools = [];

    public function __construct(ilDBInterface $db, ilObjTest $test_obj, ilTestRandomQuestionSetSourcePoolDefinitionFactory $source_pool_definition_factory)
    {
        $this->db = $db;
        $this->test_obj = $test_obj;
        $this->source_pool_definition_factory = $source_pool_definition_factory;
    }

    public function addDefinition(ilTestRandomQuestionSetSourcePoolDefinition $source_pool_definition)
    {
        $this->source_pool_definitions[ $source_pool_definition->getId() ] = $source_pool_definition;
    }

    protected function addLostPool(ilTestRandomQuestionSetNonAvailablePool $lost_pool)
    {
        $this->lost_pools[$lost_pool->getId()] = $lost_pool;
    }

    public function isLostPool(?int $pool_id): bool
    {
        return isset($this->lost_pools[$pool_id]);
    }

    public function hasLostPool(): bool
    {
        return (bool) count($this->lost_pools);
    }

    public function getLostPools(): array
    {
        return $this->lost_pools;
    }

    public function getLostPool(int $pool_id)
    {
        if ($this->isLostPool($pool_id)) {
            return $this->lost_pools[$pool_id];
        }

        return null;
    }

    public function isTrashedPool(int $pool_id): bool
    {
        return isset($this->trashed_pools[$pool_id]);
    }

    public function hasTrashedPool(): bool
    {
        return (bool) count($this->trashed_pools);
    }

    public function getTrashedPools(): array
    {
        return $this->trashed_pools;
    }

    public function setTrashedPools(array $trashed_pools): void
    {
        $this->trashed_pools = $trashed_pools;
    }

    // hey: fixRandomTestBuildable - provide single definitions, quantities distribution likes to deal with objects

    public function hasDefinition(int $source_pool_definition_id): bool
    {
        return $this->getDefinition($source_pool_definition_id) !== null;
    }

    public function getDefinition(int $source_pool_definition_id): ?ilTestRandomQuestionSetSourcePoolDefinition
    {
        if (isset($this->source_pool_definitions[$source_pool_definition_id])) {
            return $this->source_pool_definitions[$source_pool_definition_id];
        }

        return null;
    }

    public function getDefinitionBySourcePoolId(int $source_pool_id): ilTestRandomQuestionSetSourcePoolDefinition
    {
        foreach ($this as $definition) {
            if ($definition->getPoolId() != $source_pool_id) {
                continue;
            }

            return $definition;
        }

        throw new InvalidArgumentException('invalid source pool id given');
    }

    public function getDefinitionIds(): array
    {
        return array_keys($this->source_pool_definitions);
    }

    public function getDefinitionCount(): int
    {
        return count($this->source_pool_definitions);
    }
    // hey.

    public function loadDefinitions(): void
    {
        $query = "
			SELECT tst_rnd_quest_set_qpls.*, odat.obj_id pool_id, odat.title actual_pool_title, tree.child
			FROM tst_rnd_quest_set_qpls
			LEFT JOIN object_data odat
			ON odat.obj_id = pool_fi
			LEFT JOIN object_reference oref
			ON oref.obj_id = pool_fi
			LEFT JOIN tree
			ON tree = %s
			AND child = oref.ref_id
			WHERE test_fi = %s
			ORDER BY sequence_pos ASC
		";

        $res = $this->db->queryF($query, ['integer', 'integer'], [1, $this->test_obj->getTestId()]);

        $handled_definitions = [];
        $trashed_pools = [];

        while ($row = $this->db->fetchAssoc($res)) {
            $source_pool_definition = $this->source_pool_definition_factory->getEmptySourcePoolDefinition();
            $source_pool_definition->initFromArray($row);

            if (!isset($handled_definitions[$source_pool_definition->getId()])) {
                $this->addDefinition($source_pool_definition);
                $handled_definitions[$source_pool_definition->getId()] = $source_pool_definition->getId();

                $trashedPool = new ilTestRandomQuestionSetNonAvailablePool();
                $trashedPool->assignDbRow($row);

                $trashedPool->setUnavailabilityStatus(
                    ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_TRASHED
                );

                $trashed_pools[$trashedPool->getId()] = $trashedPool;
            }

            if (!$this->isLostPool($row['pool_fi'])
                && !$row['pool_id']) {
                $lost_pool = new ilTestRandomQuestionSetNonAvailablePool();
                $lost_pool->assignDbRow($row);

                $lost_pool->setUnavailabilityStatus(
                    ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST
                );

                $this->addLostPool($lost_pool);

                if (isset($trashed_pools[$lost_pool->getId()])) {
                    unset($trashed_pools[$lost_pool->getId()]);
                }
            }

            if (isset($row['actual_pool_title'])
                && $source_pool_definition->getPoolTitle() !== $row['actual_pool_title']) {
                $source_pool_definition->setPoolTitle($row['actual_pool_title']);
                $source_pool_definition->saveToDb();
            }

            if ($row['child']) {
                unset($trashed_pools[$row['pool_id']]);
            }
        }

        $this->setTrashedPools($trashed_pools);
    }

    public function saveDefinitions(): void
    {
        foreach ($this as $source_pool_definition) {
            $source_pool_definition->saveToDb();
        }
    }

    public function cloneDefinitionsForTestId(int $test_id): array
    {
        $definition_id_map = [];

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

            $original_id = $definition->getId();
            $definition->cloneToDbForTestId($test_id);
            $clone_id = $definition->getId();

            $definition_id_map[$original_id] = $clone_id;
        }

        return $definition_id_map;
    }

    public function deleteDefinitions(): void
    {
        $query = "DELETE FROM tst_rnd_quest_set_qpls WHERE test_fi = %s";
        $this->db->manipulateF($query, ['integer'], [$this->test_obj->getTestId()]);
    }

    public function reindexPositions(): void
    {
        $position_index = [];

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $position_index[ $definition->getId() ] = $definition->getSequencePosition();
        }

        asort($position_index);

        $i = 1;

        foreach (array_keys($position_index) as $definition_id) {
            $position_index[$definition_id] = $i++;
        }

        foreach ($this as $definition) {
            $definition->setSequencePosition($position_index[$definition->getId()]);
        }
    }

    public function getNextPosition(): int
    {
        return (count($this->source_pool_definitions) + 1);
    }

    public function getInvolvedSourcePoolIds(): array
    {
        $involved_source_pool_ids = [];

        foreach ($this as $definition) {
            $involved_source_pool_ids[ $definition->getPoolId() ] = $definition->getPoolId();
        }

        return array_values($involved_source_pool_ids);
    }

    public function getQuestionAmount(): ?int
    {
        $question_amount = 0;

        foreach ($this as $definition) {
            $question_amount += $definition->getQuestionAmount();
        }

        return $question_amount;
    }

    /**
     * @return bool
     */
    public function savedDefinitionsExist(): bool
    {
        $query = "SELECT COUNT(*) cnt FROM tst_rnd_quest_set_qpls WHERE test_fi = %s";
        $res = $this->db->queryF($query, ['integer'], [$this->test_obj->getTestId()]);

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    public function hasTaxonomyFilters(): bool
    {
        foreach ($this as $definition) {
            if (count($definition->getMappedTaxonomyFilter())) {
                return true;
            }
        }

        return false;
    }

    public function hasTypeFilters(): bool
    {
        foreach ($this as $definition) {
            if (count($definition->getTypeFilter())) {
                return true;
            }
        }
        return false;
    }

    public function areAllUsedPoolsAvailable(): bool
    {
        if ($this->hasLostPool()) {
            return false;
        }

        if ($this->hasTrashedPool()) {
            return false;
        }

        return true;
    }

    public function rewind(): void
    {
        reset($this->source_pool_definitions);
    }

    public function current(): ?ilTestRandomQuestionSetSourcePoolDefinition
    {
        $current = current($this->source_pool_definitions);
        return $current !== false ? $current : null;
    }

    public function key(): ?int
    {
        return key($this->source_pool_definitions);
    }

    public function next(): void
    {
        next($this->source_pool_definitions);
    }

    public function valid(): bool
    {
        return key($this->source_pool_definitions) !== null;
    }

    public function getNonAvailablePools(): array
    {
        return array_merge($this->getTrashedPools(), $this->getLostPools());
    }
}
