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

use PHPUnit\Framework\TestCase;

class ilLSItemsDBStub extends ilLSItemsDB
{
    protected function getIconPathForType(string $type): string
    {
        return './image/tester/myimage.png';
    }
}

class ilLSItemsDBTest extends TestCase
{
    protected ilTree $tree;
    protected ilContainerSorting $container_sorting;
    protected ilLSPostConditionDB $post_conditions_db;
    protected LSItemOnlineStatus $ls_item_online_status;
    protected ilContainerSortingSettings $sorting_settings;

    protected function setUp(): void
    {
        $this->tree = $this->createMock(ilTree::class);
        $this->container_sorting = $this->createMock(ilContainerSorting::class);
        $this->post_conditions_db = $this->createMock(ilLSPostConditionDB::class);
        $this->ls_item_online_status = $this->createMock(LSItemOnlineStatus::class);
        $this->sorting_settings = $this->createMock(ilContainerSortingSettings::class);
    }

    public function testCreateObject(): void
    {
        $obj = new ilLSItemsDB(
            $this->tree,
            $this->container_sorting,
            $this->post_conditions_db,
            $this->ls_item_online_status
        );

        $this->assertInstanceOf(ilLSItemsDB::class, $obj);
    }

    public function testGetLSItemsWithoutData(): void
    {
        $this->tree
            ->expects($this->once())
            ->method('getChilds')
            ->with(22)
            ->willReturn([])
        ;

        $this->sorting_settings
            ->expects($this->once())
            ->method('setSortMode')
            ->with(ilContainer::SORT_MANUAL)
        ;

        $this->container_sorting
            ->expects($this->once())
            ->method('getSortingSettings')
            ->willReturn($this->sorting_settings)
        ;
        $this->container_sorting
            ->expects($this->once())
            ->method('sortItems')
            ->with(['lsitems' => []])
            ->willReturn(['lsitems' => []])
        ;

        $obj = new ilLSItemsDB(
            $this->tree,
            $this->container_sorting,
            $this->post_conditions_db,
            $this->ls_item_online_status
        );

        $result = $obj->getLSItems(22);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetLSItemsWithData(): void
    {
        $value = [
            '22' => [
                'child' => 14,
                'type' => 'lsitem',
                'title' => 'ls_title',
                'description' => 'tiny_description'
            ]
        ];

        $this->tree
            ->expects($this->once())
            ->method('getChilds')
            ->with(22)
            ->willReturn($value)
        ;

        $this->sorting_settings
            ->expects($this->once())
            ->method('setSortMode')
            ->with(ilContainer::SORT_MANUAL)
        ;

        $this->container_sorting
            ->expects($this->once())
            ->method('getSortingSettings')
            ->willReturn($this->sorting_settings)
        ;
        $this->container_sorting
            ->expects($this->once())
            ->method('sortItems')
            ->with(['lsitems' => $value])
            ->willReturn(['lsitems' => $value])
        ;

        $condition = $this->createMock(ilLSPostCondition::class);
        $condition
            ->expects($this->once())
            ->method('getRefId')
            ->willReturn(14)
        ;

        $this->post_conditions_db
            ->expects($this->once())
            ->method('select')
            ->with([22 => 14])
            ->willReturn(['14' => $condition])
        ;

        $this->ls_item_online_status
            ->expects($this->once())
            ->method('getOnlineStatus')
            ->with(14)
            ->willReturn(true)
        ;

        $obj = new ilLSItemsDBStub(
            $this->tree,
            $this->container_sorting,
            $this->post_conditions_db,
            $this->ls_item_online_status
        );

        $result = $obj->getLSItems(22);

        foreach ($result as $ls_item) {
            $this->assertEquals('lsitem', $ls_item->getType());
            $this->assertEquals('ls_title', $ls_item->getTitle());
            $this->assertEquals('tiny_description', $ls_item->getDescription());
            $this->assertEquals('./image/tester/myimage.png', $ls_item->getIconPath());
            $this->assertTrue($ls_item->isOnline());
            $this->assertEquals(22, $ls_item->getOrderNumber());
            $this->assertInstanceOf(ilLSPostCondition::class, $ls_item->getPostCondition());
            $this->assertEquals(14, $ls_item->getRefId());
        }
    }

    public function testStoreItems(): void
    {
        $condition = $this->createMock(ilLSPostCondition::class);

        $ls_item = new LSItem(
            'ls_item',
            'ls_title',
            'ls_description',
            '',
            true,
            22,
            $condition,
            14
        );

        $this->ls_item_online_status
            ->expects($this->once())
            ->method('setOnlineStatus')
            ->with(14, true)
        ;

        $this->container_sorting
            ->expects($this->once())
            ->method('savePost')
            ->with([14 => 22])
        ;

        $this->post_conditions_db
            ->expects($this->once())
            ->method('upsert')
            ->with([$condition])
        ;

        $obj = new ilLSItemsDB(
            $this->tree,
            $this->container_sorting,
            $this->post_conditions_db,
            $this->ls_item_online_status
        );

        $obj->storeItems([$ls_item]);
    }
}
