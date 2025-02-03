<?php

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

declare(strict_types=1);

use ILIAS\Data\Order;
use PHPUnit\Framework\TestCase;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class orderTest extends TestCase
{
    public function testFactory(): Order
    {
        $f = new ILIAS\Data\Factory();
        $order = $f->order('subject', Order::ASC);
        $this->assertInstanceOf(Order::class, $order);
        return $order;
    }

    /**
     * @depends testFactory
     */
    public function testValues(Order $order): void
    {
        $this->assertEquals(
            ['subject' => Order::ASC],
            $order->get()
        );
    }

    /**
     * @depends testFactory
     */
    public function testAppend(Order $order): Order
    {
        $order = $order->append('sub2', Order::DESC);
        $this->assertEquals(
            [
                'subject' => Order::ASC,
                'sub2' => Order::DESC
            ],
            $order->get()
        );
        return $order;
    }

    /**
     * @depends testFactory
     */
    public function testJoinOne(Order $order): void
    {
        $this->assertEquals(
            'SORT BY subject ASC',
            $order->join(
                'SORT BY',
                function ($pre, $k, $v) {
                    return "$pre $k $v";
                }
            )
        );
    }

    /**
     * @depends testAppend
     */
    public function testJoinMore(Order $order): void
    {
        $this->assertEquals(
            'Sorting subject ASC, sub2 DESC,',
            $order->join(
                'Sorting',
                function ($pre, $k, $v) {
                    return "$pre $k $v,";
                }
            )
        );
    }

    /**
     * @depends testFactory
     */
    public function testInvalidDirection(Order $order): void
    {
        $this->expectException(TypeError::class);
        $order = $order->append('sub3', -1);
    }

    /**
     * @depends testFactory
     */
    public function testInvalidSubject(Order $order): void
    {
        $this->expectException(InvalidArgumentException::class);
        $order = $order->append('subject', Order::ASC);
    }
}
