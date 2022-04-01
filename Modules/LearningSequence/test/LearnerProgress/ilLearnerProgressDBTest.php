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

class ilLearnerProgressDBStub extends ilLearnerProgressDB
{
    protected function getLearningProgressFor(int $usr_id, LSItem $ls_item) : int
    {
        return 20;
    }
}

class ilLearnerProgressDBTest extends TestCase
{
    /**
     * @var ilLSItemsDB|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $items_db;

    /**
     * @var ilAccess|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $access;

    protected function setUp() : void
    {
        $this->items_db = $this->createMock(ilLSItemsDB::class);
        $this->access = $this->createMock(ilAccess::class);
        $this->obj_data_cache = $this->createMock(ilObjectDataCache::class);
    }

    public function testCreateObject() : void
    {
        $obj = new ilLearnerProgressDB($this->items_db, $this->access, $this->obj_data_cache);

        $this->assertInstanceOf(ilLearnerProgressDB::class, $obj);
    }

    public function testGetLearnerItemsWithoutData() : void
    {
        $this->items_db
            ->expects($this->once())
            ->method('getLsItems')
            ->with(33)
            ->willReturn([])
        ;

        $obj = new ilLearnerProgressDB($this->items_db, $this->access, $this->obj_data_cache);
        $result = $obj->getLearnerItems(100, 33);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetLearnerItemsWithNonVisibleLSItem() : void
    {
        $ls_item = $this->createMock(LSItem::class);

        $ls_item
            ->expects($this->once())
            ->method('isOnline')
            ->willReturn(false)
        ;
        $ls_item
            ->expects($this->once())
            ->method('getRefId')
            ->willReturn(33)
        ;

        $this->access
            ->expects($this->once())
            ->method('checkAccessOfUser')
            ->with(100, 'visible', '', 33)
            ->willReturn(false)
        ;

        $this->items_db
            ->expects($this->once())
            ->method('getLSItems')
            ->with(44)
            ->willReturn([$ls_item])
        ;

        $obj = new ilLearnerProgressDB($this->items_db, $this->access, $this->obj_data_cache);
        $result = $obj->getLearnerItems(100, 44);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetLearnerItemsWithVisibleLSItem() : void
    {
        $ls_item = $this->createMock(LSItem::class);

        $ls_item
            ->expects($this->exactly(2))
            ->method('isOnline')
            ->willReturn(true)
        ;
        $ls_item
            ->expects($this->exactly(3))
            ->method('getRefId')
            ->willReturn(33)
        ;

        $this->access
            ->expects($this->once())
            ->method('clear')
        ;
        $this->access
            ->expects($this->exactly(2))
            ->method('checkAccessOfUser')
            ->withConsecutive([100, 'visible', '', 33], [100, 'read', '', 33])
            ->willReturn(true)
        ;

        $this->items_db
            ->expects($this->once())
            ->method('getLSItems')
            ->with(44)
            ->willReturn([$ls_item])
        ;

        $obj = new ilLearnerProgressDBStub($this->items_db, $this->access, $this->obj_data_cache);
        $result = $obj->getLearnerItems(100, 44);

        foreach ($result as $ls_learner_item) {
            $this->assertInstanceOf(LSLearnerItem::class, $ls_learner_item);
            $this->assertEquals(33, $ls_learner_item->getRefId());
            $this->assertEquals(20, $ls_learner_item->getLearningProgressStatus());
            $this->assertEquals(1, $ls_learner_item->getAvailability());
        }
    }
}
