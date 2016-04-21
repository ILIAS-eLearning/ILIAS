<?php
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
class GevWBDErrorTest extends ErrorTestBase {
	protected $backupGlobals = FALSE;

	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();

		$this->error = new gevWBDError("Die ist ein Fehler",'usr', 'new_user',124,0);
	}

	public function error_with_message_null() {
		return array(array(null,1,2,3,'new_user')
					 ,array(null,1,2,3,'new_user')
					 ,array(null,1,2,3,'new_user')
					 ,array(null,1,2,3,'new_user')
					);
	}

	public function error_with_user_id_null() {
		return array(array("lala",null,2,3,'new_user')
					 ,array("lulu",null,2,3,'new_user')
					 ,array("lele",null,2,3,'new_user')
					 ,array("lili",null,2,3,'new_user')
					);
	}

	public function error_with_row_id_null() {
		return array(array("lala",1,null,3,'new_user')
					 ,array("lulu",1,null,3,'new_user')
					 ,array("lele",1,null,3,'new_user')
					 ,array("lili",1,null,3,'new_user')
				);
	}

	public function error_with_crs_id_null() {
		return array(array("lala",1,2,null,'new_user')
					 ,array("lulu",1,2,null,'new_user')
					 ,array("lele",1,2,null,'new_user')
					 ,array("lili",1,2,null,'new_user')
					);
	}

	public function error_with_crs_id_zero() {
		return array(array("lala",1,2,0,'new_user')
					 ,array("lulu",1,2,0,'new_user')
					 ,array("lele",1,2,0,'new_user')
					 ,array("lili",1,2,0,'new_user')
					);
	}

	public function error_with_message() {
		return array(
			array('USER_EXISTS' 			, 'Der Benutzer wurde bereits angelegt:','user')
			,array('USER_EXISTS_TP'			, 'Der Benutzer wurde von einem anderen TP angelegt:','user')
			,array('USER_UNKNOWN'			, 'Die VermittlerID ist nicht vorhanden.','user')
			,array('USER_DIFFERENT_TP'		, 'Der TP 95473000 ist dem Vermittler','user')
			,array('USER_DEACTIVATED'		, 'Der Vermittler ist deaktiviert.','user')
			,array('USER_DEACTIVATED'		,  "' ist deaktiviert",'user')
			,array('USER_SELF_SERVICE'		, 'nicht zugeordnet. VV-Selbstverwalter','user')
			,array('WRONG_USERDATA'			, 'not well formed:','user')
			,array('WRONG_USERDATA'			, 'mandatory field missing: street','user')
			,array('WRONG_USERDATA'			, 'not in list:','user')
			,array('WRONG_USERDATA'			, 'date not between 1900 and 2000 (birthday)','user')
			,array('WRONG_USERDATA'			, 'Daten sind nicht plausibel: 1 Ungültiges Feld','user')
			,array('WRONG_COURSEDATA'		, 'Daten sind nicht plausibel: 1 Das Ende des Seminars liegt in der Zukunft','crs')
			,array('WRONG_COURSEDATA'		, 'Daten sind nicht plausibel: 2 Der Anfang des Seminars liegt in der Zukunft','crs')
			,array('WRONG_COURSEDATA'		, 'dates implausible: begin > end','crs')
			,array('WRONG_COURSEDATA'		, 'mandatory field missing: study_content','crs')
			,array('WRONG_COURSEDATA'		, 'mandatory field missing: study_type_selection','crs')
			,array('TOO_OLD'				, 'date older than one year','crs')
			,array('NO_RELEASE'				, 'Die Organisation ist nicht berechtigt den Vermittler transferfähig zu machen','user')
			,array('CREATE_DUPLICATE'		, 'Der Nutzer konnte nicht im ISTS geändert werden. Status Code: 100', 'user')
		);
	}

	public function test_UserId() {
		$this->assertInternalType("integer", $this->error->userId());
	}

	/**
     * @dataProvider error_with_user_id_null
   	 * @expectedException LogicException
     */
	public function test_noUserId($errMsg,$usr_id,$row_id,$crs_id,$service) {
		$error = new gevWBDError($errMsg,'user',$service,$usr_id,$row_id,$crs_id);
		$this->assertNull($error->userId());
	}

	public function test_RowId() {
		$this->assertInternalType("integer", $this->error->rowId());
	}

	/**
     * @dataProvider error_with_row_id_null
	 * @expectedException LogicException     
     */
	public function test_noRowId($errMsg,$usr_id,$row_id,$crs_id,$service) {
		$error = new gevWBDError($errMsg,'user',$service,$usr_id,$row_id,$crs_id);
		$this->assertNull($error->rowId());
	}

	public function test_CrsId() {
		$this->assertInternalType("integer", $this->error->crsId());
	}

	/**
     * @dataProvider error_with_crs_id_zero
     */
	public function test_zeroCrsId($errMsg,$usr_id,$row_id,$crs_id,$service) {
		$error = new gevWBDError($errMsg,'crs',$service,$usr_id,$row_id,$crs_id);
		$this->assertEquals(0,$error->crsId());
	}
	/**
     * @dataProvider error_with_message
     */
	public function test_reason($reason, $error, $group) {
		$err = new gevWBDError($error, $group,'new_user', 2, 0);
		$this->assertEquals($reason, $err->reason());
	}
}