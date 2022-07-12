<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/


use ILIAS\DI\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ilCASSettingsTest extends TestCase
{
    protected function setUp() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock()
        );
        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function testBasicSessionBehaviour() : void
    {
        global $DIC;

        //setup some method calls
        /** @var $setting MockObject */
        $setting = $DIC['ilSetting'];
        $setting->method("get")->withConsecutive(
            ['cas_server'],
            ['cas_port'],
            ['cas_uri'],
            ['cas_active'],
            ['cas_user_default_role'],
            ['cas_login_instructions'],
            ['cas_allow_local'],
            ['cas_create_users']
        )->
        willReturnOnConsecutiveCalls(
            'casserver',
            "1",
            'cas',
            'true',
            '0',
            'casInstruction',
            'false',
            'true'
        );

        $casSettings = ilCASSettings::getInstance();
        $this->assertEquals("casserver", $casSettings->getServer());
        $this->assertTrue($casSettings->isActive());
    }
}
