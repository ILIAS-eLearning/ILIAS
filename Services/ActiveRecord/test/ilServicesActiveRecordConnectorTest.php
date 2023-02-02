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
        $test_ar = new ilBiblEntry();
        $this->db_mock->expects($this->once())
                      ->method('nextId')
                      ->with(ilBiblEntry::TABLE_NAME)
                      ->willReturn(1);

        $connector = new arConnectorDB($this->db_mock);
        $this->assertEquals(1, $connector->nextID($test_ar));

        $this->db_mock->expects($this->once())
                      ->method('tableExists')
                      ->with(ilBiblEntry::TABLE_NAME)
                      ->willReturn(true);
        $this->assertEquals(true, $connector->checkTableExists($test_ar));

        $this->db_mock->expects($this->once())
                      ->method('tableColumnExists')
                      ->with(ilBiblEntry::TABLE_NAME, 'data_id')
                      ->willReturn(true);
        $this->assertEquals(true, $connector->checkFieldExists($test_ar, 'data_id'));
    }

    public function testConnectorMap(): void
    {
        $cache_connector = new arConnectorCache(new arConnectorDB($this->db_mock));
        $ar = new class () extends ActiveRecord {
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
        };
        arConnectorMap::register($ar, $cache_connector);
        $this->assertEquals($cache_connector, arConnectorMap::get($ar));
    }
}
