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
					( "id"				=> array("integer", 0)
					, "url"				=> array("text", "www.concepts-and-training.de")
					, "vc_type"			=> array("text", "cat")
					, "tutor_password"	=> array("text", "tutor_pw1")
					, "tutor_login"		=> array("text", "tutor_login1")
					, "member_password"	=> array("text", "member_pw1")
					));
		
		$ilDB->insert(ilVCPool::URL_POOL_TABLE, array
					( "id"		=> array("integer", 1)
					, "url"		=> array("text", "www.concepts-and-training.de")
					, "vc_type"	=> array("text", "cat")
					, "tutor_password"	=> array("text", "tutor_pw2")
					, "tutor_login"		=> array("text", "tutor_login2")
					, "member_password"	=> array("text", "member_pw2")
					));
		
		$ilDB->insert(ilVCPool::URL_POOL_TABLE, array
					( "id"		=> array("integer", 2)
					, "url"		=> array("text", "www.google.de")
					, "vc_type"	=> array("text", "other")
					, "tutor_password"	=> array("text", "tutor_pw3")
					, "tutor_login"		=> array("text", "tutor_login3")
					, "member_password"	=> array("text", "member_pw3")
					));
	}
	
	public function testGetAssignment() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
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
		
		$start = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass1 = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
		$ass2 = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
		
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
		
		$start = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass1 = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
		$ass2 = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
		$ass3 = $vc_pool->getVCAssignment("cat", $obj_id, $start, $end);
		
		$this->assertNull($ass3);
	}
	
	public function testNoAssignmentToUnknownCategory() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass = $vc_pool->getVCAssignment("foobar", $obj_id, $start, $end);
		$this->assertNull($ass);
	}
	
	public function testReleaseAssignment() {
		$vc_pool = ilVCPool::getInstance();
		
		$start = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		// First assignment
		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start, $end);
		$this->assertInstanceOf("ilVCAssignment", $ass1);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass1->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass1->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass1->getId());
		$this->assertEquals("other", $ass1->getVC()->getType());
		$this->assertEquals($ass1->getVC()->getId(), 2);
		
		// No second assignment possible.
		$ass2 = $vc_pool->getVCAssignment("other", $obj_id, $start, $end);
		$this->assertNull($ass2);
		
		// Release first assignment
		$ass1->release();
		
		// Now another assignment should be possible.
		$ass3 = $vc_pool->getVCAssignment("other", $obj_id, $start, $end);
		
		$this->assertInstanceOf("ilVCAssignment", $ass1);
		$this->assertEquals( $start->get(IL_CAL_DATETIME)
						   , $ass1->getStart()->get(IL_CAL_DATETIME));
		$this->assertEquals( $end->get(IL_CAL_DATETIME)
						   , $ass1->getEnd()->get(IL_CAL_DATETIME));
		$this->assertInternalType("int", $ass1->getId());
		$this->assertEquals("other", $ass1->getVC()->getType());
		$this->assertEquals($ass1->getVC()->getId(), 2);
	}
	
	public function testNonOverlappingAssignments() {
		$vc_pool = ilVCPool::getInstance();
		
		$start1 = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end1 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$start2 = new ilDateTime("2015-05-05 10:00:00", IL_CAL_DATETIME);
		$end2 = new ilDateTime("2015-05-05 11:00:00", IL_CAL_DATETIME);
		$start3 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$end3 = new ilDateTime("2015-04-05 12:00:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start1, $end1);
		$this->assertNotNull($ass1);
		
		$ass2 = $vc_pool->getVCAssignment("other", $obj_id, $start2, $end2);
		$this->assertNotNull($ass2);
		
		$ass3 = $vc_pool->getVCAssignment("other", $obj_id, $start3, $end3);
		$this->assertNotNull($ass3);
	}
	
	public function testOverlappingAssignments() {
		$vc_pool = ilVCPool::getInstance();
		
		$start1 = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end1 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$start2 = new ilDateTime("2015-04-05 10:45:00", IL_CAL_DATETIME);
		$end2 = new ilDateTime("2015-04-05 11:45:00", IL_CAL_DATETIME);
		$obj_id = 307;
		
		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start1, $end1);
		$this->assertNotNull($ass1);
		
		$ass2 = $vc_pool->getVCAssignment("other", $obj_id, $start2, $end2);
		$this->assertNull($ass2);
	}

	public function testGetAssignmentById() {
		$vc_pool = ilVCPool::getInstance();
		
		$start1 = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end1 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;		

		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start1, $end1);
		$this->assertNotNull($ass1);

		$assigns1 = $vc_pool->getVCAssignmentById((integer)$ass1->getId());
		$this->assertNotNull($assigns1);
	}

	public function testGetAssignmentsByObjId() {
		$vc_pool = ilVCPool::getInstance();
		
		$start1 = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end1 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;		

		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start1, $end1);
		$this->assertNotNull($ass1);

		$assigns1 = $vc_pool->getVCAssignmentsByObjId($obj_id);
		$this->assertNotEmpty($assigns1);
	}

	public function testDataForGetAssignmentsByObjId() {
		$vc_pool = ilVCPool::getInstance();
		
		$start1 = new ilDateTime("2015-04-05 10:00:00", IL_CAL_DATETIME);
		$end1 = new ilDateTime("2015-04-05 11:00:00", IL_CAL_DATETIME);
		$obj_id = 307;		

		$ass1 = $vc_pool->getVCAssignment("other", $obj_id, $start1, $end1);
		$this->assertNotNull($ass1);

		$assigns1 = $vc_pool->getVCAssignmentsByObjId($obj_id);
		$this->assertNotEmpty($assigns1);

		$vc = array_shift($assigns1)->getVC();
		
		$this->assertEquals("www.google.de", $vc->getUrl());
		$this->assertEquals("tutor_pw3", $vc->getTutorPassword());
		$this->assertEquals("tutor_login3", $vc->getTutorLogin());
		$this->assertEquals("member_pw3", $vc->getMemberPassword());
	}
	
	public function testVCTypes() {
		$vc_pool = ilVCPool::getInstance();
		
		$vc_types = $vc_pool->getVCTypes();
		$this->assertEquals(array("cat", "other"), $vc_types);
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
