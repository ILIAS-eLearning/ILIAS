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
 * TestCase for the ilDatabaseAtomBaseTest
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDatabaseAtomRunTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var bool
	 */
	protected $backupGlobals = false;
	/**
	 * @var ilDBInterface
	 */
	protected $ilDBInterfaceGalera;
	/**
	 * @var ilDBInterface
	 */
	protected $ilDBInterfaceGaleraSecond;
	/**
	 * @var ilDBInterface
	 */
	protected $ilDBInterfaceInnoDB;


	protected function setUp() {
		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();

		global $ilClientIniFile;
		$this->ilDBInterfaceGalera = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_GALERA);
		$this->ilDBInterfaceGalera->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceGalera->connect();

		$this->ilDBInterfaceGaleraSecond = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_GALERA);
		$this->ilDBInterfaceGaleraSecond->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceGaleraSecond->connect();

		$this->ilDBInterfaceInnoDB = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
		$this->ilDBInterfaceInnoDB->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceInnoDB->connect();

		$this->setupTable();
	}


	public function testConnection() {
		$this->assertTrue($this->ilDBInterfaceGalera->connect(true));
		$this->assertTrue($this->ilDBInterfaceGaleraSecond->connect(true));
		$this->assertTrue($this->ilDBInterfaceInnoDB->connect(true));
	}


	/**
	 * @depends testConnection
	 */
	public function setupTable() {
		if ($this->ilDBInterfaceGalera->sequenceExists('il_db_tests_atom')) {
			$this->ilDBInterfaceGalera->dropSequence('il_db_tests_atom');
		}
		$this->ilDBInterfaceGalera->createTable('il_db_tests_atom', $fields = array(
			'id'        => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
			),
			'is_online' => array(
				'type'    => 'integer',
				'length'  => 1,
				'notnull' => false,
			),
		), true);
		$this->ilDBInterfaceGalera->addPrimaryKey('il_db_tests_atom', array( 'id' ));
		$this->ilDBInterfaceGalera->createSequence('il_db_tests_atom');
	}


	public function testTableExists() {
		$this->assertTrue($this->ilDBInterfaceGalera->tableExists('il_db_tests_atom'));
	}


	/**
	 * @depends testConnection
	 */
	public function testWriteAtomOne() {
		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$query = $this->getInsertQueryCallable();
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->run();
		
		$this->assertEquals($this->getExpectedresult(), $this->getResultFromDB());
	}


	/**
	 * @depends testConnection
	 */
	public function testWriteWithTransactions() {
		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->addTable('il_db_tests_atom', ilAtomQuery::LOCK_WRITE);
		$query = $this->getInsertQueryCallable();
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->addQueryCallable($query);

		$ilAtomQuery->run();

		$this->assertEquals($this->getExpectedresult(), $this->getResultFromDB());
	}


	/**
	 * @depends testConnection
	 */
	public function testWriteWithLocks() {
		$ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
		$ilAtomQuery->addTable('il_db_tests_atom', ilAtomQuery::LOCK_WRITE, true);
		$query = $this->getInsertQueryCallable();
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->addQueryCallable($query);

		$ilAtomQuery->run();

		$this->assertEquals($this->getExpectedresult(), $this->getResultFromDB());
	}
	
	/**
	 * @depends testConnection
	 */
	public function testUpdateDuringTransaction() {
		$this->ilDBInterfaceGalera->insert('il_db_tests_atom', array(
			'id'        => array( 'integer', $this->ilDBInterfaceGalera->nextId('il_db_tests_atom') ),
			'is_online' => array( 'integer', 1 ),
		));
		// Start a Transaction with one instance and update the same entry as another instance
		$this->ilDBInterfaceGalera->beginTransaction();
		$this->ilDBInterfaceGalera->update('il_db_tests_atom', array(
			'is_online' => array( 'integer', 5 ),
		), array( 'id' => array( 'integer', 1 ) ));
		// Update the same entry with another instance
		$this->ilDBInterfaceGaleraSecond->update('il_db_tests_atom', array(
			'is_online' => array( 'integer', 6 ),
		), array( 'id' => array( 'integer', 1 ) ));

		$this->ilDBInterfaceGalera->commit();
	}

	//
	// HELPERS
	//

	/**
	 * @return \Closure
	 */
	protected function getInsertQueryCallable() {
		$query = function (ilDBInterface $ilDB) {
			$ilDB->insert('il_db_tests_atom', array(
				'id'        => array( 'integer', $ilDB->nextId('il_db_tests_atom') ),
				'is_online' => array( 'integer', 1 ),
			));
		};

		return $query;
	}


	/**
	 * @return array
	 */
	protected function gettableLocksForDbInterface() {
		$tables = array(
			array(
				'name'     => 'il_db_tests_atom',
				'type'     => ilAtomQuery::LOCK_WRITE,
				'sequence' => true,
			),
		);

		return $tables;
	}


	/**
	 * @return array
	 */
	protected function getResultFromDB() {
		$res = $this->ilDBInterfaceGalera->query('SELECT * FROM il_db_tests_atom');
		$results = array();
		while ($d = $this->ilDBInterfaceGalera->fetchAssoc($res)) {
			$results[] = $d;
		}

		return $results;
	}


	/**
	 * @return array
	 */
	protected function getExpectedresult() {
		return array(
			0 => array(
				'id'        => '1',
				'is_online' => '1',
			),
			1 => array(
				'id'        => '2',
				'is_online' => '1',
			),
		);
	}
}