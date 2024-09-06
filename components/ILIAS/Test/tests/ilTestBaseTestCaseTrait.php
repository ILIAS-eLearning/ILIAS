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

    /**
     * @throws Exception
     */
    protected function getGlobalTemplateMock(): ilTemplate
    {
        return $this->createMock(ilTemplate::class);
    }

    /**
     * @throws Exception
     */
    protected function getDatabaseMock(): ilDBInterface
    {
        return $this->createMock(ilDBInterface::class);
    }

    /**
     * @throws Exception
     */
    protected function getIliasMock(): ILIAS
    {
        $ilias = $this->createMock(ILIAS::class);

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $ilias->account = $account;
        $ilias->ini_ilias = $this->createMock(ilIniFile::class);

        return $ilias;
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
        $db
            ->method('loadModule')
            ->willReturnCallback(
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

        $http_mock = $this->createMock(HTTPServices::class);
        $http_mock
            ->method('request')
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

    /**
     * @throws Exception
     */
    protected function addGlobal_refinery(): void
    {
        $refinery_factory = $this->createMock(RefineryFactory::class);
        $refinery_factory
            ->method('random')
            ->willReturn($this->createMock(RandomGroup::class));

        $this->setGlobalVariable('refinery', $refinery_factory);
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_skillService(): void
    {
        $this->setGlobalVariable('skill', $this->createMock(SkillService::class));
    }

    /**
     * @throws Exception
     */
    protected function addGlobal_objectService(): void
    {
        global $DIC;
        $DIC['object.customicons.factory'] = $this->createMock(ilObjectCustomIconFactory::class);

        $this->setGlobalVariable('object', $this->createMock(ilObjectService::class));
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
        $http_services = $this->createMock(HTTPServices::class);
        $response_builder = $this->createMock(ResponseBuilder::class);

        return new FileDeliveryServices(
            new StreamDelivery(
                $data_signer,
                $http_services,
                $response_builder,
                $response_builder
            ),
            new LegacyDelivery(
                $http_services,
                $response_builder,
                $response_builder
            ),
            $data_signer
        );
    }

    /**
     * @throws Exception
     */
    protected function getTestObjMock(): ilObjTest
    {
        return $this->createConfiguredMock(ilObjTest::class, [
            'getLocalDIC' => $this->buildLocalDICMock()
        ]);
    }

    protected function buildLocalDICMock(): TestDIC
    {
        $test_dic = $this->getMockBuilder(TestDIC::class)->onlyMethods([])->getMock();

        $test_dic['question.general_properties.repository'] = fn(Container $c) => $this->createMock(
            GeneralQuestionPropertiesRepository::class
        );
        $test_dic['request_data_collector'] = fn(Container $c) => $this->createMock(
            RequestDataCollector::class
        );
        $test_dic['participant.access_filter.factory'] = fn(Container $c) => $this->createMock(
            ilTestParticipantAccessFilterFactory::class
        );
        $test_dic['logging.logger'] = fn(Container $c) => $this->createMock(
            TestLogger::class
        );
        $test_dic['logging.viewer'] = fn(Container $c) => $this->createMock(
            TestLogViewer::class
        );
        $test_dic['shuffler'] = fn(Container $c) => $this->createMock(
            ilTestShuffler::class
        );
        $test_dic['results.factory'] = fn(Container $c) => $this->createMock(
            ilTestResultsFactory::class
        );
        $test_dic['results.presentation.factory'] = fn(Container $c) => $this->createMock(
            ilTestResultsPresentationFactory::class
        );

        return $test_dic;
    }

    /**
     * @throws \Exception|ReflectionException|Exception
     */
    protected function adaptDICServiceMock(string $service_name, callable $adapt): void
    {
        $reflection_function = new ReflectionFunction($adapt);
        if ($reflection_function->getNumberOfParameters() !== 1) {
            throw new \Exception('Callable must have exactly one parameter of type MockObject.');
        }

        if (isset($this->services[$service_name])) {
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
     * @param mixed $expected_content The expected content for the template.
     */
    protected function expectTplContent(mixed $expected_content): void
    {
        $this->dic['tpl']->expects($this->once())->method('setContent')->with($expected_content);
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
        $this->dic['ilCtrl']->expects($expects)->method('redirect')->with($this->anything(), $this->equalTo($method));
    }

    /**
     * Mock a POST request with the specified properties and optional query parameters.
     *
     * @param array<array-key, mixed> $properties The properties to set in the POST request body.
     * @param array<array-key, mixed> $query_parameters Optional query parameters for the request.
     *
     * @throws Exception
     */
    public function mockPostRequest(array $properties, array $query_parameters = []): void
    {
        $this->mockRequest('POST', $properties, $query_parameters);
    }

    /**
     * Mock a GET request with optional query parameters.
     *
     * @param array<array-key, mixed> $query_parameters Optional query parameters for the request.
     *
     * @throws Exception
     */
    public function mockGetRequest(array $query_parameters = []): void
    {
        $this->mockRequest('GET', [], $query_parameters);
    }

    /**
     * @throws Exception
     */
    private function mockRequest(string $request_method, array $properties, array $query_parameters): void
    {
        $_SERVER['REQUEST_METHOD'] = $request_method;
        $request = $this->createConfiguredMock(ServerRequestInterface::class, [
            'getServerParams' => ['REQUEST_METHOD' => $request_method],
            'getParsedBody' => $properties,
            'getQueryParams' => $query_parameters
        ]);
        $this->dic['http']->method('request')->willReturn($request);
        $this->dic['http']->method('wrapper')->willReturn(new WrapperFactory($request));
    }
}
