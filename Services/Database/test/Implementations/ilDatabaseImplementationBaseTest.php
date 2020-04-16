<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * TestCase for the ilDatabaseCommonTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
abstract class ilDatabaseImplementationBaseTest extends PHPUnit_Framework_TestCase
{
    const INDEX_NAME = 'i1';
    const TABLE_NAME = 'il_ut_en';
    const CREATE_TABLE_ARRAY_KEY = 'create table';
    /**
     * @var int
     */
    protected $error_reporting_backup = 0;
    /**
     * @var bool
     */
    protected $backupGlobals = false;
    /**
     * @var ilDBPdoMySQL|ilDBPdoMySQLInnoDB|ilDBInnoDB|ilDBMySQL|ilDBPostgreSQL
     */
    protected $db;
    /**
     * @var ilDatabaseCommonTestMockData
     */
    protected $mock;
    /**
     * @var ilDatabaseMySQLTestsDataOutputs|ilDatabasePostgresTestsDataOutputs
     */
    protected $outputs;
    /**
     * @var ilDBInterface
     */
    protected $ildb_backup;
    /**
     * @var string
     */
    protected $type = '';
    /**
     * @var string
     */
    protected $ini_file = '/var/www/ilias/data/trunk/client.ini.php';
    /**
     * @var bool
     */
    protected $set_up = false;


    protected function setUp()
    {
        if ($this->set_up) {
            return;
        }
        //		echo phpversion() . "\n";
        $this->error_reporting_backup = error_reporting();

        PHPUnit_Framework_Error_Notice::$enabled = false;
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        require_once('./libs/composer/vendor/autoload.php');
        if (!defined('DEVMODE')) {
            define('DEVMODE', true);
        }
        require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');
        $this->db = $this->getDBInstance();
        global $DIC, $ilDB;
        $DIC['ilDB'] = $this->db;
        $ilDB = $this->db;
        $this->connect($this->db);

        switch ($this->type) {
            default:
                require_once('./Services/Database/test/Implementations/data/MySQL/class.ilDatabaseMySQLTestMockData.php');
                require_once('./Services/Database/test/Implementations/data/MySQL/class.ilDatabaseMySQLTestsDataOutputs.php');
                $this->mock = new ilDatabaseMySQLTestMockData();
                $this->outputs = new ilDatabaseMySQLTestsDataOutputs();
                break;
            case ilDBConstants::TYPE_POSTGRES:
            case ilDBConstants::TYPE_PDO_POSTGRE:
                require_once('./Services/Database/test/Implementations/data/Postgres/class.ilDatabasePostgresTestMockData.php');
                require_once('./Services/Database/test/Implementations/data/Postgres/class.ilDatabasePostgresTestsDataOutputs.php');
                $this->mock = new ilDatabasePostgresTestMockData();
                $this->outputs = new ilDatabasePostgresTestsDataOutputs();
                break;
        }
        $this->set_up = true;
    }


    /**
     * @return string
     */
    protected function getIniFile()
    {
        return $this->ini_file;
    }


    /**
     * @param \ilDBInterface $ilDBInterface
     * @return bool
     */
    final protected function connect(ilDBInterface $ilDBInterface, $missing_ini = false)
    {
        require_once('./Services/Init/classes/class.ilIniFile.php');
        require_once('./Services/Init/classes/class.ilErrorHandling.php');
        $ilClientIniFile = new ilIniFile($this->getIniFile());
        $ilClientIniFile->read();
        $this->type = $ilClientIniFile->readVariable("db", "type");
        if ($missing_ini) {
            $ilClientIniFile = new ilIniFile('');
        }
        $ilDBInterface->initFromIniFile($ilClientIniFile);
        $return = $ilDBInterface->connect($missing_ini);

        return $return;
    }


    /**
     * @return string
     */
    protected function getTableName()
    {
        return strtolower(self::TABLE_NAME . '_' . $this->db->getDBType());
    }


    protected function tearDown()
    {
        $this->db = null;
        $this->mock = null;
        $this->outputs = null;
        $this->type = null;
        $this->set_up = false;
        error_reporting($this->error_reporting_backup);
    }


    /**
     * @return \ilDBPdoMySQLInnoDB
     * @throws \ilDatabaseException
     */
    abstract protected function getDBInstance();


    /**
     * Test instance implements ilDBInterface and is ilDBInnoDB
     */
    public function testInstance()
    {
        $this->assertTrue($this->db instanceof ilDBInterface);
    }


    public function testConnection()
    {
        $this->assertTrue($this->connect($this->getDBInstance()));
    }


    public function testCompareCreateTableQueries()
    {
        /**
         * @var $manager ilDBPdoManagerPostgres|ilDBPdoManager
         */
        $manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
        $query = $manager->getTableCreationQuery($this->getTableName(), $this->mock->getDBFields(), array());
        $this->assertEquals($this->outputs->getCreationQueryBuildByILIAS($this->getTableName()), $this->normalizeSQL($query));
    }


    /**
     * @depends testConnection
     */
    public function testCreateDatabase()
    {
        $fields = $this->mock->getDBFields();
        $this->db->createTable($this->getTableName(), $fields, true);
        $this->db->addPrimaryKey($this->getTableName(), array( 'id' ));
        $this->assertTrue($this->db->tableExists($this->getTableName()));

        if (in_array($this->type, array( ilDBConstants::TYPE_PDO_POSTGRE, ilDBConstants::TYPE_POSTGRES ))) {
            return; // SHOW CREATE TABLE CURRENTLY NOT SUPPORTED IN POSTGRES
        }

        $res = $this->db->query('SHOW CREATE TABLE ' . $this->getTableName());
        $data = $this->db->fetchAssoc($res);

        $data = array_change_key_case($data, CASE_LOWER);

        $create_table = $this->normalizeSQL($data[self::CREATE_TABLE_ARRAY_KEY]);
        $create_table_mock = $this->normalizeSQL($this->mock->getTableCreateSQL($this->getTableName(), $this->db->getStorageEngine()));

        $this->assertEquals($create_table_mock, $create_table);
    }


    /**
     * @depends testConnection
     */
    public function testInsertNative()
    {
        $values = $this->mock->getInputArray(false, false);
        $id = $values['id'][1];

        // PDO
        $this->db->insert($this->getTableName(), $values);
        $this->db->setLimit(1);
        $res_pdo = $this->db->query("SELECT * FROM " . $this->getTableName() . " WHERE id = $id");
        $data_pdo = $this->db->fetchAssoc($res_pdo);
        $this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_input, $data_pdo);
    }


    public function testQueryUtils()
    {
        $this->assertEquals($this->mock->getLike(), $this->db->like('column', 'text', 22));

        $this->assertEquals($this->mock->getNow(), $this->db->now());

        $this->assertEquals($this->mock->getLocate(), $this->db->locate('needle', 'mystring', 5));

        $this->assertEquals($this->mock->getConcat(false), $this->db->concat(array( 'one', 'two', 'three' ), false));

        $this->assertEquals($this->mock->getConcat(true), $this->db->concat(array( 'one', 'two', 'three' ), true));
    }


    /**
     * @depends testConnection
     */
    public function testUpdateNative()
    {
        // With / without clob
        $with_clob = $this->mock->getInputArray(2222, true);
        $without_clob = $this->mock->getInputArray(2222, true, false);
        $id = $with_clob['id'][1];

        // PDO
        $this->db->update($this->getTableName(), $with_clob, array( 'id' => array( 'integer', $id ) ));
        $this->db->setLimit(1, 0);
        $res_pdo = $this->db->query("SELECT * FROM " . $this->getTableName() . " WHERE id = $id ");
        $data_pdo = $this->db->fetchAssoc($res_pdo);
        $this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_update, $data_pdo);

        $this->db->update($this->getTableName(), $without_clob, array( 'id' => array( 'integer', $id ) ));
        $this->db->setLimit(1, 0);
        $res_pdo = $this->db->query("SELECT * FROM " . $this->getTableName() . " WHERE id = $id ");
        $data_pdo = $this->db->fetchAssoc($res_pdo);
        $this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_update, $data_pdo);
    }


    /**
     * @depends testConnection
     */
    public function testInsertSQL()
    {
        // PDO
        $this->db->manipulate($this->mock->getInsertQuery($this->getTableName()));
        $this->db->setLimit(1, 0);
        $res_pdo = $this->db->query("SELECT * FROM " . $this->getTableName() . " WHERE id = 58");
        $data_pdo = $this->db->fetchObject($res_pdo);

        $this->assertEquals((object) ilDatabaseCommonTestsDataOutputs::$insert_sql_output, $data_pdo);
    }


    /**
     * @throws \ilDatabaseException
     * @depends testConnection
     */
    public function testSelectUsrData()
    {
        $output = (object) ilDatabaseCommonTestsDataOutputs::$select_usr_data_output;

        $query = 'SELECT usr_id, login, is_self_registered FROM usr_data WHERE usr_id = 6';
        // PDO
        $this->db->setLimit(1, 0);
        $result = $this->db->query($query);
        $data = $this->db->fetchObject($result);
        $this->assertEquals($output, $data);

        $result = $this->db->queryF('SELECT usr_id, login, is_self_registered FROM usr_data WHERE usr_id = %s', array( ilDBPdoFieldDefinition::T_INTEGER ), array( 6 ));
        $this->db->setLimit(1, 0);
        $data = $this->db->fetchObject($result);
        $this->assertEquals($output, $data);

        $query = 'SELECT usr_id, login, is_self_registered FROM usr_data WHERE ' . $this->db->in('usr_id', array( 6, 13 ), false, 'integer');
        $this->db->setLimit(2, 0);
        $result = $this->db->query($query);
        $data = $this->db->fetchAll($result);
        foreach (ilDatabaseCommonTestsDataOutputs::$select_usr_data_2_output as $item) {
            $this->assertTrue(in_array($item, $data));
        }

        $this->assertEquals(2, $this->db->numRows($result));
    }


    /**
     * @depends testConnection
     */
    public function testIndices()
    {
        // Add index
        $this->db->addIndex($this->getTableName(), array( 'init_mob_id' ), self::INDEX_NAME);
        $this->assertTrue($this->db->indexExistsByFields($this->getTableName(), array( 'init_mob_id' )));

        // Drop index
        $this->db->dropIndex($this->getTableName(), self::INDEX_NAME);
        $this->assertFalse($this->db->indexExistsByFields($this->getTableName(), array( 'init_mob_id' )));

        // FullText
        $this->db->addIndex($this->getTableName(), array( 'address' ), 'i2', true);
        if ($this->db->supportsFulltext()) {
            $this->assertTrue($this->db->indexExistsByFields($this->getTableName(), array( 'address' )));
        } else {
            $this->assertFalse($this->db->indexExistsByFields($this->getTableName(), array( 'address' )));
        }

        // Drop By Fields
        $this->db->addIndex($this->getTableName(), array( 'elevation' ), 'i3');
        $this->assertTrue($this->db->indexExistsByFields($this->getTableName(), array( 'elevation' )));

        $this->db->dropIndexByFields($this->getTableName(), array( 'elevation' ));
        $this->assertFalse($this->db->indexExistsByFields($this->getTableName(), array( 'elevation' )));
    }


    /**
     * @depends testConnection
     */
    public function testTableColums()
    {
        $this->assertTrue($this->db->tableColumnExists($this->getTableName(), 'init_mob_id'));

        $this->db->addTableColumn($this->getTableName(), "export", array( "type" => "text", "length" => 1024 ));
        $this->assertTrue($this->db->tableColumnExists($this->getTableName(), 'export'));

        $this->db->dropTableColumn($this->getTableName(), "export");
        $this->assertFalse($this->db->tableColumnExists($this->getTableName(), 'export'));
    }


    /**
     * @depends testConnection
     */
    public function testSequences()
    {
        if ($this->db->sequenceExists($this->getTableName())) {
            $this->db->dropSequence($this->getTableName());
        }
        $this->db->createSequence($this->getTableName(), 10);
        $this->assertEquals(10, $this->db->nextId($this->getTableName()));
        $this->assertEquals(11, $this->db->nextId($this->getTableName()));
    }


    /**
     * @depends testConnection
     */
    public function testReverse()
    {
        /**
         * @var $reverse  ilDBPdoReverse
         */
        $reverse = $this->db->loadModule(ilDBConstants::MODULE_REVERSE);

        // getTableFieldDefinition
        $this->assertEquals($this->outputs->getTableFieldDefinition(), $reverse->getTableFieldDefinition($this->getTableName(), 'comment_mob_id'));

        // getTableIndexDefinition
        $this->db->addIndex($this->getTableName(), array( 'init_mob_id' ), self::INDEX_NAME);
        $tableIndexDefinition = $reverse->getTableIndexDefinition($this->getTableName(), $this->db->constraintName($this->getTableName(), self::INDEX_NAME));
        $this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_index_definition_output, $tableIndexDefinition);
        $this->db->dropIndex($this->getTableName(), self::INDEX_NAME);

        // getTableConstraintDefinition
        $this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_constraint_definition_output, $reverse->getTableConstraintDefinition($this->getTableName(), $this->db->constraintName($this->getTableName(), strtolower($this->db->getPrimaryKeyIdentifier()))));
    }


    /**
     * @depends testConnection
     */
    public function testManager()
    {
        /**
         * @var $manager  ilDBPdomanager|ilDBPdoManagerPostgres
         */
        $manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);

        // table fields
        $this->assertEquals($this->outputs->getTableFields(), $manager->listTableFields($this->getTableName()));

        // constraints
        $this->assertEquals($this->outputs->getTableConstraints($this->getTableName()), $manager->listTableConstraints($this->getTableName()));

        // Indices
        $this->db->dropIndexByFields($this->getTableName(), array( 'init_mob_id' ));
        $this->db->addIndex($this->getTableName(), array( 'init_mob_id' ), self::INDEX_NAME);
        $this->assertEquals($this->outputs->getNativeTableIndices($this->getTableName(), $this->db->supportsFulltext()), $manager->listTableIndexes($this->getTableName()));

        // listTables
        $list_tables_output = $this->outputs->getListTables($this->getTableName());
        sort($list_tables_output);
        $list_tables_native = $manager->listTables();
        sort($list_tables_native);
        $this->assertEquals($list_tables_output, $list_tables_native);

        // listSequences
        $table_sequences_output = $this->outputs->getTableSequences($this->getTableName());
        $this->assertTrue(count(array_diff($table_sequences_output, $manager->listSequences())) < 3);
    }


    /**
     * @depends testConnection
     */
    public function testDBAnalyser()
    {
        require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
        $analyzer = new ilDBAnalyzer($this->db);

        // Field info
        //		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$analyzer_field_info, $analyzer_pdo->getFieldInformation($this->getTableName())); // FSX

        // getBestDefinitionAlternative
        $def = $this->db->loadModule(ilDBConstants::MODULE_REVERSE)->getTableFieldDefinition($this->getTableName(), 'comment_mob_id');
        $this->assertEquals(0, $analyzer->getBestDefinitionAlternative($def)); // FSX

        // getAutoIncrementField
        $this->assertEquals(false, $analyzer->getAutoIncrementField($this->getTableName()));

        // getPrimaryKeyInformation
        $this->assertEquals($this->outputs->getPrimaryInfo($this->getTableName()), $analyzer->getPrimaryKeyInformation($this->getTableName()));

        // getIndicesInformation
        if ($this->db->supportsFulltext()) {
            $this->assertEquals($this->outputs->getIndexInfo(true, $this->getTableName()), $analyzer->getIndicesInformation($this->getTableName()));
        } else {
            $this->assertEquals($this->outputs->getIndexInfo(false, $this->getTableName()), $analyzer->getIndicesInformation($this->getTableName()));
        }

        // getConstraintsInformation
        $this->assertEquals(array(), $analyzer->getConstraintsInformation($this->getTableName())); // TODO

        // hasSequence
        $this->assertEquals(59, $analyzer->hasSequence($this->getTableName()));
    }


    /**
     * @depends testConnection
     */
    public function testDropSequence()
    {
        $this->assertTrue($this->db->sequenceExists($this->getTableName()));
        if ($this->db->sequenceExists($this->getTableName())) {
            $this->db->dropSequence($this->getTableName());
        }
        $this->assertFalse($this->db->sequenceExists($this->getTableName()));
    }


    public function testConstraints()
    {
    }


    /**
     * @depends testConnection
     */
    public function testChangeTableName()
    {
        $this->db->dropTable($this->getTableName() . '_a', false);
        $this->db->renameTable($this->getTableName(), $this->getTableName() . '_a');
        $this->assertTrue($this->db->tableExists($this->getTableName() . '_a'));
        $this->db->renameTable($this->getTableName() . '_a', $this->getTableName());
    }


    /**
     * @depends testConnection
     */
    public function testRenameTableColumn()
    {
        $this->changeGlobal($this->db);

        $this->db->renameTableColumn($this->getTableName(), 'comment_mob_id', 'comment_mob_id_altered');
        if (in_array($this->type, array( ilDBConstants::TYPE_PDO_POSTGRE, ilDBConstants::TYPE_POSTGRES ))) {
            return; // SHOW CREATE TABLE CURRENTLY NOT SUPPORTED IN POSTGRES
        }
        $res = $this->db->query('SHOW CREATE TABLE ' . $this->getTableName());
        $data = $this->db->fetchAssoc($res);
        $data = array_change_key_case($data, CASE_LOWER);

        $this->assertEquals($this->normalizeSQL($this->mock->getTableCreateSQLAfterRename($this->getTableName(), $this->db->getStorageEngine(), $this->db->supportsFulltext())), $this->normalizeSQL($data[self::CREATE_TABLE_ARRAY_KEY]));

        $this->changeBack();
    }


    /**
     * @depends testConnection
     */
    public function testModifyTableColumn()
    {
        $changes = array(
            "type" => "text",
            "length" => 250,
            "notnull" => false,
            'fixed' => false,
        );

        $this->changeGlobal($this->db);

        $this->db->modifyTableColumn($this->getTableName(), 'comment_mob_id_altered', $changes);
        if (in_array($this->type, array( ilDBConstants::TYPE_PDO_POSTGRE, ilDBConstants::TYPE_POSTGRES ))) {
            return; // SHOW CREATE TABLE CURRENTLY NOT SUPPORTED IN POSTGRES
        }
        $res = $this->db->query('SHOW CREATE TABLE ' . $this->getTableName());
        $data = $this->db->fetchAssoc($res);

        $data = array_change_key_case($data, CASE_LOWER);

        $this->changeBack();

        $this->assertEquals($this->normalizeSQL($this->mock->getTableCreateSQLAfterAlter($this->getTableName(), $this->db->getStorageEngine(), $this->db->supportsFulltext())), $this->normalizeSQL($data[self::CREATE_TABLE_ARRAY_KEY]));
    }


    /**
     * @depends testConnection
     */
    public function testLockTables()
    {
        $locks = array(
            0 => array( 'name' => 'usr_data', 'type' => ilDBConstants::LOCK_WRITE ),
            //			1 => array( 'name' => 'object_data', 'type' => ilDBConstants::LOCK_READ ),
        );

        $this->db->lockTables($locks);
        $this->db->manipulate('DELETE FROM usr_data WHERE usr_id = -1');
        $this->db->unlockTables();
    }


    /**
     * @depends testConnection
     */
    public function testTransactions()
    {
        // PDO
        //		$this->db->beginTransaction();
        //		$this->db->insert($this->getTableName(), $this->mock->getInputArrayForTransaction());
        //		$this->db->rollback();
        //		$st = $this->db->query('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ' . $this->db->quote(123456, 'integer'));
        //		$this->assertEquals(0, $this->db->numRows($st));
    }


    /**
     * @depends testConnection
     */
    public function testDropTable()
    {
        $this->db->dropTable($this->getTableName());
        $this->assertTrue(!$this->db->tableExists($this->getTableName()));
    }

    //
    // HELPERS
    //

    /**
     * @param \ilDBInterface $ilDBInterface
     */
    protected function changeGlobal(ilDBInterface $ilDBInterface)
    {
        global $ilDB;
        $this->ildb_backup = $ilDB;
        $ilDB = $ilDBInterface;
    }


    protected function changeBack()
    {
        global $ilDB;
        $ilDB = $this->ildb_backup;
    }


    /**
     * @param $sql
     * @return string
     */
    protected function normalizeSQL($sql)
    {
        return preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', " ", preg_replace("/\n/", "", preg_replace("/`/", "", $sql))));
    }


    /**
     * @param $sql
     * @return string
     */
    protected function normalizetableName($sql)
    {
        return preg_replace("/" . $this->getTableName() . "|" . $this->getTableName() . "/", "table_name_replaced", $sql);
    }
}
