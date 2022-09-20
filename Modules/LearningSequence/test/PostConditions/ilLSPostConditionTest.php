<?php

declare(strict_types=1);

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

class ilLSPostConditionTest extends TestCase
{
    public function testObjectCreation(): void
    {
        $obj = new ilLSPostCondition(33, 'operator1');

        $this->assertInstanceOf(ilLSPostCondition::class, $obj);
        $this->assertEquals(33, $obj->getRefId());
        $this->assertEquals('operator1', $obj->getConditionOperator());
        $this->assertNull($obj->getValue());
    }

    public function testWithConditionOperator(): void
    {
        $obj = new ilLSPostCondition(23, 'operator2', 15);
        $new_obj = $obj->withConditionOperator('operator3');

        $this->assertEquals(23, $obj->getRefId());
        $this->assertEquals('operator2', $obj->getConditionOperator());
        $this->assertEquals(15, $obj->getValue());

        $this->assertEquals(23, $new_obj->getRefId());
        $this->assertEquals('operator3', $new_obj->getConditionOperator());
        $this->assertEquals(15, $new_obj->getValue());
    }

    public function testWithValue(): void
    {
        $obj = new ilLSPostCondition(45, 'operator5', 15);
        $new_obj = $obj->withValue(22);

        $this->assertEquals(45, $obj->getRefId());
        $this->assertEquals('operator5', $obj->getConditionOperator());
        $this->assertEquals(15, $obj->getValue());

        $this->assertEquals(45, $new_obj->getRefId());
        $this->assertEquals('operator5', $new_obj->getConditionOperator());
        $this->assertEquals(22, $new_obj->getValue());
    }
}
