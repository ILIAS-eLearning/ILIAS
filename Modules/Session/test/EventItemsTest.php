<?php declare(strict_types=1);

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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class ilEventItems
 * @author Thomas Famula <famula@leifos.de>
 */
class EventItemsTest extends TestCase
{
    protected ilEventItems $event_items;

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }


    protected function setUp() : void
    {
        parent::setUp();

        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        $db = $this->createMock(ilDBInterface::class);
        $tree = $this->createMock(ilTree::class);

        $this->setGlobalVariable(
            "ilDB",
            $db
        );
        $this->setGlobalVariable(
            "tree",
            $tree
        );

        $this->event_items = new ilEventItems(4);
    }

    protected function tearDown() : void
    {
    }

    public function testSetEventId() : void
    {
        $ei = $this->event_items;
        $ei->setEventId(7);

        $this->assertNotEquals(
            4,
            $ei->getEventId()
        );
        $this->assertEquals(
            7,
            $ei->getEventId()
        );
    }
}
