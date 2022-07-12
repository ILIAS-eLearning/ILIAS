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

        $return = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($return)
        ;
        $this->db
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with($return)
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

        $return_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($return_statement)
        ;
        $this->db
            ->expects($this->any())
            ->method('fetchAssoc')
            ->with($return_statement)
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
