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

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri as GuzzleURI;
use ILIAS\Administration\Setting;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Services as FileDeliveryServices;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\ResourceStorage\Services;
use ILIAS\Skill\Service\SkillService;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\DefaultRenderer as ImplementationDefaultRenderer;
use ILIAS\UI\Implementation\Factory as ImplementationFactory;
use ILIAS\UI\Renderer as UIRenderer;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

trait ilTestBaseTestCaseTrait
{
    private array $services = [
        ilAccess::class => 'ilAccess',
        ilAccessHandler::class => 'ilAccess',
        ilOrgUnitPositionAndRBACAccessHandler::class => 'ilAccess',
        ilOrgUnitPositionAccessHandler::class => 'ilAccess',
        ilRBACAccessHandler::class => 'ilAccess',
        DataFactory::class => 'DataFactory',
        ilGlobalPageTemplate::class => 'tpl',
        ilGlobalTemplateInterface::class => 'tpl',
        ilDBInterface::class => 'ilDB',
        ilObjUser::class => 'ilUser',
        ilErrorHandling::class => 'ilErr',
        ilTree::class => 'tree',
        ilLanguage::class => 'lng',
        Language::class => 'lng',
        ilAppEventHandler::class => 'ilAppEventHandler',
        ilObjectDefinition::class => 'objDefinition',
        RefineryFactory::class => 'refinery',
        ilRbacSystem::class => 'rbacsystem',
        ilRbacReview::class => 'rbacreview',
        ilRbacAdmin::class => 'rbacadmin',
        HTTPServices::class => 'http',
        GlobalHttpState::class => 'http',
        ilComponentFactory::class => 'component.factory',
        ilComponentRepository::class => 'component.repository',
        ImplementationFactory::class => 'ui.factory',
        UIFactory::class => 'ui.factory',
        ImplementationDefaultRenderer::class => 'ui.renderer',
        UIRenderer::class => 'ui.renderer',
        ilUIService::class => 'uiService',
        StaticURLServices::class => 'static_url',
        FileUpload::class => 'upload',
        ilLogger::class => 'ilLog',
        ilSetting::class => 'ilSetting',
        Setting::class => 'ilSetting',
        ilCtrl::class => 'ilCtrl',
        ilCtrlInterface::class => 'ilCtrl',
        ilObjectDataCache::class => 'ilObjDataCache',
        ilHelpGUI::class => 'ilHelp',
        ilCtrlBaseClassInterface::class => 'ilHelp',
        ilTabsGUI::class => 'ilTabs',
        ilLocatorGUI::class => 'ilLocator',
        ilToolbarGUI::class => 'ilToolbar',
        ilObjectCustomIconFactory::class => 'object.customicons.factory',
        Filesystems::class => 'filesystem',
        ilObjTest::class => 'ilObjTest'
    ];

    protected function defineGlobalConstants(): void
    {
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'http://localhost');
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', '/var/iliasdata');
        }
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }
        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', 8);
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', true);
        }
        if (!defined('ILIAS_LOG_DIR')) {
            define('ILIAS_LOG_DIR', '/var/log');
        }
        if (!defined('ILIAS_LOG_FILE')) {
            define('ILIAS_LOG_FILE', '/var/log/ilias.log');
        }
        if (!defined('IL_INST_ID')) {
            define('IL_INST_ID', 'someid');
        }
        if (!defined('CLIENT_WEB_DIR')) {
            define('CLIENT_WEB_DIR', './public/data');
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', './external_data');
        }
        if (!defined('CLIENT_ID')) {
            define('CLIENT_ID', 'default');
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, mixed $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function getGlobalTemplateMock(): ilTemplate
    {
        return $this->getMockBuilder(ilTemplate::class)->disableOriginalConstructor()->getMock();
    }

    protected function getDatabaseMock(): ilDBInterface
    {
        return $this->getMockBuilder(ilDBInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @throws Exception
     */
    protected function getIliasMock(): ILIAS
    {
        $mock = $this->createMock(ILIAS::class);

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $mock->account = $account;
        $mock->ini_ilias = $this->createMock(ilIniFile::class);

        return $mock;
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilAccess(): void
    {
        $this->setGlobalVariable('ilAccess', $this->createMock(ilAccess::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_dataFactory(): void
    {
        $this->setGlobalVariable('DataFactory', $this->createMock(DataFactory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilUser(): void
    {
        $this->setGlobalVariable('ilUser', $this->createMock(ilObjUser::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_objDefinition(): void
    {
        $this->setGlobalVariable('objDefinition', $this->createMock(ilObjectDefinition::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_tree(): void
    {
        $this->setGlobalVariable('tree', $this->createMock(ilTree::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilSetting(): void
    {
        $this->setGlobalVariable('ilSetting', $this->createMock(ilSetting::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_rbacsystem(): void
    {
        $this->setGlobalVariable('rbacsystem', $this->createMock(ilRbacSystem::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilRbacAdmin(): void
    {
        $this->setGlobalVariable('rbacadmin', $this->createMock(ilRbacAdmin::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilCtrl(): void
    {
        $this->setGlobalVariable('ilCtrl', $this->createMock(ilCtrl::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_lng(): void
    {
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_filesystem(): void
    {
        $this->setGlobalVariable('filesystem', $this->createMock(Filesystems::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_static_url(): void
    {
        $this->setGlobalVariable('static_url', $this->createMock(StaticURLServices::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_upload(): void
    {
        $this->setGlobalVariable('upload', $this->createMock(FileUpload::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilDB(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->method('loadModule')->willReturnCallback(
            function ($module): ilDBPdoManager|ilDBPdoReverse|null {
                return match ($module) {
                    ilDBConstants::MODULE_MANAGER => $this->createMock(ilDBPdoManager::class),
                    ilDBConstants::MODULE_REVERSE => $this->createMock(ilDBPdoReverse::class),
                    default => null
                };
            }
        );

        $this->setGlobalVariable('ilDB', $db);
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilBench(): void
    {
        $this->setGlobalVariable('ilBench', $this->createMock(ilBenchmark::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilLog(): void
    {
        $this->setGlobalVariable('ilLog', $this->createMock(ilLogger::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilias(): void
    {
        $this->setGlobalVariable('ilias', $this->getIliasMock());
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilErr(): void
    {
        $this->setGlobalVariable('ilErr', $this->createMock(ilErrorHandling::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_GlobalScreenService(): void
    {
        $this->setGlobalVariable('global_screen', $this->createMock(GlobalScreenServices::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilNavigationHistory(): void
    {
        $this->setGlobalVariable('ilNavigationHistory', $this->createMock(ilNavigationHistory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilAppEventHandler(): void
    {
        $this->setGlobalVariable('ilAppEventHandler', $this->createMock(ilAppEventHandler::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_tpl(): void
    {
        $this->setGlobalVariable('tpl', $this->createMock(ilGlobalPageTemplate::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilComponentRepository(): void
    {
        $this->setGlobalVariable('component.repository', $this->createMock(ilComponentRepository::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilComponentFactory(): void
    {
        $this->setGlobalVariable('component.factory', $this->createMock(ilComponentFactory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilTabs(): void
    {
        $this->setGlobalVariable('ilTabs', $this->createMock(ilTabsGUI::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilObjDataCache(): void
    {
        $this->setGlobalVariable('ilObjDataCache', $this->createMock(ilObjectDataCache::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilLocator(): void
    {
        $this->setGlobalVariable('ilLocator', $this->createMock(ilLocatorGUI::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_rbacreview(): void
    {
        $this->setGlobalVariable('rbacreview', $this->createMock(ilRbacReview::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilToolbar(): void
    {
        $this->setGlobalVariable('ilToolbar', $this->createMock(ilToolbarGUI::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilObjectCustomIconFactory(): void
    {
        $this->setGlobalVariable('object.customicons.factory', $this->createMock(ilObjectCustomIconFactory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_http(): void
    {
        $extended_request = new class('GET', '') extends ServerRequest {
            public static function getUriFromGlobals(): UriInterface
            {
              return new GuzzleURI('http://wwww.ilias.de');
            }

            public function getUri(): UriInterface
            {
              return new GuzzleURI('http://wwww.ilias.de');
            }
        };
        $http_mock = $this->getMockBuilder(HTTPServices::class)->disableOriginalConstructor()
            ->getMock();
        $http_mock->method('request')
            ->willReturn($extended_request);
        $this->setGlobalVariable('http', $http_mock);
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilIliasIniFile(): void
    {
        $this->setGlobalVariable('ilIliasIniFile', $this->createMock(ilIniFile::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilLoggerFactory(): void
    {
        $this->setGlobalVariable('ilLoggerFactory', $this->createMock(ilLoggerFactory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilHelp(): void
    {
        $this->setGlobalVariable('ilHelp', $this->createMock(ilHelpGUI::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_uiService(): void
    {
        $this->setGlobalVariable('uiService', $this->createMock(ilUIService::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_uiFactory(): void
    {
        $this->setGlobalVariable('ui.factory', $this->createMock(UIFactory::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_uiRenderer(): void
    {
        $this->setGlobalVariable('ui.renderer', $this->createMock(ImplementationDefaultRenderer::class));
    }

    protected function addGlobal_refinery(): void
    {
        $refineryMock = $this->getMockBuilder(RefineryFactory::class)->disableOriginalConstructor()->getMock();
        $refineryMock->method('random')->willReturn($this->getMockBuilder(RandomGroup::class)->getMock());
        $this->setGlobalVariable('refinery', $refineryMock);
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_skillService(): void
    {
        $this->setGlobalVariable('skill', $this->createMock(SkillService::class));
    }

    protected function addGlobal_objectService(): void
    {
        global $DIC;
        $DIC['object.customicons.factory'] = $this->getMockBuilder(ilObjectCustomIconFactory::class)->disableOriginalConstructor()->getMock();
        $object_mock = $this->getMockBuilder(ilObjectService::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('object', $object_mock);
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_resourceStorage(): void
    {
        $this->setGlobalVariable('resource_storage', $this->createMock(Services::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_fileDelivery(): void
    {
        $this->setGlobalVariable('file_delivery', $this->getFileDelivery());
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_requestDataCollector(): void
    {
        $this->setGlobalVariable('request_data_collector', $this->createMock(RequestDataCollector::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_ilObjTest(): void
    {
        $this->setGlobalVariable('ilObjTest', $this->createConfiguredMock(ilObjTest::class, [
            'getLocalDIC' => $this->buildLocalDICMock()
        ]));
    }

    /**
     * @throws Exception
     */
    protected function getFileDelivery(): FileDeliveryServices
    {
        $data_signer = new DataSigner(new SecretKeyRotation(new SecretKey('blup')));
        $http_mock = $this->getMockBuilder(HTTPServices::class)->disableOriginalConstructor()->getMock();
        $response_builder_mock = $this->createMock(ResponseBuilder::class);
        return new FileDeliveryServices(
            new StreamDelivery(
                $data_signer,
                $http_mock,
                $response_builder_mock
            ),
            new LegacyDelivery(
                $http_mock,
                $response_builder_mock
            ),
            $data_signer
        );
    }

    protected function getTestObjMock(): ilObjTest
    {
        $test_mock = $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock();
        $test_mock->method('getLocalDIC')->willReturn($this->buildLocalDICMock());
        return $test_mock;
    }

    protected function buildLocalDICMock(): TestDIC
    {
        $local_dic_mock = $this->getMockBuilder(TestDIC::class)->onlyMethods([])->getMock();

        $local_dic_mock['question.general_properties.repository'] = fn(Container $c) => $this->createMock(
            GeneralQuestionPropertiesRepository::class
        );
        $local_dic_mock['request_data_collector'] = fn(Container $c) => $this->createMock(
            RequestDataCollector::class
        );
        $local_dic_mock['participant.access_filter.factory'] = fn(Container $c) => $this->createMock(
            ilTestParticipantAccessFilterFactory::class
        );
        $local_dic_mock['logging.logger'] = fn(Container $c) => $this->createMock(
            TestLogger::class
        );
        $local_dic_mock['logging.viewer'] = fn(Container $c) => $this->createMock(
            TestLogViewer::class
        );
        $local_dic_mock['shuffler'] = fn(Container $c) => $this->createMock(
            ilTestShuffler::class
        );
        $local_dic_mock['results.factory'] = fn(Container $c) => $this->createMock(
            ilTestResultsFactory::class
        );
        $local_dic_mock['results.presentation.factory'] = fn(Container $c) => $this->createMock(
            ilTestResultsPresentationFactory::class
        );

        return $local_dic_mock;
    }

    /**
     * @throws \Exception|ReflectionException|Exception
     */
    protected function adaptDICServiceMock(string $service_name, callable $adapt): void
    {
        $reflection = new ReflectionFunction($adapt);
        if ($reflection->getNumberOfParameters() !== 1) {
            throw new \Exception('Callable must have exactly one parameter of type MockObject.');
        }

        if(isset($this->services[$service_name])) {
            global $DIC;
            if (!isset($DIC[$this->services[$service_name]])) {
                $DIC[$this->services[$service_name]] = $this->createMock($service_name);
            }

            $adapt($DIC[$this->services[$service_name]]);
        }
    }

    /**
     * Expect that the template content will be set to the specified expected content.
     *
     * @param mixed $expectedContent The expected content for the template.
     */
    protected function expectTplContent(mixed $expectedContent): void
    {
        $this->dic['tpl']->expects($this->once())->method('setContent')->with($expectedContent);
    }

    /**
     * Mock a command by configuring the control object to return the specified command.
     *
     * @param string $command The command to be mocked.
     */
    public function mockCommand(string $command): void
    {
        $this->dic['ilCtrl']->method('getCmd')->willReturn($command);
    }

    /**
     * Expect that a redirect will be called with the specified method.
     *
     * @param InvocationOrder $expects The expected invocation order for the redirect method.
     * @param string          $method  The method to be redirected to.
     */
    public function expectRedirect(InvocationOrder $expects, string $method): void
    {
        $this->dic['ilCtrl']->expects($expects)
            ->method('redirect')
            ->with($this->anything(), $this->equalTo($method));
    }

    /**
     * Mock a POST request with the specified properties and optional query parameters.
     *
     * @param array<array-key, mixed> $properties The properties to set in the POST request body.
     * @param array<array-key, mixed> $queryParameters Optional query parameters for the request.
     *
     * @throws Exception
     */
    public function mockPostRequest(array $properties, array $queryParameters = []): void
    {
        $this->mockRequest('POST', $properties, $queryParameters);
    }

    /**
     * Mock a GET request with optional query parameters.
     *
     * @param array<array-key, mixed> $queryParameters Optional query parameters for the request.
     *
     * @throws Exception
     */
    public function mockGetRequest(array $queryParameters = []): void
    {
        $this->mockRequest('GET', [], $queryParameters);
    }

    /**
     * @throws Exception
     */
    private function mockRequest(string $requestMethod, array $properties, array $queryParameters): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $request->method('getServerParams')->willReturn(['REQUEST_METHOD' => $requestMethod]);
        $request->method('getParsedBody')->willReturn($properties);
        $request->method('getQueryParams')->willReturn($queryParameters);
        $this->dic['http']->method('request')->willReturn($request);
        $this->dic['http']->method('wrapper')->willReturn(new WrapperFactory($request));
    }
}
