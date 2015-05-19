<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Unit tests for VC-assignment logic.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

require_once("Services/VCPool/classes/class.ilVCPool.php");

class ilVCPoolTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobal = false;
	
	protected function containsLiveData() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT COUNT(*) cnt FROM ".ilVCPool::URL_POOL_TABLE);
		$rec = $ilDB->fetchAssoc($res);
		$count = (int)$rec["cnt"];
		
		$res = $ilDB->query("SELECT COUNT(*) cnt FROM ".ilVCPool::ASSIGNMENT_TABLE);
		$rec = $ilDB->fetchAssoc($res);
		$count += (int)$rec["cnt"];
		
		return $count != 0;
	}
	
	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		
		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	
		if ($this->containsLiveData()) {
			throw new ilException("VCPool-Tables seem to contain live data.");
		}
	}
	
	protected function tearDown() {
		if ($this->containsLiveData()) {
			return;
		}
		
		global $ilDB;
		
		$ilDB->manipulate("TRUNCATE TABLE ".ilVCPool::URL_POOL_TABLE);
		$ilDB->manipulate("TRUNCATE TABLE ".ilVCPool::ASSIGNMENT_TABLE);
	}
}
