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
	
	/**
    * @dataProvider error_with_crs_id
    */
    public function test_error_1($error_data,$error) {
		$this->assertTrue($this->data_collector->error(new gevWBDError($error[0],$error[1],$error[2],$error[3],$error[4])) > 0);
	}

	public function test_create_new_user_list() {
		$this->data_collector->createNewUserList();
		$i = 0;
		while($this->data_collector->getNextRecord()) {
			$i++;
		}
		$this->assertEquals($i,0);
	}
	
	public function test_create_update_user_list() {
		$this->data_collector->createUpdateUserList();
		$i = 0;
		while($this->data_collector->getNextRecord()) {
			$i++;
		}
		$this->assertEquals($i,0);
	}
}
?>