<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilServicesActiveRecordFieldTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup = null;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db_mock;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testFieldList(): void
    {
        $test_ar = new class () extends ActiveRecord {
            /**
             * @var int
             *
             * @con_is_primary true
             * @con_is_unique  true
             * @con_has_field  true
             * @con_fieldtype  integer
             * @con_length     8
             */
            protected int $id = 0;

            /**
             * @var string
             *
             * @con_has_field  true
             * @con_fieldtype  text
             * @con_index      true
             * @con_length     256
             */
            protected string $string_data;

            public function getConnectorContainerName(): string
            {
                return 'table_name';
            }
        };

        $field_list = arFieldList::getInstance($test_ar);

        $primary_field = $field_list->getPrimaryField();
        $this->assertEquals('id', $primary_field->getName());
        $this->assertEquals(8, $primary_field->getLength());
        $this->assertEquals('integer', $primary_field->getFieldType());
        $this->assertEquals(false, $primary_field->getIndex());
        $this->assertEquals(true, $primary_field->getPrimary());

        $string_field = $field_list->getFieldByName('string_data');
        $this->assertEquals('string_data', $string_field->getName());
        $this->assertEquals(256, $string_field->getLength());
        $this->assertEquals('text', $string_field->getFieldType());
        $this->assertEquals(true, $string_field->getIndex());
        $this->assertEquals(false, $string_field->getPrimary());
    }
}
