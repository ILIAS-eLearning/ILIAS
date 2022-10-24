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
use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Manager\Manager;

class ilModulesOrgUnitTest extends TestCase
{
    private ?Container $dic_backup = null;
    /**
     * @var Services|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storage_mock;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db_mock;
    protected $manager_mock;
    private PHPUnit\Framework\MockObject\MockObject $component_factory_mock;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $DIC['resource_storage'] = $this->storage_mock = $this->createMock(Services::class);
        $this->manager_mock = $this->createMock(Manager::class);
        $DIC['ilUser'] = $this->createMock(ilObjUser::class);
        $DIC['ilUser']->expects($this->any())->method('getPref')->willReturn('en');
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
        $DIC['upload'] = $this->createMock(FileUpload::class);
        $DIC['ilias'] = $this->createMock(ILIAS::class);
        $DIC['objDefinition'] = $this->createMock(ilObjectDefinition::class);
        $DIC['ilLog'] = $this->createMock(ilLogger::class);
        $DIC['ilErr'] = $this->createMock(ilErrorHandling::class);
        $DIC['tree'] = $this->createMock(ilTree::class);
        $DIC['tpl'] = $this->createMock(ilGlobalTemplateInterface::class);
        $DIC['ilClientIniFile'] = $this->createMock(ilIniFile::class);
        $DIC['ilAccess'] = $this->createMock(ilAccess::class);
        $DIC['ilObjDataCache'] = $this->createMock(ilObjectDataCache::class);
        $DIC['ilAppEventHandler'] = $this->createMock(ilAppEventHandler::class);
        $DIC['lng'] = $this->createMock(ilLanguage::class);
        $DIC['ilCtrl'] = $this->createMock(ilCtrlInterface::class);
        $DIC['component.factory'] = $this->component_factory_mock = $this->createMock(ilComponentFactory::class);
        /*  $DIC['ilCtrl'] = $this->getMockBuilder(ilCtrl::class)
                                ->disableOriginalConstructor()
                                ->disableArgumentCloning()
                                ->getMock();*/

        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testIfOrgUnitHasRequiredLocalRoles(): void
    {
        $rec = new stdClass();
        $rec->icon = '';
        $rec->owner = 13;
        $rec->last_update = 0;
        $rec->create_date = 0;
        $rec->default_lang = 'en';

        $this->db_mock->method('numRows')->willReturn(1);
        $this->db_mock->method('fetchObject')->willReturn($rec);
        $this->component_factory_mock->method('getActivePluginsInSlot')->willReturn(new ArrayIterator([]));

        $instance = ilOrgUnitType::getInstance(1);

        $plugins = $instance->getActivePlugins();
        self::assertEmpty($plugins);
    }
}
