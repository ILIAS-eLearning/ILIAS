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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\StaticURL\Builder\URIBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\DI\Container;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\UI\Implementation\Factory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;
use GuzzleHttp\Psr7\Uri as GuzzleURI;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Psr\Http\Message\ServerRequestInterface;

trait ilTestBaseTestCaseTrait
{
    protected function defineGlobalConstants(): void
    {
        if (!defined("ILIAS_HTTP_PATH")) {
            define("ILIAS_HTTP_PATH", "http://localhost");
        }
        if (!defined("CLIENT_DATA_DIR")) {
            define("CLIENT_DATA_DIR", "/var/iliasdata");
        }
        if (!defined('IL_INST_ID')) {
            define('IL_INST_ID', '0');
        }
        if (!defined("ANONYMOUS_USER_ID")) {
            define("ANONYMOUS_USER_ID", 13);
        }
        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", 8);
        }
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", true);
        }
        if (!defined("ILIAS_LOG_DIR")) {
            define("ILIAS_LOG_DIR", '/var/log');
        }
        if (!defined("ILIAS_LOG_FILE")) {
            define("ILIAS_LOG_FILE", '/var/log/ilias.log');
        }
    }

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
        $db->method('loadModule')->willReturnCallback(
            function ($module): \ilDBPdoManager|\ilDBPdoReverse|null {
                switch ($module) {
                    case ilDBConstants::MODULE_MANAGER:
                        return $this->createMock(ilDBPdoManager::class);
                    case ilDBConstants::MODULE_REVERSE:
                        return $this->createMock(ilDBPdoReverse::class);
                    default:
                        return null;
                }
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

    protected function addGlobal_ilObjectCustomIconFactory(): void
    {
        $this->setGlobalVariable('object.customicons.factory', $this->createMock(ilObjectCustomIconFactory::class));
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

    protected function getFileDelivery(): \ILIAS\FileDelivery\Services
    {
        $data_signer = new ILIAS\FileDelivery\Token\DataSigner(
            new ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation(
                new ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey('blup')
            )
        );
        $http_mock = $this->getMockBuilder(HTTPServices::class)->disableOriginalConstructor()->getMock();
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
            $data_signer
        );
    }

    protected function getTestObjMock(): ilObjTest
    {
        $test_mock = $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock();
        $test_mock->method('getLocalDIC')->willReturn(
            $this->buildLocalDICMock()
        );
        return $test_mock;
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
        $local_dic_mock['results.factory'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ilTestResultsFactory::class
            );
        $local_dic_mock['results.presentation.factory'] = fn(Pimple\Container $c)
            => $this->createMock(
                \ilTestResultsPresentationFactory::class
            );
        return $local_dic_mock;
    }

    protected function mockRbacAccess(bool $will_return): void
    {
        $this->dic['rbacsystem']
            ->expects($this->any())
            ->method("checkAccess")
            ->willReturn($will_return);
    }

    protected function mockObjectDefinitionGetClassName(string $willReturn): void
    {
        $this->dic['objDefinition']
            ->expects($this->any())
            ->method("getClassName")
            ->willReturn($willReturn);
    }

    protected function mockDBQuery(ilDBStatement $ilDBStatement): void
    {
        $this->dic['ilDB']
            ->expects($this->any())
            ->method("query")
            ->willReturn($ilDBStatement);
    }

    protected function mockDBFetchAssoc(array $willReturn): void
    {
        $this->dic['ilDB']
            ->expects($this->any())
            ->method("fetchAssoc")
            ->willReturn($willReturn);
    }

    /**
     * Expect that the template content will be set to the specified expected content.
     *
     * @param mixed $expectedContent The expected content for the template.
     */
    protected function expectTplContent($expectedContent): void
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
     * @param array<array-key, mixed> $properties      The properties to set in the POST request body.
     * @param array<array-key, mixed> $queryParameters Optional query parameters for the request.
     */
    public function mockPostRequest(array $properties, array $queryParameters = []): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request->method('getServerParams')->willReturn(['REQUEST_METHOD' => 'POST']);
        $request->method('getParsedBody')->willReturn($properties);
        $request->method('getQueryParams')->willReturn($queryParameters);
        $this->dic['http']->method('request')->willReturn($request);
        $this->dic['http']->method('wrapper')->willReturn(new WrapperFactory($request));
    }

    /**
     * Mock a GET request with optional query parameters.
     *
     * @param array<array-key, mixed> $queryParameters Optional query parameters for the request.
     */
    public function mockGetRequest(array $queryParameters = []): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request->method('getServerParams')->willReturn(['REQUEST_METHOD' => 'GET']);
        $request->method('getParsedBody')->willReturn([]);
        $request->method('getQueryParams')->willReturn($queryParameters);
        $this->dic['http']->method('request')->willReturn($request);
        $this->dic['http']->method('wrapper')->willReturn(new WrapperFactory($request));
    }

    protected function mockLanguageVariables(): void
    {
        $this->dic['lng']
            ->method('txt')->willReturnCallback(function ($var): string {
                return $var;
            });
    }

    protected function setUriBuilderMock(URIBuilder $builder): void
    {
        $this->dic['static_url']
            ->method('builder')->willReturn($builder);
    }

    protected function mockUIRenderFunction(string $willReturn): void
    {
        $this->dic['ui.renderer']
            ->method('render')->willReturn($willReturn);
    }



}
