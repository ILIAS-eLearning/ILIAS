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
use ILIAS\Services\Database\Integrity\Field;

class FieldTest extends TestCase
{
    public function testConstruct(): void
    {
        $field = new Field('table', 'field');
        $this->assertInstanceOf(Field::class, $field);
    }

    public function testTableName(): void
    {
        $field = new Field('table', 'field');
        $this->assertEquals('table', $field->tableName());
    }

    public function testTableNameWithAlias(): void
    {
        $field = new Field('table', 'field', 'alias');
        $this->assertEquals('table as alias', $field->tableName());
    }

    public function testFieldName(): void
    {
        $field = new Field('table', 'field');
        $this->assertEquals('table.field', $field->fieldName());
    }

    public function testFieldNameWithAlias(): void
    {
        $field = new Field('table', 'field', 'alias');
        $this->assertEquals('alias.field', $field->fieldName());
    }

    public function testRawFieldName(): void
    {
        $field = new Field('table', 'field');
        $this->assertEquals('field', $field->rawFieldName());
    }

    public function testRawFieldNameWithAlias(): void
    {
        $field = new Field('table', 'field', 'alias');
        $this->assertEquals('field', $field->rawFieldName());
    }

    public function testRawTableName(): void
    {
        $field = new Field('table', 'field');
        $this->assertEquals('table', $field->rawTableName());
    }

    public function testRawTableNameWithAlias(): void
    {
        $field = new Field('table', 'field', 'alias');
        $this->assertEquals('table', $field->rawTableName());
    }
}
