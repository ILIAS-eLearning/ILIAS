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
use ILIAS\Services\Database\Integrity\Association;
use ILIAS\Services\Database\Integrity\Definition;
use ILIAS\Services\Database\Integrity\Field;
use ILIAS\Services\Database\Integrity\Ignore;
use InvalidArgumentException;

class DefinitionTest extends TestCase
{
    public function testConstruct(): void
    {
        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $instance = new Definition([$association]);
        $this->assertInstanceOf(Definition::class, $instance);
    }

    public function testZeroAssociationsWillFail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Definition([]);
    }


    public function testTableName(): void
    {
        $tableName = 'hej';
        $field = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();
        $field->method('tableName')->willReturn($tableName);

        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $association->method('field')->willReturn($field);

        $instance = new Definition([$association]);

        $this->assertEquals($tableName, $instance->tableName());
    }

    public function testAssociations(): void
    {
        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $instance = new Definition([$association]);

        $this->assertEquals([$association], $instance->associations());
    }

    public function testDefaultIgnoreValues(): void
    {
        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $instance = new Definition([$association]);

        $this->assertEquals([], $instance->ignoreValues());
    }

    public function testIgnoreValuesWithValues(): void
    {
        $ignoredValues = ['lorem', 'ipsum'];

        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();

        $ignore = $this->getMockBuilder(Ignore::class)->disableOriginalConstructor()->getMock();
        $ignore->method('values')->willReturn($ignoredValues);

        $instance = new Definition([$association], $ignore);

        $this->assertEquals($ignoredValues, $instance->ignoreValues());
    }

    public function testReferenceTableName(): void
    {
        $tableName = 'hej';
        $field = $this->getMockBuilder(Field::class)->disableOriginalConstructor()->getMock();
        $field->method('tableName')->willReturn($tableName);

        $association = $this->getMockBuilder(Association::class)->disableOriginalConstructor()->getMock();
        $association->method('referenceField')->willReturn($field);

        $instance = new Definition([$association]);

        $this->assertEquals($tableName, $instance->referenceTableName());
    }

    public function testInvalidName(): void
    {
        $association_one = new Association(
            new Field('table_one', 'field_one'),
            new Field('table_two', 'field_two')
        );

        $association_two = new Association(
            new Field('table_three', 'field_one'),
            new Field('table_four', 'field_two')
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All fields must have the same table');
        $instance = new Definition([$association_one, $association_two]);
    }


    public function testInvalidParameter(): void
    {
        $association_one = new Association(
            new Field('table_one', 'field_one'),
            new Field('table_two', 'field_two')
        );

        $association_two = 'funny string';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Associations must be of type ILIAS\Services\Database\Integrity\Association.');
        $instance = new Definition([$association_one, $association_two]);
    }
}
