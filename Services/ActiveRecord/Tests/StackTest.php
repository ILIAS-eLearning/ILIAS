<?php
require_once("./Tests/Records/class.arUnitTestRecord.php");
require_once("./Connector/class.arConnectorPdoDB.php");

/**
 * Class StackTest
 *
 * @description PHP Unit-Test for ILIAS ActiveRecord
 *
 * @author      Oskar truffer <ot@studer-raimann.ch>
 * @version     2.0.5
 */
class StackTest extends PHPUnit_Framework_TestCase {

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


	public function testCreation() {
		$entry = new arUnitTestRecord();
		$entry->setDescription("Description");
		$entry->setTitle("Title");
		$entry->setId(1);
		$entry->setUsrIds(array( 1, 5, 9 ));
		$entry->create();

		$statement = $this->pdo->query("SELECT * FROM $this->table_name");
		$row = $this->pdo->fetchAssoc($statement);
		$statement->closeCursor();

		$this->assertEquals($row["id"], 1);
		$this->assertEquals($row["title"], "Title");
		$this->assertEquals($row["description"], "Description");
		$this->assertEquals($row["usr_ids"], "[1,5,9]");

		$entry = new arUnitTestRecord();
		$entry->setDescription("Fscription");
		$entry->setTitle("Title 2");
		$entry->setId(2);
		$entry->setUsrIds(array( 10, 5, 3 ));
		$entry->create();

		$entry = new arUnitTestRecord();
		$entry->setDescription("Eescription");
		$entry->setTitle("Title 3");
		$entry->setId(3);
		$entry->setUsrIds(array( 100, 2, 7 ));
		$entry->create();
	}


	public function testFind() {
		$entry = new arUnitTestRecord(1);
		$this->assertEquals($entry->getTitle(), "Title");

		$entry = new arUnitTestRecord(2);
		$this->assertEquals($entry->getTitle(), "Title 2");

		/** @var arUnitTestRecord $entry */
		$entry = arUnitTestRecord::find(3);
		$this->assertEquals($entry->getTitle(), "Title 3");
	}


	public function testFindOrGetInstance() {
		$entry = arUnitTestRecord::findOrGetInstance(1);
		$this->assertEquals($entry->getTitle(), "Title");

		$entry = arUnitTestRecord::findOrGetInstance(1337);
		$this->assertTrue($entry instanceof arUnitTestRecord);
		$this->assertEquals($entry->getId(), 1337);
	}


	/**
	 * @expectedException arException
	 */
	public function testBehaviourOnInvalidId() {
		$entry = arUnitTestRecord::find(80085);
		$this->assertEquals($entry, NULL);

		$entry = new arUnitTestRecord(80085);
	}


	public function testWhere() {
		$entry = arUnitTestRecord::where(array( "title" => "Title" ));
		/** @var arUnitTestRecord $element */
		$element = $entry->first();
		$this->assertEquals($element->getId(), 1);

		$query = arUnitTestRecord::where(array( "id" => array( 1, 3 ) ));
		$array = $query->getArray();
		$this->assertEquals(count($array), 2);
		$first = array_shift($array);
		$second = array_shift($array);
		$this->assertEquals($first["id"], 1);
		$this->assertEquals($second["id"], 3);
	}


	public function testLimitAndOrder() {
		$list = arUnitTestRecord::limit(0, 2)->orderBy("description", "DESC");
		$array = $list->get();
		$first = array_shift($array);
		$second = array_shift($array);
		$this->assertTrue($first->getId() == 2);
		$this->assertTrue($second->getId() == 3);

		$list = arUnitTestRecord::orderBy("description", "DESC")->orderBy("id", "ASC");
		$array = $list->get();
		$this->assertEquals(count($array), 3);
		$first = array_shift($array);
		$second = array_shift($array);
		$third = array_shift($array);
		$this->assertEquals($first->getId(), 2);
		$this->assertEquals($second->getId(), 3);
		$this->assertEquals($third->getId(), 1);
	}

	//TODO joins.

	//TODO mehr active records und list funktionen.

	public function testMoreListFuntionality() {
		$entry = arUnitTestRecord::where(array( "title" => "Title" ), '!=')->limit(0,100)->orderBy('title', 'DESC')->where('id != 1')->count();
		$this->assertEquals($entry, 2);

		$arUnitTestRecord8 = arUnitTestRecord::findOrGetInstance(8);
		$this->assertTrue($arUnitTestRecord8 instanceof arUnitTestRecord);

		$arUnitTestRecord8_fromCache = arUnitTestRecord::find(8);
		$this->assertEquals($arUnitTestRecord8_fromCache, NULL);

		$arUnitTestRecordInstance = arFactory::getInstance('arUnitTestRecord');
		$this->assertEquals($arUnitTestRecordInstance->getId(), 0);

		$arUnitTestRecord1_from_arObjectCache = arObjectCache::get('arUnitTestRecord', 2);
		$this->assertEquals($arUnitTestRecord1_from_arObjectCache->getId(), 2);

		$arUnitTestRecord8_fromCache = arUnitTestRecord::find(2);
		$this->assertEquals($arUnitTestRecord8_fromCache->getId(), 2);
		$this->assertTrue(arObjectCache::isCached('arUnitTestRecord', 2));
		arObjectCache::purge($arUnitTestRecord1_from_arObjectCache);
		$this->assertFalse(arObjectCache::isCached('arUnitTestRecord', 2));
		$arUnitTestRecord8_fromCache = arUnitTestRecord::find(2);
		$this->assertEquals($arUnitTestRecord8_fromCache->getId(), 2);


		$arUnitTestRecord6 = new arUnitTestRecord();
		$arUnitTestRecord6->setId(16);
		$arUnitTestRecord6->setTitle('Title 16');
		$arUnitTestRecord6->create();

		$this->assertTrue(arObjectCache::isCached('arUnitTestRecord', 16));
		$arUnitTestRecord6->delete();
		$this->assertFalse(arObjectCache::isCached('arUnitTestRecord', 16));
		$this->assertEquals(arUnitTestRecord::find(16), NULL);
	}

	public function testMoreActiveRecordFunctionality() {
		$entry = arUnitTestRecord::find(1);
		$csv = $entry->__asCsv(";", true);
		$this->assertEquals($csv, "id;title;description;usr_ids\n1;Title;Description;[1,5,9]");
		$array = $entry->__asArray();
		$array2 = array(
			'id' => 1,
			'title' => 'Title',
			'description' => 'Description',
			'usr_ids' => array(
				1,
				5,
				9
			)
		);
		$this->assertEquals($array, $array2);

		/** @var arUnitTestRecord $copy */
		$copy = $entry->copy(5050);
		$this->assertEquals($copy->getTitle(), "Title");
		$this->assertEquals($copy->getId(), 5050);
	}


	public function testStoreUpdateAndDelete() {
		$entry = new arUnitTestRecord();
		$entry->setTitle("StoredEntry");
		$entry->setDescription("Testi");
		$entry->store();

		$statement = $this->pdo->query("SELECT * FROM $this->table_name WHERE title = 'StoredEntry'");
		$row = $this->pdo->fetchAssoc($statement);
		$statement->closeCursor();
		$this->assertEquals($row["description"], "Testi");

		$entry->setDescription("Testi2");
		$entry->update();
		$statement = $this->pdo->query("SELECT * FROM $this->table_name WHERE title = 'StoredEntry'");
		$row = $this->pdo->fetchAssoc($statement);
		$statement->closeCursor();
		$this->assertEquals($row["description"], "Testi2");

		$entry->delete();
		$statement = $this->pdo->query("SELECT * FROM $this->table_name WHERE title = 'StoredEntry'");
		$count = $this->pdo->numRows($statement);
		$statement->closeCursor();
		$this->assertEquals($count, 0);
	}


	public function testAffectedRows() {
		$affectedRows = arUnitTestRecord::where("TRUE")->count();
		$this->assertEquals(3, $affectedRows);
	}


	/**
	 * @expectedException arException
	 */
	public function testCopyToWrongLocation() {
		$entry = arUnitTestRecord::find(1);
		$copy = $entry->copy(1);
	}


	public static function tearDownAfterClass() {
		$tableName = arUnitTestRecord::returnDbTableName();
		$pbo = new pdoDB();
		$pbo->manipulate("DROP TABLE {$tableName}");
	}
}

?>
