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

use ILIAS\DI\Container;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ilSessionTest
 */
class ilSessionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        //$this->setGlobalVariable('lng', $this->getLanguageMock());
        $this->setGlobalVariable(
            'ilCtrl',
            $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilUser',
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilDB',
            $this->getMockBuilder(ilDBInterface::class)->disableAutoReturnValueGeneration()->getMock()
        );
        $this->setGlobalVariable(
            'ilClientIniFile',
            $this->getMockBuilder(ilIniFile::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(\ILIAS\Administration\Setting::class)->getMock()
        );
        parent::setUp();
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
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function tstBasicSessionBehaviour(): void
    {
        global $DIC;

        //setup some method calls
        /** @var $setting MockObject */
        $setting = $DIC['ilSetting'];
        $setting->method("get")->willReturnCallback(
            function ($arg) {
                if ($arg === 'session_handling_type') {
                    return (string) ilSession::SESSION_HANDLING_FIXED;
                }
                if ($arg === 'session_statistics') {
                    return "0";
                }

                throw new \RuntimeException($arg);
            }
        );
        /** @var $ilDB MockObject */
        $data = null;
        $ilDB = $DIC['ilDB'];
        $ilDB->method("update")->
            with("usr_session")->willReturn(1);

        $ilDB->method("quote")->withConsecutive(
            ["123456"],
            ["123456"],
            ["123456"],
            ["e10adc3949ba59abbe56e057f20f883e"],
            ["123456"],
            ["e10adc3949ba59abbe56e057f20f883e"],
            ["e10adc3949ba59abbe56e057f20f883e"],
            ["123456"],
            ["123456"],
            ["123456"],
            [$this->greaterThan(time() - 100)],
            ["e10adc3949ba59abbe56e057f20f883e"],
            ["17"]
        )->
        willReturnOnConsecutiveCalls(
            "123456",
            "123456",
            "123456",
            "e10adc3949ba59abbe56e057f20f883e",
            "e10adc3949ba59abbe56e057f20f883e",
            "123456",
            "e10adc3949ba59abbe56e057f20f883e",
            "e10adc3949ba59abbe56e057f20f883e",
            "123456",
            "123456",
            "123456",
            (string) time(),
            "e10adc3949ba59abbe56e057f20f883e",
            "17"
        );
        $ilDB->expects($this->exactly(6))->method("numRows")->willReturn(1, 1, 1, 0, 1, 0);


        $ilDB->method("query")->withConsecutive(
            ["SELECT 1 FROM usr_session WHERE session_id = 123456"],
            ["SELECT 1 FROM usr_session WHERE session_id = 123456"],
            ["SELECT data FROM usr_session WHERE session_id = 123456"],
            ['SELECT * FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e'],
            ['SELECT * FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e'],
            ["SELECT 1 FROM usr_session WHERE session_id = 123456"],
            ['SELECT data FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e'],
            ['SELECT 1 FROM usr_session WHERE session_id = 123456'],
            ['SELECT session_id,expires FROM usr_session WHERE expires < 123456'],
            [$this->stringStartsWith('SELECT 1 FROM usr_session WHERE session_id = ')],
            ['SELECT 1 FROM usr_session WHERE session_id = 17']
        )->
            willReturnOnConsecutiveCalls(
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock(),
                $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock()
            );
        $ilDB->expects($this->exactly(4))->method("fetchAssoc")->willReturn(
            ["data" => "Testdata"],
            [],
            ["data" => "Testdata"],
            []
        );
        $ilDB->expects($this->exactly(1))->method("fetchObject")->willReturn((object) array(
            'data' => "Testdata"
        ));

        $ilDB->method("manipulate")->withConsecutive(
            ['DELETE FROM usr_sess_istorage WHERE session_id = 123456'],
            ['DELETE FROM usr_session WHERE session_id = e10adc3949ba59abbe56e057f20f883e'],
            ['DELETE FROM usr_session WHERE user_id = e10adc3949ba59abbe56e057f20f883e']
        )->
        willReturnOnConsecutiveCalls(
            1,
            1,
            1
        );

        $result = "";
        ilSession::_writeData("123456", "Testdata");
        if (ilSession::_exists("123456")) {
            $result .= "exists-";
        }
        if (ilSession::_getData("123456") === "Testdata") {
            $result .= "write-get-";
        }
        $duplicate = ilSession::_duplicate("123456");
        if (ilSession::_getData($duplicate) === "Testdata") {
            $result .= "duplicate-";
        }
        ilSession::_destroy("123456");
        if (!ilSession::_exists("123456")) {
            $result .= "destroy-";
        }
        ilSession::_destroyExpiredSessions();
        if (ilSession::_exists($duplicate)) {
            $result .= "destroyExp-";
        }

        ilSession::_destroyByUserId(17);
        if (!ilSession::_exists($duplicate)) {
            $result .= "destroyByUser-";
        }
        $this->assertEquals("exists-write-get-duplicate-destroy-destroyExp-destroyByUser-", $result);
    }

    public function testPasswordAssisstanceSession(): void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $result = "";
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        return;
        // write session
        db_pwassist_session_write("12345", 60, $ilUser->getId());

        // find
        $res = db_pwassist_session_find($ilUser->getId());
        if ($res["pwassist_id"] === "12345") {
            $result .= "find-";
        }

        // read
        $res = db_pwassist_session_read("12345");
        if ((int) $res["user_id"] === $ilUser->getId()) {
            $result .= "read-";
        }

        // destroy
        db_pwassist_session_destroy("12345");
        $res = db_pwassist_session_read("12345");
        if (!$res) {
            $result .= "destroy-";
        }

        db_pwassist_session_gc();

        $this->assertEquals("find-read-destroy-", $result);// TODO PHP8-REVIEW This method does not exists (because this class has no parent class)
    }
}
