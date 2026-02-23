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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilServicesActiveRecordFieldTest extends TestCase
{
    private ?Container $dic_backup = null;
    /**
     * @var ilDBInterface|MockObject
     */
    protected ?MockObject $db_mock = null;

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
             *
             * @con_is_primary true
             * @con_is_unique  true
             * @con_has_field  true
             * @con_fieldtype  integer
             * @con_length     8
             */
            protected int $id = 0;

            /**
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

        $arFieldList = arFieldList::getInstance($test_ar);

        $primaryField = $arFieldList->getPrimaryField();
        $this->assertSame('id', $primaryField->getName());
        $this->assertSame(8, $primaryField->getLength());
        $this->assertSame('integer', $primaryField->getFieldType());
        $this->assertEquals(false, $primaryField->getIndex());
        $this->assertEquals(true, $primaryField->getPrimary());

        $arField = $arFieldList->getFieldByName('string_data');
        $this->assertInstanceOf(\arField::class, $arField);
        $this->assertSame('string_data', $arField->getName());
        $this->assertSame(256, $arField->getLength());
        $this->assertSame('text', $arField->getFieldType());
        $this->assertEquals(true, $arField->getIndex());
        $this->assertEquals(false, $arField->getPrimary());
    }
}
