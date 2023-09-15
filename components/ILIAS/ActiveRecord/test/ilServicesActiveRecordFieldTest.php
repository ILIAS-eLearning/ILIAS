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
        $this->assertEquals('id', $primaryField->getName());
        $this->assertEquals(8, $primaryField->getLength());
        $this->assertEquals('integer', $primaryField->getFieldType());
        $this->assertEquals(false, $primaryField->getIndex());
        $this->assertEquals(true, $primaryField->getPrimary());

        $arField = $arFieldList->getFieldByName('string_data');
        $this->assertEquals('string_data', $arField->getName());
        $this->assertEquals(256, $arField->getLength());
        $this->assertEquals('text', $arField->getFieldType());
        $this->assertEquals(true, $arField->getIndex());
        $this->assertEquals(false, $arField->getPrimary());
    }
}
