<?php
include_once("./Services/Exceptions/classes/class.ilException.php");

/**
 * Class ilObjDataCollectionTest
 * @group needsInstalledILIAS
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjDataCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ilObjDataCollection
     */
    protected $root_object;

    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;
//        PHPUnit_Framework_Error_Warning::$enabled = FALSE;
//        PHPUnit_Framework_Error_Notice::$enabled = FALSE;
        parent::setUp();

        require_once("./Modules/DataCollection/classes/class.ilObjDataCollection.php");

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        $this->root_object = new ilObjDataCollection();
        $this->root_object->setTitle('DataCollection');

        $this->root_object->create();
        $this->root_object->createReference();
        $this->root_object_obj_id = $this->root_object->getId();
        $this->root_object_ref_id = $this->root_object->getRefId();
        $this->root_object->putInTree(ROOT_FOLDER_ID);

        //

        global $DIC;
        $tree = $DIC['tree'];
        $this->tree = $tree;

        global $DIC;
        $objDefinition = $DIC['objDefinition'];
        $this->obj_definition = $objDefinition;
    }

    protected function tearDown()
    {
        if ($this->root_object) {
            $this->root_object->delete();
        }
    }

    /**
     * Test creation of ilObjStudyProgramme
     */
    public function testCreation()
    {
        $this->assertNotEmpty($this->root_object_obj_id);
        $this->assertGreaterThan(0, $this->root_object_obj_id);

        $this->assertNotEmpty($this->root_object_ref_id);
        $this->assertGreaterThan(0, $this->root_object_ref_id);

        $this->assertTrue($this->tree->isInTree($this->root_object_ref_id));
    }

    public function testDefaultTableCreated()
    {
        $tables = $this->root_object->getTables();
        $this->assertEquals(count($tables), 1);
        
        $table = array_shift($tables);
        $this->assertTrue($table instanceof ilDclTable);
        return $table;
    }

    /**
     * @depends testDefaultTableCreated
     */
    public function testDefaultTable(ilDclTable $table)
    {
//        $this->assertEquals($table->getId(), $this->root_object->getMainTableId());
        $this->assertEquals($table->getTitle(), $this->root_object->getTitle());
//        $this->assertEquals($table->getObjId(), $this->root_object_obj_id);
        $this->assertFalse((bool) $table->getPublicCommentsEnabled());
        $this->assertEmpty($table->getRecords());
        $this->assertEmpty($table->getRecordFields());
        $this->assertEquals(count($table->getFields()), count($table->getStandardFields()));

        $this->assertTrue($this->root_object->_hasTableByTitle($this->root_object->getTitle(), $this->root_object_obj_id));
    }

    /**
     * @depends testDefaultTableCreated
     */
    public function testDefaultTableViewCreated(ilDclTable $table)
    {
        $tableviews = $table->getTableViews();
        $this->assertEquals(count($tableviews), 1);

        $tableview = array_shift($tableviews);
        $this->assertTrue($tableview instanceof ilDclTableView);
        return array('table' => $table, 'tableview' => $tableview);
    }

    /**
     * @depends testDefaultTableViewCreated
     */
    public function testDefaultTableView(array $array)
    {
        $this->assertEquals(count(ilDclTableView::getAllForTableId($array['table']->getId())), 1);
        $this->assertEquals($array['tableview']->getTable(), $array['table']);

        $this->assertEquals($array['tableview']->getOrder(), 10);
    }

    /**
     * @depends testDefaultTableViewCreated
     */
    public function testDefaultTableViewFieldSettings(array $array)
    {
        $field_settings = $array['tableview']->getFieldSettings();
//        $this->assertEquals(count($field_settings), count($array['table']->getFields()) - $array['table']->getPublicCommentsEnabled());

        foreach ($array['table']->getFields() as $field) {
            $f_sets = $field->getFieldSettings();
            $this->assertNotEmpty($f_sets);
        }
    }
}
