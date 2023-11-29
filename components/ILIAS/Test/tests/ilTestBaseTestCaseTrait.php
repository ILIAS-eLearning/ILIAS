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
use ILIAS\DI\Container;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\Services;
use ILIAS\UI\Implementation\Factory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;

trait ilTestBaseTestCaseTrait
{
    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    /**
     * @return ilTemplate|mixed|MockObject
     */
    protected function getGlobalTemplateMock()
    {
        return $this->getMockBuilder(ilTemplate::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ilDBInterface|mixed|MockObject
     */
    protected function getDatabaseMock()
    {
        return $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ILIAS|mixed|MockObject
     */
    protected function getIliasMock()
    {
        $mock = $this->getMockBuilder(ILIAS::class)->disableOriginalConstructor()->getMock();

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $mock->account = $account;

        return $mock;
    }

    protected function addGlobal_ilAccess(): void
    {
        $this->setGlobalVariable('ilAccess', $this->createMock(ilAccess::class));
    }

    protected function addGlobal_ilUser(): void
    {
        $this->setGlobalVariable('ilUser', $this->createMock(ilObjUser::class));
    }

    protected function addGlobal_objDefinition(): void
    {
        $this->setGlobalVariable('objDefinition', $this->createMock(ilObjectDefinition::class));
    }

    protected function addGlobal_tree(): void
    {
        $this->setGlobalVariable('tree', $this->createMock(ilTree::class));
    }

    protected function addGlobal_ilSetting(): void
    {
        $this->setGlobalVariable('ilSetting', $this->createMock(ilSetting::class));
    }

    protected function addGlobal_rbacsystem(): void
    {
        $this->setGlobalVariable('rbacsystem', $this->createMock(ilRbacSystem::class));
    }

    protected function addGlobal_ilRbacAdmin(): void
    {
        $this->setGlobalVariable('rbacadmin', $this->createMock(ilRbacAdmin::class));
    }

    protected function addGlobal_ilCtrl(): void
    {
        $this->setGlobalVariable('ilCtrl', $this->createMock(ilCtrl::class));
    }

    protected function addGlobal_lng(): void
    {
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
    }

    protected function addGlobal_filesystem(): void
    {
        $this->setGlobalVariable('filesystem', $this->createMock(Filesystems::class));
    }

    protected function addGlobal_upload(): void
    {
        $this->setGlobalVariable('upload', $this->createMock(FileUpload::class));
    }

    protected function addGlobal_ilDB(): void
    {
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
    }

    protected function addGlobal_ilBench(): void
    {
        $this->setGlobalVariable('ilBench', $this->createMock(ilBenchmark::class));
    }

    protected function addGlobal_ilLog(): void
    {
        $this->setGlobalVariable('ilLog', $this->createMock(ilLogger::class));
    }

    protected function addGlobal_ilias(): void
    {
        $this->setGlobalVariable('ilias', $this->getIliasMock());
    }

    protected function addGlobal_ilErr(): void
    {
        $this->setGlobalVariable('ilErr', $this->createMock(ilErrorHandling::class));
    }

    protected function addGlobal_GlobalScreenService(): void
    {
        $this->setGlobalVariable('global_screen', $this->createMock(ILIAS\GlobalScreen\Services::class));
    }

    protected function addGlobal_ilNavigationHistory(): void
    {
        $this->setGlobalVariable('ilNavigationHistory', $this->createMock(ilNavigationHistory::class));
    }

    protected function addGlobal_ilAppEventHandler(): void
    {
        $this->setGlobalVariable('ilAppEventHandler', $this->createMock(ilAppEventHandler::class));
    }

    protected function addGlobal_tpl(): void
    {
        $this->setGlobalVariable('tpl', $this->createMock(ilGlobalPageTemplate::class));
    }

    protected function addGlobal_ilComponentRepository(): void
    {
        $this->setGlobalVariable('component.repository', $this->createMock(ilComponentRepository::class));
    }

    protected function addGlobal_ilComponentFactory(): void
    {
        $this->setGlobalVariable('component.factory', $this->createMock(ilComponentFactory::class));
    }

    protected function addGlobal_ilTabs(): void
    {
        $this->setGlobalVariable('ilTabs', $this->createMock(ilTabsGUI::class));
    }

    protected function addGlobal_ilObjDataCache(): void
    {
        $this->setGlobalVariable('ilObjDataCache', $this->createMock(ilObjectDataCache::class));
    }

    protected function addGlobal_ilLocator(): void
    {
        $this->setGlobalVariable('ilLocator', $this->createMock(ilLocatorGUI::class));
    }

    protected function addGlobal_rbacreview(): void
    {
        $this->setGlobalVariable('rbacreview', $this->createMock(ilRbacReview::class));
    }

    protected function addGlobal_ilToolbar(): void
    {
        $this->setGlobalVariable('ilToolbar', $this->createMock(ilToolbarGUI::class));
    }

    protected function addGlobal_http(): void
    {
        $http_mock = $this->getMockBuilder(Services::class)->disableOriginalConstructor()->getMock();
        $http_mock->method('request')->willReturn($this->getMockBuilder(\Psr\Http\Message\ServerRequestInterface::class)->disableOriginalConstructor()->getMock());
        $this->setGlobalVariable('http', $http_mock);
    }

    protected function addGlobal_ilIliasIniFile(): void
    {
        $this->setGlobalVariable('ilIliasIniFile', $this->createMock(ilIniFile::class));
    }

    protected function addGlobal_ilLoggerFactory(): void
    {
        $this->setGlobalVariable('ilLoggerFactory', $this->createMock(ilLoggerFactory::class));
    }

    protected function addGlobal_ilHelp(): void
    {
        $this->setGlobalVariable('ilHelp', $this->createMock(ilHelpGUI::class));
    }

    protected function addGlobal_ui(): void
    {
        $this->setGlobalVariable('ui', $this->createMock(ILIAS\DI\UIServices::class));
    }

    protected function addGlobal_uiFactory(): void
    {
        $this->setGlobalVariable('ui.factory', $this->createMock(Factory::class));
    }

    protected function addGlobal_uiRenderer(): void
    {
        $this->setGlobalVariable('ui.renderer', $this->createMock(ILIAS\UI\Implementation\DefaultRenderer::class));
    }

    protected function addGlobal_refinery(): void
    {
        $refineryMock = $this->getMockBuilder(RefineryFactory::class)->disableOriginalConstructor()->getMock();
        $refineryMock->expects(self::any())->method('random')->willReturn($this->getMockBuilder(RandomGroup::class)->getMock());
        $this->setGlobalVariable('refinery', $refineryMock);
    }

    protected function addGlobal_skillService(): void
    {
        $this->setGlobalVariable('skill', $this->createMock(ILIAS\Skill\Service\SkillService::class));
    }

    protected function addGlobal_objectService(): void
    {
        global $DIC;
        $DIC['object.customicons.factory'] = $this->getMockBuilder(ilObjectCustomIconFactory::class)->disableOriginalConstructor()->getMock();
        $object_mock = $this->getMockBuilder(\ilObjectService::class)->disableOriginalConstructor()->getMock();

        $this->setGlobalVariable('object', $object_mock);
    }

    protected function getTestObjMock(): ilObjTest
    {
        $test_mock = $this->createMock(ilObjTest::class);
        $test_mock->method('getLocalDIC')->willReturn(ilTestDIC::dic());
        return $test_mock;
    }
}