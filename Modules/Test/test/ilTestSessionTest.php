<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSessionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionTest extends ilTestBaseTestCase
{
    private ilTestSession $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSession();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSession::class, $this->testObj);
    }

    public function testRefId() : void
    {
        $this->testObj->setRefId(20);
        $this->assertEquals(20, $this->testObj->getRefId());
    }

    public function testActiveId() : void
    {
        $this->testObj->active_id = 20;
        $this->assertEquals(20, $this->testObj->getActiveId());
    }

    public function testUserId() : void
    {
        $this->testObj->setUserId(20);
        $this->assertEquals(20, $this->testObj->getUserId());
    }

    public function testTestId() : void
    {
        $this->testObj->setTestId(20);
        $this->assertEquals(20, $this->testObj->getTestId());
    }

    public function testAnonymousId() : void
    {
        $this->testObj->setAnonymousId(20);
        $this->assertEquals(20, $this->testObj->getAnonymousId());
    }

    public function testLastSequence() : void
    {
        $this->testObj->setLastSequence(20);
        $this->assertEquals(20, $this->testObj->getLastSequence());
    }

    public function testPass() : void
    {
        $this->testObj->setPass(20);
        $this->assertEquals(20, $this->testObj->getPass());
    }

    public function testIncreasePass() : void
    {
        $this->testObj->setPass(20);
        $this->assertEquals(20, $this->testObj->getPass());

        $this->testObj->increasePass();
        $this->assertEquals(21, $this->testObj->getPass());
    }

    public function testSubmitted() : void
    {
        $this->assertFalse($this->testObj->isSubmitted());
        $this->testObj->setSubmitted();

        $this->assertTrue($this->testObj->isSubmitted());
    }

    public function testSubmittedTimestamp() : void
    {
        $this->assertEmpty($this->testObj->getSubmittedTimestamp());
        $this->testObj->setSubmittedTimestamp();

        $this->assertIsString($this->testObj->getSubmittedTimestamp());
    }

    public function testLastFinishedPass() : void
    {
        $this->testObj->setLastFinishedPass(20);
        $this->assertEquals(20, $this->testObj->getLastFinishedPass());
    }

    public function testObjectiveOrientedContainerId() : void
    {
        $this->testObj->setObjectiveOrientedContainerId(20);
        $this->assertEquals(20, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testLastStartedPass() : void
    {
        $this->testObj->setLastStartedPass(20);
        $this->assertEquals(20, $this->testObj->getLastStartedPass());
    }

    public function testIsObjectiveOriented() : void
    {
        $this->assertFalse($this->testObj->isObjectiveOriented());

        $this->testObj->setObjectiveOrientedContainerId(20);
        $this->assertTrue($this->testObj->isObjectiveOriented());
    }

    public function testSetAccessCodeToSession() : void
    {
        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = "";
        $this->testObj->setAccessCodeToSession(17);
        $this->assertEquals([17], $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX]);
    }

    public function testUnsetAccessCodeInSession() : void
    {
        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = "";
        $this->testObj->setAccessCodeToSession(17);
        $this->assertEquals([17], $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX]);

        $this->testObj->unsetAccessCodeInSession();
        $this->assertEmpty($_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX]);
    }

    public function testGetAccessCodeFromSession() : void
    {
        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = "";
        $this->assertNull($this->testObj->getAccessCodeFromSession());

        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = [];
        $this->assertNull($this->testObj->getAccessCodeFromSession());

        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = [0 => 17];
        $this->assertEquals(17, $this->testObj->getAccessCodeFromSession());
    }

    public function testDoesAccessCodeInSessionExists() : void
    {
        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = "";
        $this->assertFalse($this->testObj->doesAccessCodeInSessionExists());

        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = [];
        $this->assertFalse($this->testObj->doesAccessCodeInSessionExists());

        $_SESSION[ilTestSession::ACCESS_CODE_SESSION_INDEX] = [0 => 17];
        $this->assertTrue($this->testObj->doesAccessCodeInSessionExists());
    }

    public function testIsAnonymousUser() : void
    {
        $this->assertFalse($this->testObj->isAnonymousUser());

        $this->testObj->setUserId(ANONYMOUS_USER_ID);
        $this->assertTrue($this->testObj->isAnonymousUser());
    }
}