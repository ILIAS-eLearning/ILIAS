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
class ilLDAPServerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('lng', $this->getLanguageMock());
        $this->setGlobalVariable(
            'ilDB',
            $this->getMockBuilder(ilDBInterface::class)->disableAutoReturnValueGeneration()->getMock()
        );

        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(\ILIAS\Administration\Setting::class)->getMock()
        );
        $this->setGlobalVariable(
            'ilErr',
            $this->getMockBuilder(ilErrorHandling::class)->getMock()
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

    /**
     * @return MockObject|ilLanguage
     */
    protected function getLanguageMock(): ilLanguage
    {
        $lng = $this
            ->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'getInstalledLanguages', 'loadLanguageModule'])
            ->getMock();

        return $lng;
    }

    public function testConstructorWithoutParam(): void
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
        $ilDB->expects($this->never())->method("quote");

        $server = new ilLDAPServer();
        $this->assertFalse($server->isActive());
    }

    public function testConstructorWithParameter(): void
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
        $ilDB = $DIC['ilDB'];
        $ilDB->expects($this->once())->method("quote")->with(1)->willReturn("1");

        $res = $this->getMockBuilder(ilDBStatement::class)->disableAutoReturnValueGeneration()->getMock();
        $ilDB->method("query")->with(
            "SELECT * FROM ldap_server_settings WHERE server_id = 1"
        )->
        willReturn($res);
        $res->expects($this->exactly(2))->method("fetchRow")->willReturnOnConsecutiveCalls((object) array(
            'active' => "true",
            'name' => "testserver",
            'url' => "ldap://testurl:389",
            'version' => "3",
            'base_dn' => "true",
            'referrals' => "false",
            'tls' => "false",
            'bind_type' => "1",
            'bind_user' => "nobody",
            'bind_pass' => "password",
            'search_base' => "dc=de",
            'user_scope' => "1",
            'user_attribute' => "user",
            'filter' => ".",
            'group_dn' => "dc=group",
            'group_scope' => "1",
            'group_filter' => "",
            'group_member' => "",
            'group_attribute' => "",
            'group_optional' => "false",
            'group_user_filter' => ".*",
            'group_memberisdn' => "true",
            'group_name' => "",
            'sync_on_login' => "true",
            'sync_per_cron' => "false",
            'role_sync_active' => "true",
            'role_bind_dn' => "rolebind",
            'role_bind_pass' => "rolebindpwd",
            'migration' => "true",
            'authentication' => "true",
            'authentication_type' => "1",
            'username_filter' => ".*",
            'escape_dn' => "false"
        ), null);

        $server = new ilLDAPServer(1);
        $this->assertTrue($server->isActive());
    }
}
