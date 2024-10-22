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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use ILIAS\Data\Result;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\DI\UIServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\UserSettings;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\DefaultMappings;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Provide;
use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;
use ilObjUser;
use ilSetting;
use ilLanguage;
use ilGlobalTemplateInterface;
use ilCtrl;
use stdClass;
use DateTimeImmutable;
use ILIAS\LegalDocuments\PageFragment\PageContent;

require_once __DIR__ . '/../ContainerMock.php';

class BlocksTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Blocks::class, new Blocks('foo', $this->mock(Container::class), $this->mock(Provide::class)));
    }

    public function testConstructors(): void
    {
        $instance = new Blocks('foo', $this->mock(Container::class), $this->mock(Provide::class));

        $this->assertInstanceOf(DefaultMappings::class, $instance->defaultMappings());
        $this->assertInstanceOf(Marshal::class, $instance->marshal());
        $this->assertInstanceOf(SelectSetting::class, $instance->selectSettingsFrom($this->mock(KeyValueStore::class)));
        $this->assertInstanceOf(KeyValueStore::class, $instance->readOnlyStore($this->mock(KeyValueStore::class)));
        $this->assertInstanceOf(KeyValueStore::class, $instance->userStore($this->mock(ilObjUser::class)));
        $this->assertInstanceOf(KeyValueStore::class, $instance->sessionStore());
        $this->assertInstanceOf(User::class, $instance->user(
            $this->mock(Settings::class),
            $this->mock(UserSettings::class),
            $this->mock(ilObjUser::class)
        ));
    }

    public function testGlobalStore(): void
    {
        $instance = new Blocks(
            'foo',
            $this->mockMethod(Container::class, 'settings', [], $this->mock(ilSetting::class)),
            $this->mock(Provide::class)
        );

        $this->assertInstanceOf(KeyValueStore::class, $instance->globalStore());

    }

    public function testUi(): void
    {
        $container = $this->mock(Container::class);
        $container->expects(self::once())->method('ui')->willReturn($this->mock(UIServices::class));
        $container->expects(self::once())->method('language')->willReturn($this->mock(ilLanguage::class));

        $instance = new Blocks(
            'foo',
            $container,
            $this->mock(Provide::class)
        );

        $this->assertInstanceOf(UI::class, $instance->ui());
    }

    public function testRouting(): void
    {
        $container = $this->mockMethod(Container::class, 'ctrl', [], $this->mock(ilCtrl::class));

        $instance = new Blocks('foo', $container, $this->mock(Provide::class));
        $this->assertInstanceOf(Routing::class, $instance->routing());
    }

    public function testRetrieveQueryParameter(): void
    {
        $selected = new stdClass();
        $called = false;

        $refinery = $this->mock(Refinery::class);
        $transformation = $this->mock(Transformation::class);

        $container = $this->mockTree(Container::class, [
            'refinery' => $refinery,
            'http' => [
                'wrapper' => [
                    'query' => $this->mockMethod(ArrayBasedRequestWrapper::class, 'retrieve', ['bar', $transformation], $selected)
                ],
            ],
        ]);

        $instance = new Blocks('foo', $container, $this->mock(Provide::class));
        $result = $instance->retrieveQueryParameter('bar', function (Refinery $r) use (&$called, $transformation, $refinery): object {
            $this->assertSame($refinery, $r);
            $called = true;
            return $transformation;
        });

        $this->assertSame($selected, $result);
        $this->assertTrue($called);
    }

    public function testUserManagementAgreeDateField(): void
    {
        $result = $this->mockTree(Result::class, ['value' => 'dummy']);
        $result->expects(self::once())->method('map')->willReturn($result);
        $result->expects(self::once())->method('except')->willReturn($result);
        $user = $this->mock(ilObjUser::class);
        $date = new DateTimeImmutable();
        $ldoc_user = $this->mockTree(User::class, [
            'agreeDate' => ['value' => $date],
            'acceptedVersion' => $result,
        ]);

        $language = $this->mock(ilLanguage::class);
        $language->expects(self::exactly(2))->method('loadLanguageModule')->withConsecutive(['dummy lang'], ['ldoc']);

        $instance = new Blocks(
            'foo',
            $this->mockTree(Container::class, ['language' => $language]),
            $this->mock(Provide::class),
            function (DateTimeImmutable $d) use ($date) {
                $this->assertSame($date, $d);
                return 'formatted date';
            }
        );
        $proc = $instance->userManagementAgreeDateField(function (ilObjUser $u) use ($user, $ldoc_user) {
            $this->assertSame($user, $u);
            return $ldoc_user;
        }, 'foo', 'dummy lang');

        $this->assertSame(['foo' => 'dummy'], $proc($user));
    }

    public function testWithRequest(): void
    {
        $data = new stdClass();
        $called = false;

        $container = $this->mockTree(Container::class, [
            'http' => ['request' => $this->mockMethod(ServerRequestInterface::class, 'getMethod', [], 'POST')],
        ]);

        $form = $this->mock(Form::class);
        $form->expects(self::once())->method('withRequest')->with($container->http()->request())->willReturn($form);
        $form->expects(self::once())->method('getData')->willReturn($data);

        $instance = new Blocks('foo', $container, $this->mock(Provide::class));
        $this->assertSame($form, $instance->withRequest($form, function ($x) use ($data, &$called) {
            $this->assertSame($data, $x);
            $called = true;
        }));
        $this->assertTrue($called);
    }

    public function testWithoutRequest(): void
    {
        $container = $this->mockTree(Container::class, [
            'http' => ['request' => ['getMethod' => 'GET']],
        ]);

        $form = $this->mock(Form::class);

        $instance = new Blocks('foo', $container, $this->mock(Provide::class));
        $result = $instance->withRequest($form, $this->fail(...));
        $this->assertSame($form, $result);
    }

    public function testNotAvailable(): void
    {
        $called = false;
        $container = $this->mockTree(Container::class, []);

        $instance = new Blocks('foo', $container, $this->mock(Provide::class), null, function () use (&$called): string {
            $called = true;
            return 'bar';
        });

        $result = $instance->notAvailable();
        $this->assertInstanceOf(PageContent::class, $result);
        $this->assertTrue($called);
    }
}
