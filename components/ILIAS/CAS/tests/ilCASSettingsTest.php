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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ilCASSettingsTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
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
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    public function testBasicSessionBehaviour(): void
    {
        global $DIC;

        //setup some method calls
        /** @var $setting MockObject */
        $setting = $DIC['ilSetting'];
        $consecutive_returns = [
            'cas_server' => 'casserver',
            'cas_port' => '1',
            'cas_uri' => 'cas',
            'cas_active' => 'true',
            'cas_user_default_role' => '0',
            'cas_login_instructions' => 'casInstruction',
            'cas_allow_local' => 'false',
            'cas_create_users' => 'true',
        ];
        $setting->method("get")
            ->willReturnCallback(fn($k) => $consecutive_returns[$k]);

        $casSettings = ilCASSettings::getInstance();
        $this->assertEquals("casserver", $casSettings->getServer());
        $this->assertTrue($casSettings->isActive());
    }
}
