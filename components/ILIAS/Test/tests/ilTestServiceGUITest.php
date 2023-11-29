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
 * Class ilTestServiceGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestServiceGUITest extends ilTestBaseTestCase
{
    private ilTestServiceGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_ilAccess();
        $this->addGlobal_tpl();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_tree();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilLog();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_ilNavigationHistory();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $this->testObj = new ilTestServiceGUI($this->getTestObjMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestServiceGUI::class, $this->testObj);
    }

    public function testContextResultPresentation(): void
    {
        $this->testObj->setContextResultPresentation(false);
        $this->assertFalse($this->testObj->isContextResultPresentation());

        $this->testObj->setContextResultPresentation(true);
        $this->assertTrue($this->testObj->isContextResultPresentation());
    }

    public function testParticipantData(): void
    {
        $mock = $this->createMock(ilTestParticipantData::class);
        $this->testObj->setParticipantData($mock);
        $this->assertEquals($mock, $this->testObj->getParticipantData());
    }

    public function testObjectiveOrientedContainer(): void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveOrientedContainer($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveOrientedContainer());
    }

    public function testGetCommand(): void
    {
        $cmd = 'testCmd';
        $this->assertEquals($cmd, $this->testObj->getCommand($cmd));
    }

    /**
     * @dataProvider buildFixedShufflerSeedDataProvider
     */
    public function testBuildFixedShufflerSeed(int $question_id, int $pass_id, int $active_id, int $return): void
    {
        $reflection = new ReflectionClass(ilTestShuffler::class);
        $method = $reflection->getMethod('buildFixedShufflerSeed');
        $ilTestShuffler = new ilTestShuffler($this->createMock(ILIAS\Refinery\Factory::class));

        $this->assertEquals($return, $method->invoke($ilTestShuffler, $question_id, $pass_id, $active_id));
    }

    public function buildFixedShufflerSeedDataProvider(): array
    {
        return [
            [
                'question_id' => 1,
                'pass_id' => 1,
                'active_id' => 1,
                'return' => 10000004
            ],
            [
                'question_id' => 9999999,
                'pass_id' => 1,
                'active_id' => 1,
                'return' => 10000000
            ],
            [
                'question_id' => 234342342342342334,
                'pass_id' => 11,
                'active_id' => 1634545234234232344,
                'return' => 1634545234234232355
            ],
            [
                'question_id' => 23434234,
                'pass_id' => 11,
                'active_id' => 9223372036854775804,
                'return' => 9223372036854775804
            ]
        ];
    }
}