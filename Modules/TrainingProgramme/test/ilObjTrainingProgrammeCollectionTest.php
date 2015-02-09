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
 * TestCase for the ilObjTrainingProgrammeCollection
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjTrainingProgrammeCollectionTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCollection.php");
		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

		require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}

	/**
	 * Helper function to generate collections
	 * @param int $length
	 * @return array|ilObjTrainingProgrammeCollection
	 */
	protected function getCollection($length = 3) {
		$collection = new ilObjTrainingProgrammeCollection();
		for($i = 0; $i < $length; $i++) {
			$collection[] = new ilObjTrainingProgramme();
		}

		return $collection;
	}

	/**
	 * Test the creation of the Collection
	 */
	public function testCreation() {
		$collection = $this->getCollection(3);

		$this->assertEquals(3, count($collection));
	}

	/**
	 * Test Iterator on Collection
	 */
	public function testIterator() {
		$count = 0;
		$data  = $this->getCollection(3);
		foreach($data as $key=>$item) {
			$count++;
		}

		$this->assertEquals(3, $count);
	}

	/**
	 * Removing element from collection
	 */
	public function testRemoving() {
		$data = $this->getCollection(5);
		$this->assertEquals(5, count($data));

		unset($data[0]);
		$this->assertEquals(4, count($data));
	}

	/**
	 * Check type safety of the collection
	 * @expectedException ilException
	 */
	public function testTypeChecking() {
		$collection = new ilObjTrainingProgrammeCollection();
		$collection[] = "Should not work!";
	}
}
