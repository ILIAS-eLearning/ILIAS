<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilLSPostConditionDBTest extends TestCase
{
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db;

    protected function setUp() : void
    {
        $this->db = $this->createMock(ilDBInterface::class);
    }

    public function testCreateObject() : void
    {
        $obj = new ilLSPostConditionDB($this->db);

        $this->assertInstanceOf(ilLSPostConditionDB::class, $obj);
    }

    public function testSelectWithEmptyArray() : void
    {
        $obj = new ilLSPostConditionDB($this->db);

        $result = $obj->select([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSelectWithNoDBResults() : void
    {
        $sql =
              "SELECT ref_id, condition_operator, value" . PHP_EOL
            . "FROM post_conditions" . PHP_EOL
            . "WHERE ref_id IN (20,22)" . PHP_EOL
        ;

        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([])
        ;
        $this->db
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([])
            ->willReturn([])
        ;

        $obj = new ilLSPostConditionDB($this->db);
        $result = $obj->select([20,22]);

        $this->assertEquals(20, $result[0]->getRefId());
        $this->assertEquals(ilLSPostConditionDB::STD_ALWAYS_OPERATOR, $result[0]->getConditionOperator());
        $this->assertNull($result[0]->getValue());

        $this->assertEquals(22, $result[1]->getRefId());
        $this->assertEquals(ilLSPostConditionDB::STD_ALWAYS_OPERATOR, $result[1]->getConditionOperator());
        $this->assertNull($result[1]->getValue());
    }

    public function testSelectWithDBResults() : void
    {
        $sql =
              "SELECT ref_id, condition_operator, value" . PHP_EOL
            . "FROM post_conditions" . PHP_EOL
            . "WHERE ref_id IN (33,44)" . PHP_EOL
        ;

        $rows = [
            [
                'ref_id' => 33,
                'condition_operator' => 'operator1',
                'value' => 11
            ],
            [
                'ref_id' => 44,
                'condition_operator' => 'operator2',
                'value' => 12
            ],
            null
        ];

        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($rows)
        ;
        $this->db
            ->expects($this->any())
            ->method('fetchAssoc')
            ->with($rows)
            ->willReturnOnConsecutiveCalls(...$rows)
        ;

        $obj = new ilLSPostConditionDB($this->db);
        $result = $obj->select([33,44]);

        $this->assertEquals(33, $result[0]->getRefId());
        $this->assertEquals('operator1', $result[0]->getConditionOperator());
        $this->assertEquals(11, $result[0]->getValue());

        $this->assertEquals(44, $result[1]->getRefId());
        $this->assertEquals('operator2', $result[1]->getConditionOperator());
        $this->assertEquals(12, $result[1]->getValue());
    }

    public function testDelete() : void
    {
        $sql =
              "DELETE FROM post_conditions" . PHP_EOL
            . "WHERE ref_id IN (20,22)" . PHP_EOL
        ;

        $this->db
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql)
        ;

        $obj = new ilLSPostConditionDB($this->db);
        $obj->delete([20,22]);
    }
}
