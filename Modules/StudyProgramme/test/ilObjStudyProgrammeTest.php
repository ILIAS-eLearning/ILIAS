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

require_once(__DIR__ . "/mocks.php");

/**
 * TestCase for the ilObjStudyProgramme
 * @group needsInstalledILIAS
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilObjStudyProgrammeTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        
        $this->root_object = ilObjStudyProgramme::createInstance();
        $this->root_object_obj_id = $this->root_object->getId();
        $this->root_object_ref_id = $this->root_object->getRefId();
        $this->root_object->putInTree(ROOT_FOLDER_ID);
        
        //
        
        global $DIC;
        $tree = $DIC['tree'];
        $this->tree = $tree;
        
        global $DIC;
        $objDefinition = $DIC['objDefinition'];
        $this->obj_definition = $objDefinition;
    }
    
    protected function tearDown()
    {
        if ($this->root_object) {
            $this->root_object->delete();
        }
    }
    
    /**
     * Test creation of ilObjStudyProgramme
     */
    public function testCreation()
    {
        $this->assertNotEmpty($this->root_object_obj_id);
        $this->assertGreaterThan(0, $this->root_object_obj_id);

        $this->assertNotEmpty($this->root_object_ref_id);
        $this->assertGreaterThan(0, $this->root_object_ref_id);

        $this->assertTrue($this->tree->isInTree($this->root_object_ref_id));
    }
    
    public function testDefaults()
    {
        $this->assertEquals($this->root_object->getStatus(), ilStudyProgramme::STATUS_DRAFT);
    }

    /**
     * Test loading of ilObjStudyProgramme with obj_id. and ref_id
     *
     * @depends testCreation
     */
    public function testLoadByObjId()
    {
        $loaded = new ilObjStudyProgramme($this->root_object_obj_id, false);
        $orig = $this->root_object;
        $load_ref_id = ilObjStudyProgramme::getInstanceByRefId($this->root_object_ref_id);

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals($orig->getId(), $loaded->getId());
        $this->assertEquals(
            $orig->getLastChange()->get(IL_CAL_DATETIME),
            $loaded->getLastChange()->get(IL_CAL_DATETIME)
        );
        $this->assertEquals($orig->getPoints(), $loaded->getPoints());
        $this->assertEquals($orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals($orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test loading of ilObjStudyProgramme with ref_id.
     *
     * @depends testCreation
     */
    public function testLoadByRefId()
    {
        $loaded = new ilObjStudyProgramme($this->root_object_ref_id);
        $orig = $this->root_object;

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals($orig->getId(), $loaded->getId());
        $this->assertEquals(
            $orig->getLastChange()->get(IL_CAL_DATETIME),
            $loaded->getLastChange()->get(IL_CAL_DATETIME)
        );
        $this->assertEquals($orig->getPoints(), $loaded->getPoints());
        $this->assertEquals($orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals($orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test loading over getInstance
     *
     * @depends testCreation
     */
    public function testGetInstanceByRefId()
    {
        require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgrammeCache.php");

        ilObjStudyProgrammeCache::singleton()->test_clear();
        $this->assertTrue(ilObjStudyProgrammeCache::singleton()->test_isEmpty());
        
        $loaded = ilObjStudyProgramme::getInstanceByRefId($this->root_object_ref_id);
        $orig = $this->root_object;

        $this->assertNotNull($loaded);
        $this->assertGreaterThan(0, $loaded->getId());
        $this->assertEquals($orig->getId(), $loaded->getId());
        $this->assertEquals(
            $orig->getLastChange()->get(IL_CAL_DATETIME),
            $loaded->getLastChange()->get(IL_CAL_DATETIME)
        );
        $this->assertEquals($orig->getPoints(), $loaded->getPoints());
        $this->assertEquals($orig->getLPMode(), $loaded->getLPMode());
        $this->assertEquals($orig->getStatus(), $loaded->getStatus());
    }

    /**
     * Test 	tings on ilObjStudyProgramme
     *
     * @depends testCreation
     */
    public function testSettings()
    {
        $obj = ilObjStudyProgramme::getInstanceByRefId($this->root_object_ref_id);

        $obj->setPoints(10);
        $obj->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $obj->update();
        
        $obj = ilObjStudyProgramme::getInstanceByRefId($this->root_object_ref_id);

        $this->assertEquals(10, $obj->getPoints());
        $this->assertEquals(ilStudyProgramme::STATUS_ACTIVE, $obj->getStatus());

        $midnight = strtotime("today midnight");
        $this->assertGreaterThan($midnight, $obj->getLastChange()->getUnixTime());
    }

    /**
     * Test deletion of a ilObjStudyProgramme
     *
     * @depends testCreation
     */
    public function testDelete()
    {
        $deleted_object = ilObjStudyProgramme::getInstanceByRefId($this->root_object_ref_id);

        $this->assertTrue($deleted_object->delete());
    }

    /**
     * Creates a small tree, used by various tests.
     */
    protected function createSmallTree()
    {
        $first_node = ilObjStudyProgramme::createInstance();
        $second_node = ilObjStudyProgramme::createInstance();
        $third_node = ilObjStudyProgramme::createInstance();

        $this->root_object->addNode($first_node);
        $this->root_object->addNode($second_node);
        $this->root_object->addNode($third_node);

        $third_first_node = ilObjStudyProgramme::createInstance();
        $third_node->addNode($third_first_node);
    }

    /**
     * Test creating a small tree
     *
     * @depends testCreation
     */
    public function testTreeCreation()
    {
        $this->createSmallTree();
        $this->assertEquals(3, $this->root_object->getAmountOfChildren());
    }

    /**
     * Test function to get children or information about them
     *
     * @depends testTreeCreation
     * @depends testGetInstanceByRefId
     */
    public function testTreeGetChildren()
    {
        $this->createSmallTree();
        
        $children = $this->root_object->getChildren();
        $this->assertEquals(3, count($children), "getChildren()");

        $children = ilObjStudyProgramme::getAllChildren($this->root_object_ref_id);
        $this->assertEquals(4, count($children), "ilObjStudyProgramme::getAllChildren(" . $this->root_object_ref_id . ")");

        $this->assertTrue($this->root_object->hasChildren(), "hasChildren()");
        $this->assertEquals(3, $this->root_object->getAmountOfChildren(), "getAmountOfChildren()");
        
        $this->assertFalse($children[0]->hasChildren(), "hasChildren()");
        $this->assertEquals(0, $children[0]->getAmountOfChildren(), "getAmountOfChildren()");
        $this->assertEquals(0, count($children[0]->getChildren()));
    }

    /**
     * Test getParent on ilObjStudyProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeGetParent()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();

        $child = $children[0];
        $this->assertNotNull($child->getParent());
        $this->assertNull($this->root_object->getParent());
    }
    
    /**
     * @depends testTreeCreation
     */
    public function testTreeGetParents()
    {
        $this->createSmallTree();
        $node3 = ilObjStudyProgramme::createInstance();
        $children = $this->root_object->getChildren();
        $children[0]->addNode($node3);
        
        $parents = $node3->getParents();
        $parent_ids = array_map(function ($node) {
            return $node->getId();
        }, $parents);
        $parent_ids_expected = array( $this->root_object->getId()
                                    , $children[0]->getId()
                                    );
        
        $this->assertEquals($parent_ids_expected, $parent_ids);
    }

    /**
     * Test getDepth on ilObjStudyProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeDepth()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();

        $child = $children[0];

        $this->assertEquals(1, $child->getDepth());
    }

    /**
     * Test getRoot on ilObjStudyProgramme
     *
     * @depends testTreeCreation
     */
    public function testTreeGetRoot()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child = $children[0];

        $this->assertEquals($this->root_object->getId(), $child->getRoot()->getId());
    }
    
    /**
     * Test applyToSubTreeNodes on ilObjStudyProgramme.
     *
     * @depends testTreeCreation
     */
    public function testApplyToSubTreeNodes()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        
        $val = 0;
        $this->root_object->applyToSubTreeNodes(function ($node) use (&$val) {
            $val += $node->getPoints();
        });
        
        // We didn't make modification on the points of the nodes.
        $this->assertEquals($val, 5 * ilStudyProgramme::DEFAULT_POINTS);


        $this->root_object->setPoints(1);
        $children[0]->setPoints(2);
        $children[1]->setPoints(4);
        $children[2]->setPoints(1);

        $third_level = $children[2]->getChildren();
        $third_level[0]->setPoints(2);
        
        $val = 0;
        $this->root_object->applyToSubTreeNodes(function ($node) use (&$val) {
            $val += $node->getPoints();
        });
        
        $this->assertEquals($val, 10);
    }
    
    /**
     * Test on addNode.
     *
     * @depends testTreeCreation
     */
    public function testAddNode()
    {
        $this->createSmallTree();
        
        $children = $this->root_object->getChildren();
        $child = $children[0];
        $grandchild = new ilObjStudyProgramme();
        $grandchild->create();
        $child->addNode($grandchild);
        
        $this->assertEquals($child->getId(), $grandchild->getParent()->getId());
        $this->assertEquals(
            $this->root_object->getId(),
            $grandchild->getRoot()->getId(),
            "Root of grandchild is root of tree."
        );
        $this->assertEquals(1, $child->getAmountOfChildren());
        $this->assertEquals(2, $grandchild->getDepth());
        $this->assertEquals($child->getLPMode(), ilStudyProgramme::MODE_POINTS);
    }
    
    /**
     * Test on removeNode.
     *
     * @depends testTreeCreation
     */
    public function testRemoveNode()
    {
        $this->createSmallTree();
        
        $children = $this->root_object->getChildren();
        $child = $children[0];
        $this->root_object->removeNode($child);
        
        // Is not in tree anymore...
        $raised = false;
        try {
            $child->getParent();
        } catch (ilStudyProgrammeTreeException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Child does not raise on getParent after it is removed.");
        
        $this->assertEquals(2, $this->root_object->getAmountOfChildren());
        
        // Can't be removed a second time...
        $raised = false;
        try {
            $this->root_object->removeNode($child);
        } catch (ilStudyProgrammeTreeException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Child can be removed two times.");
    }
    
    /**
     * Test on addLeaf.
     *
     * @depends testTreeCreation
     */
    public function testAddLeaf()
    {
        $this->createSmallTree();
        $mock_leaf = new ilStudyProgrammeLeafMock();

        $children = $this->root_object->getChildren();
        $first_child = $children[0];

        $first_child->addLeaf($mock_leaf);

        // We use our mock factory, since the original factory won't know how
        // to create our mock leaf.
        $first_child->object_factory = new ilObjectFactoryWrapperMock();

        $this->assertEquals(3, $this->root_object->getAmountOfChildren(), "getAmountOfChildren()");
        // Check if StudyProgrammes are not counted as LP-Children
        $this->assertEquals(0, $this->root_object->getAmountOfLPChildren(), "getAmountOfLPChildren() on root");
        $this->assertEquals(false, $this->root_object->hasLPChildren(), "hasLPChildren() on root");

        $this->assertEquals(1, $first_child->getAmountOfLPChildren(), "getAmountOfLPChildren() on first child");
        $this->assertEquals(true, $first_child->hasLPChildren(), "hasLPChildren() on first child");
        $this->assertEquals($first_child->getLPMode(), ilStudyProgramme::MODE_LP_COMPLETED);
        
        $lp_children = $first_child->getLPChildren();
        $this->assertEquals(1, count($lp_children));
        $this->assertEquals($mock_leaf->getId(), $lp_children[0]->getId());
    }
    
    /**
     * Test on removeLead.
     *
     * @depends testAddLeaf
     */
    public function testRemoveLeaf()
    {
        $mock_leaf = new ilStudyProgrammeLeafMock();
        $this->root_object->addLeaf($mock_leaf);
        
        $this->root_object->removeLeaf($mock_leaf);
        $this->assertEquals(0, $this->root_object->getAmountOfChildren(), "getAmountOfChildren()");
        $this->assertEquals(0, $this->root_object->getAmountOfLPChildren(), "getAmountOfLPChildren()");
        
        $lp_children = $this->root_object->getLPChildren();
        $this->assertEquals(0, count($lp_children));
    }
    
    /**
     * Test whether nodes can only be added when there is no leaf in the
     * parent and vice versa.
     */
    public function testAddWrongChildType()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child_n = $children[0];
        $child_l = $children[1];
        
        $mock_leaf1 = new ilStudyProgrammeLeafMock();
        $mock_leaf2 = new ilStudyProgrammeLeafMock();
        $node1 = new ilObjStudyProgramme();
        $node2 = new ilObjStudyProgramme();
        $node1->create();
        $node2->create();
        
        $child_n->addNode($node1);
        $child_l->addLeaf($mock_leaf1);
        
        $raised = false;
        try {
            $child_n->addLeaf($mock_leaf2);
        } catch (ilStudyProgrammeTreeException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Could add leaf to program containing node.");

        $raised = false;
        try {
            $child_n->addLeaf($mock_leaf2);
        } catch (ilStudyProgrammeTreeException $e) {
            $raised = true;
        }
        $this->assertTrue($raised, "Could add node to program containing leaf.");
    }
    
    /**
     * Test on moveTo.
     */
    public function testMoveTo()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child_l = $children[0];
        $child_r = $children[1];
        $child_m = $children[2];

        $child_r->moveTo($child_l);

        $this->assertEquals(2, $child_r->getDepth());
        $this->assertEquals($child_l->getId(), $child_r->getParent()->getId());
        $this->assertEquals(2, $this->root_object->getAmountOfChildren());
        $this->assertEquals(1, $child_l->getAmountOfChildren());

        // test recursive moving
        $this->assertEquals(1, $child_m->getAmountOfChildren());

        $child_m->moveTo($child_r);

        $m_children = $child_m->getChildren();
        $first_third_node = $m_children[0];

        $this->assertEquals(3, $child_m->getDepth());
        $this->assertEquals(1, $child_m->getAmountOfChildren());
        $this->assertNotNull($first_third_node);
        $this->assertEquals(4, $first_third_node->getDepth());
        $this->assertEquals($child_m->getId(), $first_third_node->getParent()->getId());

        $this->assertEquals(1, $this->root_object->getAmountOfChildren());
        $this->assertEquals(3, count(ilObjStudyProgramme::getAllChildren($child_l->getRefId())));
    }
    
    /**
     * @expectedException ilStudyProgrammeTreeException
     */
    public function testCantRemoveNodeWithRelevantProgress()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child_l = $children[0];
        $child_r = $children[1];
        $this->root_object->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $child_l->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $child_r->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $user = new ilObjUser();
        $user->create();
        
        $child_l->assignUser($user->getId());
        $this->root_object->removeNode($child_l);
    }
    
    public function testCanRemoveNodeWithNotRelevantProgress()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child_l = $children[0];
        $child_r = $children[1];
        $this->root_object->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $child_l->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $child_r->setStatus(ilStudyProgramme::STATUS_OUTDATED);
        
        $user = new ilObjUser();
        $user->create();
        
        $this->root_object->assignUser($user->getId());
        $this->root_object->removeNode($child_r);
    }
    
    public function testCreateableSubObjects()
    {
        $this->createSmallTree();
        $children = $this->root_object->getChildren();
        $child_l = $children[0];
        
        $all_possible_subobjects = $this->root_object->getPossibleSubObjects();
        // don't take rolfs into account, we don't need rolf anymore
        unset($all_possible_subobjects["rolf"]);
        
        // this is course reference and training programme
        $this->assertCount(2, $all_possible_subobjects);
        $this->assertArrayHasKey("prg", $all_possible_subobjects);
        $this->assertArrayHasKey("crsr", $all_possible_subobjects);
        
        // root already contains program nodes, so course ref is forbidden
        $subobjs = ilObjStudyProgramme::getCreatableSubObjects($all_possible_subobjects, $this->root_object->getRefId());
        $this->assertCount(1, $subobjs);
        $this->assertArrayHasKey("prg", $subobjs);
        
        // first node contains nothing, so course ref and program node are allowed
        $subobjs = ilObjStudyProgramme::getCreatableSubObjects($all_possible_subobjects, $child_l->getRefId());
        $this->assertCount(2, $subobjs);
        $this->assertArrayHasKey("prg", $subobjs);
        $this->assertArrayHasKey("crsr", $subobjs);
        
        $mock_leaf = new ilStudyProgrammeLeafMock();
        $children = $this->root_object->getChildren();
        $child_l->object_factory = new ilObjectFactoryWrapperMock();
        $child_l->addLeaf($mock_leaf);

        // Now we added a leaf, so no program nodes are allowed anymore.
        $subobjs = ilObjStudyProgramme::getCreatableSubObjects($all_possible_subobjects, $child_l->getRefId());
        $this->assertCount(1, $subobjs);
        $this->assertArrayHasKey("crsr", $subobjs);
    }
    
    public function testCreatableSubObjectsWithoutRef()
    {
        $all_possible_subobjects = $this->obj_definition->getSubObjects("prg");
        // don't take rolfs into account, we don't need rolf anymore
        unset($all_possible_subobjects["rolf"]);
        $this->assertEquals(
            $all_possible_subobjects,
            ilObjStudyProgramme::getCreatableSubObjects($all_possible_subobjects, null)
        );
    }

    /**
     * @expectedException ilException
     */
    public function testCreatableSubObjectsRaisesOnNonProgramRef()
    {
        ilObjStudyProgramme::getCreatableSubObjects(array(), 9);
    }
    
    public function testDeleteRemovesEntriesInPrgSettings()
    {
        $this->root_object->delete();
        $this->root_object = null;
        
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->query(
            "SELECT COUNT(*) cnt "
                            . " FROM " . ilStudyProgramme::returnDbTableName()
                            . " WHERE obj_id = " . $this->root_object_obj_id
        );
        $rec = $ilDB->fetchAssoc($res);
        $this->assertEquals(0, $rec["cnt"]);
    }
    
    public function testCreatePermissionExists()
    {
        // Ask for permission id for creation of "foobar" to check assumption
        // that lookupCreateOperationIds just drops unknown object types.
        $op_ids = ilRbacReview::lookupCreateOperationIds(array("prg", "foobar"));
        $this->assertCount(1, $op_ids);
    }
}
