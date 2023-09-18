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

namespace ILIAS\Tests\Services\Database\Integrity;

use PHPUnit\Framework\TestCase;
use ILIAS\Services\Database\Integrity\Integrity;
use ILIAS\Services\Database\Integrity\Definition;
use ILIAS\Services\Database\Integrity\Result;
use ILIAS\Services\Database\Integrity\Association;
use ILIAS\Services\Database\Integrity\Field;
use ilDBInterface;
use ilDBStatement;

class IntegrityTest extends TestCase
{
    public function testConstruct(): void
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $integrity = new Integrity($db);
        $this->assertInstanceOf(Integrity::class, $integrity);
    }

    public function testCheck(): void
    {
        $statement = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects(self::once())->method('query')->willReturn($statement);
        $db->expects(self::once())->method('fetchAssoc')->with($statement)->willReturn(['violations' => '17']);

        $field = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();
        $field->method('fieldName')->willReturn('hej');

        $referenceField = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();
        $referenceField->method('fieldName')->willReturn('ho');

        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $association->method('field')->willReturn($field);
        $association->method('referenceField')->willReturn($referenceField);

        $definition = $this->getMockBuilder(Definition::class)->disableOriginalConstructor()->getMock();
        $definition->expects(self::once())->method('associations')->willReturn([$association]);
        $definition->expects(self::once())->method('tableName')->willReturn('table_a');
        $definition->expects(self::once())->method('referenceTableName')->willReturn('table_b');
        $definition->method('ignoreValues')->willReturn(['Some SQL.']);

        $integrity = new Integrity($db);

        $result = $integrity->check($definition);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(17, $result->violations());
    }
}
