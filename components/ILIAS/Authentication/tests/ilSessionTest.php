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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

class ilSessionTest extends TestCase
{
    private ?Container $dic_backup = null;

    protected function setUp(): void
    {
        global $DIC;

        $this->dic_backup = $DIC;

        if (!isset($DIC)) {
            $DIC = new Container();
        }

        $this->setGlobalVariable(
            'ilDB',
            $this->createMock(ilDBInterface::class)
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic_backup;

        parent::tearDown();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function testBasicSessionBehaviour(): void
    {
        global $DIC;

        $this->setGlobalVariable(
            'ilClientIniFile',
            $this->getMockBuilder(ilIniFile::class)->disableOriginalConstructor()->getMock()
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject&ilSetting $setting */
        $settings = $this->getMockBuilder(ilSetting::class)->getMock();
        $settings->method('get')->willReturnCallback(
            function ($arg) {
                if ($arg === 'session_statistics') {
                    return '0';
                }

                throw new \RuntimeException($arg);
            }
        );
        $this->setGlobalVariable(
            'ilSetting',
            $settings
        );

        /** @var \PHPUnit\Framework\MockObject\MockObject&ilDBInterface $ilDB  */
        $ilDB = $DIC['ilDB'];
        $ilDB->method('update')->
            with('usr_session')->willReturn(1);


        $consecutive_quote = [
            '123456',
            '123456',
            '123456',
            'e10adc3949ba59abbe56e057f20f883e',
            '123456',
            'e10adc3949ba59abbe56e057f20f883e',
            'e10adc3949ba59abbe56e057f20f883e',
            '123456',
            '123456',
            '123456',
            time() - 100,
            'e10adc3949ba59abbe56e057f20f883e',
            17,
            'e10adc3949ba59abbe56e057f20f883e'
        ];
        $ilDB->method('quote')->with(
            $this->callback(function ($value) use (&$consecutive_quote) {
                if (count($consecutive_quote) === 4) {
                    $this->assertGreaterThan(array_shift($consecutive_quote), $value);
                } else {
                    $this->assertSame(array_shift($consecutive_quote), $value);
                }
                return true;
            })
        )->willReturnOnConsecutiveCalls(
            '123456',
            '123456',
            '123456',
            'e10adc3949ba59abbe56e057f20f883e',
            'e10adc3949ba59abbe56e057f20f883e',
            '123456',
            'e10adc3949ba59abbe56e057f20f883e',
            'e10adc3949ba59abbe56e057f20f883e',
            '123456',
            '123456',
            '123456',
            (string) time(),
            'e10adc3949ba59abbe56e057f20f883e',
            '17',
            'e10adc3949ba59abbe56e057f20f883e'
        );
        $ilDB->expects($this->exactly(6))->method('numRows')->willReturn(1, 1, 1, 0, 1, 0);

        $consecutive_select = [
            'SELECT 1 FROM usr_session WHERE session_id = 123456',
            'SELECT 1 FROM usr_session WHERE session_id = 123456',
            'SELECT data FROM usr_session WHERE session_id = 123456',
            'SELECT * FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e',
            'SELECT * FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e',
            'SELECT 1 FROM usr_session WHERE session_id = 123456',
            'SELECT data FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e',
            'SELECT 1 FROM usr_session WHERE session_id = 123456',
            'SELECT session_id, expires FROM usr_session WHERE expires < 123456',
            'SELECT 1 FROM usr_session WHERE session_id = ',
            'SELECT 1 FROM usr_session WHERE session_id = 17'
        ];
        $ilDB->method('query')->with(
            $this->callback(function ($value) use (&$consecutive_select) {
                if (count($consecutive_select) === 2) {
                    $this->assertStringStartsWith(array_shift($consecutive_select), $value);
                } else {
                    $this->assertSame(array_shift($consecutive_select), $value);
                }
                return true;
            })
        )->willReturnOnConsecutiveCalls(
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class),
            $this->createMock(ilDBStatement::class)
        );
        $ilDB->expects($this->exactly(4))->method('fetchAssoc')->willReturn(
            ['data' => 'Testdata'],
            [],
            ['data' => 'Testdata'],
            []
        );
        $ilDB->expects($this->once())->method('fetchObject')->willReturn(
            (object) [
                'data' => 'Testdata'
            ]
        );

        $consecutive_delete = [
            'DELETE FROM usr_sess_istorage WHERE session_id = 123456',
            'DELETE FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e',
            'DELETE FROM usr_session WHERE user_id = e10adc3949ba59abbe56e057f20f883e'
        ];
        $ilDB->method('manipulate')->with(
            $this->callback(function ($value) use (&$consecutive_delete) {
                $this->assertSame(array_shift($consecutive_delete), $value);
                return true;
            })
        )->willReturnOnConsecutiveCalls(
            1,
            1,
            1
        );

        $cron_manager = $this->createMock(\ILIAS\Cron\Job\JobManager::class);
        $cron_manager->method('isJobActive')->with($this->isType('string'))->willReturn(true);
        $this->setGlobalVariable(
            'cron.manager',
            $cron_manager
        );

        $result = '';
        ilSession::_writeData('123456', 'Testdata');
        if (ilSession::_exists('123456')) {
            $result .= 'exists-';
        }
        if (ilSession::_getData('123456') === 'Testdata') {
            $result .= 'write-get-';
        }
        $duplicate = ilSession::_duplicate('123456');
        if (ilSession::_getData($duplicate) === 'Testdata') {
            $result .= 'duplicate-';
        }
        ilSession::_destroy('123456');
        if (!ilSession::_exists('123456')) {
            $result .= 'destroy-';
        }
        ilSession::_destroyExpiredSessions();
        if (ilSession::_exists($duplicate)) {
            $result .= 'destroyExp-';
        }

        ilSession::_destroyByUserId(17);
        if (!ilSession::_exists($duplicate)) {
            $result .= 'destroyByUser-';
        }

        $this->assertEquals('exists-write-get-duplicate-destroy-destroyExp-destroyByUser-', $result);
    }

    public function testPasswordAssisstanceSession(): void
    {
        $actual = '';
        $usr_id = 4711;

        try {
            $sqlite = new PDO('sqlite::memory:');
            $create_table = <<<SQL
create table usr_pwassist
(
    pwassist_id char(180) default '' not null primary key,
    expires     int       default 0  not null,
    ctime       int       default 0  not null,
    user_id     int       default 0  not null,
    constraint c1_idx
        unique (user_id)
);
SQL;

            $sqlite->query($create_table);
        } catch (Exception $e) {
            $this->markTestIncomplete(
                'Cannot test the password assistance session storage because of missing sqlite: ' . $e->getMessage()
            );
        }

        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static function ($value, ?string $type = null) use ($sqlite): string {
            if ($value === null) {
                return 'NULL';
            }

            $pdo_type = PDO::PARAM_STR;
            switch ($type) {
                case ilDBConstants::T_TIMESTAMP:
                case ilDBConstants::T_DATETIME:
                case ilDBConstants::T_DATE:
                    if ($value === '') {
                        return 'NULL';
                    }
                    if ($value === 'NOW()') {
                        return $value;
                    }
                    $value = (string) $value;
                    break;
                case ilDBConstants::T_INTEGER:
                    return (string) (int) $value;
                case ilDBConstants::T_FLOAT:
                    $pdo_type = PDO::PARAM_INT;
                    $value = (string) $value;
                    break;
                case ilDBConstants::T_TEXT:
                default:
                    $value = (string) $value;
                    $pdo_type = PDO::PARAM_STR;
                    break;
            }

            return $sqlite->quote((string) $value, $pdo_type);
        });
        $db->method('query')->willReturnCallback(static function (string $query) use ($sqlite): ilDBStatement {
            return new ilPDOStatement($sqlite->query($query));
        });
        $db->method('manipulate')->willReturnCallback(static function (string $query) use ($sqlite): int {
            return (int) $sqlite->exec($query);
        });
        $db->method('manipulateF')->willReturnCallback(static function (...$args) use ($db): int {
            $query = $args[0];

            $quoted_values = [];
            foreach ($args[1] as $k => $t) {
                $quoted_values[] = $db->quote($args[2][$k], $t);
            }
            $query = vsprintf($query, $quoted_values);

            return $db->manipulate($query);
        });
        $db->method('fetchAssoc')->willReturnCallback(static function (ilDBStatement $statement): ?array {
            $res = $statement->fetch(PDO::FETCH_ASSOC);
            if ($res === null || $res === false) {
                $statement->closeCursor();

                return null;
            }

            return $res;
        });

        $pwa_repository = new \ILIAS\Init\PasswordAssitance\Repository\PasswordAssistanceDbRepository(
            $db,
            (new \ILIAS\Data\Factory())->clock()->system()
        );

        $hash = new \ILIAS\Init\PasswordAssitance\ValueObject\PasswordAssistanceHash(
            'ae869e66007cc9812f1752f7a3a59f07d3e28bed8361827d0a05563e5c2f4b11'
        );
        $session = $pwa_repository->createSession(
            $hash,
            (new \ILIAS\Data\Factory())->objId($usr_id)
        );

        $result = $pwa_repository->getSessionByUsrId($session->usrId());
        if ($result->value()->hash()->value() === $session->hash()->value()) {
            $actual .= 'find-';
        }

        $result = $pwa_repository->getSessionByHash($session->hash());
        if ($result->value()->usrId()->toInt() === $usr_id) {
            $actual .= 'read-';
        }

        $pwa_repository->deleteSession($session);
        $result = $pwa_repository->getSessionByHash($session->hash());
        if ($result->isError()) {
            $actual .= 'destroy-';
        }

        $this->assertEquals('find-read-destroy-', $actual);

        $sqlite = null;
    }
}
