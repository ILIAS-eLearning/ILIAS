<?php
require_once("Services/GEV/WBD/classes/class.gevWBDDataCollector.php");

class _gevWBDError extends gevWBDError {
	protected function findReason() {
		$this->reason = "hugo wars";
		$this->internal = 1;
	}
}

class _gevWBDDataCollector extends gevWBDDataCollector {
	public $called_error = 0;

	public function testable_createNewUserList($db) {
		return $this->_createNewUserList($db);
	}

	public function error(gevWBDError $error) {
		$this->called_error++;
	}

	public function testable_error(gevWBDError $error) {
		parent::error($error);
	}

	public function setDB($db) {
		$this->gDB = $db;
	}
}

class mock_db {
	public $called_query = 0;
	public $called_fetchAssoc = 0;
	public $called_executed = 0;
	public $query_string = null;

	public $data = null;

	public function __construct(array $data) {
		$this->data = $data;
	}

	public function query($string) {
		$this->called_query++;
		$this->query_string = $string;
		return $this->data;
	}

	public function fetchAssoc(&$res) {
		$this->called_fetchAssoc++;
		return array_shift($res);
	}

	public function execute($sql, $data) {
		$this->called_executed++;
	}
}

class gevWBDDataCollectorTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		$this->data_collector = new _gevWBDDataCollector();
		$this->counter = 0;
	}

	public function test_isWBDDataCollector() {
		$this->assertInstanceOf("gevWBDDataCollector",$this->data_collector);
	}

	public function test_createNewUserList() {
		$data = $this->getNewUserData();
		
		$db = new mock_db($data);

		$this->data_collector->testable_createNewUserList($db);

		$this->assertEquals(1, $db->called_query);
		$this->assertEquals(count($data) + 1, $db->called_fetchAssoc);
		$this->assertTrue($this->data_collector->called_error === 0);
	}

	public function test_createNewUserListError() {
		$data = $this->getNewUserDataError();
		
		$db = new mock_db($data);

		$this->data_collector->testable_createNewUserList($db);

		$this->assertEquals(1, $db->called_query);
		$this->assertEquals(count($data) + 1, $db->called_fetchAssoc);
		$this->assertTrue($this->data_collector->called_error > 0);
	}

	public function test_getNextNewUserRequest() {
		$data = $this->getNewUserData();
		
		$db = new mock_db($data);

		$res = $this->data_collector->testable_createNewUserList($db);

		$this->assertEquals(1, $db->called_query);
		$this->assertEquals(count($data) + 1, $db->called_fetchAssoc);
		$this->assertTrue($this->data_collector->called_error == 0);

		$recs = array();
		foreach ($res as $key => $rec) {
			$this->assertInstanceOf("gevWBDRequestVvErstanlage",$rec);
			$recs[] = $rec;
		}

		$this->assertEquals(count($data), count($recs));
	}

	

	/**
	* @dataProvider wbdErrorProvider
	*/
	public function test_writeErrorToDB($error) {
		
		$this->counter++;
		$db = new mock_db(array());
		$this->data_collector->setDB($db);
		$this->data_collector->testable_error($error);

		$this->assertEquals($this->counter, $db->called_executed);
	}
	
	/*public function test_createUpdateUserList() {
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

	public function test_createReleaseUserList() {
		$this->data_collector->createReleaseUserList();
		$this->assertNotNull($this->data_collector->getRecords());
		$this->assertTrue(is_array($this->data_collector->getRecords()));
	}

	public function test_getNextReleaseUserRequest() {
		$this->data_collector->createReleaseUserList();

		while($rec = $this->data_collector->getNextRecord()) {
			$this->assertInstanceOf("gevWBDRequestVvAenderung",$rec);
		}

		$this->assertNull($this->data_collector->getRecords());
	}*/

	protected function getNewUserData() {
		return array(array("gender"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>"OKZ1"
					  ,"firstname"=>"Stefan"
					  ,"wbd_type"=>"3 - TP-Service"
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					),array("gender"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>"OKZ1"
					  ,"firstname"=>"Stefan"
					  ,"wbd_type"=>"3 - TP-Service"
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					));
	}

	protected function getNewUserDataError() {
		return array(array("gender"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>""
					  ,"firstname"=>"Stefan"
					  ,"wbd_type"=>"3 - TP-Service"
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					),array("gender"=>"m"
					  ,"email"=>"shecken@cat06.de"
					  ,"mobile_phone_nr"=>"0162/9800608"
					  ,"birthday"=>"1981-06-19"
					  ,"country"=>"D"
					  ,"lastname"=>"Hecken"
					  ,"city"=>"Köln"
					  ,"zipcode"=>"50969"
					  ,"phone_nr"=>"0221/46757600"
					  ,"degree"=>"Dr"
					  ,"wbd_agent_status"=>"Makler"
					  ,"okz"=>"OKZ1"
					  ,"firstname"=>"Stefan"
					  ,"wbd_type"=>""
					  ,"user_id"=>3215
					  ,"street"=>"Vorgebirgstr. 338"
					  ,"row_id"=>35214
					));
	}

	public function wbdErrorProvider() {
		return array(array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3)),
						array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3)),
						array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3)),
						array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3)),
						array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3)),
						array(new _gevWBDError("mandatory field missing: gender", "NEW_USER", 1, 2, 3))
			);
	}
}
?>