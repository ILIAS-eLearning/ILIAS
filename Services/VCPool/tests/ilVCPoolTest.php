<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Unit tests for VC-assignment logic.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

require_once("Services/VCPool/classes/class.ilVCPool.php");

class ilVCPoolTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	
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
	
		$this->no_trunc = false;
		if ($this->containsLiveData()) {
			$this->no_trunc = true;
			throw new ilException("VCPool-Tables seem to contain live data.");
		}
		
		global $ilDB;
		
		$ilDB->insert(ilVCPool::URL_POOL_TABLE, array
					( "id"		=> array("integer", 0)
					, "url"		=> array("text", "www.concepts-and-training.de")
					, "vc_type"	=> array("text", "cat")
					));
		
		$ilDB->insert(ilVCPool::URL_POOL_TABLE, array
					( "id"		=> array("integer", 1)
					, "url"		=> array("text", "www.concepts-and-training.de")
					, "vc_type"	=> array("text", "cat")
					));
		
		$ilDB->insert(ilVCPool::URL_POOL_TABLE, array
					( "id"		=> array("integer", 2)
					, "url"		=> array("text", "www.google.de")
					, "vc_type"	=> array("text", "other")
					));
	}
	
	public function testGetAssignment() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-04 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-04 11:00:00", IL_CAL_DATETIME);
		
		$ass = $vc_pool->getVCAssignment("cat", $start, $end);
		$this->assertInstanceOf("ilVCAssignment", $ass);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass->getId());
		$this->assertEquals("cat", $ass->getVC()->getType());
		$this->assertTrue(in_array($ass->getVC()->getId(), array(0,1)));
	}
	
	public function testGetTwoAssignments() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-04 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-04 11:00:00", IL_CAL_DATETIME);
		
		$ass1 = $vc_pool->getVCAssignment("cat", $start, $end);
		$ass2 = $vc_pool->getVCAssignment("cat", $start, $end);
		
		$this->assertInstanceOf("ilVCAssignment", $ass1);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass1->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass1->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass1->getId());
		$this->assertEquals("cat", $ass1->getVC()->getType());
		$this->assertTrue(in_array($ass1->getVC()->getId(), array(0,1)));
		
		$this->assertInstanceOf("ilVCAssignment", $ass2);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass2->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass2->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass2->getId());
		$this->assertEquals("cat", $ass2->getVC()->getType());
		$this->assertTrue(in_array($ass2->getVC()->getId(), array(0,1)));
		
		$this->assertNotEquals($ass1->getId(), $ass2->getId());
		$this->assertNotEquals($ass1->getVC()->getId(), $ass2->getVC()->getId());
	}
	
	public function testNoThirdAssignment() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-04 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-04 11:00:00", IL_CAL_DATETIME);
		
		$ass1 = $vc_pool->getVCAssignment("cat", $start, $end);
		$ass2 = $vc_pool->getVCAssignment("cat", $start, $end);
		$ass3 = $vc_pool->getVCAssignment("cat", $start, $end);
		
		$this->assertNull($ass3);
	}
	
	public function testNoAssignmentToUnknownCategory() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-04 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-04 11:00:00", IL_CAL_DATETIME);
		
		$ass = $vc_pool->getVCAssignment("foobar", $start, $end);
		$this->assertNull($ass);
	}
	
	public function testReleaseAssignment() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-04 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-04 11:00:00", IL_CAL_DATETIME);
		
		// First assignment
		$ass1 = $vc_pool->getVCAssignment("other", $start, $end);
		$this->assertInstanceOf("ilVCAssignment", $ass1);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass1->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass1->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass1->getId());
		$this->assertEquals("other", $ass1->getVC()->getType());
		$this->assertEquals($ass1->getVC()->getId(), 2);
		
		// No second assignment possible.
		$ass2 = $vc_pool->getVCAssignment("other", $start, $end);
		$this->assertNull($ass2);
		
		// Release first assignment
		$ass1->release();
		
		// Now another assignment should be possible.
		$ass3 = $vc_pool->getVCAssignment("other", $start, $end);
		
		$this->assertInstanceOf("ilVCAssignment", $ass1);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass1->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass1->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass1->getId());
		$this->assertEquals("other", $ass1->getVC()->getType());
		$this->assertEquals($ass1->getVC()->getId(), 2);
	}
	
	protected function tearDown() {
		if ($this->no_trunc) {
			return;
		}
		
		global $ilDB;
		
		$ilDB->manipulate("TRUNCATE TABLE ".ilVCPool::URL_POOL_TABLE);
		$ilDB->manipulate("TRUNCATE TABLE ".ilVCPool::ASSIGNMENT_TABLE);
	}
}
