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
 * TestCase for the ilObjTrainingProgramme
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjTrainingProgrammeTest extends PHPUnit_Framework_TestCase {
    protected $backupGlobals = FALSE;

    protected function setUp() {
        PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

        require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        
        $this->root_object =  new ilObjTrainingProgramme();
        $this->root_object->create();
        $this->root_object_obj_id = $this->root_object->getId();
        $this->root_object_ref_id = $this->root_object->createReference();
        $this->root_object->putInTree(ROOT_FOLDER_ID);

        global $tree;
        $this->tree = $tree;
    }

    /**
     * Test creation of ilObjTrainingProgramme
     */
    public function testCreation() {
        $this->assertNotEmpty($this->root_object_obj_id);
        $this->assertGreaterThan(0, $this->root_object_obj_id);

        $this->assertNotEmpty($this->root_object_ref_id);
        $this->assertGreaterThan(0, $this->root_object_ref_id);

        $this->assertTrue($this->tree->isInTree($this->root_object_ref_id));
    }

    /**
     * Test loading of ilObjTrainingProgramme with obj_id. and ref_id
     *
     * @depends testCreation
     */
    public function testLoadByObjId() {
        $loaded = new ilObjTrainingProgramme($this->root_object_obj_id, false);
        $orig = $this->root_object;
        $load_ref_id = new ilObjTrainingProgramme($this->root_object_ref_id);

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals( $orig->getId(), $loaded->getId());
        $this->assertEquals( $orig->getLastChange()->get(IL_CAL_DATETIME)
                           , $loaded->getLastChange()->get(IL_CAL_DATETIME)
                           );
        $this->assertEquals( $orig->getPoints(), $loaded->getPoints());
        $this->assertEquals( $orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals( $orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test loading of ilObjTrainingProgramme with ref_id.
     *
     * @depends testCreation
     */
    public function testLoadByRefId() {
        $loaded = new ilObjTrainingProgramme($this->root_object_ref_id);
        $orig = $this->root_object;

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals( $orig->getId(), $loaded->getId());
        $this->assertEquals( $orig->getLastChange()->get(IL_CAL_DATETIME)
                           , $loaded->getLastChange()->get(IL_CAL_DATETIME)
                           );
        $this->assertEquals( $orig->getPoints(), $loaded->getPoints());
        $this->assertEquals( $orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals( $orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test loading over getInstance
     *
     * @depends testCreation
     */
    public function testGetInstance() {
        require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCache.php");

        ilObjTrainingProgrammeCache::singleton()->test_clear();
        $this->assertTrue(ilObjTrainingProgrammeCache::singleton()->test_isEmpty());
        
        $loaded = ilObjTrainingProgramme::getInstance($this->root_object_ref_id);
        $orig = $this->root_object;

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals( $orig->getId(), $loaded->getId());
        $this->assertEquals( $orig->getLastChange()->get(IL_CAL_DATETIME)
                           , $loaded->getLastChange()->get(IL_CAL_DATETIME)
                           );
        $this->assertEquals( $orig->getPoints(), $loaded->getPoints());
        $this->assertEquals( $orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals( $orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test settings on ilObjTrainingProgramme
     *
     * @depends testCreation
     */
    public function testSettings() {
        $obj = new ilObjTrainingProgramme($this->root_object_ref_id);

        $obj->setPoints(10);
        $obj->setStatus(ilTrainingProgramme::STATUS_ACTIVE);
        $obj->update();

        $obj = new ilObjTrainingProgramme($this->root_object_ref_id);

        $this->assertEquals(10, $obj->getPoints());
        $this->assertEquals(ilTrainingProgramme::STATUS_ACTIVE, $obj->getStatus());

        $midnight = strtotime("today midnight");
        $this->assertGreaterThan($midnight, $obj->getLastChange()->getUnixTime());
    }

    /**
     * Test deletion of a ilObjTrainingProgramme
     *
     * @depends testCreation
     */
    public function testDelete() {
        $deleted_object = new ilObjTrainingProgramme($this->root_object_ref_id);

        $this->assertTrue($deleted_object->delete());
    }

    /**
     * Creates a small tree, used by various tests.
     */
    protected function createSmallTree() {
        $first_node = new ilObjTrainingProgramme();
        $first_node->create();

        $second_node = new ilObjTrainingProgramme();
        $second_node->create();

        $this->root_object->addNode($first_node);
        $this->root_object->addNode($second_node);
    }

    /**
     * Test creating a small tree
     *
     * @depends testCreation
     */
    public function testTreeCreation() {
        $this->createSmallTree();
        $this->assertEquals(2, $this->root_object->getAmountOfChildren());
    }

    /**
     * Test function to get children or information about them
     *
     * @depends testTreeCreation
     * @depends testGetInstance
     */
    public function testTreeGetChildren() {
        $children = ilObjTrainingProgramme::getAllChildren($this->root_object_ref_id);
        $this->assertEquals(2, count($children), "ilObjTrainingProgramme::getAllChildren(".$this->root_object_ref_id.")");

        $children = $$this->root_object->getChildren();
        $this->assertEquals(2, count($children), "getChildren()");

        // Test
        $this->assertTrue($$this->root_object->hasChildren(), "hasChildren()");
        $this->assertEquals(2, $$this->root_object->getAmountOfChildren(), "getAmountOfChildren()");
    }

    /**
     * Test getParent on ilObjTrainingProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeGetParent() {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();

        $child = $children[0];
        $this->assertNotNull($child->getParent());
        $this->assertNull($root->getParent());
    }

    /**
     * Test getDepth on ilObjTrainingProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeDepth() {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();

        $child = $children[0];

        $this->assertEquals(1, $child->getDepth());
    }

    /**
     * Test getRoot on ilObjTrainingProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeGetRoot() {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child = $children[0];

        $this->assertEquals($this->root_object->getId(), $child->getRoot()->getId());
    }

}