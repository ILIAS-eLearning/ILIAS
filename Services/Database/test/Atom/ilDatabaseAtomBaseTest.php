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


	protected function setUp() {
		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		require_once('./Services/Database/classes/Atom/class.ilAtomQuery.php');
	}


	public function testGetInstance() {
		global $ilDB;
		$serializable = new ilAtomQuery($ilDB);
		$this->assertEquals($serializable->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);

		$serializable = new ilAtomQuery($ilDB, ilAtomQuery::ISOLATION_SERIALIZABLE);
		$this->assertEquals($serializable->getIsolationLevel(), ilAtomQuery::ISOLATION_SERIALIZABLE);
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadUncommited() {
		global $ilDB;
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($ilDB, ilAtomQuery::ISOLATION_READ_UNCOMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadCommited() {
		global $ilDB;
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($ilDB, ilAtomQuery::ISOLATION_READ_COMMITED);
		$other->run();
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function testReadRepeatedRead() {
		global $ilDB;
		$this->setExpectedException('ilDatabaseException');
		$other = new ilAtomQuery($ilDB, ilAtomQuery::ISOLATION_REPEATED_READ);
		$other->run();
	}
}