<?php
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotification;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class NotificationFactoryTest
 */
class NotificationFactoryTest extends BaseNotificationSetUp
{
    public function testAvailableMethods()
    {
        $r = new ReflectionClass($this->factory);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertEquals(
            $methods, [
                0 => 'standard',
                1 => 'standardGroup',
            ]
        );
    }


    public function testCorrectReturn()
    {
        $this->assertInstanceOf(StandardNotification::class, $this->factory->standard($this->id));
        $this->assertInstanceOf(StandardNotificationGroup::class, $this->factory->standardGroup($this->id));
    }
}
