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
 *********************************************************************/
 
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
    ) : MockObject {
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

    public function testSort() : void
    {
        $item1 = $this->createItemMock(false, 'c', 1);
        $item2 = $this->createItemMock(true, 'b', 2);
        $item3 = $this->createItemMock(true, 'a', 3);
        $item4 = $this->createItemMock(false, 'e', 4);
        $item5 = $this->createItemMock(false, 'd', 5);

        $sort_settings = Mockery::mock('alias:' . ilContainerSortingSettings::class);
        $sort_settings->shouldReceive('_lookupSortMode')
                      ->twice()
                      ->with(13)
                      ->andReturn(ilContainer::SORT_TITLE, ilContainer::SORT_MANUAL);

        $sort = Mockery::mock('alias:' . ilContainerSorting::class);
        $sort->shouldReceive('lookupPositions')
             ->once()
             ->with(13)
             ->andReturn([1 => 10, 2 => 30, 3 => 20]);

        $array_util = Mockery::mock('alias:' . ilArrayUtil::class);
        $array_util->shouldReceive('sortArray')
                   ->once()
                   ->andReturn(
                       [
                           3 => ['title' => 'a', 'item' => $item3],
                           2 => ['title' => 'b', 'item' => $item2],
                           1 => ['title' => 'c', 'item' => $item1],
                           5 => ['title' => 'd', 'item' => $item5],
                           4 => ['title' => 'e', 'item' => $item4]
                       ],
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

        $container = new ilWebLinkItemsContainer(
            13,
            [$item1, $item2, $item3, $item4, $item5]
        );

        $this->assertSame($container, $container->sort());
        $this->assertSame(
            [$item3, $item2, $item1, $item5, $item4],
            $container->getItems()
        );
        $this->assertSame($container, $container->sort());
        $this->assertSame(
            [$item1, $item3, $item2, $item5, $item4],
            $container->getItems()
        );
    }
}
