<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\DI\Container;

/**
 * Class assBaseTestCase
 */
abstract class assBaseTestCase extends TestCase
{
    protected ?Container $dic = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $lng_mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->onlyMethods(['txt'])->getMock();
        $lng_mock->expects($this->any())->method('txt')->willReturn('Test');
        $this->setGlobalVariable('lng', $lng_mock);

        $dataCache_mock = $this->getMockBuilder(ilObjectDataCache::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('ilObjDataCache', $dataCache_mock);

        $access_mock = $this->createMock(ilAccessHandler::class);
        $this->setGlobalVariable('ilAccess', $access_mock);

        $help_mock = $this->getMockBuilder(ilHelpGUI::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('ilHelp', $help_mock);

        $user_mock = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('ilUser', $user_mock);

        $tabs_mock = $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('ilTabs', $tabs_mock);

        $rbacsystem_mock = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('rbacsystem', $rbacsystem_mock);

        $refineryMock = $this->getMockBuilder(RefineryFactory::class)->disableOriginalConstructor()->getMock();
        $refineryMock->method('random')->willReturn($this->getMockBuilder(RandomGroup::class)->getMock());
        $this->setGlobalVariable('refinery', $refineryMock);

        $dbMock = $this->createMock(ilDBInterface::class);
        $this->setGlobalVariable('ilDB', $dbMock);

        $this->setGlobalVariable('http', $this->getMockBuilder(ILIAS\HTTP\Services::class)->disableOriginalConstructor()->getMock());

        $this->setGlobalVariable('upload', $this->createMock(ILIAS\FileUpload\FileUpload::class));

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    /**
     * @param string $name
     * @param mixed $value
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

    protected function getGlobalTemplateMock()
    {
        return $this->getMockBuilder(\ilGlobalPageTemplate::class)->disableOriginalConstructor()->getMock();
    }

    protected function getDatabaseMock()
    {
        return $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
    }

    protected function getIliasMock()
    {
        $mock = $this->getMockBuilder(\ILIAS::class)->disableOriginalConstructor()->getMock();

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $mock->account = $account;

        return $mock;
    }
}
