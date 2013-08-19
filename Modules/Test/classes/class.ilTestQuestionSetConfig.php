<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * abstract parent class that manages/holds the data for a question set configuration
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestQuestionSetConfig
{
	/**
	 * global $tree object instance
	 *
	 * @var ilTree
	 */
	protected $tree = null;
	
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
	 * Constructor
	 */
	public function __construct(ilTree $tree, ilDB $db, ilObjTest $testOBJ)
	{
		$this->tree = $tree;
		$this->db = $db;
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * loads the question set config for current test from the database
	 * 
	 * @return boolean
	 */
	public function loadFromDb()
	{
		return true;
	}
	
	/**
	 * saves the question set config for current test to the database
	 * 
	 * @return boolean
	 */
	public function saveToDb()
	{
		return true;
	}
	
	/**
	 * deletes the question set config for current test from the database
	 * 
	 * @return boolean
	 */
	public function deleteFromDb()
	{
		return true;
	}
	
	public function areDepenciesInVulnerableState()
	{
		return false;
	}
	
	public function getDepenciesInVulnerableStateMessage(ilLanguage $lng)
	{
		return '';
	}
	
	public function areDepenciesBroken()
	{
		return false;
	}
	
	public function getDepenciesBrokenMessage(ilLanguage $lng)
	{
		return '';
	}

	abstract public function isQuestionSetConfigured();
	
	/**
	 * checks wether question set config related data exists or not
	 */
	abstract public function doesQuestionSetRelatedDataExist();
	
	/**
	 * removes all question set config related data
	 */
	abstract public function removeQuestionSetRelatedData();
}
