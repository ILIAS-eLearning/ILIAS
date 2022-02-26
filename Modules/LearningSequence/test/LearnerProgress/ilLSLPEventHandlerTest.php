<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLPStatusWrapperStub extends ilLPStatusWrapper
{
    public static function _refreshStatus(int $a_obj_id, ?array $a_users = null) : void
    {
        throw new \Exception('Do not use ilLPStatusWrapper::_refreshStatus here; use _updateStatus instead');
    }

    public static function _updateStatus(int $a_obj_id, int $a_usr_id, ?object $a_obj = null, bool $a_percentage = false, bool $a_force_raise = false) : void
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

        $obj_id = 43;

        $this->tree
            ->expects($this->exactly(2))
            ->method('getParentNodeData')
            ->willReturn([
                "type" => "lso",
                "obj_id" => $obj_id
            ])
        ;

        $obj = new ilLSLPEventHandlerStub($this->tree, $this->lp_status);
        $obj->updateLPForChildEvent($values);
        //do not call getParentNodeData again!
        $obj->updateLPForChildEvent($values);
    }
}
