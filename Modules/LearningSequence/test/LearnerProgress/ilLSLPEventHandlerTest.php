<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLPStatusWrapperStub extends ilLPStatusWrapper
{
    public static function _refreshStatus($a_obj_id, $a_users = null)
    {
        throw new \Exception('Do not use ilLPStatusWrapper::_refreshStatus here; use _updateStatus instead');
    }
    public static function _updateStatus($a_obj_id, $a_user)
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
    /**
     * @var ilTree
     */
    protected $tree;
    
    /**
     * @var ilLPStatusWrapper
     */
    protected $lp_status;

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
            ->willReturn(['type' => 'lso', 'obj_id'=>$obj_id])
        ;

        $obj = new ilLSLPEventHandlerStub($this->tree, $this->lp_status);
        $obj->updateLPForChildEvent($values);
        //do not call getParentNodeData again!
        $obj->updateLPForChildEvent($values);
    }
}
