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
use ILIAS\UI\Component\Listing\Workflow\Step;

class LSPlayerTest extends TestCase
{
    protected LSLearnerItem $available;
    protected LSLearnerItem $unavailable;
    protected ilLSPlayer $player;

    protected function setUp(): void
    {
        $this->available = $this->createMock(LSLearnerItem::class);
        $this->available->method('getAvailability')
            ->willReturn(Step::AVAILABLE);
        $this->unavailable = $this->createMock(LSLearnerItem::class);
        $this->unavailable->method('getAvailability')
            ->willReturn(Step::NOT_AVAILABLE);

        $this->player = new class () extends ilLSPlayer {
            public function __construct()
            {
            }
            public function _getNextAvailableItem(
                array $items,
                LSLearnerItem $current_item
            ): ?LSLearnerItem {
                return parent::getNextAvailableItem($items, $current_item);
            }
        };
    }

    public function testLearningSequenceNextAvailableIsSame()
    {
        $items = [
            clone $this->available,
            clone $this->available,
            clone $this->available,
            clone $this->unavailable,
        ];
        $this->assertNotSame($items[0], $items[2]);
        $this->assertEquals(Step::AVAILABLE, $items[1]->getAvailability());
        $this->assertEquals(Step::NOT_AVAILABLE, $items[3]->getAvailability());

        $current = $items[1];
        $next = $this->player->_getNextAvailableItem($items, $current);
        $this->assertSame($current, $next);
    }

    public function testLearningSequenceNextAvailableIsNull()
    {
        $items = [
            clone $this->unavailable,
            clone $this->unavailable,
            clone $this->unavailable,
        ];
        $current = $items[1];
        $this->assertNull(
            $this->player->_getNextAvailableItem($items, $current)
        );
    }

    public function testLearningSequenceNextAvailableBackwards()
    {
        $items = [
            clone $this->unavailable,
            clone $this->available, //expected
            clone $this->unavailable,
            clone $this->unavailable, //current
            clone $this->available,
        ];
        $current = $items[3];
        $this->assertEquals(
            1,
            array_search($this->player->_getNextAvailableItem($items, $current), $items)
        );
    }

    public function testLearningSequenceNextAvailableForwards()
    {
        $items = [
            clone $this->unavailable,
            clone $this->unavailable, //current
            clone $this->unavailable,
            clone $this->available, //expected
            clone $this->available,
        ];
        $current = $items[1];
        $this->assertEquals(
            3,
            array_search($this->player->_getNextAvailableItem($items, $current), $items)
        );
    }

}
