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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for ilWebLinkItemsContainer
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceItemsContainerTest extends TestCase
{
    /**
     * @return ilWebLinkItem&MockObject
     */
    protected function createItemMock(
        bool $internal,
        string $title,
        int $link_id
    ): MockObject {
        if ($internal) {
            $class = ilWebLinkItemInternal::class;
        } else {
            $class = ilWebLinkItemExternal::class;
        }

        $item = $this->getMockBuilder($class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getTitle','getLinkId'])
                     ->getMock();

        $item->method('getTitle')->willReturn($title);
        $item->method('getLinkId')->willReturn($link_id);

        return $item;
    }

    public function testSortByTitle(): void
    {
        $item1 = $this->createItemMock(false, 'c', 1);
        $item2 = $this->createItemMock(true, 'b', 2);
        $item3 = $this->createItemMock(true, 'a', 3);
        $item4 = $this->createItemMock(false, 'e', 4);
        $item5 = $this->createItemMock(false, 'd', 5);

        $container = $this->getMockBuilder(ilWebLinkItemsContainer::class)
                          ->setConstructorArgs([
                              13,
                              [$item1, $item2, $item3, $item4, $item5]
                          ])
                          ->onlyMethods(['lookupSortMode', 'lookupManualPositions', 'sortArray'])
                          ->getMock();
        $container->expects($this->once())
                  ->method('lookupSortMode')
                  ->with(13)
                  ->willReturn(ilContainer::SORT_TITLE);
        $container->expects($this->never())
                  ->method('lookupManualPositions');
        $container->expects($this->once())
                  ->method('sortArray')
                  ->willReturn(
                      [
                          3 => ['title' => 'a', 'item' => $item3],
                          2 => ['title' => 'b', 'item' => $item2],
                          1 => ['title' => 'c', 'item' => $item1],
                          5 => ['title' => 'd', 'item' => $item5],
                          4 => ['title' => 'e', 'item' => $item4]
                      ]
                  );

        $container_after_sorting = $container->sort();
        $this->assertSame($container, $container_after_sorting);
        $this->assertSame(
            [$item3, $item2, $item1, $item5, $item4],
            $container_after_sorting->getItems()
        );
    }

    public function testSortManual(): void
    {
        $item1 = $this->createItemMock(false, 'c', 1);
        $item2 = $this->createItemMock(true, 'b', 2);
        $item3 = $this->createItemMock(true, 'a', 3);
        $item4 = $this->createItemMock(false, 'e', 4);
        $item5 = $this->createItemMock(false, 'd', 5);

        $container = $this->getMockBuilder(ilWebLinkItemsContainer::class)
                          ->setConstructorArgs([
                              13,
                              [$item1, $item2, $item3, $item4, $item5]
                          ])
                          ->onlyMethods(['lookupSortMode', 'lookupManualPositions', 'sortArray'])
                          ->getMock();
        $container->expects($this->once())
                  ->method('lookupSortMode')
                  ->with(13)
                  ->willReturn(ilContainer::SORT_MANUAL);
        $container->expects($this->once())
                  ->method('lookupManualPositions')
                  ->with(13)
                  ->willReturn([1 => 10, 2 => 30, 3 => 20]);
        $container->expects($this->exactly(2))
                  ->method('sortArray')
                  ->willReturnOnConsecutiveCalls(
                      [
                          1 => ['position' => 10, 'item' => $item1],
                          3 => ['position' => 20, 'item' => $item3],
                          2 => ['position' => 30, 'item' => $item2]
                      ],
                      [
                          5 => ['title' => 'd', 'item' => $item5],
                          4 => ['title' => 'e', 'item' => $item4]
                      ]
                  );

        $container_after_sorting = $container->sort();
        $this->assertSame($container, $container_after_sorting);
        $this->assertSame(
            [$item1, $item3, $item2, $item5, $item4],
            $container_after_sorting->getItems()
        );
    }
}
