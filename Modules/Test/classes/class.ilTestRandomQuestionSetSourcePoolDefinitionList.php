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
    protected ilObjTest $testOBJ;
    private array $sourcePoolDefinitions = [];
    private ilTestRandomQuestionSetSourcePoolDefinitionFactory $sourcePoolDefinitionFactory;
    protected array $lostPools = [];
    protected array $trashedPools = [];

    public function __construct(ilDBInterface $db, ilObjTest $testOBJ, ilTestRandomQuestionSetSourcePoolDefinitionFactory $sourcePoolDefinitionFactory)
    {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
        $this->sourcePoolDefinitionFactory = $sourcePoolDefinitionFactory;
    }

    public function addDefinition(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition)
    {
        $this->sourcePoolDefinitions[ $sourcePoolDefinition->getId() ] = $sourcePoolDefinition;
    }

    protected function addLostPool(ilTestRandomQuestionSetNonAvailablePool $lostPool)
    {
        $this->lostPools[$lostPool->getId()] = $lostPool;
    }

    public function isLostPool($poolId): bool
    {
        return isset($this->lostPools[$poolId]);
    }

    public function hasLostPool(): bool
    {
        return (bool) count($this->lostPools);
    }

    public function getLostPools(): array
    {
        return $this->lostPools;
    }

    public function getLostPool($poolId)
    {
        if ($this->isLostPool($poolId)) {
            return $this->lostPools[$poolId];
        }

        return null;
    }

    public function isTrashedPool($poolId): bool
    {
        return isset($this->trashedPools[$poolId]);
    }

    public function hasTrashedPool(): bool
    {
        return (bool) count($this->trashedPools);
    }

    public function getTrashedPools(): array
    {
        return $this->trashedPools;
    }

    /**
     * @param array $trashedPools
     */
    public function setTrashedPools($trashedPools)
    {
        $this->trashedPools = $trashedPools;
    }

    // hey: fixRandomTestBuildable - provide single definitions, quantities distribution likes to deal with objects

    public function hasDefinition($sourcePoolDefinitionId): bool
    {
        return $this->getDefinition($sourcePoolDefinitionId) !== null;
    }

    public function getDefinition($sourcePoolDefinitionId): ?ilTestRandomQuestionSetSourcePoolDefinition
    {
        if (isset($this->sourcePoolDefinitions[$sourcePoolDefinitionId])) {
            return $this->sourcePoolDefinitions[$sourcePoolDefinitionId];
        }

        return null;
    }

    public function getDefinitionBySourcePoolId($sourcePoolId): ilTestRandomQuestionSetSourcePoolDefinition
    {
        foreach ($this as $definition) {
            if ($definition->getPoolId() != $sourcePoolId) {
                continue;
            }

            return $definition;
        }

        throw new InvalidArgumentException('invalid source pool id given');
    }

    public function getDefinitionIds(): array
    {
        return array_keys($this->sourcePoolDefinitions);
    }

    public function getDefinitionCount(): int
    {
        return count($this->sourcePoolDefinitions);
    }
    // hey.

    public function loadDefinitions()
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

        $res = $this->db->queryF($query, array('integer', 'integer'), array(1, $this->testOBJ->getTestId()));

        $handledDefinitions = array();
        $trashedPools = array();

        while ($row = $this->db->fetchAssoc($res)) {
            $sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getEmptySourcePoolDefinition();
            $sourcePoolDefinition->initFromArray($row);

            if (!isset($handledDefinitions[$sourcePoolDefinition->getId()])) {
                $this->addDefinition($sourcePoolDefinition);
                $handledDefinitions[$sourcePoolDefinition->getId()] = $sourcePoolDefinition->getId();

                $trashedPool = new ilTestRandomQuestionSetNonAvailablePool();
                $trashedPool->assignDbRow($row);

                $trashedPool->setUnavailabilityStatus(
                    ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_TRASHED
                );

                $trashedPools[$trashedPool->getId()] = $trashedPool;
            }

            if (!$this->isLostPool($row['pool_id'])
                && !$row['pool_id']) {
                $lostPool = new ilTestRandomQuestionSetNonAvailablePool();
                $lostPool->assignDbRow($row);

                $lostPool->setUnavailabilityStatus(
                    ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST
                );

                $this->addLostPool($lostPool);

                if (isset($trashedPools[$lostPool->getId()])) {
                    unset($trashedPools[$lostPool->getId()]);
                }
            }

            if (isset($row['actual_pool_title'])
                && $sourcePoolDefinition->getPoolTitle() !== $row['actual_pool_title']) {
                $sourcePoolDefinition->setPoolTitle($row['actual_pool_title']);
                $sourcePoolDefinition->saveToDb();
            }

            if ($row['child']) {
                unset($trashedPools[$row['pool_id']]);
            }
        }

        $this->setTrashedPools($trashedPools);
    }

    public function saveDefinitions(): void
    {
        foreach ($this as $source_pool_definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $source_pool_definition->saveToDb();
        }
    }

    public function cloneDefinitionsForTestId($testId): array
    {
        $definitionIdMap = array();

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

            $originalId = $definition->getId();
            $definition->cloneToDbForTestId($testId);
            $cloneId = $definition->getId();

            $definitionIdMap[$originalId] = $cloneId;
        }

        return $definitionIdMap;
    }

    public function deleteDefinitions()
    {
        $query = "DELETE FROM tst_rnd_quest_set_qpls WHERE test_fi = %s";
        $this->db->manipulateF($query, array('integer'), array($this->testOBJ->getTestId()));
    }

    public function reindexPositions()
    {
        $positionIndex = array();

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $positionIndex[ $definition->getId() ] = $definition->getSequencePosition();
        }

        asort($positionIndex);

        $i = 1;

        foreach ($positionIndex as $definitionId => $definitionPosition) {
            $positionIndex[$definitionId] = $i++;
        }

        foreach ($this as $definition) {
            $definition->setSequencePosition($positionIndex[$definition->getId()]);
        }
    }

    public function getNextPosition(): int
    {
        return (count($this->sourcePoolDefinitions) + 1);
    }

    public function getInvolvedSourcePoolIds(): array
    {
        $involvedSourcePoolIds = array();

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $involvedSourcePoolIds[ $definition->getPoolId() ] = $definition->getPoolId();
        }

        return array_values($involvedSourcePoolIds);
    }

    public function getQuestionAmount(): ?int
    {
        $questionAmount = 0;

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $questionAmount += $definition->getQuestionAmount();
        }

        return $questionAmount;
    }

    /**
     * @return bool
     */
    public function savedDefinitionsExist(): bool
    {
        $query = "SELECT COUNT(*) cnt FROM tst_rnd_quest_set_qpls WHERE test_fi = %s";
        $res = $this->db->queryF($query, array('integer'), array($this->testOBJ->getTestId()));

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    public function hasTaxonomyFilters(): bool
    {
        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            // fau: taxFilter/typeFilter - new check for existing taxonomy filter
            if (count($definition->getMappedTaxonomyFilter())) {
                return true;
            }
            #if( $definition->getMappedFilterTaxId() && $definition->getMappedFilterTaxNodeId() )
            #{
            #	return true;
            #}
            // fau.
        }

        return false;
    }

    // fau: taxFilter/typeFilter - check for existing type filters
    public function hasTypeFilters(): bool
    {
        foreach ($this as $definition) {
            if (count($definition->getTypeFilter())) {
                return true;
            }
        }
        return false;
    }
    // fau.

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
        reset($this->sourcePoolDefinitions);
    }

    public function current(): ?ilTestRandomQuestionSetSourcePoolDefinition
    {
        $current = current($this->sourcePoolDefinitions);
        return $current !== false ? $current : null;
    }

    public function key(): ?int
    {
        return key($this->sourcePoolDefinitions);
    }

    public function next(): void
    {
        next($this->sourcePoolDefinitions);
    }

    /**
     * @return boolean
     */
    public function valid(): bool
    {
        return key($this->sourcePoolDefinitions) !== null;
    }

    public function getNonAvailablePools(): array
    {
        return array_merge($this->getTrashedPools(), $this->getLostPools());
    }
}
