<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionList
{
	/**
	 * global $ilDB object instance
	 *
	 * @var ilDB
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
	 * Constructor
	 * 
	 * @param ilDB $db
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilDB $db, ilObjTest $testOBJ)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}
	
	
	public function loadDefinitions()
	{
		
	}
	
	public function saveDefinitions()
	{
		
	}
	
	public function definitionExists()
	{
		
	}
	
	public function reindexPositions()
	{
		
	}
	
	public function getNextPosition()
	{
		
	}
}
