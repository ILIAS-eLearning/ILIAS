<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLPStatusWrapperStub extends ilLPStatusWrapper
{
    public static function _refreshStatus($a_obj_id, $a_users = null)
    {
    }
}

class ilLSLPEventHandlerStub extends ilLSLPEventHandler
{
    /**
     * @return int[]
     */
    protected function getRefIdsOfObjId(int $triggerer_obj_id) : array
    {
        return [14, 20];
    }
}

class ilLSLPEventHandlerTest extends TestCase
{
    protected ilTree $tree;
    protected ilLPStatusWrapper $lp_status;

    protected function setUp() : void
    {
        $this->tree = $this->createMock(ilTree::class);
        $this->lp_status = new ilLPStatusWrapperStub();
    }

    public function testCreateObject() : void
    {
        $obj = new ilLSLPEventHandler($this->tree, $this->lp_status);

        $this->assertInstanceOf(ilLSLPEventHandler::class, $obj);
    }

    public function testUpdateLPForChildEvent() : void
    {
        $values = [
            'obj_id' => 12,
            'usr_id' => 101
        ];

        $path = [
            [
                'type' => 'lso',
                'obj_id' => 43
            ]
        ];

        $this->tree
            ->expects($this->atLeast(2))
            ->method('getPathFull')
            ->withConsecutive([14], [20])
            ->willReturn($path)
        ;

        $obj = new ilLSLPEventHandlerStub($this->tree, $this->lp_status);
        $obj->updateLPForChildEvent($values);
    }
}
