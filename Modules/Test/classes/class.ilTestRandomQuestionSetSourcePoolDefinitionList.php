<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetNonAvailablePool.php';
/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionList implements Iterator
{
    /**
     * global $ilDB object instance
     *
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * object instance of current test
     *
     * @var ilObjTest
     */
    protected $testOBJ = null;
    
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinition[]
     */
    private $sourcePoolDefinitions = array();

    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionFactory
     */
    private $sourcePoolDefinitionFactory = null;

    /**
     * @var array
     */
    protected $lostPools = array();
    
    /**
     * @var array
     */
    protected $trashedPools = array();
    
    /**
     * Constructor
     *
     * @param ilDBInterface $db
     * @param ilObjTest $testOBJ
     */
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
    
    public function isLostPool($poolId)
    {
        return isset($this->lostPools[$poolId]);
    }

    public function hasLostPool()
    {
        return (bool) count($this->lostPools);
    }
    
    public function getLostPools()
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
    
    public function isTrashedPool($poolId)
    {
        return isset($this->trashedPools[$poolId]);
    }
    
    public function hasTrashedPool()
    {
        return (bool) count($this->trashedPools);
    }
    
    /**
     * @return array
     */
    public function getTrashedPools()
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
    
    public function hasDefinition($sourcePoolDefinitionId)
    {
        return $this->getDefinition($sourcePoolDefinitionId) !== null;
    }
    
    public function getDefinition($sourcePoolDefinitionId)
    {
        if (isset($this->sourcePoolDefinitions[$sourcePoolDefinitionId])) {
            return $this->sourcePoolDefinitions[$sourcePoolDefinitionId];
        }
        
        return null;
    }
    
    public function getDefinitionBySourcePoolId($sourcePoolId)
    {
        foreach ($this as $definition) {
            if ($definition->getPoolId() != $sourcePoolId) {
                continue;
            }
            
            return $definition;
        }
        
        throw new InvalidArgumentException('invalid source pool id given');
    }
    
    public function getDefinitionIds()
    {
        return array_keys($this->sourcePoolDefinitions);
    }
    
    public function getDefinitionCount()
    {
        return count($this->sourcePoolDefinitions);
    }
    // hey.
    
    public function loadDefinitions()
    {
        $query = "
			SELECT tst_rnd_quest_set_qpls.*, odat.obj_id pool_id, tree.child
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
            
            if (!$this->isLostPool($row['pool_id'])) {
                if (!$row['pool_id']) {
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
            }
            
            if ($row['child']) {
                unset($trashedPools[$row['pool_id']]);
            }
        }
        
        $this->setTrashedPools($trashedPools);
    }
    
    public function saveDefinitions()
    {
        foreach ($this as $sourcePoolDefinition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $sourcePoolDefinition->saveToDb();
        }
    }

    public function cloneDefinitionsForTestId($testId)
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
    
    public function getNextPosition()
    {
        return (count($this->sourcePoolDefinitions) + 1);
    }

    public function getInvolvedSourcePoolIds()
    {
        $involvedSourcePoolIds = array();

        foreach ($this as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            $involvedSourcePoolIds[ $definition->getPoolId() ] = $definition->getPoolId();
        }

        return array_values($involvedSourcePoolIds);
    }

    public function getQuestionAmount()
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
    public function savedDefinitionsExist()
    {
        $query = "SELECT COUNT(*) cnt FROM tst_rnd_quest_set_qpls WHERE test_fi = %s";
        $res = $this->db->queryF($query, array('integer'), array($this->testOBJ->getTestId()));

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    public function hasTaxonomyFilters()
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
    public function hasTypeFilters()
    {
        foreach ($this as $definition) {
            if (count($definition->getTypeFilter())) {
                return true;
            }
        }
        return false;
    }
    // fau.

    public function areAllUsedPoolsAvailable()
    {
        if ($this->hasLostPool()) {
            return false;
        }
        
        if ($this->hasTrashedPool()) {
            return false;
        }
        
        return true;
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function rewind()
    {
        return reset($this->sourcePoolDefinitions);
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function current()
    {
        return current($this->sourcePoolDefinitions);
    }

    /**
     * @return integer
     */
    public function key()
    {
        return key($this->sourcePoolDefinitions);
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function next()
    {
        return next($this->sourcePoolDefinitions);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return key($this->sourcePoolDefinitions) !== null;
    }
    
    public function getNonAvailablePools()
    {
        //echo get_class($this->getTrashedPools()[0]);
        return array_merge($this->getTrashedPools(), $this->getLostPools());
    }
}
