<?php

declare(strict_types=1);

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

/**
 * Class ilTestParticipantDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantDataTest extends ilTestBaseTestCase
{
    private ilTestParticipantData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantData(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantData::class, $this->testObj);
    }

    public function testParticipantAccessFilter(): void
    {
        $callback = static function () {
            return "Hello";
        };

        $this->testObj->setParticipantAccessFilter($callback);
        $this->assertEquals($callback, $this->testObj->getParticipantAccessFilter());
    }

    public function testScoredParticipantsFilterEnabled(): void
    {
        $this->testObj->setScoredParticipantsFilterEnabled(false);
        $this->assertFalse($this->testObj->isScoredParticipantsFilterEnabled());

        $this->testObj->setScoredParticipantsFilterEnabled(true);
        $this->assertTrue($this->testObj->isScoredParticipantsFilterEnabled());
    }

    public function testGetScoredParticipantsFilterExpression(): void
    {
        $this->assertEquals("1 = 1", $this->testObj->getScoredParticipantsFilterExpression());

        $this->testObj->setScoredParticipantsFilterEnabled(true);
        $this->assertEquals(
            "ta.last_finished_pass = ta.last_started_pass",
            $this->testObj->getScoredParticipantsFilterExpression()
        );
    }

    public function testActiveIdsFilter(): void
    {
        $expected = [1, 125, 1290];
        $this->testObj->setActiveIdsFilter($expected);
        $this->assertEquals($expected, $this->testObj->getActiveIdsFilter());
    }

    public function testUserIdsFilter(): void
    {
        $expected = [1, 125, 1290];
        $this->testObj->setUserIdsFilter($expected);
        $this->assertEquals($expected, $this->testObj->getUserIdsFilter());
    }

    public function testAnonymousIdsFilter(): void
    {
        $expected = [1, 125, 1290];
        $this->testObj->setAnonymousIdsFilter($expected);
        $this->assertEquals($expected, $this->testObj->getAnonymousIdsFilter());
    }
}
