<?php declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\DI\Container;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\Services;
use ILIAS\UI\Implementation\Factory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;

/**
 * Class ilTestBaseClass
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestBaseTestCase extends TestCase
{
    protected ?Container $dic = null;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->addGlobal_tpl();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_lng();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_refinery();

        $this->getMockBuilder(\ILIAS\DI\LoggingServices::class)->disableOriginalConstructor()->getMock();
        
        $http_mock = $this
            ->getMockBuilder(\ILIAS\HTTP\Services::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'wrapper'])->getMock();

        $request_mock = $this
            ->getMockBuilder(\GuzzleHttp\Psr7\ServerRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParsedBody'])->getMock();
        $request_mock->method('getParsedBody')->willReturn(array());
        $http_mock->method('request')->willReturn($request_mock);

        $wrapper_mock = $this->createMock(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $http_mock->method('wrapper')->willReturn($wrapper_mock);

        $this->setGlobalVariable('http', $http_mock);

        parent::setUp();
    }

    protected function tearDown() : void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
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

    protected function addGlobal_ilAccess() : void
    {
        $this->setGlobalVariable("ilAccess", $this->createMock(ilAccess::class));
    }

    protected function addGlobal_ilUser() : void
    {
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));
    }

    protected function addGlobal_objDefinition() : void
    {
        $this->setGlobalVariable("objDefinition", $this->createMock(ilObjectDefinition::class));
    }

    protected function addGlobal_tree() : void
    {
        $this->setGlobalVariable("tree", $this->createMock(ilTree::class));
    }

    protected function addGlobal_ilSetting() : void
    {
        $this->setGlobalVariable("ilSetting", $this->createMock(ilSetting::class));
    }

    protected function addGlobal_rbacsystem() : void
    {
        $this->setGlobalVariable("rbacsystem", $this->createMock(ilRbacSystem::class));
    }

    protected function addGlobal_ilCtrl() : void
    {
        $this->setGlobalVariable("ilCtrl", $this->createMock(ilCtrl::class));
    }

    protected function addGlobal_lng() : void
    {
        $this->setGlobalVariable("lng", $this->createMock(ilLanguage::class));
    }

    protected function addGlobal_filesystem() : void
    {
        $this->setGlobalVariable("filesystem", $this->createMock(Filesystems::class));
    }

    protected function addGlobal_upload() : void
    {
        $this->setGlobalVariable("upload", $this->createMock(FileUpload::class));
    }

    protected function addGlobal_ilDB() : void
    {
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));
    }

    protected function addGlobal_ilLog() : void
    {
        $this->setGlobalVariable("ilLog", $this->createMock(ilLogger::class));
    }

    protected function addGlobal_ilias() : void
    {
        $this->setGlobalVariable("ilias", $this->getIliasMock());
    }

    protected function addGlobal_ilErr() : void
    {
        $this->setGlobalVariable("ilErr", $this->createMock(ilErrorHandling::class));
    }

    protected function addGlobal_ilAppEventHandler() : void
    {
        $this->setGlobalVariable("ilAppEventHandler", $this->createMock(ilAppEventHandler::class));
    }

    protected function addGlobal_tpl() : void
    {
        $this->setGlobalVariable("tpl", $this->createMock(ilGlobalPageTemplate::class));
    }

    protected function addGlobal_ilComponentRepository() : void
    {
        $this->setGlobalVariable("component.repository", $this->createMock(ilComponentRepository::class));
    }

    protected function addGlobal_ilTabs() : void
    {
        $this->setGlobalVariable("ilTabs", $this->createMock(ilTabsGUI::class));
    }

    protected function addGlobal_ilObjDataCache() : void
    {
        $this->setGlobalVariable("ilObjDataCache", $this->createMock(ilObjectDataCache::class));
    }

    protected function addGlobal_ilLocator() : void
    {
        $this->setGlobalVariable("ilLocator", $this->createMock(ilLocatorGUI::class));
    }

    protected function addGlobal_rbacreview() : void
    {
        $this->setGlobalVariable("rbacreview", $this->createMock(ilRbacReview::class));
    }

    protected function addGlobal_ilToolbar() : void
    {
        $this->setGlobalVariable("ilToolbar", $this->createMock(ilToolbarGUI::class));
    }

    protected function addGlobal_http() : void
    {
        $this->setGlobalVariable("http", $this->createMock(Services::class));
    }

    protected function addGlobal_ilIliasIniFile() : void
    {
        $this->setGlobalVariable("ilIliasIniFile", $this->createMock(ilIniFile::class));
    }

    protected function addGlobal_ilLoggerFactory() : void
    {
        $this->setGlobalVariable("ilLoggerFactory", $this->createMock(ilLoggerFactory::class));
    }

    protected function addGlobal_ilHelp() : void
    {
        $this->setGlobalVariable("ilHelp", $this->createMock(ilHelp::class));
    }

    protected function addGlobal_uiFactory() : void
    {
        $this->setGlobalVariable("ui.factory", $this->createMock(Factory::class));
    }

    protected function addGlobal_uiRenderer() : void
    {
        $this->setGlobalVariable("ui.renderer", $this->createMock(ILIAS\UI\Implementation\DefaultRenderer::class));
    }

    protected function addGlobal_refinery() : void
    {
        $refineryMock = $this->getMockBuilder(RefineryFactory::class)->disableOriginalConstructor()->getMock();
        $refineryMock->expects(self::any())->method('random')->willReturn($this->getMockBuilder(RandomGroup::class)->getMock());
        $this->setGlobalVariable("refinery", $refineryMock);
    }
}
