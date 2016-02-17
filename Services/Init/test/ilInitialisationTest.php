<?php
/**
 * TestCase for the ilContext
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilInitialisationTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	* @dataProvider globalsProvider
	*/
	public function test_DIC($global_name, $class_name) {
		global $DIC;

		$this->assertInstanceOf($class_name, $GLOBALS[$global_name]);
		$this->assertInstanceOf($class_name, $DIC[$global_name]);
		$this->assertSame($GLOBALS[$global_name], $DIC[$global_name]);
	}

	public function globalsProvider() {
		// Add combinations of globals and their classes here...
		return array
			( array("ilIliasIniFile", "ilIniFile")
			, array("ilCtrl", "ilCtrl")
			, array("tree", "ilTree")
			, array("ilLog", "ilLogger")
			, array("ilDB", "ilDB")
			);
	}
}
