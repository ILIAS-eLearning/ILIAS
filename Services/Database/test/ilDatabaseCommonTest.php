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
class ilDatabaseCommonTest extends PHPUnit_Framework_TestCase {

	const TABLE_PDO = 'il_unittest_db_pdo';
	const TABLE_MDB2 = 'il_unittest_db_mdb2';
	const INDEX_NAME = 'i1';
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;
	/**
	 * @var ilDBInnoDB
	 */
	protected $mdb2_innodb;
	/**
	 * @var ilDBPdoMySQLInnoDB
	 */
	protected $pdo_innodb;
	/**
	 * @var ilDatabaseCommonTestMockData
	 */
	protected $mock;
	/**
	 * @var ilDBInterface
	 */
	protected $ildb_backup;


	protected function setUp() {
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING); // Due to PEAR Lib MDB2

		PHPUnit_Framework_Error_Notice::$enabled = false;
		PHPUnit_Framework_Error_Deprecated::$enabled = false;

		set_include_path("./Services/PEAR/lib" . PATH_SEPARATOR . ini_get('include_path'));

		require_once('./libs/composer/vendor/autoload.php');
		if (!defined('DEVMODE')) {
			define('DEVMODE', true);
		}
		require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');
		require_once('./Services/Database/test/mock_data/class.ilDatabaseCommonTestMockData.php');
		$this->mock = new ilDatabaseCommonTestMockData();
		$this->mdb2_innodb = self::getMDB2Instance();
		$this->pdo_innodb = self::getPDOInstance();
		$this->connect($this->mdb2_innodb);
		$this->connect($this->pdo_innodb);
	}


	/**
	 * @param \ilDBInterface $ilDBInterface
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
	protected static function getPDOInstance() {
		return ilDBWrapperFactory::getWrapper('pdo-mysql-innodb');
	}


	/**
	 * @return \ilDBInnoDB
	 * @throws \ilDatabaseException
	 */
	protected static function getMDB2Instance() {
		require_once('./Services/Database/classes/MDB2/class.ilDBInnoDB.php');

		return ilDBWrapperFactory::getWrapper('innodb');
	}


	/**
	 * Test instance implements ilDBInterface and is ilDBInnoDB
	 */
	public function testInstance() {
		$this->assertTrue($this->mdb2_innodb instanceof ilDBInterface);
		$this->assertTrue($this->mdb2_innodb instanceof ilDBInnoDB);

		$this->assertTrue($this->pdo_innodb instanceof ilDBInterface);
		$this->assertTrue($this->pdo_innodb instanceof ilDBPdoMySQLInnoDB);
	}


	public function testConnection() {
		$this->assertTrue($this->connect(self::getPDOInstance()));
		$this->assertTrue($this->connect(self::getMDB2Instance()));
	}


	public function testCompareCreateTableQueries() {
		/**
		 * @var $manager_mdb2 MDB2_Driver_Manager_mysqli
		 */
		$manager_mdb2 = $this->mdb2_innodb->loadModule(ilDBConstants::MODULE_MANAGER);
		$mdb2 = $manager_mdb2->getTableCreationQuery(self::TABLE_PDO, $this->mock->getDBFields(), array());
		$pdo = ilMySQLQueryUtils::getInstance($this->pdo_innodb)->createTable(self::TABLE_PDO, $this->mock->getDBFields(), array());

		$this->assertEquals($mdb2, $pdo);
	}


	/**
	 * @depends testConnection
	 */
	public function testCreateDatabase() {
		// MDB2
		if ($this->mdb2_innodb->tableExists(self::TABLE_MDB2)) {
			$this->mdb2_innodb->dropTable(self::TABLE_MDB2);
		}
		$fields = $this->mock->getDBFields();
		$this->mdb2_innodb->createTable(self::TABLE_MDB2, $fields);
		$this->mdb2_innodb->addPrimaryKey(self::TABLE_MDB2, array( 'id' ));
		$this->assertTrue($this->mdb2_innodb->tableExists(self::TABLE_MDB2));

		$res = $this->pdo_innodb->query('SHOW CREATE TABLE ' . self::TABLE_MDB2);
		$data = $this->pdo_innodb->fetchAssoc($res);

		$create_table = $this->normalizeSQL($data['Create Table']);
		$create_table_mock = $this->normalizeSQL($this->mock->getTableCreateSQL(self::TABLE_MDB2));

		$this->assertEquals($create_table_mock, $create_table);

		// PDO
		if ($this->pdo_innodb->tableExists(self::TABLE_PDO)) {
			$this->pdo_innodb->dropTable(self::TABLE_PDO);
		}
		$fields = $this->mock->getDBFields();
		$this->pdo_innodb->createTable(self::TABLE_PDO, $fields);
		$this->pdo_innodb->addPrimaryKey(self::TABLE_PDO, array( 'id' ));
		$this->assertTrue($this->pdo_innodb->tableExists(self::TABLE_PDO));

		$res = $this->pdo_innodb->query('SHOW CREATE TABLE ' . self::TABLE_PDO);
		$data = $this->pdo_innodb->fetchAssoc($res);

		$create_table = $this->normalizeSQL($data['Create Table']);
		$create_table_mock = $this->normalizeSQL($this->mock->getTableCreateSQL(self::TABLE_PDO));

		$this->assertEquals($create_table_mock, $create_table);
	}


	public function testInsertNative() {
		$values = $this->mock->getInputArray();
		$id = $values['id'][1];
		// PDO
		$this->pdo_innodb->insert(self::TABLE_PDO, $values);
		$res_pdo = $this->pdo_innodb->query("SELECT * FROM " . self::TABLE_PDO . " WHERE id = $id LIMIT 0,1");
		$data_pdo = $this->pdo_innodb->fetchObject($res_pdo);

		// MDB2
		$this->mdb2_innodb->insert(self::TABLE_MDB2, $this->mock->getInputArray());
		$res_mdb2 = $this->mdb2_innodb->query("SELECT * FROM " . self::TABLE_MDB2 . " WHERE id = $id LIMIT 0,1");
		$data_mdb2 = $this->mdb2_innodb->fetchObject($res_mdb2);

		$this->assertEquals($data_mdb2, $data_pdo);
	}


	public function testInsertSQL() {
		// PDO
		$this->pdo_innodb->manipulate($this->mock->getInsertQuery(self::TABLE_PDO));
		$res_pdo = $this->pdo_innodb->query("SELECT * FROM " . self::TABLE_PDO . " WHERE id = 58 LIMIT 0,1");
		$data_pdo = $this->pdo_innodb->fetchObject($res_pdo);

		// MDB2
		$this->mdb2_innodb->manipulate($this->mock->getInsertQuery(self::TABLE_MDB2));
		$res_mdb2 = $this->mdb2_innodb->query("SELECT * FROM " . self::TABLE_MDB2 . " WHERE id = 58 LIMIT 0,1");
		$data_mdb2 = $this->mdb2_innodb->fetchObject($res_mdb2);

		$this->assertEquals($data_mdb2, $data_pdo);
	}


	/**
	 * @throws \ilDatabaseException
	 * @depends testConnection
	 */
	public function testSelectUsrData() {
		$query = 'SELECT * FROM usr_data WHERE usr_id = 6 LIMIT 0,1';
		// PDO
		$res_pdo = $this->pdo_innodb->query($query);
		$data_pdo = $this->pdo_innodb->fetchObject($res_pdo);

		// MDB2
		$res_mdb2 = $this->mdb2_innodb->query($query);
		$data_mdb2 = $this->mdb2_innodb->fetchObject($res_mdb2);

		$this->assertEquals($data_mdb2, $data_pdo);
	}


	public function testIndices() {
		$this->mdb2_innodb->addIndex(self::TABLE_MDB2, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->assertTrue($this->mdb2_innodb->indexExistsByFields(self::TABLE_MDB2, array( 'init_mob_id' )));

		$this->pdo_innodb->addIndex(self::TABLE_PDO, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->assertTrue($this->pdo_innodb->indexExistsByFields(self::TABLE_PDO, array( 'init_mob_id' )));

		$this->mdb2_innodb->dropIndex(self::TABLE_MDB2, self::INDEX_NAME);
		$this->assertFalse($this->mdb2_innodb->indexExistsByFields(self::TABLE_MDB2, array( 'init_mob_id' )));

		$this->pdo_innodb->dropIndex(self::TABLE_PDO, self::INDEX_NAME);
		$this->assertFalse($this->pdo_innodb->indexExistsByFields(self::TABLE_PDO, array( 'init_mob_id' )));

		// Add them again for later test cases
		$this->mdb2_innodb->addIndex(self::TABLE_MDB2, array( 'init_mob_id' ), self::INDEX_NAME);
		$this->pdo_innodb->addIndex(self::TABLE_PDO, array( 'init_mob_id' ), self::INDEX_NAME);
	}


	public function testSequences() {
		if ($this->mdb2_innodb->sequenceExists(self::TABLE_MDB2)) {
			$this->mdb2_innodb->dropSequence(self::TABLE_MDB2);
		}
		$this->mdb2_innodb->createSequence(self::TABLE_MDB2, 10);
		$this->assertTrue($this->mdb2_innodb->nextId(self::TABLE_MDB2) == 10);
		$this->assertTrue($this->mdb2_innodb->nextId(self::TABLE_MDB2) == 11);

		if ($this->pdo_innodb->sequenceExists(self::TABLE_PDO)) {
			$this->pdo_innodb->dropSequence(self::TABLE_PDO);
		}
		$this->pdo_innodb->createSequence(self::TABLE_PDO, 10);
		$this->assertTrue($this->pdo_innodb->nextId(self::TABLE_PDO) == 10);
		$this->assertTrue($this->pdo_innodb->nextId(self::TABLE_PDO) == 11);
	}


	public function testReverse() {
		/**
		 * @var $reverse_mdb2 MDB2_Driver_Reverse_mysqli
		 * @var $reverse_pdo  ilDBPdoReverse
		 */
		$reverse_mdb2 = $this->mdb2_innodb->loadModule(ilDBConstants::MODULE_REVERSE);
		$reverse_pdo = $this->pdo_innodb->loadModule(ilDBConstants::MODULE_REVERSE);

		// getTableFieldDefinition
		$this->assertEquals($reverse_mdb2->getTableFieldDefinition(self::TABLE_MDB2, 'comment_mob_id'), $reverse_pdo->getTableFieldDefinition(self::TABLE_PDO, 'comment_mob_id'));
		// getTableIndexDefinition
		$this->assertEquals($reverse_mdb2->getTableIndexDefinition(self::TABLE_MDB2, self::INDEX_NAME), $reverse_pdo->getTableIndexDefinition(self::TABLE_PDO, self::INDEX_NAME));
		// getTableConstraintDefinition
		$this->assertEquals($reverse_mdb2->getTableConstraintDefinition(self::TABLE_MDB2, 'primary'), $reverse_pdo->getTableConstraintDefinition(self::TABLE_PDO, 'primary'));
	}


	public function testManager() {
		/**
		 * @var $manager_mdb2 MDB2_Driver_Manager_mysqli
		 * @var $manager_pdo  ilDBPdomanager
		 */
		$manager_mdb2 = $this->mdb2_innodb->loadModule(ilDBConstants::MODULE_MANAGER);
		$manager_pdo = $this->pdo_innodb->loadModule(ilDBConstants::MODULE_MANAGER);

		// table fields
		$this->assertEquals($manager_mdb2->listTableFields(self::TABLE_MDB2), $manager_pdo->listTableFields(self::TABLE_PDO));

		// constraints
		$this->assertEquals($manager_mdb2->listTableConstraints(self::TABLE_MDB2), $manager_pdo->listTableConstraints(self::TABLE_PDO));

		// Indices (uses indices created in testIndices)
		$this->assertEquals($manager_mdb2->listTableIndexes(self::TABLE_MDB2), $manager_pdo->listTableIndexes(self::TABLE_PDO));

		// listTables
		$this->assertEquals(sort($manager_mdb2->listTables()), sort($manager_pdo->listTables()));

		// listSequences
		$this->assertEquals($manager_mdb2->listSequences(), $manager_pdo->listSequences());
		// createSequence
	}


	public function testDBAnalyser() {
		require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
		$analyzer_mdb2 = new ilDBAnalyzer($this->mdb2_innodb);
		$analyzer_pdo = new ilDBAnalyzer($this->pdo_innodb);

		// Field info
		$this->assertEquals($analyzer_mdb2->getFieldInformation(self::TABLE_MDB2), $analyzer_pdo->getFieldInformation(self::TABLE_PDO));

		// getBestDefinitionAlternative
		$def = $this->mdb2_innodb->loadModule(ilDBConstants::MODULE_REVERSE)->getTableFieldDefinition(self::TABLE_PDO, 'comment_mob_id');
		$this->assertEquals($analyzer_mdb2->getBestDefinitionAlternative($def), $analyzer_pdo->getBestDefinitionAlternative($def));

		// getAutoIncrementField
		$this->assertEquals($analyzer_mdb2->getAutoIncrementField(self::TABLE_MDB2), $analyzer_pdo->getAutoIncrementField(self::TABLE_PDO));

		// getPrimaryKeyInformation
		//		$analyzer_mdb2->getPrimaryKeyInformation(self::TABLE_MDB2); // FSX
		//		$analyzer_pdo->getPrimaryKeyInformation(self::TABLE_PDO); // FSX
		$this->assertEquals($analyzer_mdb2->getPrimaryKeyInformation(self::TABLE_MDB2), $analyzer_pdo->getPrimaryKeyInformation(self::TABLE_PDO)); // TODO

		// getIndicesInformation
		$this->assertEquals($analyzer_mdb2->getIndicesInformation(self::TABLE_MDB2), $analyzer_pdo->getIndicesInformation(self::TABLE_PDO));

		// getConstraintsInformation
		$this->assertEquals($analyzer_mdb2->getConstraintsInformation(self::TABLE_MDB2), $analyzer_pdo->getConstraintsInformation(self::TABLE_PDO)); // TODO

		// hasSequence
		$this->assertEquals($analyzer_mdb2->hasSequence(self::TABLE_MDB2), $analyzer_pdo->hasSequence(self::TABLE_PDO));
	}


	public function testDropSequence() {
		$this->assertTrue($this->pdo_innodb->sequenceExists(self::TABLE_PDO));
		if ($this->pdo_innodb->sequenceExists(self::TABLE_PDO)) {
			$this->pdo_innodb->dropSequence(self::TABLE_PDO);
		}
		$this->assertFalse($this->pdo_innodb->sequenceExists(self::TABLE_PDO));

		$this->assertTrue($this->mdb2_innodb->sequenceExists(self::TABLE_MDB2));
		if ($this->mdb2_innodb->sequenceExists(self::TABLE_MDB2)) {
			$this->mdb2_innodb->dropSequence(self::TABLE_MDB2);
		}
		$this->assertFalse($this->mdb2_innodb->sequenceExists(self::TABLE_MDB2));
	}


	public function testChangeTableName() {
		$this->mdb2_innodb->dropTable(self::TABLE_MDB2 . '_a', false);
		$this->mdb2_innodb->renameTable(self::TABLE_MDB2, self::TABLE_MDB2 . '_a');
		$this->assertTrue($this->mdb2_innodb->tableExists(self::TABLE_MDB2 . '_a'));
		$this->mdb2_innodb->renameTable(self::TABLE_MDB2 . '_a', self::TABLE_MDB2);

		$this->pdo_innodb->dropTable(self::TABLE_PDO . '_a', false);
		$this->pdo_innodb->renameTable(self::TABLE_PDO, self::TABLE_PDO . '_a');
		$this->assertTrue($this->pdo_innodb->tableExists(self::TABLE_PDO . '_a'));
		$this->pdo_innodb->renameTable(self::TABLE_PDO . '_a', self::TABLE_PDO);
	}


	public function testRenameTableColumn() {
		$this->changeGlobal($this->mdb2_innodb);
		$this->mdb2_innodb->renameTableColumn(self::TABLE_MDB2, 'comment_mob_id', 'comment_mob_id_altered');

		$res = $this->mdb2_innodb->query('SHOW CREATE TABLE ' . self::TABLE_MDB2);
		$data_mdb2 = $this->mdb2_innodb->fetchAssoc($res);

		$this->changeBack();

		$this->pdo_innodb->renameTableColumn(self::TABLE_PDO, 'comment_mob_id', 'comment_mob_id_altered');

		$res = $this->pdo_innodb->query('SHOW CREATE TABLE ' . self::TABLE_PDO);
		$data_pdo = $this->pdo_innodb->fetchAssoc($res);

		$this->assertEquals($this->normalizetableName($data_mdb2['create table']), $this->normalizetableName($data_pdo['Create Table']));
	}


	public function testModifyTableColumn() {
		$changes = array(
			"type"    => "text",
			"length"  => 250,
			"notnull" => false,
			'fixed'   => false,
		);

		$this->changeGlobal($this->mdb2_innodb);
		$this->mdb2_innodb->modifyTableColumn(self::TABLE_MDB2, 'comment_mob_id_altered', $changes);
		$res = $this->mdb2_innodb->query('SHOW CREATE TABLE ' . self::TABLE_MDB2);
		$data_mdb2 = $this->mdb2_innodb->fetchAssoc($res);
		$this->changeBack();

		$this->pdo_innodb->modifyTableColumn(self::TABLE_PDO, 'comment_mob_id_altered', $changes);
		$res = $this->pdo_innodb->query('SHOW CREATE TABLE ' . self::TABLE_PDO);
		$data_pdo = $this->pdo_innodb->fetchAssoc($res);

		$this->assertEquals($this->normalizetableName($data_mdb2['create table']), $this->normalizetableName($data_pdo['Create Table']));
	}


	public function testLockTables() {
		$locks = array(
			0 => array( 'name' => 'usr_data', 'type' => ilDBConstants::LOCK_WRITE ),
		);
		$this->mdb2_innodb->lockTables($locks);
		$this->mdb2_innodb->manipulate('DELETE FROM usr_data WHERE usr_id = -1');
		$this->mdb2_innodb->unlockTables();

		$this->pdo_innodb->lockTables($locks);
		$this->pdo_innodb->manipulate('DELETE FROM usr_data WHERE usr_id = -1');
		$this->pdo_innodb->unlockTables();
	}


	public function testDropTable() {
		// PDO
		$this->pdo_innodb->dropTable(self::TABLE_PDO);
		$this->assertTrue(!$this->pdo_innodb->tableExists(self::TABLE_PDO));

		// MDB2
		$this->pdo_innodb->dropTable(self::TABLE_MDB2);
		$this->assertTrue(!$this->pdo_innodb->tableExists(self::TABLE_MDB2));
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
		return preg_replace("/" . self::TABLE_PDO . "|" . self::TABLE_MDB2 . "/", "table_name_replaced", $sql);
	}
}