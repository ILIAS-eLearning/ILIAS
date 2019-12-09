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
 * @version 1.0.0
 */
class ilStudyProgrammeEventsTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
        
        $this->root = ilObjStudyProgramme::createInstance();
        $this->root_obj_id = $this->root->getId();
        $this->root_ref_id = $this->root->getRefId();
        $this->root->putInTree(ROOT_FOLDER_ID);
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $this->root->object_factory = new ilObjectFactoryWrapperMock();
        
        $this->node = ilObjStudyProgramme::createInstance();
        $this->node->object_factory = new ilObjectFactoryWrapperMock();
        $this->root->addNode($this->node);
        
        $this->leaf = new ilStudyProgrammeLeafMock();
        $this->node->addLeaf($this->leaf);
        $this->node->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        
        $this->users = array();
        
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");
        $this->event_handler_mock = new ilAppEventHandlerMock();
        ilStudyProgrammeEvents::$app_event_handler = $this->event_handler_mock;
    }
    
    protected function newUser()
    {
        $user = new ilObjUser();
        $user->create();
        $this->users[] = $user;
        return $user;
    }
    
    protected function tearDown()
    {
        foreach ($this->users as $user) {
            $user->delete();
        }
        if ($this->root) {
            $this->root->delete();
        }
    }
    
    public function testAssignUser()
    {
        $user = $this->newUser();
        $ass = $this->root->assignUser($user->getId());
        
        $this->assertCount(1, $this->event_handler_mock->events);
        $event = array_pop($this->event_handler_mock->events);
        
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userAssigned", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);
    }
    
    public function testDeassignUser()
    {
        $user = $this->newUser();
        $ass = $this->root->assignUser($user->getId());
        $this->event_handler_mock->events = array();
        
        $ass->deassign();
        
        $this->assertCount(1, $this->event_handler_mock->events);
    
        $event = array_pop($this->event_handler_mock->events);
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userDeassigned", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);
    }
    
    public function testUserSuccessfulByCompletion()
    {
        $user = $this->newUser();
        $ass = $this->root->assignUser($user->getId());
        $this->event_handler_mock->events = array();
    
        $this->leaf->markCompletedFor($user->getId());
        
        $this->assertCount(2, $this->event_handler_mock->events);
        
        $event = array_shift($this->event_handler_mock->events);
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userSuccessful", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($this->node->getId(), $event["parameters"]["prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);

        $event = array_shift($this->event_handler_mock->events);
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userSuccessful", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);
    }
    
    public function testUserSuccessfulByAccredited()
    {
        $user = $this->newUser();
        $ass = $this->root->assignUser($user->getId());
        $this->event_handler_mock->events = array();
    
        $progress = $this->node->getProgressForAssignment($ass->getId());
        $progress->markAccredited(6);
        
        $this->assertCount(2, $this->event_handler_mock->events);
        
        $event = array_shift($this->event_handler_mock->events);
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userSuccessful", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($this->node->getId(), $event["parameters"]["prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);

        $event = array_shift($this->event_handler_mock->events);
        $this->assertEquals("Modules/StudyProgramme", $event["component"]);
        $this->assertEquals("userSuccessful", $event["event"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["root_prg_id"]);
        $this->assertEquals($this->root->getId(), $event["parameters"]["prg_id"]);
        $this->assertEquals($user->getId(), $event["parameters"]["usr_id"]);
        $this->assertEquals($ass->getId(), $event["parameters"]["ass_id"]);
    }
}
