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

use ILIAS\Administration\Setting;
use ILIAS\Test\RequestDataCollector;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\Container;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Implementation\DefaultRenderer as ImplementationDefaultRenderer;
use ILIAS\UI\Implementation\FactoryInternal;
use ILIAS\UI\Implementation\Factory as ImplementationFactory;
use ILIAS\User\PublicInterface;
use GuzzleHttp\Psr7\Uri as GuzzleURI;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

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
        ilBenchmark::class => 'ilBench',
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
        ilObjTest::class => 'ilObjTest',
        \ILIAS\Cache\Services::class => 'global_cache',
    ];

    protected function defineGlobalConstants(): void
    {
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'http://localhost');
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', '/var/iliasdata');
        }
        if (!defined('IL_INST_ID')) {
            define('IL_INST_ID', '0');
        }
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
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
     * @return ilTemplate|mixed|MockObject
     */
    protected function getGlobalTemplateMock()
    {
        return $this->createMock(ilTemplate::class);
    }

    /**
     * @return ilDBInterface|mixed|MockObject
     */
    protected function getDatabaseMock()
    {
        return $this->createMock(ilDBInterface::class);
    }

    /**
     * @return ILIAS|mixed|MockObject
     */
    protected function getIliasMock()
    {
        $mock = $this->createMock(ILIAS::class);

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $mock->account = $account;
        $mock->ini_ilias = $this->createMock(ilIniFile::class);

        return $mock;
    }

    protected function addGlobal_ilAccess(): void
    {
        $this->setGlobalVariable('ilAccess', $this->createMock(ilAccess::class));
    }

    protected function addGlobal_dataFactory(): void
    {
        $this->setGlobalVariable('DataFactory', $this->createMock(DataFactory::class));
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

    protected function addGlobal_static_url(): void
    {
        $this->setGlobalVariable('static_url', $this->createMock(ILIAS\StaticURL\Services::class));
    }

    protected function addGlobal_upload(): void
    {
        $this->setGlobalVariable('upload', $this->createMock(FileUpload::class));
    }

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
        $request_mock = $this->getMockBuilder(\Psr\Http\Message\ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request_mock->method('getUri')
            ->willReturn(new GuzzleURI('http://wwww.ilias.de'));
        $http_mock = $this->getMockBuilder(HTTPServices::class)->disableOriginalConstructor()
            ->getMock();
        $http_mock->method('request')
            ->willReturn($request_mock);
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

    protected function addGlobal_uiService(): void
    {
        $this->setGlobalVariable('uiService', $this->createMock(\ilUIService::class));
    }

    protected function addGlobal_uiFactory(): void
    {
        $this->setGlobalVariable('ui.factory', $this->createMock(FactoryInternal::class));
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

    protected function addGlobal_objectMetadata(): void
    {
        $this->setGlobalVariable('learning_object_metadata', $this->createMock(ILIAS\MetaData\Services\ServicesInterface::class));
    }

    protected function addGlobal_objectService(): void
    {
        $this->setGlobalVariable('object.customicons.factory', $this->getMockBuilder(ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\Factory::class)->disableOriginalConstructor()->getMock());
        $object_mock = $this->getMockBuilder(\ilObjectService::class)->disableOriginalConstructor()->getMock();

        $this->setGlobalVariable('object', $object_mock);
    }

    protected function addGlobal_resourceStorage(): void
    {
        $this->setGlobalVariable('resource_storage', $this->createMock(\ILIAS\ResourceStorage\Services::class));
    }

    protected function addGlobal_fileDelivery(): void
    {
        $this->setGlobalVariable(
            'file_delivery',
            $this->getFileDelivery()
        );
    }

    protected function addGlobal_user(): void
    {
        $this->setGlobalVariable(
            'user',
            $this->createMock(PublicInterface::class)
        );
    }

    protected function addGlobal_uiUploadLimitResolver(): void
    {
        $this->setGlobalVariable('ui.upload_limit_resolver', $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class));
    }

    protected function addGlobal_mail(): void
    {
        $this->setGlobalVariable(
            \ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface::class,
            $this->createMock(\ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface::class)
        );
    }

    protected function getFileDelivery(): \ILIAS\FileDelivery\Services
    {
        $data_signer = new ILIAS\FileDelivery\Token\DataSigner(
            new ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation(
                new ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey('blup')
            )
        );
        $http_mock = $this->createMock(HTTPServices::class);
        $response_builder_mock = $this->createMock(\ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder::class);
        return new \ILIAS\FileDelivery\Services(
            new ILIAS\FileDelivery\Delivery\StreamDelivery(
                $data_signer,
                $http_mock,
                $response_builder_mock,
                $response_builder_mock
            ),
            new \ILIAS\FileDelivery\Delivery\LegacyDelivery(
                $http_mock,
                $response_builder_mock,
                $response_builder_mock
            ),
            $data_signer,
            $http_mock
        );
    }

    protected function getTestObjMock(): ilObjTest
    {
        return $this->createConfiguredMock(ilObjTest::class, [
            'getLocalDIC' => $this->buildLocalDICMock()
        ]);
    }

    protected function buildLocalDICMock(): ILIAS\Test\TestDIC
    {
        $local_dic_mock = $this->getMockBuilder(ILIAS\Test\TestDIC::class)
            ->onlyMethods([])->getMock();
        $local_dic_mock['question.general_properties.repository'] = fn(Pimple\Container $c)
            => $this->createMock(
                ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class
            );
        $local_dic_mock['request_data_collector'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ILIAS\Test\RequestDataCollector::class
            );
        $local_dic_mock['participant.access_filter.factory'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ilTestParticipantAccessFilterFactory::class
            );
        $local_dic_mock['logging.logger'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ILIAS\Test\Logging\TestLogger::class
            );
        $local_dic_mock['logging.viewer'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ILIAS\Test\Logging\TestLogViewer::class
            );
        $local_dic_mock['shuffler'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ilTestShuffler::class
            );
        $local_dic_mock['results.presentation.factory'] = fn(Pimple\Container $c)
            => $this->createMock(
                ILIAS\Test\Results\Presentation\Factory::class
            );
        $local_dic_mock['results.data.factory'] = fn(Pimple\Container $c)
            => $this->createMock(
                ILIAS\Test\Results\Data\Factory::class
            );
        $local_dic_mock['results.toplist.repository'] = fn(Pimple\Container $c)
            => $this->createMock(
                ILIAS\Test\Results\Toplist\TestTopListRepository::class
            );
        $local_dic_mock['results.data.repository'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ILIAS\Test\Results\Data\Repository::class
            );
        $local_dic_mock['questions.properties.repository'] = fn(Pimple\Container $c)
            => $this->createMock(
                ILIAS\Test\Questions\Properties\Repository::class
            );
        return $local_dic_mock;
    }

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
     */
    protected function expectTplContent(mixed $expected_content): void
    {
        $this->dic['tpl']->expects($this->once())->method('setContent')->with($expected_content);
    }

    public function mockCommand(string $command): void
    {
        $this->dic['ilCtrl']->method('getCmd')->willReturn($command);
    }

    public function expectRedirect(InvocationOrder $expects, string $method): void
    {
        $this->dic['ilCtrl']->expects($expects)->method('redirect')->with($this->anything(), $this->equalTo($method));
    }

    public function mockPostRequest(array $properties, array $query_parameters = []): void
    {
        $this->mockRequest('POST', $properties, $query_parameters);
    }

    public function mockGetRequest(array $query_parameters = []): void
    {
        $this->mockRequest('GET', [], $query_parameters);
    }

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
