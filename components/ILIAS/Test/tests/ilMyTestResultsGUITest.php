<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Class ilMyTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMyTestResultsGUITest extends ilTestBaseTestCase
{
    private ilMyTestResultsGUI $testObj;
    private ilObjTest $test;
    private ilTestAccess $access;
    private ilTestSession $session;
    private ilTestObjectiveOrientedContainer $objective_parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->test = $this->createMock(ilObjTest::class);
        $this->access = $this->createMock(ilTestAccess::class);
        $this->session = $this->createMock(ilTestSession::class);
        $this->objective_parent = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj = new ilMyTestResultsGUI(
            $this->test,
            $this->access,
            $this->session,
            $this->objective_parent,
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrlInterface::class),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(\ILIAS\Test\RequestDataCollector::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilMyTestResultsGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $this->assertEquals($this->test, $this->testObj->getTestObj());
    }

    public function testTestAccess(): void
    {
        $this->assertEquals($this->access, $this->testObj->getTestAccess());
    }

    public function testTestSession(): void
    {
        $this->assertEquals($this->session, $this->testObj->getTestSession());
    }

    public function testObjectiveParent(): void
    {
        $this->assertEquals($this->objective_parent, $this->testObj->getObjectiveParent());
    }
}
