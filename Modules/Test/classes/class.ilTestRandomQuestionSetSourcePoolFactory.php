<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePool.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolFactory
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
	 * @return \ilTestRandomQuestionSetSourcePool
	 */
	public function getSourcePoolByOriginalPoolData($originalPoolData)
	{
		$sourcePool = new ilTestRandomQuestionSetSourcePool($this->db, $this->testOBJ);
		
		$sourcePool->setPoolTitle( $originalPoolData['qpl_title'] );
		$sourcePool->setPoolPath( $originalPoolData['qpl_path'] );
		$sourcePool->setPoolQuestionCount( $originalPoolData['count'] );

		return $sourcePool;
	}
	
	/**
	 * @return \ilTestRandomQuestionSetSourcePool
	 */
	public function buildSourcePoolByMirroredPoolData($sourcePoolId)
	{
		$sourcePool = new ilTestRandomQuestionSetSourcePool($this->db, $this->testOBJ);
		
		$sourcePool->loadFromDb($sourcePoolId);
		
		return $sourcePool;
	}
}
