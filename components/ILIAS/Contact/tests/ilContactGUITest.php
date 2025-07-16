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

use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use ILIAS\Contact\BuddySystem\Tables\RelationsTable;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\UI\Component\Listing\Unordered;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Factory as Listing;
use ILIAS\UI\Component\MessageBox\Factory as MessageBoxFactory;
use ILIAS\UI\Component\MessageBox\MessageBox;

require_once __DIR__ . '/../../LegalDocuments/tests/ContainerMock.php';

class ilContactGUITest extends TestCase
{
    use ContainerMock;

    private ?Container $old_dic = null;
    private array $container = [];

    public function setUp(): void
    {
        $this->container = [
            'ilDB' => $this->mock(ilDBInterface::class),
            'tpl' => $this->mock(ilGlobalTemplateInterface::class),
            'ilCtrl' => $this->mock(ilCtrlInterface::class),
            'lng' => $this->mock(ilLanguage::class),
            'ilTabs' => $this->mock(ilTabsGUI::class),
            'ilHelp' => $this->mock(ilHelpGUI::class),
            'ilToolbar' => $this->mock(ilToolbarGUI::class),
            'ilUser' => $this->mock(ilObjUser::class),
            'ilErr' => $this->mock(ilErrorHandling::class),
            'rbacsystem' => $this->mock(ilRbacSystem::class),
            'ui.renderer' => $this->mock(Renderer::class),
            'ui.factory' => $this->mock(UIFactory::class),
            'query' => $this->mock(ArrayBasedRequestWrapper::class),
            'static_url' => $this->mock(StaticURL::class),
            'ilObjDataCache' => $this->mock(ilObjectDataCache::class),
        ];
        $this->old_dic = $GLOBALS['DIC'] ?? null;
        $GLOBALS['DIC'] = $this->mockTree(Container::class, [
            'database' => $this->container['ilDB'],
            'ui' => ['renderer' => $this->container['ui.renderer'], 'factory' => $this->container['ui.factory']],
            'http' => ['wrapper' => ['query' => $this->container['query']]],
        ]);
        $GLOBALS['DIC']->method('offsetGet')->willReturnCallback(fn($k) => $this->container[$k]);
    }

    public function tearDown(): void
    {
        $GLOBALS['DIC'] = $this->old_dic;
    }

    public function testConstruct(): void
    {
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }

        $this->assertInstanceOf(ilContactGUI::class, new ilContactGUI((new class () extends ilFormatMail {
            public function __construct()
            {
            }
        })::class));
    }

    public function testShowContacts(): void
    {
        $this->setMailRefId(123);
        $this->setBuddySystem($this->mockMethod(ilBuddySystem::class, 'isEnabled', [], true));

        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'http://ilias.de/');
        }
        if (!defined('CLIENT_ID')) {
            define('CLIENT_ID', 'dummy');
        }

        $query_params = [
            'inv_room_ref_id' => 9,
            'inv_usr_ids' => [56],
        ];

        $this->container['query']->expects(self::exactly(2))->method('retrieve')->willReturnCallback(fn(string $key) => $query_params[$key]);

        $this->container['query']->method('has')->willReturnCallback(fn($key) => isset($query_params[$key]));

        $relations_table_class = get_class(new class () extends RelationsTable {
            public static array $components;
            public function __construct()
            {
            }

            public function build(array $multi_actions, string $target_url, callable $action): array
            {
                return self::$components;
            }
        });

        $relations_table_class::$components = [$this->mock(Component::class)];

        $ul = $this->mock(Unordered::class);
        $message_box = $this->mock(MessageBox::class);
        $message_box->expects(self::once())->method('withButtons')->willReturn($message_box);


        $this->container['ui.factory']->method('messageBox')->willReturn($this->mockTree(MessageBoxFactory::class, ['success' => $message_box]));
        $this->container['ui.factory']->method('listing')->willReturn($this->mockTree(Listing::class, ['unordered' => $ul]));

        $render_params = [null, 'bar' => $ul, 'foo' => [$message_box, ...$relations_table_class::$components]];
        $this->container['ui.renderer']->expects(self::exactly(2))->method('render')->willReturnCallback(function ($arg) use (&$render_params) {
            $this->assertSame(next($render_params), $arg);
            return key($render_params);
        });
        $this->container['tpl']->expects(self::once())->method('setContent')->with('foo');

        $gui = new ilContactGUI(get_class(new class () extends ilFormatMail {
            public function __construct()
            {
            }
        }), $relations_table_class);

        (new ReflectionMethod($gui, 'showContacts'))->invoke($gui);

        $this->setBuddySystem(null);
        $this->setMailRefId(null);
    }

    private function setBuddySystem(?ilBuddySystem $system): void
    {
        $p = new ReflectionProperty(ilBuddySystem::class, 'instance');
        $p->setValue(null, $system);
    }

    private function setMailRefId(?int $mail_ref): void
    {
        $p = new ReflectionProperty(ilMailGlobalServices::class, 'global_mail_services_cache');
        $array = $p->getValue();
        $array[ilMailGlobalServices::CACHE_TYPE_REF_ID] = $mail_ref;
        $p->setValue(null, $array);
    }
}
