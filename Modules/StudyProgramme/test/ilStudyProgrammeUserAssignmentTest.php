<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/mocks.php");

/**
 * TestCase for the assignment of users to a programme.
 * @group needsInstalledILIAS
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilStudyProgrammeUserAssignmentTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        
        $this->root = ilObjStudyProgramme::createInstance();
        $this->root->putInTree(ROOT_FOLDER_ID);
        $this->root->object_factory = new ilObjectFactoryWrapperMock();
        
        $this->node1 = ilObjStudyProgramme::createInstance();
        $this->node2 = ilObjStudyProgramme::createInstance();
        
        $this->leaf1 = new ilStudyProgrammeLeafMock();
        $this->leaf2 = new ilStudyProgrammeLeafMock();
        
        $this->root->addNode($this->node1);
        $this->root->addNode($this->node2);
        $this->node1->addLeaf($this->leaf1);
        $this->node2->addLeaf($this->leaf2);
        
        global $DIC;
        $tree = $DIC['tree'];
        $this->tree = $tree;
    }
    
    protected function tearDown()
    {
        if ($this->root) {
            $this->root->delete();
        }
    }

    
    protected function newUser()
    {
        $user = new ilObjUser();
        $user->create();
        return $user;
    }
    
    /**
     * @expectedException ilException
     */
    public function testNoAssignmentWhenDraft()
    {
        $user = $this->newUser();
        $this->assertEquals(ilStudyProgramme::STATUS_DRAFT, $this->root->getStatus());
        $this->root->assignUser($user->getId());
    }
    
    /**
     * @expectedException ilException
     */
    public function testNoAssignmentWhenOutdated()
    {
        $user = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_OUTDATED);
        $this->assertEquals(ilStudyProgramme::STATUS_OUTDATED, $this->root->getStatus());
        $this->root->assignUser($user->getId());
    }
    
    /**
     * @expectedException ilException
     */
    public function testNoAssignementWhenNotCreated()
    {
        $user = $this->newUser();
        $prg = new ilObjStudyProgramme();
        $prg->assignUser($user->getId());
    }
    
    public function testUserId()
    {
        $user1 = $this->newUser();
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $ass = $this->root->assignUser($user1->getId());
        $this->assertEquals($user1->getId(), $ass->getUserId());
    }
    
    public function testHasAssignmentOf()
    {
        $user1 = $this->newUser();
        $user2 = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $this->root->assignUser($user1->getId());
        $this->assertTrue($this->root->hasAssignmentOf($user1->getId()));
        $this->assertTrue($this->node1->hasAssignmentOf($user1->getId()));
        $this->assertTrue($this->node2->hasAssignmentOf($user1->getId()));
        
        $this->assertFalse($this->root->hasAssignmentOf($user2->getId()));
        $this->assertFalse($this->node1->hasAssignmentOf($user2->getId()));
        $this->assertFalse($this->node2->hasAssignmentOf($user2->getId()));
    }
    
    public function testGetAmountOfAssignments()
    {
        $user1 = $this->newUser();
        $user2 = $this->newUser();
        $user3 = $this->newUser();
        $user4 = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $this->node2->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $this->assertEquals(0, $this->root->getAmountOfAssignmentsOf($user1->getId()));
        
        $this->root->assignUser($user2->getId());
        $this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user2->getId()));
        $this->assertEquals(1, $this->node1->getAmountOfAssignmentsOf($user2->getId()));
        $this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user2->getId()));
    
        $this->root->assignUser($user3->getId());
        $this->root->assignUser($user3->getId());
        $this->assertEquals(2, $this->root->getAmountOfAssignmentsOf($user3->getId()));
        $this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user3->getId()));
        $this->assertEquals(2, $this->node2->getAmountOfAssignmentsOf($user3->getId()));

        $this->root->assignUser($user4->getId());
        $this->node1->assignUser($user4->getId());
        $this->assertEquals(1, $this->root->getAmountOfAssignmentsOf($user4->getId()));
        $this->assertEquals(2, $this->node1->getAmountOfAssignmentsOf($user4->getId()));
        $this->assertEquals(1, $this->node2->getAmountOfAssignmentsOf($user4->getId()));
    }
    
    public function testGetAssignmentsOf()
    {
        $user1 = $this->newUser();
        $user2 = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $this->assertEquals(0, count($this->root->getAssignmentsOf($user1->getId())));
        $this->assertEquals(0, count($this->node1->getAssignmentsOf($user1->getId())));
        $this->assertEquals(0, count($this->node2->getAssignmentsOf($user1->getId())));

        $this->root->assignUser($user2->getId());
        $this->node1->assignUser($user2->getId());
        
        $root_ass = $this->root->getAssignmentsOf($user2->getId());
        $node1_ass = $this->node1->getAssignmentsOf($user2->getId());
        $node2_ass = $this->node2->getAssignmentsOf($user2->getId());
        
        $this->assertEquals(1, count($root_ass));
        $this->assertEquals(2, count($node1_ass));
        $this->assertEquals(1, count($node2_ass));
        
        $this->assertEquals($this->root->getId(), $root_ass[0]->getStudyProgramme()->getId());
        $this->assertEquals($this->root->getId(), $node2_ass[0]->getStudyProgramme()->getId());
        
        $node1_ass_prg_ids = array_map(function ($ass) {
            return $ass->getStudyProgramme()->getId();
        }, $node1_ass);
        $this->assertContains($this->root->getId(), $node1_ass_prg_ids);
        $this->assertContains($this->node1->getId(), $node1_ass_prg_ids);
    }
    
    /**
     * @expectedException ilException
     */
    public function testRemoveOnRootNodeOnly1()
    {
        $user = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $ass1 = $this->root->assignUser($user->getId());
        $this->node1->removeAssignment($ass1);
    }

    /**
     * @expectedException ilException
     */
    public function testRemoveOnRootNodeOnly2()
    {
        $user = $this->newUser();
        
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $ass1 = $this->node1->assignUser($user->getId());
        $this->root->removeAssignment($ass1);
    }
    
    public function testRemoveAssignment1()
    {
        $user = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $ass1 = $this->root->assignUser($user->getId());
        $this->root->removeAssignment($ass1);
        $this->assertFalse($this->root->hasAssignmentOf($user->getId()));
    }
    
    public function testRemoveAssignment2()
    {
        $user = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $ass1 = $this->root->assignUser($user->getId());
        $ass1->deassign();
        $this->assertFalse($this->root->hasAssignmentOf($user->getId()));
    }
    
    public function testGetAssignments()
    {
        $user1 = $this->newUser();
        $user2 = $this->newUser();
        
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $ass1 = $this->root->assignUser($user1->getId());
        $ass2 = $this->node1->assignUser($user2->getId());
        
        $asses = $this->node1->getAssignments();
        $ass_ids = array_map(function ($ass) {
            return $ass->getId();
        }, $asses);
        $this->assertContains($ass1->getId(), $ass_ids);
        $this->assertContains($ass2->getId(), $ass_ids);
    }
    
    public function testDeleteOfProgrammeRemovesEntriesInPrgUsrAssignment()
    {
        $user1 = $this->newUser();
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $ass = $this->root->assignUser($user1->getId());

        $root_id  = $this->root->getId();
        $this->root->delete();
        $this->root = null;
        
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->query(
            "SELECT COUNT(*) cnt "
                            . " FROM " . ilStudyProgrammeAssignment::returnDbTableName()
                            . " WHERE root_prg_id = " . $root_id
        );
        $rec = $ilDB->fetchAssoc($res);
        $this->assertEquals(0, $rec["cnt"]);
    }

    public function testDeassignRemovesEntriesInPrgUsrAssignment()
    {
        $user = $this->newUser();
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $ass1 = $this->root->assignUser($user->getId());
        $ass1->deassign();
        
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->query(
            "SELECT COUNT(*) cnt "
                            . " FROM " . ilStudyProgrammeAssignment::returnDbTableName()
                            . " WHERE id = " . $ass1->getId()
        );
        $rec = $ilDB->fetchAssoc($res);
        $this->assertEquals(0, $rec["cnt"]);
    }
}
