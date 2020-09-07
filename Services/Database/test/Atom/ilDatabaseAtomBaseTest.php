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
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 * @version                1.0.0
 *
 * @group                  needsInstalledILIAS
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ilDatabaseAtomBaseTest extends PHPUnit_Framework_TestCase
{

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


    protected function setUp()
    {
        require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        require_once('./Services/Database/classes/Atom/class.ilAtomQueryBase.php');
        require_once('./Services/Database/classes/Atom/class.ilAtomQueryTransaction.php');
        require_once('./Services/Database/classes/Atom/class.ilAtomQueryLock.php');
        require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');

        global $ilClientIniFile;
        $this->ilDBInterfaceGalera = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_GALERA);
        $this->ilDBInterfaceGalera->initFromIniFile($ilClientIniFile);
        $this->ilDBInterfaceGalera->connect();

        $this->ilDBInterfaceInnoDB = ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
        $this->ilDBInterfaceInnoDB->initFromIniFile($ilClientIniFile);
        $this->ilDBInterfaceInnoDB->connect();
    }


    public function testGetInstance()
    {
        $ilAtomQueryTransaction = $this->ilDBInterfaceGalera->buildAtomQuery();
        $this->assertEquals($ilAtomQueryTransaction->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
        $this->assertTrue($ilAtomQueryTransaction instanceof ilAtomQueryTransaction);

        $ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
        $this->assertEquals($ilAtomQuery->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
        $this->assertTrue($ilAtomQuery instanceof ilAtomQueryLock);
    }


    public function testReadUncommited()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        $other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_UNCOMMITED);
        $other->run();
    }


    public function testReadCommited()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        $other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_READ_COMMITED);
        $other->run();
    }


    public function testReadRepeatedRead()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        $other = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, ilAtomQuery::ISOLATION_REPEATED_READ);
        $other->run();
    }


    public function testAnomalies()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_ANO_NOT_AVAILABLE);
        ilAtomQueryTransaction::checkAnomaly('lorem');
    }


    public function testLevel()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        ilAtomQueryTransaction::checkIsolationLevel('lorem');
    }


    public function testRisks()
    {
        $ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
        $ilAtomQuery->addTableLock('object_data');
        $this->assertEquals(array(), $ilAtomQuery->getRisks());
    }


    public function testCallables()
    {
        require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQL.php');
        require_once('./Services/Database/test/Atom/data/class.ilAtomQueryTestHelper.php');

        $ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
        // Working
        $this->assertTrue($ilAtomQuery->checkCallable(function (ilDBInterface $ilDBInterface) {
        })); // ilDBInterface as first Parameter
        $this->assertTrue($ilAtomQuery->checkCallable(new ilAtomQueryTestHelper())); // Class with implemented __invoke

        // Non working
        $this->assertFalse($ilAtomQuery->checkCallable(function () {
        })); // No Parameter
        $this->assertFalse($ilAtomQuery->checkCallable(function (ilDBInterface $ilDBInterface, $someOtherParameter) {
        })); // More than one parameter
        $this->assertFalse($ilAtomQuery->checkCallable(function ($someOtherParameter, ilDBInterface $ilDBInterface) {
        })); // ilDBInterface not first parameter
        $this->assertFalse($ilAtomQuery->checkCallable(function (ilDBPdoMySQL $ilDBInterface) {
        })); // not ilDBInterface
        $this->assertFalse($ilAtomQuery->checkCallable(function ($ilDBInterface) {
        })); // not ilDBInterface
        function noClosure()
        {
        }

        $this->assertFalse($ilAtomQuery->checkCallable('noClosure')); // Not a Closure
    }


    public function testWrongIsolationLevel()
    {
        $this->setExpectedException('ilDatabaseException');
        $ilAtomQuery = new ilAtomQueryTransaction($this->ilDBInterfaceGalera, 'non_existing');
        $ilAtomQuery->addTableLock('il_db_tests_atom');
        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) {
            $ilDB->getDBType();
        });
        $ilAtomQuery->run();
    }


    public function testQueryWithFiveException()
    {
        $counter = 0;
        $max = 5;
        $result = null;
        $query = function (ilDBInterface $ilDBInterface) use (&$counter, &$max, &$result) {
            if ($counter < $max) {
                $counter++;
                throw new ilDatabaseException('', ilDatabaseException::DB_GENERAL);
            }
            $result = $ilDBInterface->listTables();
        };

        $ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
        $ilAtomQuery->addQueryCallable($query);
        $ilAtomQuery->addTableLock('object_data');
        $ilAtomQuery->run();
        $this->assertTrue(is_array($result));
    }


    public function testQueryWithTenException()
    {
        $this->setExpectedException('ilDatabaseException', ilDatabaseException::DB_GENERAL);
        $counter = 0;
        $max = 10;
        $result = null;
        $query = function (ilDBInterface $ilDBInterface) use (&$counter, &$max, &$result) {
            if ($counter < $max) {
                $counter++;
                throw new ilDatabaseException('', ilDatabaseException::DB_GENERAL);
            }
            $result = $ilDBInterface->listTables();
        };

        $ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
        $ilAtomQuery->addQueryCallable($query);
        $ilAtomQuery->addTableLock('object_data');

        $ilAtomQuery->run();

        $this->assertTrue(is_null($result));
    }


    public function testWithOutLocks()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_LOCK_NO_TABLE);
        $ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
        $ilAtomQuery->run();
    }


    public function testWithOutClosures()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_CLOSURE_NONE);
        $ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('object_data');
        $ilAtomQuery->run();
    }


    public function testMultipleClosures()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_CLOSURE_ALREADY_SET);
        $ilAtomQuery = $this->ilDBInterfaceGalera->buildAtomQuery();
        $ilAtomQuery->addTableLock('object_data');
        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDBInterface) {
        });
        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDBInterface) {
        });
    }


    public function testLockSameTable()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_IDENTICAL_TABLES);
        $ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('il_db_tests_atom');
        $ilAtomQuery->addTableLock('il_db_tests_atom');
        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDBInterface) {
        });
        $ilAtomQuery->run();
    }


    public function testLockSameTableWithAlias()
    {
        $this->setExpectedException('ilAtomQueryException', ilAtomQueryException::DB_ATOM_IDENTICAL_TABLES);
        $ilAtomQuery = $this->ilDBInterfaceInnoDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('il_db_tests_atom')->aliasName('alias_one');
        $ilAtomQuery->addTableLock('il_db_tests_atom')->aliasName('alias_one');
        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDBInterface) {
        });
        $ilAtomQuery->run();
    }
}
