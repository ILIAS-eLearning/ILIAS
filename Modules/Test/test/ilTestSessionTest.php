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
 * Class ilTestSessionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionTest extends ilTestBaseTestCase
{
    private ilTestSession $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSession();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSession::class, $this->testObj);
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(20);
        $this->assertEquals(20, $this->testObj->getRefId());
    }

    public function testActiveId(): void
    {
        $this->testObj->active_id = 20;
        $this->assertEquals(20, $this->testObj->getActiveId());
    }

    public function testUserId(): void
    {
        $this->testObj->setUserId(20);
        $this->assertEquals(20, $this->testObj->getUserId());
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(20);
        $this->assertEquals(20, $this->testObj->getTestId());
    }

    public function testAnonymousId(): void
    {
        $this->testObj->setAnonymousId(20);
        $this->assertEquals(20, $this->testObj->getAnonymousId());
    }

    public function testLastSequence(): void
    {
        $this->testObj->setLastSequence(20);
        $this->assertEquals(20, $this->testObj->getLastSequence());
    }

    public function testPass(): void
    {
        $this->testObj->setPass(20);
        $this->assertEquals(20, $this->testObj->getPass());
    }

    public function testIncreasePass(): void
    {
        $this->testObj->setPass(20);
        $this->assertEquals(20, $this->testObj->getPass());

        $this->testObj->increasePass();
        $this->assertEquals(21, $this->testObj->getPass());
    }

    public function testSubmitted(): void
    {
        $this->assertFalse($this->testObj->isSubmitted());
        $this->testObj->setSubmitted();

        $this->assertTrue($this->testObj->isSubmitted());
    }

    public function testSubmittedTimestamp(): void
    {
        $this->assertEmpty($this->testObj->getSubmittedTimestamp());
        $this->testObj->setSubmittedTimestamp();

        $this->assertIsString($this->testObj->getSubmittedTimestamp());
    }

    public function testLastFinishedPass(): void
    {
        $this->testObj->setLastFinishedPass(20);
        $this->assertEquals(20, $this->testObj->getLastFinishedPass());
    }

    public function testObjectiveOrientedContainerId(): void
    {
        $this->testObj->setObjectiveOrientedContainerId(20);
        $this->assertEquals(20, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testLastStartedPass(): void
    {
        $this->testObj->setLastStartedPass(20);
        $this->assertEquals(20, $this->testObj->getLastStartedPass());
    }

    public function testIsObjectiveOriented(): void
    {
        $this->assertFalse($this->testObj->isObjectiveOriented());

        $this->testObj->setObjectiveOrientedContainerId(20);
        $this->assertTrue($this->testObj->isObjectiveOriented());
    }

    public function testSetAccessCodeToSession(): void
    {
        ilSession::set(ilTestSession::ACCESS_CODE_SESSION_INDEX, "");
        $this->testObj->setAccessCodeToSession(17);
        $this->assertEquals([17], ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));
    }

    public function testUnsetAccessCodeInSession(): void
    {
        ilSession::set(ilTestSession::ACCESS_CODE_SESSION_INDEX, "");
        $this->testObj->setAccessCodeToSession(17);
        $this->assertEquals([17], ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));

        $this->testObj->unsetAccessCodeInSession();
        $this->assertEmpty(ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));
    }

    public function testIsAnonymousUser(): void
    {
        $this->assertFalse($this->testObj->isAnonymousUser());

        $this->testObj->setUserId(ANONYMOUS_USER_ID);
        $this->assertTrue($this->testObj->isAnonymousUser());
    }
}
