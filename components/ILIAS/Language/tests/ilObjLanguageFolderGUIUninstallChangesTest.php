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

use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use PHPUnit\Framework\TestCase;

class ilObjLanguageFolderGUIUninstallChangesTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageFolderGUITestFixture;

    public function testUninstallChangesObjectEmbedsTheThrowableMessageFromAnErrorResultIntoTheFailureMessage(): void
    {
        // the error path returns before refreshPlugins(), so only the "lang" logger stub is needed
        $exception_message = 'boom';
        $this->stubLoggerLangError($exception_message);

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectShowsTheNoPermissionMessageForA403ErrorResult(): void
    {
        $error_string = 'msg_no_perm_write';
        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \DomainException('internal wording', 403))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', $this->stringContains($error_string), true);

        $ctrl = $this->createStub(ilCtrl::class);

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectRedirectsToViewAfterAnErrorResultAndDoesNotContinueToTheSuccessPath(): void
    {
        // the error path returns before refreshPlugins(), so only the "lang" logger stub is needed
        $this->stubLoggerLangError('boom');

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
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

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    /** maybePerformAs() gets only "language_keys"; the empty id list yields the Activity's "at least one language key" error. */
    public function testUninstallChangesObjectPassesOnlyLanguageKeysWithoutAModeKeyToMaybePerformAs(): void
    {
        $this->stubLoggerLangError('At least one language key is required.', \InvalidArgumentException::class);

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_keys' => []])
            ->willReturn(new ResultError(
                new \InvalidArgumentException('At least one language key is required.')
            ));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', $this->anything(), true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectSetsSuccessMessageWhenOnlyRemovedLocalChangesLanguageKeysIsNonEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult(['de'], [], []))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('success', 'selected_languages_updated<br />meta_l_de', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectSetsFailureMessageWhenOnlyInvalidLanguageFileKeysIsNonEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult([], ['fr'], []))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'meta_l_fr: file_not_valid', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectSetsInfoMessageWhenOnlyNotInstalledLanguageKeysIsNonEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult([], [], ['it']))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('info', 'meta_l_it language_not_installed', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectSetsAllThreeMessagesWhenAllBucketsAreNonEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(new ResultOk(
            $this->removeLocalChangesPerformResult(['de'], ['fr'], ['it'])
        ));

        $captured_calls = [];
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->exactly(3))
            ->method('setOnScreenMessage')
            ->willReturnCallback(function (string $type, string $message, bool $keep) use (&$captured_calls): void {
                $captured_calls[] = [$type, $message];
            });

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);

        self::assertCount(3, $captured_calls);
        self::assertSame(['success', 'selected_languages_updated<br />meta_l_de'], $captured_calls[0]);
        self::assertSame(['failure', 'meta_l_fr: file_not_valid'], $captured_calls[1]);
        self::assertSame(['info', 'meta_l_it language_not_installed'], $captured_calls[2]);
    }

    /** With every bucket empty no message appears, not even an empty one. */
    public function testUninstallChangesObjectSetsNoMessageWhenAllBucketsAreEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult([], [], []))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->never())->method('setOnScreenMessage');

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallChangesObject([]);
    }

    public function testUninstallChangesObjectCombinesTheOverlayFailureWithTheInvalidFileFailure(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();
        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(new ResultOk(
            ['overlay_write_failed_language_keys' => ['de']] + $this->removeLocalChangesPerformResult(['de'], ['fr'], [])
        ));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->uninstallChangesObject([]);

        $this->assertSame(
            [
                ['success', 'selected_languages_updated<br />meta_l_de'],
                ['failure', 'meta_l_fr: file_not_valid<br />lng_po_overlay_not_written_languages: meta_l_de'],
            ],
            $messages
        );
    }

    public function testUninstallChangesObjectShowsTheOverlayFailureAlone(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();
        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(new ResultOk(
            ['overlay_write_failed_language_keys' => ['de']] + $this->removeLocalChangesPerformResult(['de'], [], [])
        ));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithRemoveLocalLanguageChangesCollaborators(
            $remove_local_language_changes,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->uninstallChangesObject([]);

        $this->assertContains(['failure', 'lng_po_overlay_not_written_languages: meta_l_de'], $messages);
    }
}
