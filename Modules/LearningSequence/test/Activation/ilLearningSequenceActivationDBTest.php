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

class ilLearningSequenceActivationDBTest extends TestCase
{
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db;

    protected function setUp() : void
    {
        $this->db = $this->createMock(ilDBInterface::class);
    }

    public function testCreateObjectMinimal() : void
    {
        $obj = new ilLearningSequenceActivationDB($this->db);

        $this->assertInstanceOf(ilLearningSequenceActivationDB::class, $obj);
    }

    public function testGetActivationForRefIdWithoutData() : void
    {
        $sql =
             'SELECT ref_id, online, effective_online, activation_start_ts, activation_end_ts' . PHP_EOL
            . 'FROM lso_activation' . PHP_EOL
            . 'WHERE ref_id = 22' . PHP_EOL
        ;

        $values = [
            "ref_id" => ["integer", 22],
            "online" => ["integer", false],
            "effective_online" => ["integer", false],
            "activation_start_ts" => ["integer", null],
            "activation_end_ts" => ["integer", null]
        ];

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn('22')
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
            ->method('numRows')
            ->willReturn(0)
        ;
        $this->db
            ->expects($this->once())
            ->method('insert')
            ->with('lso_activation', $values)
        ;

        $obj = new ilLearningSequenceActivationDB($this->db);
        $settings = $obj->getActivationForRefId(22);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $settings);
        $this->assertEquals(22, $settings->getRefId());
        $this->assertFalse($settings->getIsOnline());
        $this->assertFalse($settings->getEffectiveOnlineStatus());
        $this->assertNull($settings->getActivationStart());
        $this->assertNull($settings->getActivationEnd());
    }

    public function testGetActivationForRefIdWithData() : void
    {
        $start_date = new DateTime('2021-07-21 08:19');
        $end_date = new DateTime('2021-07-21 08:20');

        $sql =
            'SELECT ref_id, online, effective_online, activation_start_ts, activation_end_ts' . PHP_EOL
            . 'FROM lso_activation' . PHP_EOL
            . 'WHERE ref_id = 33' . PHP_EOL
        ;

        $values = [
            "ref_id" => 33,
            "online" => true,
            "effective_online" => true,
            "activation_start_ts" => $start_date->getTimestamp(),
            "activation_end_ts" => $end_date->getTimestamp()
        ];

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(33, 'integer')
            ->willReturn('33')
        ;
        $return_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($return_statement)
        ;
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1)
        ;
        $this->db
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with($return_statement)
            ->willReturn($values)
        ;

        $obj = new ilLearningSequenceActivationDB($this->db);
        $settings = $obj->getActivationForRefId(33);

        $this->assertInstanceOf(ilLearningSequenceActivation::class, $settings);
        $this->assertEquals(33, $settings->getRefId());
        $this->assertTrue($settings->getIsOnline());
        $this->assertTrue($settings->getEffectiveOnlineStatus());
        $this->assertEquals($start_date, $settings->getActivationStart());
        $this->assertEquals($end_date, $settings->getActivationEnd());
    }

    public function testDeleteForRefId() : void
    {
        $sql =
             'DELETE FROM lso_activation' . PHP_EOL
            . 'WHERE ref_id = 44' . PHP_EOL
        ;

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(44, 'integer')
            ->willReturn('44')
        ;
        $this->db
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql)
        ;

        $obj = new ilLearningSequenceActivationDB($this->db);
        $obj->deleteForRefId(44);
    }

    public function testStore() : void
    {
        $start_date = new DateTime('2021-07-21 08:19');
        $end_date = new DateTime('2021-07-21 08:20');


        $where = ['ref_id' => ['integer', 35]];

        $values = [
            "online" => ["integer", true],
            "activation_start_ts" => ["integer", $start_date->getTimestamp()],
            "activation_end_ts" => ["integer", $end_date->getTimestamp()]
        ];

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with('lso_activation', $values, $where)
        ;

        $settings = new ilLearningSequenceActivation(35, true, false, $start_date, $end_date);
        $obj = new ilLearningSequenceActivationDB($this->db);
        $obj->store($settings);
    }
}
