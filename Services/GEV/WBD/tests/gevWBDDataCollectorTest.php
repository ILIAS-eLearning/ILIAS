<?php
require_once("Services/GEV/WBD/classes/class.gevWBDDataCollector.php");
class gevWBDDataCollectorTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
			$this->data_collector = new gevWBDDataCollector();
	}

	public function error_with_crs_id() {
		return array(
					 array(array('mandatory field missing: study_type_selection','new_user',1,0,10), array('mandatory field missing: study_type_selection','new_user',1,0,10))
					,array(array('mandatory field missing: study_type_selection','new_user',1,2,20), array('mandatory field missing: study_type_selection','new_user',1,2,20))
					,array(array('mandatory field missing: study_type_selection','new_user',4,2,30), array('mandatory field missing: study_type_selection','new_user',4,2,30))
					,array(array('mandatory field missing: study_type_selection','new_user',1,2,0), array('mandatory field missing: study_type_selection','new_user',1,2,0))
					);
	}
	
	public function test_isWBDDataCollector() {
		$this->assertInstanceOf("gevWBDDataCollector",$this->data_collector);
	}

	/**
	* @dataProvider error_with_crs_id
	*/
	public function test_error_1($error_data,$error) {
		$this->assertTrue($this->data_collector->error(new gevWBDError($error[0],$error[1],$error[2],$error[3],$error[4])) > 0);
	}

	public function test_createNewUserList() {
		$this->data_collector->createNewUserList();
		$this->assertNotNull($this->data_collector->getRecords());
		$this->assertTrue(is_array($this->data_collector->getRecords()));
	}

	public function test_getNextNewUserRequest() {
		$this->data_collector->createNewUserList();

		while($rec = $this->data_collector->getNextRecord()) {
			$this->assertInstanceOf("gevWBDRequestVvErstanlage",$rec);
		}

		$this->assertNull($this->data_collector->getRecords());
	}
	
	public function test_create_update_user_list() {
		$this->data_collector->createUpdateUserList();
		$this->assertNotNull($this->data_collector->getRecords());
		$this->assertTrue(is_array($this->data_collector->getRecords()));
	}

	public function test_getNextUpdateUserRequest() {
		$this->data_collector->createUpdateUserList();

		while($rec = $this->data_collector->getNextRecord()) {
			$this->assertInstanceOf("gevWBDRequestVvAenderung",$rec);
		}

		$this->assertNull($this->data_collector->getRecords());
	}
}
?>