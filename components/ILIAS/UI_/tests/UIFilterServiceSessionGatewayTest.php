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

use PHPUnit\Framework\TestCase;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class UIFilterServiceSessionGatewayTest extends TestCase
{
    protected ilUIFilterServiceSessionGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new ilUIFilterServiceSessionGateway();
        $this->gateway->reset("filter_id");
    }

    protected function tearDown(): void
    {
    }

    public function testClear(): void
    {
        $gateway = $this->gateway;
        $gateway->writeActivated("filter_id", true);
        $gateway->writeExpanded("filter_id", true);
        $gateway->writeRendered("filter_id", "input_id", true);
        $gateway->writeValue("filter_id", "input_id", "a value");
        $gateway->reset("filter_id");

        $this->assertEquals(
            false,
            $gateway->isActivated("filter_id", false)
        );
        $this->assertEquals(
            false,
            $gateway->isExpanded("filter_id", false)
        );
        $this->assertEquals(
            false,
            $gateway->isRendered("filter_id", "input_id", false)
        );
        $this->assertEquals(
            null,
            $gateway->getValue("filter_id", "input_id")
        );
    }

    public function testFilterActivated(): void
    {
        $gateway = $this->gateway;
        $gateway->writeActivated("filter_id", true);
        $this->assertEquals(
            true,
            $gateway->isActivated("filter_id", false)
        );
    }

    public function testFilterExpanded(): void
    {
        $gateway = $this->gateway;
        $gateway->writeExpanded("filter_id", true);
        $this->assertEquals(
            true,
            $gateway->isExpanded("filter_id", false)
        );
    }

    public function testFilterInputRendered(): void
    {
        $gateway = $this->gateway;
        $gateway->writeRendered("filter_id", "input_id", true);
        $this->assertEquals(
            true,
            $gateway->isRendered("filter_id", "input_id", false)
        );
    }

    public function testFilterInputValue(): void
    {
        $gateway = $this->gateway;
        $gateway->writeValue("filter_id", "input_id", "a value");
        $this->assertEquals(
            "a value",
            $gateway->getValue("filter_id", "input_id")
        );
    }
}
