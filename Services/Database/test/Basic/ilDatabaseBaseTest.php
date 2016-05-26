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
class ilDatabaseBaseTest extends PHPUnit_Framework_TestCase {

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


	protected function setUp() {
		if ($this->set_up) {
			return;
		}
		echo phpversion() . "\n";
		$this->error_reporting_backup = error_reporting();
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING & ~E_STRICT); // Due to PEAR Lib MDB2

		PHPUnit_Framework_Error_Notice::$enabled = false;
		PHPUnit_Framework_Error_Deprecated::$enabled = false;

		set_include_path("./Services/PEAR/lib" . PATH_SEPARATOR . ini_get('include_path'));

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
	protected function getDBInstance() {
		return ilDBWrapperFactory::getWrapper(ilDBConstants::TYPE_PDO_MYSQL_INNODB);
	}


	/**
	 * @return string
	 */
	protected function getIniFile() {
		return $this->ini_file;
	}


	/**
	 * @param \ilDBInterface $ilDBInterface
	 * @return bool
	 */
	protected function connect(ilDBInterface $ilDBInterface) {
		require_once('./Services/Init/classes/class.ilIniFile.php');
		require_once('./Services/Init/classes/class.ilErrorHandling.php');
		$ilClientIniFile = new ilIniFile($this->getIniFile());
		$ilClientIniFile->read();
		$this->type = $ilClientIniFile->readVariable("db", "type");
		$ilDBInterface->initFromIniFile($ilClientIniFile);
		$return = $ilDBInterface->connect();

		return $return;
	}


	protected function tearDown() {
		error_reporting($this->error_reporting_backup);
	}


	public function testPrimaryKeys() {
		/**
		 * @var $manager ilDBPdoManager
		 */
		$ignore = array(
			'il_request_token',
			'il_event_handling',
			'il_dcl_viewdefinition',
			'cp_suspend',
			'copg_section_timings',
			'cmi_gobjective',
			'bookmark_tree',
		);

		$manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
		foreach ($this->db->listTables() as $table) {
			if (in_array($table, $ignore)) {
				//				continue;
			}
			$constraints = $manager->listTableConstraints($table);
			$this->assertTrue(in_array('primary', $constraints));
			if (!in_array('primary', $constraints)) {
//				throw new ilDatabaseException("Table {$table} has no correct primary key. Existing contraints: " . print_r($constraints, true));
			}
		}
	}
}