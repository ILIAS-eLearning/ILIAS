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
	protected $ilDBInterfaceInnoDB;


	protected function setUp() {
		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		require_once('./Services/Database/classes/Atom/class.ilAtomQuery.php');

		global $ilClientIniFile;
		$this->ilDBInterfaceGalera = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_GALERA);
		$this->ilDBInterfaceGalera->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceGalera->connect();

		$this->ilDBInterfaceInnoDB = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
		$this->ilDBInterfaceInnoDB->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceInnoDB->connect();
	}


	public function testConnection() {
		$this->assertTrue($this->ilDBInterfaceGalera->connect(true));
		$this->assertTrue($this->ilDBInterfaceInnoDB->connect(true));
	}


	/**
	 * @depends testConnection
	 */
	public function testCreateTable() {
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
		$this->assertTrue($this->ilDBInterfaceGalera->tableExists('il_db_tests_atom'));
	}


	/**
	 * @depends testConnection
	 */
	public function testWriteAtomOne() {
		$ilAtomQuery = new ilAtomQuery($this->ilDBInterfaceGalera);
		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$query = function (ilDBInterface $ilDB) {
			$ilDB->insert('il_db_tests_atom', array(
				'id'        => array( 'integer', $ilDB->nextId('il_db_tests_atom') ),
				'is_online' => array( 'integer', 1 ),
			));
		};
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->run();

		$res = $this->ilDBInterfaceGalera->query('SELECT * FROM il_db_tests_atom');
		$results = array();
		while ($d = $this->ilDBInterfaceGalera->fetchAssoc($res)) {
			$results[] = $d;
		}
		$this->assertEquals(array(
			0 => array(
				'id'        => '1',
				'is_online' => '1',
			),
			1 => array(
				'id'        => '2',
				'is_online' => '1',
			),
		), $results);
	}


	/**
	 * @depends testConnection
	 */
	public function testWriteAtomParallel() {
		$ilAtomQueryOne = new ilAtomQuery($this->ilDBInterfaceGalera);
		$ilAtomQueryOne->addTable('il_db_tests_atom', ilAtomQuery::LOCK_WRITE);
		$query = function (ilDBInterface $ilDB) {
			$ilDB->insert('il_db_tests_atom', array(
				'id'        => array( 'integer', $ilDB->nextId('il_db_tests_atom') ),
				'is_online' => array( 'integer', 1 ),
			));
		};
		$ilAtomQueryOne->addQueryCallable($query);
		$ilAtomQueryOne->addQueryCallable($query);

		// Run both
		$ilAtomQueryOne->run();
	}

	/**
	 * @depends testConnection
	 */
	public function testWriteWithLocks() {
		$ilAtomQueryOne = new ilAtomQuery($this->ilDBInterfaceInnoDB);
		$ilAtomQueryOne->addTable('il_db_tests_atom', ilAtomQuery::LOCK_WRITE, true);
		$query = function (ilDBInterface $ilDB) {
			$ilDB->insert('il_db_tests_atom', array(
				'id'        => array( 'integer', $ilDB->nextId('il_db_tests_atom') ),
				'is_online' => array( 'integer', 1 ),
			));
		};
		$ilAtomQueryOne->addQueryCallable($query);
		$ilAtomQueryOne->addQueryCallable($query);

		// Run both
		$ilAtomQueryOne->run();
	}
}