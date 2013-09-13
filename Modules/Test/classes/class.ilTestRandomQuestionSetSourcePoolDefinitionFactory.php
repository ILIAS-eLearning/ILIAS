<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinition.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionFactory
{
	/**
	 * @var ilDB
	 */
	private $db = null;
	
	/**
	 * @var ilObjTest
	 */
	private $testOBJ = null;
	
	/**
	 * @param ilDB $db
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function getSourcePoolDefinitionByOriginalPoolData($originalPoolData)
	{
		$sourcePoolDefinition = $this->buildDefinitionInstance();

		$sourcePoolDefinition->setPoolId( $originalPoolData['qpl_id'] );
		$sourcePoolDefinition->setPoolTitle( $originalPoolData['qpl_title'] );
		$sourcePoolDefinition->setPoolPath( $originalPoolData['qpl_path'] );
		$sourcePoolDefinition->setPoolQuestionCount( $originalPoolData['count'] );

		return $sourcePoolDefinition;
	}

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function getSourcePoolDefinitionByDefinitionId($definitionId)
	{
		$sourcePoolDefinition = $this->buildDefinitionInstance();

		$sourcePoolDefinition->loadFromDb($definitionId);

		return $sourcePoolDefinition;
	}

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	public function getEmptySourcePoolDefinition()
	{
		return $this->buildDefinitionInstance();
	}

	/**
	 * @return ilTestRandomQuestionSetSourcePoolDefinition
	 */
	private function buildDefinitionInstance()
	{
		return new ilTestRandomQuestionSetSourcePoolDefinition($this->db, $this->testOBJ);
	}
}
