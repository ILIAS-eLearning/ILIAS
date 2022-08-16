<?php declare(strict_types=1);

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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\Modules\Test\CanAccessFileUploadAnswer;
use ILIAS\DI\Container;
use ilAccessHandler;
use ilObjUser;
use ilTestSession;
use ilDBInterface;
use ilDBStatement;

class CanAccessFileUploadAnswerTest extends TestCase
{
    public function testConstruct() : void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(CanAccessFileUploadAnswer::class, new CanAccessFileUploadAnswer($container));
    }

    public function testNoUploadPath() : void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();

        $instance = new CanAccessFileUploadAnswer($container);

        $this->assertTrue($instance->isTrue('/data/some/path/file.pdf')->isError());
    }

    public function testFalseWithZeroAsTestId() : void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();

        $instance = new CanAccessFileUploadAnswer($container);

        $object_id_of_test_id = function () : void {
            $this->assertFalse('Should not be called.');
        };

        $this->assertFalse($instance->isTrue('/data/assessment/tst_0/ignored/file.mp3')->value());
    }

    public function testFalseWithInvalidTestId() : void
    {
        $called = false;
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 0;
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id);

        $this->assertFalse($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
    }

    public function testCantRead() : void
    {
        $called = false;

        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->expects(self::once())->method('checkAccess')->with('read', '', '678')->willReturn(false);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('access')->willReturn($access);

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 934;
        };

        $references_of = function (int $object_id) : array {
            $this->assertEquals(934, $object_id);
            return ['678'];
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id, $references_of);

        $this->assertFalse($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
    }

    public function testAnonymousWithoutAccessCode() : void
    {
        $called = false;

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->expects(self::never())->method('getId');
        $user->expects(self::once())->method('isAnonymous')->willReturn(true);

        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->expects(self::once())->method('checkAccess')->with('read', '', '678')->willReturn(true);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('access')->willReturn($access);
        $container->expects(self::once())->method('user')->willReturn($user);

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 934;
        };

        $references_of = function (int $object_id) : array {
            $this->assertEquals(934, $object_id);
            return ['678'];
        };

        $session = function (string $key) : ?array {
            $this->assertEquals(ilTestSession::ACCESS_CODE_SESSION_INDEX, $key);

            return null;
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id, $references_of, $session);

        $this->assertFalse($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
    }

    public function testAnonymousWithInvalidAccessCode() : void
    {
        $called = false;

        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();

        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->expects(self::once())->method('queryF')->willReturnCallback(function (string $query, array $types, array $values) use ($statement) : ilDBStatement {
            $this->assertEquals([8389, 'file.mp3', 'Random access code.', 8], $values);

            return $statement;
        });
        $database->expects(self::once())->method('numRows')->with($statement)->willReturn(0);

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->expects(self::once())->method('getId')->willReturn(8389);
        $user->expects(self::once())->method('isAnonymous')->willReturn(true);

        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->expects(self::once())->method('checkAccess')->with('read', '', '678')->willReturn(true);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('access')->willReturn($access);
        $container->method('user')->willReturn($user);
        $container->method('database')->willReturn($database);

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 934;
        };

        $references_of = function (int $object_id) : array {
            $this->assertEquals(934, $object_id);
            return ['678'];
        };

        $session = function (string $key) : ?array {
            $this->assertEquals(ilTestSession::ACCESS_CODE_SESSION_INDEX, $key);

            return [8 => 'Random access code.'];
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id, $references_of, $session);

        $this->assertFalse($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
    }

    public function testAnonymousWithValidAccessCode() : void
    {
        $called = false;

        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();

        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->expects(self::once())->method('queryF')->willReturnCallback(function (string $query, array $types, array $values) use ($statement) : ilDBStatement {
            $this->assertEquals([8389, 'file.mp3', 'Random access code.', 8], $values);

            return $statement;
        });
        $database->expects(self::once())->method('numRows')->with($statement)->willReturn(1);

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->expects(self::once())->method('getId')->willReturn(8389);
        $user->expects(self::once())->method('isAnonymous')->willReturn(true);

        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->expects(self::once())->method('checkAccess')->with('read', '', '678')->willReturn(true);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('access')->willReturn($access);
        $container->method('user')->willReturn($user);
        $container->method('database')->willReturn($database);

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 934;
        };

        $references_of = function (int $object_id) : array {
            $this->assertEquals(934, $object_id);
            return ['678'];
        };

        $session = function (string $key) : ?array {
            $this->assertEquals(ilTestSession::ACCESS_CODE_SESSION_INDEX, $key);

            return [8 => 'Random access code.'];
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id, $references_of, $session);

        $this->assertTrue($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
    }

    public function testUserWhichCanAccessTheTestResults() : void
    {
        $called = false;
        $checkResultsAccessCalled = false;

        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();

        $database = $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
        $database->expects(self::once())->method('queryF')->willReturnCallback(function (string $query, array $types, array $values) use ($statement) : ilDBStatement {
            $this->assertEquals(['assFileUpload', 8], $values);

            return $statement;
        });
        $database->expects(self::once())->method('fetchAssoc')->with($statement)->willReturn([
            'active_fi' => '11111',
            'value1' => 'file.mp3',
        ]);

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $user->method('getId')->willReturn(8389);
        $user->expects(self::once())->method('isAnonymous')->willReturn(false);

        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->expects(self::once())->method('checkAccess')->with('read', '', '678')->willReturn(true);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('access')->willReturn($access);
        $container->method('user')->willReturn($user);
        $container->method('database')->willReturn($database);

        $object_id_of_test_id = function (int $test) use (&$called) : int {
            $this->assertEquals(8, $test);
            $called = true;
            return 934;
        };

        $references_of = function (int $object_id) : array {
            $this->assertEquals(934, $object_id);
            return ['678'];
        };

        $session = function (string $key) : ?array {
            $this->assertEquals(ilTestSession::ACCESS_CODE_SESSION_INDEX, $key);

            return [8 => 'Random access code.'];
        };

        $checkResultsAccess = function (string $reference, int $test, int $active_id) use (&$checkResultsAccessCalled) : bool {
            $checkResultsAccessCalled = true;
            $this->assertEquals('678', $reference);
            $this->assertEquals(8, $test);
            $this->assertEquals(11111, $active_id);

            return true;
        };

        $instance = new CanAccessFileUploadAnswer($container, $object_id_of_test_id, $references_of, $session, $checkResultsAccess);

        $this->assertTrue($instance->isTrue('/data/assessment/tst_8/ignored/file.mp3')->value());
        $this->assertTrue($called);
        $this->assertTrue($checkResultsAccessCalled);
    }
}
