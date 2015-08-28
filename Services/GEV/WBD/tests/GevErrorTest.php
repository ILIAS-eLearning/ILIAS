<?php
chdir("/Library/WebServer/Documents/dev/4_4_generali2_new_wbd/");
require_once("Services/GEV/WBD/classes/Error/class.gevWBDError.php");
class GevWBDErrorTest extends ErrorTestBase {
	public function setUp() {
		$this->error = new gevWBDError("Die ist ein Fehler",3251,124,0);
	}

	public function error_with_message_null() {
		return array(array(new gevWBDError(null,1,2,3))
					 ,array(new gevWBDError(null,1,2,3))
					 ,array(new gevWBDError(null,1,2,3))
					 ,array(new gevWBDError(null,1,2,3))
					);
	}

	public function error_with_user_id_null() {
		return array(array(new gevWBDError("lala",null,2,3))
					 ,array(new gevWBDError("lulu",null,2,3))
					 ,array(new gevWBDError("lele",null,2,3))
					 ,array(new gevWBDError("lili",null,2,3))
					);
	}

	public function error_with_row_id_null() {
		return array(array(new gevWBDError("lala",1,null,3))
					 ,array(new gevWBDError("lulu",1,null,3))
					 ,array(new gevWBDError("lele",1,null,3))
					 ,array(new gevWBDError("lili",1,null,3))
					);
	}

	public function error_with_crs_id_null() {
		return array(array(new gevWBDError("lala",1,2,null))
					 ,array(new gevWBDError("lulu",1,2,null))
					 ,array(new gevWBDError("lele",1,2,null))
					 ,array(new gevWBDError("lili",1,2,null))
					);
	}

	public function error_with_crs_id_zero() {
		return array(array(new gevWBDError("lala",1,2,0))
					 ,array(new gevWBDError("lulu",1,2,0))
					 ,array(new gevWBDError("lele",1,2,0))
					 ,array(new gevWBDError("lili",1,2,0))
					);
	}

	public function test_UserId() {
		$this->assertInternalType("integer", $this->error->userId());
	}

	/**
     * @dataProvider error_with_user_id_null
   	 * @expectedException LogicException
     */
	public function test_noUserId($error) {
		$this->assertNull($error->userId());
	}

	public function test_RowId() {
		$this->assertInternalType("integer", $this->error->rowId());
	}

	/**
     * @dataProvider error_with_row_id_null
	 * @expectedException LogicException     
     */
	public function test_noRowId($error) {
		$this->assertNull($error->rowId());
	}

	public function test_CrsId() {
		$this->assertInternalType("integer", $this->error->crsId());
	}

	/**
     * @dataProvider error_with_crs_id_zero
     */
	public function test_zeroCrsId($error) {
		$this->assertEquals(0,$error->crsId());
	}
}