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
 * ******************************************************************* */

declare(strict_types=1);

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
        $active_id = 125;
        $this->testObj->setActiveId($active_id);
        $this->assertEquals($active_id, $this->testObj->getActiveId());
    }

    public function testAnonymousId(): void
    {
        $anonymous_id = 125;
        $this->testObj->setAnonymousId($anonymous_id);
        $this->assertEquals($anonymous_id, $this->testObj->getAnonymousId());
    }

    public function testUsrId(): void
    {
        $usr_id = 125;
        $this->testObj->setUsrId($usr_id);
        $this->assertEquals($usr_id, $this->testObj->getUsrId());
    }

    public function testLogin(): void
    {
        $login = 'testLogin';
        $this->testObj->setLogin($login);
        $this->assertEquals($login, $this->testObj->getLogin());
    }

    public function testLastname(): void
    {
        $lastname = 'testLastname';
        $this->testObj->setLastname($lastname);
        $this->assertEquals($lastname, $this->testObj->getLastname());
    }

    public function testFirstname(): void
    {
        $firstname = 'testFirstname';
        $this->testObj->setFirstname($firstname);
        $this->assertEquals($firstname, $this->testObj->getFirstname());
    }

    public function testMatriculation(): void
    {
        $matriculation = 'testMatriculation';
        $this->testObj->setMatriculation($matriculation);
        $this->assertEquals($matriculation, $this->testObj->getMatriculation());
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
        $client_id = '127.0.0.1';
        $this->testObj->setClientIp($client_id);
        $this->assertEquals($client_id, $this->testObj->getClientIp());
    }

    public function testFinishedTries(): void
    {
        $finished_tries = 125;
        $this->testObj->setFinishedTries($finished_tries);
        $this->assertEquals($finished_tries, $this->testObj->getFinishedTries());
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
