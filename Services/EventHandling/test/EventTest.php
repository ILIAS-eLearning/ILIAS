<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EventTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
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
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function tearDown() : void
    {
    }

    protected function getHandler() : ilAppEventHandler
    {
        return new ilAppEventHandler();
    }

    /**
     * Test event
     */
    public function testEvent() : void
    {
        $handler = $this->getHandler();

        $this->expectException(ilEventHandlingTestException::class);

        $handler->raise(
            "MyTestComponent",
            "MyEvent",
            [
                "par1" => "val1",
                "par2" => "val2"
            ]
        );
    }
}
