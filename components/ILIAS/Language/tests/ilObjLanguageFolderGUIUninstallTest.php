<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use PHPUnit\Framework\TestCase;

class ilObjLanguageFolderGUIUninstallTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageFolderGUITestFixture;

    public function testUninstallObjectEmbedsTheThrowableMessageFromAnErrorResultIntoTheFailureMessage(): void
    {
        $exception_message = 'boom';
        $this->stubLoggerLangError($exception_message);

        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    public function testUninstallObjectShowsTheNoPermissionMessageForA403ErrorResult(): void
    {
        $error_string = 'msg_no_perm_write';
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \DomainException('internal wording', 403))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', $this->stringContains($error_string), true);

        $ctrl = $this->createStub(ilCtrl::class);

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    public function testUninstallObjectRedirectsToViewAfterAnErrorResultAndDoesNotContinueToTheSuccessPath(): void
    {
        $this->stubLoggerLangError('boom');

        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        // a dropped `return` after the redirect would fall through to $result->value(), which throws on an Error
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    /** maybePerformAs() gets only "language_keys"; the empty id list yields the Activity's "at least one language key" error. */
    public function testUninstallObjectPassesOnlyLanguageKeysWithoutAModeKeyToMaybePerformAs(): void
    {
        $this->stubLoggerLangError('At least one language key is required.', \InvalidArgumentException::class);

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_keys' => []])
            ->willReturn(new ResultError(
                new \InvalidArgumentException('At least one language key is required.')
            ));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', $this->anything(), true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    public function testUninstallObjectSetsSuccessMessageWhenOnlyUninstalledLanguageKeysIsNonEmpty(): void
    {
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultOk($this->uninstallPerformResult(['de'], [], [], []))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('success', 'meta_l_de uninstalled', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    /** The three "nothing changed" buckets form one info message, because setOnScreenMessage() keeps one message per type. */
    public function testUninstallObjectCombinesSystemUserAndNotInstalledIntoOneInfoMessage(): void
    {
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(new ResultOk(
            $this->uninstallPerformResult([], ['de'], ['en'], ['fr'])
        ));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with(
                'info',
                'cannot_uninstall_systemlanguage: meta_l_de<br />'
                    . 'cannot_uninstall_language_in_use: meta_l_en<br />'
                    . 'languages_already_uninstalled: meta_l_fr',
                true
            );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }

    public function testUninstallObjectSetsBothSuccessAndInfoMessagesWhenBothCategoriesAreNonEmpty(): void
    {
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(new ResultOk(
            $this->uninstallPerformResult(['de'], ['en'], [], [])
        ));

        $captured_calls = [];
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->exactly(2))
            ->method('setOnScreenMessage')
            ->willReturnCallback(function (string $type, string $message, bool $keep) use (&$captured_calls): void {
                $captured_calls[] = [$type, $message];
            });

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);

        self::assertCount(2, $captured_calls);
        self::assertSame(['success', 'meta_l_de uninstalled'], $captured_calls[0]);
        self::assertSame(['info', 'cannot_uninstall_systemlanguage: meta_l_en'], $captured_calls[1]);
    }

    /** A non-empty overlay_write_failed_language_keys shows the overlay failure although the uninstall succeeded. */
    public function testUninstallObjectShowsOverlayWriteFailureMessageAlongsideTheSuccessMessage(): void
    {
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(new ResultOk([
            'uninstalled_language_keys' => ['de'],
            'system_language_keys' => [],
            'user_language_keys' => [],
            'not_installed_language_keys' => [],
            'overlay_write_failed_language_keys' => ['de'],
        ]));

        $captured_calls = [];
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->exactly(2))
            ->method('setOnScreenMessage')
            ->willReturnCallback(function (string $type, string $message, bool $keep) use (&$captured_calls): void {
                $captured_calls[] = [$type, $message];
            });

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        // txt() stub with a real "%s", so the assertion can check the language list is interpolated
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(
            static fn(string $key): string => $key === 'lng_po_overlay_not_written_languages'
                ? 'overlay not written for: %s'
                : $key
        );

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $lng
        );

        $gui->uninstallObject([]);

        self::assertCount(2, $captured_calls);
        self::assertSame(['success', 'meta_l_de uninstalled'], $captured_calls[0]);
        self::assertSame(['failure', 'overlay not written for: meta_l_de'], $captured_calls[1]);
    }

    public function testUninstallObjectSetsNoMessageWhenAllBucketsAreEmpty(): void
    {
        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultOk($this->uninstallPerformResult([], [], [], []))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->never())->method('setOnScreenMessage');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject([]);
    }
}
