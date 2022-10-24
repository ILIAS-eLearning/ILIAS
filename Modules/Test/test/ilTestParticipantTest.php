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
 * ******************************************************************* */

/**
 * Class ilTestParticipantTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantTest extends ilTestBaseTestCase
{
    private ilTestParticipant $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipant();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipant::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId("125");
        $this->assertEquals("125", $this->testObj->getActiveId());
    }

    public function testAnonymousId(): void
    {
        $this->testObj->setAnonymousId("125");
        $this->assertEquals("125", $this->testObj->getAnonymousId());
    }

    public function testUsrId(): void
    {
        $this->testObj->setUsrId("125");
        $this->assertEquals("125", $this->testObj->getUsrId());
    }

    public function testLogin(): void
    {
        $this->testObj->setLogin("testLogin");
        $this->assertEquals("testLogin", $this->testObj->getLogin());
    }

    public function testLastname(): void
    {
        $this->testObj->setLastname("testLastname");
        $this->assertEquals("testLastname", $this->testObj->getLastname());
    }

    public function testFirstname(): void
    {
        $this->testObj->setFirstname("testFirstname");
        $this->assertEquals("testFirstname", $this->testObj->getFirstname());
    }

    public function testMatriculation(): void
    {
        $this->testObj->setMatriculation("testMatriculation");
        $this->assertEquals("testMatriculation", $this->testObj->getMatriculation());
    }

    public function testActiveStatus(): void
    {
        $this->testObj->setActiveStatus(false);
        $this->assertFalse($this->testObj->isActiveStatus());

        $this->testObj->setActiveStatus(true);
        $this->assertTrue($this->testObj->isActiveStatus());
    }

    public function testClientIp(): void
    {
        $this->testObj->setClientIp("127.0.0.1");
        $this->assertEquals("127.0.0.1", $this->testObj->getClientIp());
    }

    public function testFinishedTries(): void
    {
        $this->testObj->setFinishedTries(125);
        $this->assertEquals(125, $this->testObj->getFinishedTries());
    }

    public function testTestFinished(): void
    {
        $this->testObj->setTestFinished(false);
        $this->assertFalse($this->testObj->isTestFinished());

        $this->testObj->setTestFinished(true);
        $this->assertTrue($this->testObj->isTestFinished());
    }

    public function testUnfinishedPasses(): void
    {
        $this->testObj->setUnfinishedPasses(false);
        $this->assertFalse($this->testObj->hasUnfinishedPasses());

        $this->testObj->setUnfinishedPasses(true);
        $this->assertTrue($this->testObj->hasUnfinishedPasses());
    }

    public function testScoring(): void
    {
        $mock = $this->createMock(ilTestParticipantScoring::class);
        $this->testObj->setScoring($mock);
        $this->assertEquals($mock, $this->testObj->getScoring());
    }

    public function testHasScoring(): void
    {
        $mock = $this->createMock(ilTestParticipantScoring::class);
        $this->assertFalse($this->testObj->hasScoring());

        $this->testObj->setScoring($mock);
        $this->assertEquals($mock, $this->testObj->getScoring());
        $this->assertTrue($this->testObj->hasScoring());
    }
}
