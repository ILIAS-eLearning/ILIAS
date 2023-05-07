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

use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotification;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class NotificationFactoryTest
 */
class NotificationFactoryTest extends BaseNotificationSetUp
{
    public function testAvailableMethods() : void
    {
        $r = new ReflectionClass($this->factory);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertEquals(
            [
                0 => 'administrative',
                1 => 'standard',
                2 => 'standardGroup',
            ],
            $methods
        );
    }


    public function testCorrectReturn() : void
    {
        $this->assertInstanceOf(StandardNotification::class, $this->factory->standard($this->id));
        $this->assertInstanceOf(StandardNotificationGroup::class, $this->factory->standardGroup($this->id));
    }
}
