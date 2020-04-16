<?php declare(strict_types=1);

use ILIAS\Data\Order;
use PHPUnit\Framework\TestCase;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class orderTest extends TestCase
{
    public function testFactory()
    {
        $f = new ILIAS\Data\Factory();
        $order = $f->order('subject', Order::ASC);
        $this->assertInstanceOf(Order::class, $order);
        return $order;
    }

    /**
     * @depends testFactory
     */
    public function testValues(Order $order)
    {
        $this->assertEquals(
            [['subject', Order::ASC]],
            $order->get()
        );
    }

    /**
     * @depends testFactory
     */
    public function testAppend(Order $order)
    {
        $order = $order->append('sub2', Order::DESC);
        $this->assertEquals(
            [
                ['subject', Order::ASC],
                ['sub2', Order::DESC]
            ],
            $order->get()
        );
        return $order;
    }

    /**
     * @depends testFactory
     */
    public function testJoinOne(Order $order)
    {
        $this->assertEquals(
            'subject ASC',
            $order->join()
        );
    }

    /**
     * @depends testAppend
     */
    public function testJoinMore(Order $order)
    {
        $this->assertEquals(
            'subject ASC, sub2 DESC',
            $order->join()
        );
    }

    /**
     * @depends testFactory
     */
    public function testInvalidDirection(Order $order)
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $order->append('sub3', -1);
    }
}
