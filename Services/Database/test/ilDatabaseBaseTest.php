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

// require_once(__DIR__."/mocks.php");

/**
 * TestCase for the ilDatabaseCommonTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
abstract class ilDatabaseBaseTest extends PHPUnit_Framework_TestCase {

	const INDEX_NAME = 'i1';
	const TABLE_NAME = 'il_unittest_common';
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;
	/**
	 * @var ilDBInterface
	 */
	protected $db;
	/**
	 * @var ilDatabaseCommonTestMockData
	 */
	protected $mock;
	/**
	 * @var ilDBInterface
	 */
	protected $ildb_backup;


	protected function setUp() {
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING & ~E_STRICT); // Due to PEAR Lib MDB2

		PHPUnit_Framework_Error_Notice::$enabled = false;
		PHPUnit_Framework_Error_Deprecated::$enabled = false;

		set_include_path("./Services/PEAR/lib" . PATH_SEPARATOR . ini_get('include_path'));

		require_once('./libs/composer/vendor/autoload.php');
		if (!defined('DEVMODE')) {
			define('DEVMODE', true);
		}
		require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');
		require_once('./Services/Database/test/mock_data/class.ilDatabaseCommonTestMockData.php');
		require_once('./Services/Database/test/mock_data/class.ilDatabaseCommonTestsDataOutputs.php');
		$this->mock = new ilDatabaseCommonTestMockData();
		$this->db = self::getDBInstance();
		$this->connect($this->db);
	}


	/**
	 * @param \ilDBInterface $ilDBInterface
	 * @return bool
	 */
	protected function connect(ilDBInterface $ilDBInterface) {
		require_once('./Services/Init/classes/class.ilIniFile.php');
		require_once('./Services/Init/classes/class.ilErrorHandling.php');
		$ilClientIniFile = new ilIniFile('/var/www/ilias/data/trunk/client.ini.php');
		$ilClientIniFile->read();

		$ilDBInterface->initFromIniFile($ilClientIniFile);

		return $ilDBInterface->connect();
	}


	protected function tearDown() {
		// remove objects
	}


	/**
	 * @return \ilDBPdoMySQLInnoDB
	 * @throws \ilDatabaseException
	 */
	protected static function getDBInstance() {
		return ilDBWrapperFactory::getWrapper('pdo-mysql-innodb');
	}


	/**
	 * Test instance implements ilDBInterface and is ilDBInnoDB
	 */
	public function testInstance() {
		$this->assertTrue($this->db instanceof ilDBInterface);
	}


	public function testConnection() {
		$this->assertTrue($this->connect(self::getDBInstance()), true);
	}


	public function testCompareCreateTableQueries() {
		/**
		 * @var $manager MDB2_Driver_Manager_mysqli
		 */
		$manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
		$query = $manager->getTableCreationQuery(self::TABLE_NAME, $this->mock->getDBFields(), array());
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_creation_query_native, $query);
	}


	/**
	 * @depends testConnection
	 */
	public function testCreateDatabase() {
		// PDO
		if ($this->db->tableExists(self::TABLE_NAME)) {
			$this->db->dropTable(self::TABLE_NAME);
		}
		$fields = $this->mock->getDBFields();
		$this->db->createTable(self::TABLE_NAME, $fields);
		$this->db->addPrimaryKey(self::TABLE_NAME, array( 'id' ));
		$this->assertTrue($this->db->tableExists(self::TABLE_NAME));

		$res = $this->db->query('SHOW CREATE TABLE ' . self::TABLE_NAME);
		$data = $this->db->fetchAssoc($res);

		$create_table = $this->normalizeSQL($data['Create Table']);
		$create_table_mock = $this->normalizeSQL($this->mock->getTableCreateSQL(self::TABLE_NAME));

		$this->assertEquals($create_table_mock, $create_table);
	}


	public function testInsertNative() {
		$values = $this->mock->getInputArray(false, false);
		$id = $values['id'][1];

		// PDO
		$this->db->insert(self::TABLE_NAME, $values);
		$res_pdo = $this->db->query("SELECT * FROM " . self::TABLE_NAME . " WHERE id = $id LIMIT 0,1");
		$data_pdo = $this->db->fetchAssoc($res_pdo);
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_input, $data_pdo);
	}


	public function testUpdateNative() {
		// With / without clob
		$with_clob = $this->mock->getInputArray(2222, true);
		$without_clob = $this->mock->getInputArray(2222, true, false);
		$id = $with_clob['id'][1];

		// PDO
		$this->db->update(self::TABLE_NAME, $with_clob, array( 'id' => array( 'integer', $id ) ));
		$res_pdo = $this->db->query("SELECT * FROM " . self::TABLE_NAME . " WHERE id = $id LIMIT 0,1");
		$data_pdo = $this->db->fetchAssoc($res_pdo);
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_update, $data_pdo);

		$this->db->update(self::TABLE_NAME, $without_clob, array( 'id' => array( 'integer', $id ) ));
		$res_pdo = $this->db->query("SELECT * FROM " . self::TABLE_NAME . " WHERE id = $id LIMIT 0,1");
		$data_pdo = $this->db->fetchAssoc($res_pdo);
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$output_after_native_update, $data_pdo);
	}


	public function testInsertSQL() {
		// PDO
		$this->db->manipulate($this->mock->getInsertQuery(self::TABLE_NAME));
		$res_pdo = $this->db->query("SELECT * FROM " . self::TABLE_NAME . " WHERE id = 58 LIMIT 0,1");
		$data_pdo = $this->db->fetchObject($res_pdo);

		$this->assertEquals((object)ilDatabaseCommonTestsDataOutputs::$insert_sql_output, $data_pdo);
	}


	/**
	 * @throws \ilDatabaseException
	 * @depends testConnection
	 */
	public function testSelectUsrData() {
		$output = (object)ilDatabaseCommonTestsDataOutputs::$select_usr_data_output;

		$query = 'SELECT usr_id, login, is_self_registered FROM usr_data WHERE usr_id = 6 LIMIT 0,1';
		// PDO
		$res_pdo = $this->db->query($query);
		$data_pdo = $this->db->fetchObject($res_pdo);
		$this->assertEquals($output, $data_pdo);
	}


	public function testIndices() {
		// Add index
		$this->db->addIndex(self::TABLE_NAME, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->assertTrue($this->db->indexExistsByFields(self::TABLE_NAME, array( 'init_mob_id' )));

		// Drop index
		$this->db->dropIndex(self::TABLE_NAME, self::INDEX_NAME);
		$this->assertFalse($this->db->indexExistsByFields(self::TABLE_NAME, array( 'init_mob_id' )));

		// FullText
		$this->db->addIndex(self::TABLE_NAME, array( 'title' ), 'i2', true);
		$this->assertFalse($this->db->indexExistsByFields(self::TABLE_NAME, array( 'title' )));

		// Drop By Fields
		$this->db->addIndex(self::TABLE_NAME, array( 'elevation' ), 'i3');
		$this->assertTrue($this->db->indexExistsByFields(self::TABLE_NAME, array( 'elevation' )));

		$this->db->dropIndexByFields(self::TABLE_NAME, array( 'elevation' ));
		$this->assertFalse($this->db->indexExistsByFields(self::TABLE_NAME, array( 'elevation' )));
	}


	public function testTableColums() {
		$this->assertTrue($this->db->tableColumnExists(self::TABLE_NAME, 'init_mob_id'));

		$this->db->addTableColumn(self::TABLE_NAME, "export", array( "type" => "text", "length" => 1024 ));
		$this->assertTrue($this->db->tableColumnExists(self::TABLE_NAME, 'export'));

		$this->db->dropTableColumn(self::TABLE_NAME, "export");
		$this->assertFalse($this->db->tableColumnExists(self::TABLE_NAME, 'export'));
	}


	public function testSequences() {
		if ($this->db->sequenceExists(self::TABLE_NAME)) {
			$this->db->dropSequence(self::TABLE_NAME);
		}
		$this->db->createSequence(self::TABLE_NAME, 10);
		$this->assertTrue($this->db->nextId(self::TABLE_NAME) == 10);
		$this->assertTrue($this->db->nextId(self::TABLE_NAME) == 11);
	}


	public function testReverse() {
		/**
		 * @var $reverse_mdb2 MDB2_Driver_Reverse_mysqli
		 * @var $reverse_pdo  ilDBPdoReverse
		 */
		$reverse_pdo = $this->db->loadModule(ilDBConstants::MODULE_REVERSE);

		// getTableFieldDefinition
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_field_definition_output, $reverse_pdo->getTableFieldDefinition(self::TABLE_NAME, 'comment_mob_id'));

		// getTableIndexDefinition
		$this->db->addIndex(self::TABLE_NAME, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_index_definition_output, $reverse_pdo->getTableIndexDefinition(self::TABLE_NAME, self::INDEX_NAME));
		$this->db->dropIndex(self::TABLE_NAME, self::INDEX_NAME);

		// getTableConstraintDefinition
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_constraint_definition_output, $reverse_pdo->getTableConstraintDefinition(self::TABLE_NAME, 'primary'));
	}


	public function testManager() {
		/**
		 * @var $manager_mdb2 MDB2_Driver_Manager_mysqli
		 * @var $manager_pdo  ilDBPdomanager
		 */
		$manager_pdo = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);

		// table fields
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_fields_output, $manager_pdo->listTableFields(self::TABLE_NAME));

		// constraints
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_constraints_output, $manager_pdo->listTableConstraints(self::TABLE_NAME));

		// Indices
		$this->db->addIndex(self::TABLE_NAME, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_indices, $manager_pdo->listTableIndexes(self::TABLE_NAME));

		// listTables
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$list_tables_output, $manager_pdo->listTables());

		// listSequences
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$table_sequences_output, $manager_pdo->listSequences());
	}


	public function testDBAnalyser() {
		require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
		$this->mdb2_innodb = ilDBWrapperFactory::getWrapper('innodb');
		$this->connect($this->mdb2_innodb);

		$analyzer_mdb2 = new ilDBAnalyzer($this->mdb2_innodb);
		$analyzer_pdo = new ilDBAnalyzer($this->db);

		// Field info
		//			$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$analyzer_field_info, $analyzer_pdo->getFieldInformation(self::TABLE_NAME)); FSX

		// getBestDefinitionAlternative
		$def = $this->db->loadModule(ilDBConstants::MODULE_REVERSE)->getTableFieldDefinition(self::TABLE_NAME, 'comment_mob_id');
		$this->assertEquals(0, $analyzer_pdo->getBestDefinitionAlternative($def)); // FSX

		// getAutoIncrementField
		$this->assertEquals(false, $analyzer_pdo->getAutoIncrementField(self::TABLE_NAME));

		// getPrimaryKeyInformation
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$primary_info, $analyzer_pdo->getPrimaryKeyInformation(self::TABLE_NAME)); // TODO

		// getIndicesInformation
		$this->assertEquals(ilDatabaseCommonTestsDataOutputs::$index_info, $analyzer_pdo->getIndicesInformation(self::TABLE_NAME));

		// getConstraintsInformation
		$this->assertEquals(array(), $analyzer_pdo->getConstraintsInformation(self::TABLE_NAME)); // TODO

		// hasSequence
		$this->assertEquals(59, $analyzer_pdo->hasSequence(self::TABLE_NAME));
	}


	public function testDropSequence() {
		$this->assertTrue($this->db->sequenceExists(self::TABLE_NAME));
		if ($this->db->sequenceExists(self::TABLE_NAME)) {
			$this->db->dropSequence(self::TABLE_NAME);
		}
		$this->assertFalse($this->db->sequenceExists(self::TABLE_NAME));
	}


	public function testChangeTableName() {
		$this->db->dropTable(self::TABLE_NAME . '_a', false);
		$this->db->renameTable(self::TABLE_NAME, self::TABLE_NAME . '_a');
		$this->assertTrue($this->db->tableExists(self::TABLE_NAME . '_a'));
		$this->db->renameTable(self::TABLE_NAME . '_a', self::TABLE_NAME);
	}


	public function testRenameTableColumn() {
		$this->changeGlobal($this->db);

		$this->db->renameTableColumn(self::TABLE_NAME, 'comment_mob_id', 'comment_mob_id_altered');
		$res = $this->db->query('SHOW CREATE TABLE ' . self::TABLE_NAME);
		$data_pdo = $this->db->fetchAssoc($res);

		$this->assertEquals($this->normalizeSQL($this->mock->getTableCreateSQLAfterRename(self::TABLE_NAME)), $this->normalizeSQL($data_pdo['Create Table']));

		$this->changeBack();
	}


	public function testModifyTableColumn() {
		$changes = array(
			"type"    => "text",
			"length"  => 250,
			"notnull" => false,
			'fixed'   => false,
		);

		$this->changeGlobal($this->db);

		$this->db->modifyTableColumn(self::TABLE_NAME, 'comment_mob_id_altered', $changes);
		$res = $this->db->query('SHOW CREATE TABLE ' . self::TABLE_NAME);
		$data_pdo = $this->db->fetchAssoc($res);

		$this->changeBack();

		$this->assertEquals($this->normalizeSQL($this->mock->getTableCreateSQLAfterAlter(self::TABLE_NAME)), $this->normalizeSQL($data_pdo['Create Table']));
	}


	public function testLockTables() {
		$locks = array(
			0 => array( 'name' => 'usr_data', 'type' => ilDBConstants::LOCK_WRITE ),
			1 => array( 'name' => 'object_data', 'type' => ilDBConstants::LOCK_READ ),
		);

		$this->db->lockTables($locks);
		$this->db->manipulate('DELETE FROM usr_data WHERE usr_id = -1');
		$this->db->unlockTables();
	}


	public function testTransactions() {

		// PDO
		$this->db->beginTransaction();
		$this->db->insert(self::TABLE_NAME, $this->mock->getInputArrayForTransaction());
		$this->db->rollback();
		$st = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->db->quote(123456, 'integer'));
		$this->assertEquals(0, $this->db->numRows($st));
	}


	public function testDropTable() {
		$this->db->dropTable(self::TABLE_NAME);
		$this->assertTrue(!$this->db->tableExists(self::TABLE_NAME));
	}

	//
	// HELPERS
	//

	/**
	 * @param \ilDBInterface $ilDBInterface
	 */
	protected function changeGlobal(ilDBInterface $ilDBInterface) {
		global $ilDB;
		$this->ildb_backup = $ilDB;
		$ilDB = $ilDBInterface;
	}


	protected function changeBack() {
		global $ilDB;
		$ilDB = $this->ildb_backup;
	}


	/**
	 * @param $sql
	 * @return string
	 */
	protected function normalizeSQL($sql) {
		return preg_replace('/[ \t]+/', ' ', preg_replace('/\s*$^\s*/m', " ", preg_replace("/\n/", "", $sql)));
	}


	/**
	 * @param $sql
	 * @return string
	 */
	protected function normalizetableName($sql) {
		return preg_replace("/" . self::TABLE_NAME . "|" . self::TABLE_NAME . "/", "table_name_replaced", $sql);
	}
}