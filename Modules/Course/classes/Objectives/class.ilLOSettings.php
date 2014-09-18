<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOSettings
{
	const TYPE_TEST_INITIAL = 1;
	const TYPE_TEST_QUALIFIED = 2;
	
	const QT_VISIBLE_ALL = 0;
	const QT_VISIBLE_OBJECTIVE = 1;
	
	
	const LOC_INITIAL_ALL = 1;
	const LOC_INITIAL_SEL = 2;
	const LOC_QUALIFIED = 3;
	const LOC_PRACTISE = 4;
	
	
	private static $instances = array();
	
	private $container_id = 0;
	private $type = 0;
	private $initial_test = 0;
	private $qualified_test = 0;
	private $qt_visible_all = true;
	private $qt_visible_lo = false;
	private $reset_results = true;


	private $entry_exists = false;

	
	/**
	 * Constructor
	 * @param int $a_cont_id
	 */
	protected function __construct($a_cont_id)
	{
		$this->container_id = $a_cont_id;
		$this->read();
	}
	
	/**
	 * get singleton instance
	 * @param int $a_obj_id
	 * @return ilLOSettings
	 */
	public static function getInstanceByObjId($a_obj_id)
	{
		if(self::$instances[$a_obj_id])
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilLOSettings($a_obj_id);
	}
	
	/**
	 * Check if test ref_id is used in an objective course
	 * @param int ref_id
	 */
	public static function isObjectiveTest($a_trst_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT obj_id FROM loc_settings '.
				'WHERE itest = '.$ilDB->quote($a_trst_ref_id,'integer').' '.
				'OR qtest = '.$ilDB->quote($a_trst_ref_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return 0;
	}


	/**
	 * Check if the loc is configured for initial tests
	 */
	public function worksWithInitialTest()
	{
		return 
				($this->getType() == self::LOC_INITIAL_ALL) or
				($this->getType() == self::LOC_INITIAL_SEL)
		;
	}
	
	/**
	 * Check if qualified test for all objectives is visible
	 * @return type
	 */
	public function isGeneralQualifiedTestVisible()
	{
		return $this->qt_visible_all;
	}

	/**
	 * Check if qualified test for all objectives is visible
	 * @return type
	 */
	public function setGeneralQualifiedTestVisibility($a_stat)
	{
		$this->qt_visible_all = $a_stat;
		return true;
	}
	
	public function isQualifiedTestPerObjectiveVisible()
	{
		return $this->qt_visible_lo;
	}
	
	public function setQualifiedTestPerObjectiveVisibility($a_stat)
	{
		$this->qt_visible_lo = $a_stat;
	}

	/**
	 * 
	 * @return type
	 */
	public function settingsExist()
	{
		return $this->entry_exists;
	}
	
	public function getObjId()
	{
		return $this->container_id;
	}
	
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	
	public function getTestByType($a_type)
	{
		switch($a_type)
		{
			case self::TYPE_TEST_INITIAL:
				return $this->getInitialTest();
				
			case self::TYPE_TEST_QUALIFIED:
				return $this->getQualifiedTest();
		}
	}
	
	/**
	 * Get assigned tests
	 * @return type
	 */
	public function getTests()
	{
		$tests = array();
		if($this->getInitialTest())
		{
			$tests[] = $this->getInitialTest();
		}
		if($this->getQualifiedTest())
		{
			$tests[] = $this->getQualifiedTest();
		}
		return $tests;
	}
	
	/**
	 * Check if test is of type random test
	 * @param type $a_type
	 * @return type
	 */
	public function isRandomTestType($a_type)
	{
		$tst = $this->getTestByType($a_type);
		include_once './Modules/Test/classes/class.ilObjTest.php';
		return ilObjTest::_lookupRandomTest(ilObject::_lookupObjId($tst));
	}
	
	/**
	 * set initial test id
	 * @param type $a_id
	 */
	public function setInitialTest($a_id)
	{
		$this->initial_test = $a_id;
	}
	
	public function getInitialTest()
	{
		return $this->initial_test;
	}
	
	public function setQualifiedTest($a_id)
	{
		$this->qualified_test = $a_id;
	}
	
	public function getQualifiedTest()
	{
		return $this->qualified_test;
	}
	
	public function resetResults($a_status)
	{
		$this->reset_results = $a_status;
	}
	
	public function isResetResultsEnabled()
	{
		return (bool) $this->reset_results;
	}
	
	/**
	 * Create new entry
	 */
	public function create()
	{
		global $ilDB;
		
		$query = 'INSERT INTO loc_settings '.
				'(obj_id, type,itest,qtest,qt_vis_all,qt_vis_obj,reset_results) VALUES ( '.
				$ilDB->quote($this->getObjId(),'integer').', '.
				$ilDB->quote($this->getType(),'integer').', '.
				$ilDB->quote($this->getInitialTest(),'integer').', '.
				$ilDB->quote($this->getQualifiedTest(),'integer').', '.
				$ilDB->quote($this->isGeneralQualifiedTestVisible(),'integer').', '.
				$ilDB->quote($this->isQualifiedTestPerObjectiveVisible(),'integer').', '.
				$ilDB->quote($this->isResetResultsEnabled(),'integer').' '.
				') ';
		$ilDB->manipulate($query);
	}

	
	/**
	 * update settings
	 * @global type $ilDB
	 */
	public function update()
	{
		global $ilDB;
		
		if(!$this->entry_exists)
		{
			return $this->create();
		}
		
		$query = 'UPDATE loc_settings '.' '.
				'SET type = '.$ilDB->quote($this->getType(),'integer').', '.
				'itest = '.$ilDB->quote($this->getInitialTest(),'integer').', '.
				'qtest = '.$ilDB->quote($this->getQualifiedTest(),'integer').', '.
				'qt_vis_all = '.$ilDB->quote($this->isGeneralQualifiedTestVisible(),'integer').', '.
				'qt_vis_obj = '.$ilDB->quote($this->isQualifiedTestPerObjectiveVisible(),'integer').', '.
				'reset_results = '.$ilDB->quote($this->isResetResultsEnabled(),'integer').' '.
				'WHERE obj_id = '.$ilDB->quote($this->getObjId(),'integer');
				
		$ilDB->manipulate($query);
	}

	/**
	 * Update start objects
	 * Depends on course objective settings
	 * 
	 * @param ilContainerStartObjects
	 */
	public function updateStartObjects(ilContainerStartObjects $start)
	{
		switch($this->getType())
		{
			case self::LOC_INITIAL_ALL:
				if($start->exists($this->getQualifiedTest()))
				{
					$start->deleteItem($this->getQualifiedTest());
				}
				if(!$start->exists($this->getInitialTest()))
				{
					$start->add($this->getInitialTest());
				}
				break;
				
			case self::LOC_INITIAL_SEL:
			case self::LOC_PRACTISE:
				if($start->exists($this->getQualifiedTest()))
				{
					$start->deleteItem($this->getQualifiedTest());
				}
				if($start->exists($this->getInitialTest()))
				{
					$start->deleteItem($this->getInitialTest());
				}
				break;
				
			case self::LOC_QUALIFIED:
				if(!$start->exists($this->getQualifiedTest()))
				{
					$start->add($this->getQualifiedTest());
				}
				if($start->exists($this->getInitialTest()))
				{
					$start->deleteItem($this->getInitialTest());
				}
				break;
		}
		return true;
	}
	
	
	/**
	 * Read 
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_settings '.
				'WHERE obj_id = '.$ilDB->quote($this->getObjId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->entry_exists = true;
			$this->setType($row->type);
			$this->setInitialTest($row->itest);
			$this->setQualifiedTest($row->qtest);
			#$this->setGeneralQualifiedTestVisibility($row->qt_vis_all);
			$this->setQualifiedTestPerObjectiveVisibility($row->qt_vis_obj);
			$this->resetResults($row->reset_results);
		}
		
		
		if($GLOBALS['tree']->isDeleted($this->getInitialTest()))
		{
			$this->setInitialTest(0);
		}
		if($GLOBALS['tree']->isDeleted($this->getQualifiedTest()))
		{
			$this->setQualifiedTest(0);
		}
	}
}
?>