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


	protected function setUp() {
		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		require_once('./Services/Database/classes/Atom/class.ilAtomQuery.php');

		global $ilClientIniFile;
		$this->ilDBInterfaceGalera = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_GALERA);
		$this->ilDBInterfaceGalera->initFromIniFile($ilClientIniFile);
		$this->ilDBInterfaceGalera->connect();
	}


	public function testGetInstance() {
		$serializable = new ilAtomQuery($this->ilDBInterfaceGalera);
		$this->assertEquals($serializable->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);

		$serializable = new ilAtomQuery($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_SERIALIZABLE);
		$this->assertEquals($serializable->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadUncommited() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_UNCOMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadCommited() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_COMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadRepeatedRead() {
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_REPEATED_READ);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testAnomalies() {
		$this->setExpectedException('ilDatabaseException');
		ilAtomQuery::checkAnomaly('lorem');
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testLevel() {
		$this->setExpectedException('ilDatabaseException');
		ilAtomQuery::checkIsolationLevel('lorem');
	}

	
	public function testRisks() {
		$ilAtomQuery = new ilAtomQuery($this->ilDBInterfaceGalera);
		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$this->assertEquals(array(), $ilAtomQuery->getRisks());
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function checkCallables() {
		$ilAtomQuery = new ilAtomQuery($this->ilDBInterfaceGalera);
		$ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
			$ilDB->getDBType();
		});
		$ilAtomQuery->run();
	}


	}


		$ilAtomQuery->lockTableWrite('il_db_tests_atom');
		$ilAtomQuery->run();

	}


	}
}