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
use ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\InterruptiveItem as Item;

/**
 * Dummy-implementation for testing
 */
class TestingItem extends Item
{
}

class InterruptiveItemTest extends ILIAS_UI_TestBase
{
    public function testConstruction(): void
    {
        $item = new TestingItem('1');
        $this->assertInstanceOf(
            Item::class,
            $item
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetId(): void
    {
        $id = '1';
        $item = new TestingItem($id);
        $this->assertEquals($id, $item->getId());
    }
}
