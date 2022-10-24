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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilRegistrationSettingsTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        global $DIC;
        /** @var $setting MockObject */
        $ilSetting = $DIC['ilSetting'];
        $ilSetting->method("get")->willReturnCallback(
            function ($arg, $arg2 = null) {
                if ($arg === 'approve_recipient' && $arg2=== "") {
                    return "";
                }
                return null;
            }
        );

        $settings = new ilRegistrationSettings();
        $this->assertInstanceOf(ilRegistrationSettings::class, $settings);
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilSetting', $this->createMock(ilSetting::class));
    }
}
