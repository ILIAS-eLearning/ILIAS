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

class ilServicesActiveRecordConnectorTest extends TestCase
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

    public function testDbConnector(): void
    {
        $ilBiblEntry = new ilBiblEntry();
        $this->db_mock->expects($this->once())
                      ->method('nextId')
                      ->with(ilBiblEntry::TABLE_NAME)
                      ->willReturn(1);

        $arConnectorDB = new arConnectorDB($this->db_mock);
        $this->assertEquals(1, $arConnectorDB->nextID($ilBiblEntry));

        $this->db_mock->expects($this->once())
                      ->method('tableExists')
                      ->with(ilBiblEntry::TABLE_NAME)
                      ->willReturn(true);
        $this->assertEquals(true, $arConnectorDB->checkTableExists($ilBiblEntry));

        $this->db_mock->expects($this->once())
                      ->method('tableColumnExists')
                      ->with(ilBiblEntry::TABLE_NAME, 'data_id')
                      ->willReturn(true);
        $this->assertEquals(true, $arConnectorDB->checkFieldExists($ilBiblEntry, 'data_id'));
    }

    public function testConnectorMap(): void
    {
        $arConnectorCache = new arConnectorCache(new arConnectorDB($this->db_mock));
        $ar = new class () extends ActiveRecord {
            /**
             *
             * @con_is_primary true
             * @con_is_unique  true
             * @con_has_field  true
             * @con_fieldtype  integer
             * @con_length     8
             */
            protected int $id = 0;
        };
        arConnectorMap::register($ar, $arConnectorCache);
        $this->assertEquals($arConnectorCache, arConnectorMap::get($ar));
    }
}
