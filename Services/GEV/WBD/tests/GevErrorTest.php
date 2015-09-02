<?php
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
class GevWBDErrorTest extends ErrorTestBase {
	public function setUp() {
		//$this->error = new gevWBDError("Die ist ein Fehler",3251,124,0);
	}

	public function error_with_message_null() {
		return array(/*array(new gevWBDError(null,1,2,3,'new_user'))
					 ,array(new gevWBDError(null,1,2,3,'new_user'))
					 ,array(new gevWBDError(null,1,2,3,'new_user'))
					 ,array(new gevWBDError(null,1,2,3,'new_user'))*/
					);
	}

	public function error_with_user_id_null() {
		return array(/*array(new gevWBDError("lala",null,2,3,'new_user'))
					 ,array(new gevWBDError("lulu",null,2,3,'new_user'))
					 ,array(new gevWBDError("lele",null,2,3,'new_user'))
					 ,array(new gevWBDError("lili",null,2,3,'new_user'))*/
					);
	}

	public function error_with_row_id_null() {
		return array(/*array(new gevWBDError("lala",1,null,3,'new_user'))
					 ,array(new gevWBDError("lulu",1,null,3,'new_user'))
					 ,array(new gevWBDError("lele",1,null,3,'new_user'))
					 ,array(new gevWBDError("lili",1,null,3,'new_user'))*/
				);
	}

	public function error_with_crs_id_null() {
		return array(/*array(new gevWBDError("lala",1,2,null,'new_user'))
					 ,array(new gevWBDError("lulu",1,2,null,'new_user'))
					 ,array(new gevWBDError("lele",1,2,null,'new_user'))
					 ,array(new gevWBDError("lili",1,2,null,'new_user'))*/
					);
	}

	public function error_with_crs_id_zero() {
		return array(/*array(new gevWBDError("lala",1,2,0,'new_user'))
					 ,array(new gevWBDError("lulu",1,2,0,'new_user'))
					 ,array(new gevWBDError("lele",1,2,0,'new_user'))
					 ,array(new gevWBDError("lili",1,2,0,'new_user'))*/
					);
	}

	/*public function error_with_message() {
		return array(
			array('USER_EXISTS' 			, 'Der Benutzer wurde bereits angelegt:')
			,array('USER_EXISTS_TP'			, 'Der Benutzer wurde von einem anderen TP angelegt:')
			,array('USER_UNKNOWN'			, 'Die VermittlerID ist nicht vorhanden.')
			,array('USER_DIFFERENT_TP'		, 'Der TP 95473000 ist dem Vermittler')
			,array('USER_DEACTIVATED'		, 'Der Vermittler ist deaktiviert.')
			,array('USER_DEACTIVATED'		,  "' ist deaktiviert")
			,array('USER_SERVICETYPE'		, 'nicht zugeordnet. VV-Selbstverwalter')
			,array('WRONG_USERDATA'			, 'not well formed:')
			,array('WRONG_USERDATA'			, 'mandatory field missing: street')
			,array('WRONG_USERDATA'			, 'not in list:')
			,array('WRONG_USERDATA'			, 'date not between 1900 and 2000 (birthday)')
			,array('WRONG_USERDATA'			, 'Daten sind nicht plausibel: 1 Ungültiges Feld')
			,array('WRONG_COURSEDATA'		, 'Daten sind nicht plausibel: 1 Das Ende des Seminars liegt in der Zukunft')
			,array('WRONG_COURSEDATA'		, 'Daten sind nicht plausibel: 2 Der Anfang des Seminars liegt in der Zukunft')
			,array('WRONG_COURSEDATA'		, 'dates implausible: begin > end')
			,array('WRONG_COURSEDATA'		, 'mandatory field missing: study_content')
			,array('WRONG_COURSEDATA'		, 'mandatory field missing: study_type_selection')
			,array('TOO_OLD'				, 'date older than one year')
			,array('TOO_OLD'				, 'liegt vor dem ersten gültigen Meldungsdatum (Sep 1, 2013)')
			,array('NO_RELEASE'				, 'Die Organisation ist nicht berechtigt den Vermittler transferfähig zu machen')
			,array('CREATE_DUPLICATE'		, 'Der Nutzer konnte nicht im ISTS geändert werden. Status Code: 100')
		);
	}*/

	/*public function test_UserId() {
		$this->assertInternalType("integer", $this->error->userId());
	}*/

	/**
     * @dataProvider error_with_user_id_null
   	 * @expectedException LogicException
     */
	/*public function test_noUserId($error) {
		$this->assertNull($error->userId());
	}

	public function test_RowId() {
		$this->assertInternalType("integer", $this->error->rowId());
	}*/

	/**
     * @dataProvider error_with_row_id_null
	 * @expectedException LogicException     
     */
	/*public function test_noRowId($error) {
		$this->assertNull($error->rowId());
	}

	public function test_CrsId() {
		$this->assertInternalType("integer", $this->error->crsId());
	}*/

	/**
     * @dataProvider error_with_crs_id_zero
     */
	/*public function test_zeroCrsId($error) {
		$this->assertEquals(0,$error->crsId());
	}*/
	/**
     * @dataProvider error_with_message
     */
	/*public function test_reason($reason, $error) {
		$err = new gevWBDError($error, 10, 2, 0, 'new_user');
		$this->assertEquals($reason, $err->reason());
	}*/
}