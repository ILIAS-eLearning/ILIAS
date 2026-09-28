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
 * Class ilTestSessionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionTest extends ilTestBaseTestCase
{
    private ilTestSession $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_ilNavigationHistory();

        $this->testObj = new ilTestSession(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjUser::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSession::class, $this->testObj);
    }

    public function testRefId(): void
    {
        $ref_id = 20;
        $this->testObj->setRefId($ref_id);
        $this->assertEquals($ref_id, $this->testObj->getRefId());
    }

    public function testActiveId(): void
    {
        $active_id = 20;
        $this->testObj->active_id = $active_id;
        $this->assertEquals($active_id, $this->testObj->getActiveId());
    }

    public function testUserId(): void
    {
        $user_id = 20;
        $this->testObj->setUserId($user_id);
        $this->assertEquals($user_id, $this->testObj->getUserId());
    }

    public function testTestId(): void
    {
        $test_id = 20;
        $this->testObj->setTestId($test_id);
        $this->assertEquals($test_id, $this->testObj->getTestId());
    }

    public function testAnonymousId(): void
    {
        $anonymous_id = '20';
        $this->testObj->setAnonymousId($anonymous_id);
        $this->assertEquals($anonymous_id, $this->testObj->getAnonymousId());
    }

    public function testLastSequence(): void
    {
        $lastsequence = 20;
        $this->testObj->setLastSequence($lastsequence);
        $this->assertEquals($lastsequence, $this->testObj->getLastSequence());
    }

    public function testPass(): void
    {
        $pass = 20;
        $this->testObj->setPass($pass);
        $this->assertEquals($pass, $this->testObj->getPass());
    }

    public function testIncreasePass(): void
    {
        $pass = 20;
        $this->testObj->setPass($pass);
        $this->assertEquals($pass, $this->testObj->getPass());

        $this->testObj->increasePass();
        $this->assertEquals(++$pass, $this->testObj->getPass());
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
        $lastFinishedPass = 20;
        $this->testObj->setLastFinishedPass($lastFinishedPass);
        $this->assertEquals($lastFinishedPass, $this->testObj->getLastFinishedPass());
    }

    public function testObjectiveOrientedContainerId(): void
    {
        $objectiveOriented = 20;
        $this->testObj->setObjectiveOrientedContainerId($objectiveOriented);
        $this->assertEquals($objectiveOriented, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testLastStartedPass(): void
    {
        $lastStartedPass = 20;
        $this->testObj->setLastStartedPass($lastStartedPass);
        $this->assertEquals($lastStartedPass, $this->testObj->getLastStartedPass());
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
        $access_code = '17';
        $this->testObj->setAccessCodeToSession($access_code);
        $this->assertEquals([(int) $access_code], ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));
    }

    public function testUnsetAccessCodeInSession(): void
    {
        ilSession::set(ilTestSession::ACCESS_CODE_SESSION_INDEX, "");
        $access_code = '17';
        $this->testObj->setAccessCodeToSession($access_code);
        $this->assertEquals([(int) $access_code], ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));

        $this->testObj->unsetAccessCodeInSession();
        $this->assertEmpty(ilSession::get(ilTestSession::ACCESS_CODE_SESSION_INDEX));
    }

    public function testIsAnonymousUser(): void
    {
        $this->assertFalse($this->testObj->isAnonymousUser());

        $this->testObj->setUserId(ANONYMOUS_USER_ID);
        $this->assertTrue($this->testObj->isAnonymousUser());
    }

    public function testPasswordChecked(): void
    {
        $active_id = 20;
        $this->testObj->active_id = $active_id;
        $this->testObj->setPasswordChecked(true);
        $this->assertTrue(ilSession::get("pw_checked_$active_id"));
        $this->assertTrue($this->testObj->isPasswordChecked());
    }

    public function testCheckAccessRejectsAnonymousActiveIdWithoutAccessCode(): void
    {
        ilSession::set(ilTestSession::ACCESS_CODE_SESSION_INDEX, []);

        $this->expectException(ilTestException::class);
        $this->expectExceptionMessage('active id given does not relate to current anonymous session!');

        $this->createTestSession(ANONYMOUS_USER_ID, 99, 42, 'victim-code')->checkAccess(
            $this->getTestObject(42)
        );
    }

    public function testCheckAccessRejectsAnonymousActiveIdWithForeignAccessCode(): void
    {
        $session = $this->createTestSession(ANONYMOUS_USER_ID, 99, 42, 'victim-code');
        $session->setAccessCodeToSession('attacker-code');

        $this->expectException(ilTestException::class);
        $this->expectExceptionMessage('active id given does not relate to current anonymous session!');

        $session->checkAccess(
            $this->getTestObject(42)
        );
    }

    public function testCheckAccessAcceptsAnonymousActiveIdWithMatchingAccessCode(): void
    {
        $session = $this->createTestSession(ANONYMOUS_USER_ID, 99, 42, 'matching-code');
        $session->setAccessCodeToSession('matching-code');

        $session->checkAccess(
            $this->getTestObject(42)
        );

        $this->addToAssertionCount(1);
    }

    public function testCheckAccessRejectsActiveIdFromDifferentTest(): void
    {
        $session = $this->createTestSession(ANONYMOUS_USER_ID, 99, 99, 'matching-code');
        $session->setAccessCodeToSession('matching-code');

        $this->expectException(ilTestException::class);
        $this->expectExceptionMessage('active id given does not relate to current test!');
        $session->checkAccess(
            $this->getTestObject(42)
        );
    }

    public function testCheckAccessAllowsAuthenticatedUsers(): void
    {
        $this->createTestSession(6, 99, 42)->checkAccess(
            $this->getTestObject(42)
        );

        $this->addToAssertionCount(1);
    }

    public function testCheckAccessRejectsAuthenticatedUsersWithInconsistentUserId(): void
    {
        $session = $this->createTestSession(6, 99, 42);
        $session->setUserId(2);

        $this->expectException(ilTestException::class);
        $this->expectExceptionMessage('active id given does not relate to current user!');

        $session->checkAccess(
            $this->getTestObject(42)
        );

        $this->addToAssertionCount(1);
    }

    public function testCheckAccessAllowsAnonymousWithoutActiveId(): void
    {
        $this->createTestSession(ANONYMOUS_USER_ID, 0, 42)->checkAccess(
            $this->getTestObject(42)
        );

        $this->addToAssertionCount(1);
    }

    private function getTestObject(int $test_id): ilObjTest
    {
        $test = $this->getTestObjMock();
        $test->method('getTestId')->willReturn($test_id);
        return $test;
    }

    private function createTestSession(
        int $user_id,
        int $active_id,
        int $test_id,
        ?string $access_code = null
    ): ilTestSession {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn($user_id);
        $this->setGlobalVariable('ilUser', $user);

        $session = new ilTestSession(
            $this->createMock(ilDBInterface::class),
            $user
        );
        $session->setUserId($user_id);
        $session->setTestId($test_id);
        $session->active_id = $active_id;

        if ($access_code !== null) {
            $session->setAnonymousId($access_code);
        }

        return $session;
    }
}
