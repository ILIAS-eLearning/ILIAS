<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/mocks.php");

/**
 * TestCase for the progress of users at a programme.
 * @group needsInstalledILIAS
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version 1.0.0
 */
class ilStudyProgrammeUserProgressTest extends PHPUnit_Framework_TestCase
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

        global $DIC;
        $ilUser = $DIC['ilUser'];
        $this->user = $ilUser;
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

    protected function setAllNodesActive()
    {
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node2->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
    }

    protected function assignNewUserToRoot()
    {
        $user = $this->newUser();
        return array($this->root->assignUser($user->getId()), $user);
    }

    public function testInitialProgressActive()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progresses = $this->root->getProgressesOf($user->getId());
        $this->assertCount(1, $root_progresses);
        $root_progress = $root_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals($this->root->getPoints(), $root_progress->getAmountOfPoints());
        $this->assertEquals(0, $root_progress->getCurrentAmountOfPoints());
        $this->assertEquals($this->root->getId(), $root_progress->getStudyProgramme()->getId());
        $this->assertEquals($ass->getId(), $root_progress->getAssignment()->getId());
        $this->assertEquals($user->getId(), $root_progress->getUserId());
        $this->assertNull($root_progress->getLastChangeBy());
        $this->assertNull($root_progress->getCompletionBy());

        $node1_progresses = $this->node1->getProgressesOf($user->getId());
        $this->assertCount(1, $node1_progresses);
        $node1_progress = $node1_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals($this->node1->getPoints(), $node1_progress->getAmountOfPoints());
        $this->assertEquals(0, $node1_progress->getCurrentAmountOfPoints());
        $this->assertEquals($this->node1->getId(), $node1_progress->getStudyProgramme()->getId());
        $this->assertEquals($ass->getId(), $node1_progress->getAssignment()->getId());
        $this->assertEquals($user->getId(), $node1_progress->getUserId());
        $this->assertNull($node1_progress->getLastChangeBy());
        $this->assertNull($node1_progress->getCompletionBy());

        $node2_progresses = $this->node2->getProgressesOf($user->getId());
        $this->assertCount(1, $node2_progresses);
        $node2_progress = $node2_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node2_progress->getStatus());
        $this->assertEquals($this->node2->getPoints(), $node2_progress->getAmountOfPoints());
        $this->assertEquals(0, $node2_progress->getCurrentAmountOfPoints());
        $this->assertEquals($this->node2->getId(), $node2_progress->getStudyProgramme()->getId());
        $this->assertEquals($ass->getId(), $node2_progress->getAssignment()->getId());
        $this->assertEquals($user->getId(), $node2_progress->getUserId());
        $this->assertNull($node2_progress->getLastChangeBy());
        $this->assertNull($node2_progress->getCompletionBy());
    }

    public function testInitialProgressDraft()
    {
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node2->setStatus(ilStudyProgramme::STATUS_DRAFT)->update();

        $tmp = $this->assignNewUserToRoot();
        $user = $tmp[1];
        $ass = $tmp[0];

        $root_progresses = $this->root->getProgressesOf($user->getId());
        $root_progress = $root_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());

        $node1_progresses = $this->node1->getProgressesOf($user->getId());
        $node1_progress = $node1_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());

        $node2_progresses = $this->node2->getProgressesOf($user->getId());
        $node2_progress = $node2_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $node2_progress->getStatus());
    }

    public function testInitialProgressOutdated()
    {
        $this->root->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node1->setStatus(ilStudyProgramme::STATUS_ACTIVE)->update();
        $this->node2->setStatus(ilStudyProgramme::STATUS_OUTDATED)->update();

        $tmp = $this->assignNewUserToRoot();
        $user = $tmp[1];
        $ass = $tmp[0];

        $root_progresses = $this->root->getProgressesOf($user->getId());
        $root_progress = $root_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());

        $node1_progresses = $this->node1->getProgressesOf($user->getId());
        $node1_progress = $node1_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());

        $node2_progresses = $this->node2->getProgressesOf($user->getId());
        $node2_progress = $node2_progresses[0];
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $node2_progress->getStatus());
    }

    public function testUserSelection()
    {
        $this->setAllNodesActive();
        $this->assignNewUserToRoot();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progresses = $this->root->getProgressesOf($user->getId());
        $this->assertCount(1, $root_progresses);
    }

    public function testMarkAccredited()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));

        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
        $this->assertEquals($root_progress->getAmountOfPoints(), ilStudyProgramme::DEFAULT_POINTS);
        $this->assertEquals($node1_progress->getAmountOfPoints(), ilStudyProgramme::DEFAULT_POINTS);
        $this->assertEquals($node2_progress->getAmountOfPoints(), ilStudyProgramme::DEFAULT_POINTS);

        $ts_before_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
        $node2_progress->markAccredited($USER_ID);
        $ts_after_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);

        $this->assertTrue($node2_progress->isSuccessful());
        $this->assertEquals($root_progress->getAmountOfPoints(), $root_progress->getCurrentAmountOfPoints());

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $node2_progress->getStatus());

        $this->assertEquals($USER_ID, $node2_progress->getCompletionBy());
        $this->assertLessThanOrEqual($ts_before_change, $ts_after_change);
    }

    public function testUnmarkAccredited()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node2_progress->getStatus());

        $ts_before_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
        $node2_progress->markAccredited($USER_ID);
        $node2_progress->unmarkAccredited();
        $ts_after_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);

        // The root node will still be completed, as we do not go back from completed to some other
        // status.
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node2_progress->getStatus());
        $this->assertEquals(null, $node2_progress->getCompletionBy());
        $this->assertLessThanOrEqual($ts_before_change, $ts_after_change);
    }

    public function testMarkFailed()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
        $node2_progress->markFailed($USER_ID);

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_FAILED, $node2_progress->getStatus());
    }

    public function testMarkNotFailed()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
        $node2_progress->markFailed($USER_ID);

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_FAILED, $node2_progress->getStatus());

        $node2_progress->markNotFailed($USER_ID);

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node2_progress->getStatus());
    }

    public function testMarkNotRelevant()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));
        $ts_before_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
        $node2_progress->markNotRelevant($USER_ID);
        $ts_after_change = $node2_progress->getLastChange()->get(IL_CAL_DATETIME);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $root_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $node1_progress->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $node2_progress->getStatus());
        $this->assertEquals($USER_ID, $node2_progress->getCompletionBy());
        $this->assertLessThanOrEqual($ts_before_change, $ts_after_change);
        $this->assertTrue($node2_progress->hasIndividualModifications());
    }

    // Neues Moduls: Wird dem Studierenden-Studierenden inkl. Kurse, Punkte als "Nicht relevant" hinzugefügt.
    public function testNewNodesAreNotRelevant()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $node3 = ilObjStudyProgramme::createInstance();
        $this->root->addNode($node3);

        $node3_progress = array_shift($node3->getProgressesOf($user->getId()));
        $this->assertNotNull($node3_progress);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $node3_progress->getStatus());
    }

    public function testIndividualRequiredPoints()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];


        $NEW_AMOUNT_OF_POINTS_1 = 205;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, ilStudyProgramme::DEFAULT_POINTS);

        $node2_progress1 = array_shift($this->node2->getProgressesOf($user1->getId()));
        $node2_progress1->setRequiredAmountOfPoints($NEW_AMOUNT_OF_POINTS_1, $this->user->getId());

        $this->assertEquals($NEW_AMOUNT_OF_POINTS_1, $node2_progress1->getAmountOfPoints());
    }

    public function testMaximimPossibleAmountOfPoints1()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));

        $this->assertEquals(2 * ilStudyProgramme::DEFAULT_POINTS, $root_progress->getMaximumPossibleAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $node1_progress->getMaximumPossibleAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $node2_progress->getMaximumPossibleAmountOfPoints());
    }

    public function testMaximimPossibleAmountOfPoints2()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));

        $this->assertEquals(2 * ilStudyProgramme::DEFAULT_POINTS, $root_progress->getMaximumPossibleAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $node1_progress->getMaximumPossibleAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $node2_progress->getMaximumPossibleAmountOfPoints());
    }

    public function testCanBeCompleted1()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node1_progress = array_shift($this->node1->getProgressesOf($user->getId()));
        $node2_progress = array_shift($this->node2->getProgressesOf($user->getId()));

        $this->assertTrue($root_progress->canBeCompleted());
        $this->assertTrue($node1_progress->canBeCompleted());
        $this->assertTrue($node2_progress->canBeCompleted());
    }

    public function testCanBeCompleted2()
    {
        $NEW_AMOUNT_OF_POINTS = 3003;
        $this->assertGreaterThan(ilStudyProgramme::DEFAULT_POINTS, $NEW_AMOUNT_OF_POINTS);

        $this->setAllNodesActive();
        $this->root->setPoints($NEW_AMOUNT_OF_POINTS)
                   ->update();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $this->assertLessThan($NEW_AMOUNT_OF_POINTS, $this->node1->getPoints() + $this->node2->getPoints());

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $this->assertFalse($root_progress->canBeCompleted());
    }

    public function testCanBeCompleted3()
    {
        $NEW_AMOUNT_OF_POINTS = 3003;
        $this->assertGreaterThan(ilStudyProgramme::DEFAULT_POINTS, $NEW_AMOUNT_OF_POINTS);

        $this->setAllNodesActive();
        $node3 = ilObjStudyProgramme::createInstance();
        $this->root->addNode($node3);
        $node3->setPoints($NEW_AMOUNT_OF_POINTS)
              ->setStatus(ilStudyProgramme::STATUS_ACTIVE)
              ->update();


        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $node3_progress = array_shift($node3->getProgressesOf($user->getId()));

        $this->assertFalse($root_progress->canBeCompleted());
        $this->assertFalse($node3_progress->canBeCompleted());
    }

    public function testUserDeletionDeletesAssignments()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user->delete();

        $root_progresses = $this->root->getProgressesOf($user->getId());
        $this->assertCount(0, $root_progresses);
        $node1_progresses = $this->root->getProgressesOf($user->getId());
        $this->assertCount(0, $node1_progresses);
        $node2_progresses = $this->root->getProgressesOf($user->getId());
        $this->assertCount(0, $node2_progresses);
    }

    // - Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen
    public function testNoImplicitPointUpdate()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $NEW_AMOUNT_OF_POINTS = 201;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilStudyProgramme::DEFAULT_POINTS);

        $this->root->setPoints($NEW_AMOUNT_OF_POINTS)
                   ->update();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $root_progress->getAmountOfPoints());
    }

    // Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
    //  sondern dann bei bewusster Aktualisierung.
    public function testExplicitPointUpdate1()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $NEW_AMOUNT_OF_POINTS = 202;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilStudyProgramme::DEFAULT_POINTS);

        $this->root->setPoints($NEW_AMOUNT_OF_POINTS)
                   ->update();

        $ass->updateFromProgram();
        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $this->assertEquals($NEW_AMOUNT_OF_POINTS, $root_progress->getAmountOfPoints());
    }

    // Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
    // sondern dann bei bewusster Aktualisierung.
    // Similar to testExplicitPointUpdate1, but order of calls differs.
    public function testExplicitPointUpdate2()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $NEW_AMOUNT_OF_POINTS = 203;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS, ilStudyProgramme::DEFAULT_POINTS);

        $this->root->setPoints($NEW_AMOUNT_OF_POINTS)
                   ->update();

        $root_progress = array_shift($this->root->getProgressesOf($user->getId()));
        $ass->updateFromProgram();
        $this->assertEquals($NEW_AMOUNT_OF_POINTS, $root_progress->getAmountOfPoints());
    }

    // Änderungen von Punkten bei bestehenden qua-Objekten werden nicht direkt übernommen,
    //  sondern dann bei bewusster Aktualisierung (sofern nicht ein darüberliegenden
    // Knotenpunkt manuell angepasst worden ist)
    public function testNoUpdateOnModifiedNodes()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $tmp = $this->assignNewUserToRoot();
        $ass2 = $tmp[0];
        $user2 = $tmp[1];

        $NEW_AMOUNT_OF_POINTS_1 = 205;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, ilStudyProgramme::DEFAULT_POINTS);
        $NEW_AMOUNT_OF_POINTS_2 = 206;
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS_2, ilStudyProgramme::DEFAULT_POINTS);
        $this->assertNotEquals($NEW_AMOUNT_OF_POINTS_1, $NEW_AMOUNT_OF_POINTS_2);

        $node2_progress1 = array_shift($this->node2->getProgressesOf($user1->getId()));
        $node2_progress2 = array_shift($this->node2->getProgressesOf($user2->getId()));

        $node2_progress1->setRequiredAmountOfPoints($NEW_AMOUNT_OF_POINTS_1, $this->user->getId());

        $this->node2->setPoints($NEW_AMOUNT_OF_POINTS_2)
                    ->update();
        $this->root->updateAllAssignments();

        $this->assertEquals($NEW_AMOUNT_OF_POINTS_1, $node2_progress1->getAmountOfPoints());
        $this->assertEquals($NEW_AMOUNT_OF_POINTS_2, $node2_progress2->getAmountOfPoints());
    }

    /**
     * QUA-Objekte, welche "Inaktiv" sind können bei Studierenden-Studienplänen nicht von
     * "nicht relevant" auf irgendeinen anderen Status  gesetzt werden.
     *
     * @expectedException ilException
     */
    public function testOutdatedNodesCantBeSetToRelevant()
    {
        $this->setAllNodesActive();
        $this->node1->setStatus(ilStudyProgramme::STATUS_OUTDATED);
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $progress = $this->node1->getProgressForAssignment($ass1->getId());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $progress->getStatus());
        $progress->markAccredited($this->user->getId());
    }

    // Hinweis bei der bei der Studierenden-Instanz des Studienplanes, falls dieser vom
    // Original-Studienplan abweicht.
    public function testHasDeviationToProgram1()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $progress = $this->node1->getProgressForAssignment($ass1->getId());
        $this->assertFalse($progress->hasIndividualModifications());
    }

    public function testHasDeviationToProgram2()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $progress = $this->node1->getProgressForAssignment($ass1->getId());
        $progress->setRequiredAmountOfPoints(1000, $this->user->getId());
        $this->assertTrue($progress->hasIndividualModifications());
    }

    public function testHasDeviationToProgram3()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $progress = $this->node1->getProgressForAssignment($ass1->getId());
        $progress->markNotRelevant($this->user->getId());
        $this->assertTrue($progress->hasIndividualModifications());
    }

    public function testHasDeviationToProgram4()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass1 = $tmp[0];
        $user1 = $tmp[1];

        $progress = $this->node1->getProgressForAssignment($ass1->getId());
        $progress->markAccredited($this->user->getId());
        $this->assertFalse($progress->hasIndividualModifications());
    }

    public function testGetNamesOfCompletedOrAccreditedChildren()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $user2 = $this->newUser();
        $USER_ID = $user2->getId();

        $this->node1->setTitle("node1");
        $this->node1->update();
        $this->node2->setTitle("node2");
        $this->node2->update();

        $names = $this->root->getProgressForAssignment($ass->getId())
                    ->getNamesOfCompletedOrAccreditedChildren();
        $this->assertEquals($names, array());

        $this->node1->getProgressForAssignment($ass->getId())->markAccredited($USER_ID);
        $names = $this->root->getProgressForAssignment($ass->getId())
                    ->getNamesOfCompletedOrAccreditedChildren();
        $this->assertEquals($names, array("node1"));

        $this->node2->getProgressForAssignment($ass->getId())->markAccredited($USER_ID);
        $names = $this->root->getProgressForAssignment($ass->getId())
                    ->getNamesOfCompletedOrAccreditedChildren();
        $this->assertEquals($names, array("node1", "node2"));
    }

    public function testCompletionOnDeeplyNestedProgresses()
    {
        $depth1 = ilObjStudyProgramme::createInstance();
        $depth2 = ilObjStudyProgramme::createInstance();
        $depth3 = ilObjStudyProgramme::createInstance();
        $depth4 = ilObjStudyProgramme::createInstance();
        $depth1->putInTree(ROOT_FOLDER_ID);
        $depth1->addNode($depth2);
        $depth2->addNode($depth3);
        $depth3->addNode($depth4);
        $depth1->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $depth2->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $depth3->setStatus(ilStudyProgramme::STATUS_ACTIVE);
        $depth4->setStatus(ilStudyProgramme::STATUS_ACTIVE);

        $user = $this->newUser();

        $assignment = $depth1->assignUser($user->getId());
        $progress4 = $depth4->getProgressForAssignment($assignment->getId());
        $progress4->markAccredited(6);

        $progress1 = $depth1->getProgressForAssignment($assignment->getId());
        $progress2 = $depth2->getProgressForAssignment($assignment->getId());
        $progress3 = $depth3->getProgressForAssignment($assignment->getId());

        $this->assertTrue($progress1->isSuccessful());
        $this->assertTrue($progress2->isSuccessful());
        $this->assertTrue($progress3->isSuccessful());
        $this->assertTrue($progress4->isSuccessful());

        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $progress1->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $progress2->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $progress3->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgramme::DEFAULT_POINTS, $progress4->getCurrentAmountOfPoints());
    }


    public function testPossibleActions()
    {
        //node is root-node, status is "not relevant"
        $expected = array(
            ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN,
            ilStudyProgrammeUserProgress::ACTION_REMOVE_USER
        );
        $this->assertEquals(
            $expected,
            ilStudyProgrammeUserProgress::getPossibleActions(
                1,
                1,
                ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
            )
        );

        //node is root-node, status is "in progress"
        $expected = array(
            ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN,
            ilStudyProgrammeUserProgress::ACTION_REMOVE_USER,
            ilStudyProgrammeUserProgress::ACTION_MARK_ACCREDITED
        );
        $this->assertEquals(
            $expected,
            ilStudyProgrammeUserProgress::getPossibleActions(
                1,
                1,
                ilStudyProgrammeProgress::STATUS_IN_PROGRESS
            )
        );

        //node is root-node, status is "accredited"
        $expected = array(
            ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN,
            ilStudyProgrammeUserProgress::ACTION_REMOVE_USER,
            ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED
        );
        $this->assertEquals(
            $expected,
            ilStudyProgrammeUserProgress::getPossibleActions(
                1,
                1,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            )
        );

        //node is _not_ root-node, status is "accredited"
        $expected = array(
            ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED
        );
        $this->assertEquals(
            $expected,
            ilStudyProgrammeUserProgress::getPossibleActions(
                0,
                1,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            )
        );
    }

    //get progress instance via DB-class
    public function testGetInstance()
    {
        $this->setAllNodesActive();
        $tmp = $this->assignNewUserToRoot();
        $ass = $tmp[0];
        $user = $tmp[1];

        $sp_user_progress_db = new ilStudyProgrammeUserProgressDB();
        $inst = $sp_user_progress_db->getInstance(
            $ass->getId(),
            $this->root->getId(),
            $user->getId()
        );
        $this->assertInstanceOf(ilStudyProgrammeUserProgress::class, $inst);
        $this->assertEquals(
            $this->root->getProgressesOf($user->getId()),
            $sp_user_progress_db->getInstancesForUser($this->root->getId(), $user->getId())
        );

        $up = $this->root->getProgressesOf($user->getId())[0];
        $this->assertEquals(
            $up,
            $sp_user_progress_db->getInstanceById($up->getId())
        );
    }

    public function testGetInstanceCalls()
    {
        $sp_user_progress_db = new ilStudyProgrammeUserProgressDB();
        try {
            $sp_user_progress_db->getInstanceById(-1);
            $this->assertFalse("This should not happen");
        } catch (ilException $e) {
            $this->assertTrue(true);
        }


        try {
            $sp_user_progress_db->getInstancesForAssignment(-1);
            $this->assertFalse("This should not happen");
        } catch (ilStudyProgrammeNoProgressForAssignmentException $e) {
            $this->assertTrue(true);
        }
    }
}
