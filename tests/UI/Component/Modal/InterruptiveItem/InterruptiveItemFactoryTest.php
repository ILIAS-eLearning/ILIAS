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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "../../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\Factory;

class InterruptiveItemFactoryTest extends ILIAS_UI_TestBase
{
    protected function getFactory(): Factory
    {
        return new Factory();
    }

    public function testStandard(): void
    {
        $factory = $this->getFactory();
        $this->assertInstanceOf(
            I\Modal\InterruptiveItem\Standard::class,
            $factory->standard('id', 'title')
        );
    }

    public function testKeyValue(): void
    {
        $factory = $this->getFactory();
        $this->assertInstanceOf(
            I\Modal\InterruptiveItem\KeyValue::class,
            $factory->keyValue('id', 'key', 'label')
        );
    }
}
