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
class ilDatabaseAtomBaseTest extends PHPUnit_Framework_TestCase {

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
		require_once('./Services/Database/classes/Atom/class.ilAtomQueryBase.php');
		require_once('./Services/Database/classes/Atom/class.ilAtomQueryTransaction.php');
		require_once('./Services/Database/classes/Atom/class.ilAtomQueryLock.php');

		global $ilClientIniFile;
		$this->ilDBInterfaceGalera = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_GALERA);
		$this->ilDBInterfaceGalera->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceGalera->connect();

		$this->ilDBInterfaceInnoDB = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
		$this->ilDBInterfaceInnoDB->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceInnoDB->connect();
	}


	public function testGetInstance() {
		$ilAtomQueryTransaction = $this->ilDBInterfaceGalera->buildAtomQuery();
		$this->assertEquals($ilAtomQueryTransaction->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
		$this->assertTrue($ilAtomQueryTransaction instanceof ilAtomQueryTransaction);

		$ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
		$this->assertEquals($ilAtomQuery->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
		$this->assertTrue($ilAtomQuery instanceof ilAtomQueryLock);
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadUncommited() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_UNCOMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadCommited() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_COMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadRepeatedRead() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_REPEATED_READ);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testAnomalies() {
		$this->setExpectedException('ilDatabaseException');
		ilAtomQueryTransaction::checkAnomaly('lorem');
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testLevel() {
		$this->setExpectedException('ilDatabaseException');
		ilAtomQueryTransaction::checkIsolationLevel('lorem');
	}


	public function testRisks() {
		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$this->assertEquals(array(), $ilAtomQuery->getRisks());
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function checkClosure() {
		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
			$ilDB->getDBType();
		});
		$ilAtomQuery->run();
	}


	public function testCallables() {
		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$this->assertFalse($ilAtomQuery->checkCallable(function () { }));
		$this->assertTrue($ilAtomQuery->checkCallable(function (ilDBInterface $ilDBInterface) { }));
		$this->assertFalse($ilAtomQuery->checkCallable(function (ilDBMySQL $ilDBInterface) { }));
		function noClosure() { }
		$this->assertFalse($ilAtomQuery->checkCallable('noClosure'));
		require_once('./Services/Database/test/Atom/data/class.ilAtomQueryTestHelper.php');
		$this->assertTrue($ilAtomQuery->checkCallable(new ilAtomQueryTestHelper()));
	}


	public function testWrongIsolationLevel() {
		$this->setExpectedException('ilDatabaseException');
		$ilAtomQuery = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, 'non_existing');
		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
			$ilDB->getDBType();
		});
		$ilAtomQuery->run();
	}


	public function testQueryWithFiveException() {
		$counter = 0;
		$max = 5;
		$result = null;
		$query = function (ilDBInterface $ilDBInterface) use (&$counter, &$max, &$result) {
			if ($counter < $max) {
				$counter ++;
				throw new ilDatabaseException('Some Random Exception');
			}
			$result = $ilDBInterface->listTables();
		};

		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->addQueryCallable($query);
		$ilAtomQuery->run();

		$this->assertTrue(is_array($result));
	}


	public function testQueryWithTenException() {
		$counter = 0;
		$max = 10;
		$result = null;
		$query = function (ilDBInterface $ilDBInterface) use (&$counter, &$max, &$result) {
			if ($counter < $max) {
				$counter ++;
				throw new ilDatabaseException('Some Random Exception');
			}
			$result = $ilDBInterface->listTables();
		};

		$ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
		$ilAtomQuery->addQueryCallable($query);
		try {
			$ilAtomQuery->run();
		} catch (ilDatabaseException $e) {
		}
		$this->assertEquals($e->getMessage(), 'Some Random Exception');
		$this->assertTrue(is_null($result));
	}
}