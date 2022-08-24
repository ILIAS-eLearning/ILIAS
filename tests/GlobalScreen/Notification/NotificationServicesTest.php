<?php

use ILIAS\GlobalScreen\Scope\Notification\NotificationServices;
use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class NotificationFactoryTest
 */
class NotificationServicesTest extends TestCase
{
    public function testFactory()
    {
        $factory = new NotificationServices();
        $this->assertInstanceOf(NotificationFactory::class, $factory->factory());
    }
}
