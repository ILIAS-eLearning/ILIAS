<?php


require_once("./Tests/Records/class.arUnitTestRecord.php");
require_once("./Connector/class.arConnectorPdoDB.php");

/**
 * Class ObjectCacheTest
 *
 * @description PHP Unit-Test for ILIAS ActiveRecord
 *
 * @author      Fabian Schmid <fs@studer-raimann.ch>
 * @version     2.0.5
 */
class ObjectCacheTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var pdoDB
	 */
	protected $pdo;
	/**
	 * @var string
	 */
	protected $table_name;


	public function setUp() {
		PHPUnit_Framework_Error_Notice::$enabled = false;
		arUnitTestRecord::installDB();
		$arUnitTestRecord = new arUnitTestRecord();
		//		$this->table_name = arUnitTestRecord::returnDbTableName();
		$this->table_name = $arUnitTestRecord->getConnectorContainerName();
		$this->pdo = arConnectorPdoDB::getConnector();
	}


	public function testTableExistant() {
		$this->assertTrue($this->pdo->tableExists($this->table_name));
	}

	public static function tearDownAfterClass() {
		$tableName = arUnitTestRecord::returnDbTableName();
		$pbo = new pdoDB();
		$pbo->manipulate("DROP TABLE {$tableName}");
	}


	public function chacheTestsOne() {
		echo "!!!!!!!!!!";
		echo "!!!!!!!!!!";
	}
}

?>