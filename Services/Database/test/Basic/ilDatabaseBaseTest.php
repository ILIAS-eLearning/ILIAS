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
 * @group needsInstalledILIAS
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDatabaseBaseTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ilDBPdoMySQLInnoDB
     */
    protected $db;
    /**
     * @var ilDatabaseCommonTestMockData
     */
    /**
     * @var string
     */
    protected $ini_file = '/var/www/ilias/data/trunk/client.ini.php';
    /**
     * @var int
     */
    protected $error_reporting_backup;


    protected function setUp()
    {
        $this->error_reporting_backup = error_reporting();

        PHPUnit_Framework_Error_Notice::$enabled = false;
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        require_once('./libs/composer/vendor/autoload.php');
        if (!defined('DEVMODE')) {
            define('DEVMODE', true);
        }
        require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');
        $this->db = $this->getDBInstance();
        $this->connect($this->db);
    }


    /**
     * @return \ilDBInterface
     * @throws \ilDatabaseException
     */
    protected function getDBInstance()
    {
        return ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
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
    protected function connect(ilDBInterface $ilDBInterface)
    {
        require_once('./Services/Init/classes/class.ilIniFile.php');
        require_once('./Services/Init/classes/class.ilErrorHandling.php');
        $ilClientIniFile = new ilIniFile($this->getIniFile());
        $ilClientIniFile->read();
        $this->type = $ilClientIniFile->readVariable("db", "type");
        $ilDBInterface->initFromIniFile($ilClientIniFile);
        $return = $ilDBInterface->connect();

        return $return;
    }


    protected function tearDown()
    {
        error_reporting($this->error_reporting_backup);
    }


    /**
     * Checks if every table has a primary key
     */
    public function testPrimaryKeys()
    {
        /**
         * @var $manager ilDBPdoManager
         */
        $manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
        $all_tables_primary_mock = array();
        $all_tables_primary_actual = array();
        foreach ($this->db->listTables() as $table) {
            $constraints = $manager->listTableConstraints($table);
            $all_tables_primary_actual[$table] = $constraints[0];
            $all_tables_primary_mock[$table] = 'primary';
        }

        $this->assertEquals($all_tables_primary_mock, $all_tables_primary_actual);
    }


    /**
     * Checks if every table at least has a primary and/or indices
     */
    public function testIndicesOrPrimaries()
    {
        /**
         * @var $manager ilDBPdoManager
         */
        $manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
        $all_tables_primary_mock = array();
        $all_tables_primary_actual = array();
        foreach ($this->db->listTables() as $table) {
            $indices = $manager->listTableIndexes($table);
            $constraints = $manager->listTableConstraints($table);
            $count = count($indices) + count($constraints);
            $all_tables_primary_actual[$table] = $count;
            $all_tables_primary_mock[$table] = $count ? $count : 1;
        }

        $this->assertEquals($all_tables_primary_mock, $all_tables_primary_actual);
    }
}
