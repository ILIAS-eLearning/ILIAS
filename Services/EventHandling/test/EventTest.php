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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

require_once __DIR__ . "/../../../libs/composer/vendor/autoload.php";

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EventTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp(): void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }

        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->method("fetchAssoc")
            ->will(
                $this->onConsecutiveCalls(
                    [
                     "component" => "Services/EventHandling",
                     "id" => "MyTestComponent"
                 ],
                    null
                )
            );


        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );
        $this->setGlobalVariable(
            "ilSetting",
            $this->createMock(ilSetting::class)
        );
        $component_repository = $this->createMock(ilComponentRepository::class);
        $this->setGlobalVariable(
            "component.repository",
            $component_repository
        );
        $component_factory = $this->createMock(ilComponentFactory::class);
        $this->setGlobalVariable(
            "component.factory",
            $component_factory
        );
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
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function tearDown(): void
    {
    }

    protected function getHandler(): ilAppEventHandler
    {
        $logger = $this->createMock(ilLogger::class);

        return new ilAppEventHandler($logger);
    }

    /**
     * Test event
     */
    public function testEvent(): void
    {
        $handler = $this->getHandler();

        $this->expectException(ilEventHandlingTestException::class);

        $handler->raise(
            "Services/EventHandling",
            "myEvent",
            [
                "par1" => "val1",
                "par2" => "val2"
            ]
        );
    }
}
