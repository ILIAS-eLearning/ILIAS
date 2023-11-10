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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration\Bundle;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\Intercept;
use ILIAS\LegalDocuments\Value\Target;
use ILIAS\LegalDocuments\GotoLink;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\In\Group as InGroup;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\PageFragment;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\LegalDocuments\Provide;
use ILIAS\Data\Clock\ClockInterface as Clock;
use ILIAS\LegalDocuments\Internal;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Conductor;
use ilCtrl;
use ilLegalDocumentsAgreementGUI;
use ilGlobalTemplateInterface;
use ilObjUser;

require_once __DIR__ . '/ContainerMock.php';

class ConductorTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Conductor::class, new Conductor($this->mock(Container::class), $this->mock(Internal::class), $this->mock(Clock::class)));
    }

    public function testProvide(): void
    {
        $instance = new Conductor($this->mock(Container::class), $this->mock(Internal::class), $this->mock(Clock::class));
        $this->assertInstanceOf(Provide::class, $instance->provide('foo'));
    }

    public function testOnLogout(): void
    {
        $constraint = $this->mock(Constraint::class);
        $called = false;

        $ctrl = $this->mock(ilCtrl::class);
        $ctrl->expects(self::once())->method('setParameterByClass')->with('dummy gui', 'withdraw_from', 'foo');

        $container = $this->mockTree(Container::class, [
            'refinery' => ['to' => ['string' => $constraint]],
            'http' => ['wrapper' => ['query' => $this->mockMethod(ArrayBasedRequestWrapper::class, 'retrieve', ['withdraw_consent', $constraint], 'foo')]],
            'ctrl' => $ctrl,
        ]);

        $internal = $this->mockMethod(Internal::class, 'get', ['logout', 'foo'], static function () use (&$called): void {
            $called = true;
        });

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));

        $instance->onLogout('dummy gui');
        $this->assertTrue($called);
    }

    public function testLoginPageHTML(): void
    {
        $components = [
            $this->mock(Component::class),
            $this->mock(Component::class),
        ];

        $space = $this->mock(Legacy::class);

        $container = $this->mockTree(Container::class, [
            'ui' => [
                'renderer' => $this->mockMethod(Renderer::class, 'render', [
                    $components,
                ], 'rendered'),
            ],
        ]);

        $internal = $this->mockMethod(Internal::class, 'get', ['show-on-login-page', 'foo'], fn() => $components);

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));

        $this->assertSame('rendered', $instance->loginPageHTML('foo'));
    }

    public function testLogoutText(): void
    {
        $constraint = $this->mock(Constraint::class);
        $component = $this->mock(Component::class);

        $container = $this->mockTree(Container::class, [
            'refinery' => ['to' => ['string' => $constraint]],
            'http' => ['wrapper' => ['query' => $this->mockMethod(ArrayBasedRequestWrapper::class, 'retrieve', ['withdraw_from', $constraint], 'foo')]],
            'ui' => ['renderer' => $this->mockMethod(Renderer::class, 'render', [$component], 'rendered')],
        ]);

        $internal = $this->mockMethod(Internal::class, 'get', ['logout-text', 'foo'], static function () use ($component): Component {
            return $component;
        });

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));

        $this->assertSame('rendered', $instance->logoutText());
        ;
    }

    public function testModifyFooter(): void
    {
        $footer = $this->mock(Footer::class);

        $modify_footer = function (Footer $f) use ($footer) {
            $this->assertSame($footer, $f);
            return $f;
        };

        $instance = new Conductor($this->mock(Container::class), $this->mockMethod(Internal::class, 'all', ['footer'], [
            $modify_footer,
            $modify_footer,
        ]), $this->mock(Clock::class));

        $this->assertSame($footer, $instance->modifyFooter($footer));
    }

    /**
     * @dataProvider agreeTypes
     */
    public function testAgree(string $gui, string $key): void
    {
        $main_template = $this->mock(ilGlobalTemplateInterface::class);
        $main_template->expects(self::once())->method('setContent')->with('rendered');
        $this->agreement('agree', $gui, $key, $main_template);
    }

    /**
     * @dataProvider agreeTypes
     */
    public function testAgreeContent(string $gui, string $key): void
    {
        $this->assertSame('rendered', $this->agreement('agreeContent', $gui, $key));
    }

    public function testWithdraw(): void
    {
        $main_template = $this->mock(ilGlobalTemplateInterface::class);
        $main_template->expects(self::once())->method('setContent')->with('rendered');
        $this->agreement('withdraw', 'foo', 'withdraw', $main_template);
    }

    public function testUsersWithHiddenOnlineStatus(): void
    {
        $internal = $this->mockMethod(Internal::class, 'all', ['filter-online-users'], [
            fn() => [4, 5, 6],
            fn() => [5, 6],
        ]);
        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));

        $this->assertSame(
            [1, 2, 3, 4, 7, 8, 9],
            $instance->usersWithHiddenOnlineStatus([1, 2, 3, 4, 5, 6, 7, 8, 9])
        );
    }

    public function testUserCanReadInternalMail(): void
    {
        $constraints = [
            'foo' => $this->mock(Constraint::class),
            'bar' => $this->mock(Constraint::class),
        ];

        $series = $this->mock(Transformation::class);

        $container = $this->mockTree(Container::class, [
            'refinery' => ['in' => $this->mockMethod(
                InGroup::class,
                'series',
                [array_values($constraints)],
                $series
            )],
        ]);

        $internal = $this->mockMethod(Internal::class, 'all', ['constrain-internal-mail'], $constraints);

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));
        $this->assertSame($series, $instance->userCanReadInternalMail());
    }

    public function testCanUseSoapApi(): void
    {
        $constraints = [
            'foo' => $this->mock(Constraint::class),
            'bar' => $this->mock(Constraint::class),
        ];

        $container = $this->mockTree(Container::class, [
            'refinery' => ['in' => $this->mockMethod(InGroup::class, 'series', [array_values($constraints)], $this->mock(Transformation::class))]
        ]);

        $internal = $this->mockMethod(Internal::class, 'all', ['use-soap-api'], $constraints);

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));
        $this->assertInstanceOf(Transformation::class, $instance->canUseSoapApi());
    }

    public function testAfterLogin(): void
    {
        $called = [false, false];

        $internal = $this->mockMethod(Internal::class, 'all', ['after-login'], [
            function () use (&$called): void {
                $called[0] = true;
            },
            function () use (&$called): void {
                $called[1] = true;
            }
        ]);

        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));

        $instance->afterLogin();

        $this->assertSame([true, true], $called);
    }

    public function testFindGotoLink(): void
    {
        $foo = $this->mock(Target::class);
        $internal = $this->mockMethod(Internal::class, 'all', ['goto'], [
            $this->mockTree(GotoLink::class, ['name' => 'bar']),
            $this->mockTree(GotoLink::class, ['name' => 'foo', 'target' => $foo]),
        ]);

        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));

        $target = $instance->findGotoLink('foo');
        $this->assertTrue($target->isOk());
        $this->assertSame($foo, $target->value());
    }


    public function testIntercepting(): void
    {
        $intercepting = [
            'foo' => $this->mock(Intercept::class),
            'bar' => $this->mock(Intercept::class)
        ];

        $internal = $this->mockMethod(Internal::class, 'all', ['intercept'], $intercepting);

        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));
        $this->assertSame($intercepting, $instance->intercepting());
    }

    public function testSelfRegistration(): void
    {
        $internal = $this->mockMethod(Internal::class, 'all', ['self-registration'], [
            $this->mock(SelfRegistration::class),
        ]);

        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));

        $this->assertInstanceOf(Bundle::class, $instance->selfRegistration());
    }

    public function testUserManagementFields(): void
    {
        $internal = $this->mockMethod(Internal::class, 'all', ['user-management-fields'], [
            fn() => ['foo' => 'bar', 'baz' => 'hej'],
            fn() => ['hoo' => 'har'],
        ]);

        $instance = new Conductor($this->mock(Container::class), $internal, $this->mock(Clock::class));

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'hej',
            'hoo' => 'har',
        ], $instance->userManagementFields($this->mock(ilObjUser::class)));
    }

    public function agreeTypes(): array
    {
        return [
            'Form type' => [ilLegalDocumentsAgreementGUI::class, 'agreement-form'],
            'Public type' => ['foo', 'public-page'],
        ];
    }

    private function agreement(string $method, string $gui, string $key, ?ilGlobalTemplateInterface $main_template = null)
    {
        $constraint = $this->mock(Constraint::class);

        $ctrl = $this->mock(ilCtrl::class);
        $ctrl->expects(self::once())->method('setParameterByClass')->with($gui, 'id', 'foo');

        $container = $this->mockTree(Container::class, [
            'refinery' => ['to' => ['string' => $constraint]],
            'http' => ['wrapper' => ['query' => $this->mockMethod(ArrayBasedRequestWrapper::class, 'retrieve', ['id', $constraint], 'foo')]],
            'ui' => [
                'mainTemplate' => $main_template ?? $this->mock(ilGlobalTemplateInterface::class),
                'renderer' => $this->mock(Renderer::class),
            ],
            'ctrl' => $ctrl,
        ]);

        $fragment = $this->mockMethod(PageFragment::class, 'render', [$container->ui()->mainTemplate(), $container->ui()->renderer()], 'rendered');

        $internal = $this->mockMethod(Internal::class, 'get', [$key, 'foo'], function (string $g, string $cmd) use ($fragment, $gui): Result {
            $this->assertSame($gui, $g);
            $this->assertSame('some cmd', $cmd);
            return new Ok($fragment);
        });

        $instance = new Conductor($container, $internal, $this->mock(Clock::class));

        return $instance->$method($gui, 'some cmd');
    }
}
