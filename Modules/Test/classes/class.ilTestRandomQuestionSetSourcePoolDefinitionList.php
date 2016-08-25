<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetLostPool.php';
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
	
	protected function addLostPool(ilTestRandomQuestionSetLostPool $lostPool)
	{
		$this->lostPools[$lostPool->getId()] = $lostPool;
	}
	
	public function isLostPool($poolId)
	{
		return isset($this->lostPools[$poolId]);
	}

	public function hasLostPool()
	{
		return (bool)count($this->lostPools);
	}
	
	public function getLostPools()
	{
		return $this->lostPools;
	}
	
	public function loadDefinitions()
	{
		$query = "
			SELECT tst_rnd_quest_set_qpls.*, object_data.obj_id pool_id
			FROM tst_rnd_quest_set_qpls
			LEFT JOIN object_data
			ON obj_id = pool_fi
			WHERE test_fi = %s
			ORDER BY sequence_pos ASC
		";
		
		$res = $this->db->queryF($query, array('integer'), array($this->testOBJ->getTestId()));

		while( $row = $this->db->fetchAssoc($res) )
		{
			$sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getEmptySourcePoolDefinition();

			$sourcePoolDefinition->initFromArray($row);

			$this->addDefinition($sourcePoolDefinition);
			
			if( !$row['pool_id'] && !$this->isLostPool($row['pool_fi']) )
			{
				$lostPool = new ilTestRandomQuestionSetLostPool();
				
				$lostPool->assignDbRow($row);
				
				$this->addLostPool($lostPool);
			}
		}
	}
	
	public function saveDefinitions()
	{
		foreach($this as $sourcePoolDefinition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			$sourcePoolDefinition->saveToDb();
		}
	}

	public function cloneDefinitionsForTestId($testId)
	{
		$definitionIdMap = array();
		
		foreach($this as $definition)
		{
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

		foreach($this as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			$positionIndex[ $definition->getId() ] = $definition->getSequencePosition();
		}

		asort($positionIndex);

		$i = 1;

		foreach($positionIndex as $definitionId => $definitionPosition)
		{
			$positionIndex[$definitionId] = $i++;
		}

		foreach($this as $definition)
		{
			$definition->setSequencePosition( $positionIndex[$definition->getId()] );
		}
	}
	
	public function getNextPosition()
	{
		return ( count($this->sourcePoolDefinitions) + 1 );
	}

	public function getInvolvedSourcePoolIds()
	{
		$involvedSourcePoolIds = array();

		foreach($this as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			$involvedSourcePoolIds[ $definition->getPoolId() ] = $definition->getPoolId();
		}

		return array_values($involvedSourcePoolIds);
	}

	public function getQuestionAmount()
	{
		$questionAmount = 0;

		foreach($this as $definition)
		{
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
		foreach($this as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			if( $definition->getMappedFilterTaxId() && $definition->getMappedFilterTaxNodeId() )
			{
				return true;
			}
		}
		
		return false;
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
}
