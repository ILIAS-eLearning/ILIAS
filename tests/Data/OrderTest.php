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
        $this->assertEquals('subject', $order->getSubject());
        $this->assertEquals(Order::ASC, $order->getDirection());
    }

    /**
     * @depends testFactory
     */
    public function testDirection(Order $order)
    {
        $this->assertEquals(
            Order::DESC,
            $order->withDirection(Order::DESC)->getDirection()
        );
    }

    /**
     * @depends testFactory
     */
    public function testSubject(Order $order)
    {
        $this->assertEquals(
            'new_subject',
            $order->withSubject('new_subject')->getSubject()
        );
    }

    /**
     * @depends testFactory
     */
    public function testInvalidDirection(Order $order)
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $order->withDirection('ASC');
    }
}
