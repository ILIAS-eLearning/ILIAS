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

use ILIAS\LTI\View\AuthSession;
use ILIAS\LTI\View\CurrentUser;
use ILIAS\LTI\View\Navigation;
use ILIAS\LTI\View\ScreenContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ilLTIViewGUITest extends TestCase
{
    /**
     * @return array<string, array{string, bool}>
     */
    public static function authModes(): array
    {
        return [
            'user of a platform' => ['lti_3', true],
            'local user' => ['local', false],
            'no user' => ['', false],
        ];
    }

    #[DataProvider('authModes')]
    public function testIsActiveForUsersOfAPlatformOnly(string $auth_mode, bool $active): void
    {
        $user = $this->createStub(CurrentUser::class);
        $user->method('getAuthMode')->willReturn($auth_mode);

        $this->assertSame($active, $this->getView($user)->isActive());
    }

    public function testCommandsLoadTheLanguageOfTheView(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng->expects($this->once())->method('loadLanguageModule')->with('lti');

        $navigation = $this->createStub(Navigation::class);
        $navigation->method('getCommand')->willReturn('');

        $this->getView($this->createStub(CurrentUser::class), lng: $lng, navigation: $navigation)->executeCommand();
    }

    public function testExitPageIsShownWithoutNavigation(): void
    {
        $screen_context = $this->createMock(ScreenContext::class);
        $screen_context->expects($this->exactly(2))
            ->method('addAdditionalData')
            ->willReturnCallback(function (string $key, mixed $value): void {
                match ($key) {
                    ilLTIViewGUI::GS_EXIT_MODE => $this->assertTrue($value),
                    ilLTIViewGUI::GS_PAGE_TITLE => $this->assertSame('Title', $value),
                };
            });

        $tpl = $this->createMock(ILIAS\UICore\GlobalTemplate::class);
        $tpl->expects($this->once())->method('setTitle')->with('Title', true);
        $tpl->expects($this->once())->method('setContent')->with('content');
        $tpl->expects($this->once())->method('printToStdout');

        $renderer = $this->createStub(ILIAS\UI\Renderer::class);
        $renderer->method('render')->willReturn('content');

        $this->getView(
            $this->createStub(CurrentUser::class),
            $screen_context,
            $tpl,
            $renderer
        )->printPage('Title', []);
    }

    private function getView(
        CurrentUser $user,
        ?ScreenContext $screen_context = null,
        ?ILIAS\UICore\GlobalTemplate $tpl = null,
        ?ILIAS\UI\Renderer $renderer = null,
        ?ILIAS\Language\Language $lng = null,
        ?Navigation $navigation = null,
    ): ilLTIViewGUI {
        return new ilLTIViewGUI(
            $this->createStub(ILIAS\HTTP\GlobalHttpState::class),
            $this->createStub(ILIAS\Refinery\Factory::class),
            $this->createStub(ILIAS\UI\Factory::class),
            $renderer ?? $this->createStub(ILIAS\UI\Renderer::class),
            $tpl ?? $this->createStub(ILIAS\UICore\GlobalTemplate::class),
            $lng ?? $this->createStub(ILIAS\Language\Language::class),
            $user,
            $navigation ?? $this->createStub(Navigation::class),
            $screen_context ?? $this->createStub(ScreenContext::class),
            $this->createStub(AuthSession::class),
        );
    }
}
