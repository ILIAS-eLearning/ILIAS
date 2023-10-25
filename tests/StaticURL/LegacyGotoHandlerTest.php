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

namespace ILIAS\StaticURL\Tests;

use ILIAS\StaticURL\Handler\LegacyGotoHandler;
use ILIAS\StaticURL\Request\LegacyRequestBuilder;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Request\Request;
use ILIAS\DI\Container;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LegacyGotoHandlerTest extends Base
{
    public $request_mock;
    public $component_factory_mock;
    public $refinery;
    public $request_builder;
    private \ilCtrlInterface|\PHPUnit\Framework\MockObject\MockObject $ctrl;
    private LegacyGotoHandler $subject;
    /**
     * @var \ILIAS\HTTP\Services|(\ILIAS\HTTP\Services&object&\PHPUnit\Framework\MockObject\MockObject)|(\ILIAS\HTTP\Services&\PHPUnit\Framework\MockObject\MockObject)|(object&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private \ILIAS\HTTP\Services|\PHPUnit\Framework\MockObject\MockObject $http_mock;
    private array $dic_backup = [];

    protected function setUp(): void
    {
        $this->ctrl = $this->createMock(\ilCtrlInterface::class);
        $this->http_mock = $this->createMock(\ILIAS\HTTP\Services::class);
        $this->request_mock = $this->createMock(ServerRequestInterface::class);
        $this->http_mock->method('request')->willReturn($this->request_mock);

        $this->component_factory_mock = $this->createMock(\ilComponentFactory::class);

        $this->refinery = new Factory(
            new \ILIAS\Data\Factory(),
            $this->createMock(\ilLanguage::class),
        );

        $this->request_builder = new LegacyRequestBuilder();
        $this->subject = new LegacyGotoHandler();
    }

    private function updateRequestAndWrapperMockWithParams(array $params): void
    {
        $this->request_mock->method('getQueryParams')->willReturn($params);
        $this->http_mock->method('wrapper')->willReturn(new WrapperFactory($this->request_mock));
    }

    public function urlProvider(): array
    {
        return [
            ['https://ilias.domain/goto.php?client_id=unittest&target=impr', 'impr'],
            ['https://ilias.domain/goto.php?target=root_1&client_id=unittest', 'root_1'],
            ['https://ilias.domain/goto.php?target=root_1&client_id=unittest&lang=de', 'root_1'],
            ['https://ilias.domain/sub/goto.php?target=root_1&client_id=unittest&lang=de', 'root_1'],
            ['https://ilias.domain/goto.php?target=crs_256&client_id=unittest&lang=de', 'crs_256'],
            ['https://ilias.domain/goto.php?target=lorem_256&client_id=unittest&lang=de', 'lorem_256'],
            ['https://ilias.domain/goto.php?target=wiki_wpage_4826_86154&client_id=unittest&lang=de', 'wiki_wpage_4826_86154'],
            ['https://ilias.domain/sub/goto.php?target=wiki_wpage_4826_86154&client_id=unittest&lang=de', 'wiki_wpage_4826_86154'],
            ['https://ilias.domain/goto.php/wiki/wpage_4826_86154', 'wiki_wpage_4826_86154'],
            ['https://ilias.domain/sub/goto.php/wiki/wpage_4826_86154', 'wiki_wpage_4826_86154'],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function testBase(string $called_url, string $target): void
    {
        $this->http_mock->request()->method('getUri')->willReturn(new \GuzzleHttp\Psr7\Uri($called_url));
        $this->assertEquals($called_url, $this->http_mock->request()->getUri());

        $uri = new URI($called_url);
        //$this->assertGreaterThanOrEqual(2, count($uri->getParameters()));

        $this->updateRequestAndWrapperMockWithParams($uri->getParameters());

        $request = $this->request_builder->buildRequest(
            $this->http_mock,
            $this->refinery,
            []
        );

        $this->assertInstanceOf(Request::class, $request);
        $this->assertTrue($this->subject->canHandle($request));
        $this->assertEquals($target, $request->getAdditionalParameters()[LegacyGotoHandler::TARGET]);

    }

    private function insertDIC(string $key, $value): void
    {
        global $DIC;
        $DIC = $DIC instanceof Container ? $DIC : new Container();
        if (isset($DIC[$key])) {
            $this->dic_backup[$key] = clone $DIC[$key];
        }
        $GLOBALS[$key] = $value;
        $DIC->offsetUnset($key);
        $DIC[$key] = static function () use ($value) {
            return $value;
        };
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $DIC instanceof Container ? $DIC : new Container();
        foreach ($this->dic_backup as $key => $value) {
            $DIC->offsetUnset($key);
            $DIC[$key] = $value;
            $GLOBALS[$key] = $value;
        }
    }

    protected function buildDependecies(): void
    {
        $this->insertDIC('component.factory', $this->component_factory_mock);
        $this->insertDIC('ctrl', $this->ctrl);
        $access_mock = $this->createMock(\ilAccessHandler::class);
        $access_mock->method('checkAccess')->willReturn(true);
        $this->insertDIC('ilAccess', $access_mock);
        $this->insertDIC('objDefinition', $this->createMock(\ilObjectDefinition::class));
        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->method('getId')->willReturn(42);
        $this->insertDIC('ilUser', $user_mock);
        $this->insertDIC('http', $this->http_mock);
        $this->insertDIC('tpl', $this->createMock(\ilGlobalTemplateInterface::class));
        $this->insertDIC('lng', $this->createMock(\ilLanguage::class));
        $this->insertDIC('ilObjDataCache', $this->createMock(\ilObjectDataCache::class));
        $this->insertDIC('ilDB', $this->createMock(\ilDBInterface::class));
        $this->insertDIC('tree', $this->createMock(\ilTree::class));
        $this->insertDIC('rbacreview', $this->createMock(\ilRbacReview::class));
        $this->insertDIC('ilSetting', $this->createMock(\ilSetting::class));
        $this->insertDIC('ilErr', $this->createMock(\ilErrorHandling::class));
        $this->insertDIC('ilCtrl', $this->ctrl);

        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', 1);
        }

        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }
    }

}
