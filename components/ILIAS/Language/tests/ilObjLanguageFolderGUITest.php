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

use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use PHPUnit\Framework\TestCase;

/**
 * The GUI is bound to the legacy $DIC bootstrap, so it is built via newInstanceWithoutConstructor() plus
 * reflection-injected collaborators and called with empty $ids, which avoids `new ilObjLanguage(...)` in the loop.
 */
class ilObjLanguageFolderGUITest extends TestCase
{
    /** Set by stubLoggerLangError(), attached to the GUI's `activity_error_logger`; reset in tearDown() as $GLOBALS['DIC'] is global state. */
    private ?\ilLogger $stubbed_activity_error_logger = null;

    protected function tearDown(): void
    {
        unset($GLOBALS['DIC']);
        $this->stubbed_activity_error_logger = null;
    }

    /**
     * activityErrorMessage() logs the raw \Throwable via `activity_error_logger` before returning the generic text.
     * Only the "<class>: <message>\n" prefix is asserted, the trace is environment-dependent.
     *
     * @param non-empty-string $expected_logged_message the raw message, not the generic text shown to the user
     * @param class-string<\Throwable> $expected_exception_class
     */
    private function stubLoggerLangError(
        string $expected_logged_message,
        string $expected_exception_class = \RuntimeException::class
    ): void {
        $expected_prefix = $expected_exception_class . ': ' . $expected_logged_message . "\n";

        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('error')->with(
            $this->callback(
                static fn(string $logged): bool => str_starts_with($logged, $expected_prefix)
            )
        );

        $this->stubbed_activity_error_logger = $logger;
    }

    /** The logger of stubLoggerLangError(), or a permissive stub for tests expecting no logging. */
    private function activityErrorLoggerForGui(): \ilLogger
    {
        return $this->stubbed_activity_error_logger ?? $this->createMock(\ilLogger::class);
    }

    private function createGuiWithCollaborators(
        InstallLanguage $install_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $this->setProperty($gui, 'install_language', $install_language);
        $this->setProperty($gui, 'current_user_id', 6);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /** Every GUI reaching maybePerformAs() needs a `ui_factory` (typed property of ilObjectGUI); a bare mock suffices. */
    private function stubUiFactoryForMaybePerformAs(): \ILIAS\UI\Factory&\PHPUnit\Framework\MockObject\MockObject
    {
        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('input')->willReturn(
            $this->createMock(\ILIAS\UI\Component\Input\Factory::class)
        );

        return $ui_factory;
    }

    private function setProperty(object $object, string $property_name, mixed $value): void
    {
        $property = new ReflectionProperty($object, $property_name);
        $property->setValue($object, $value);
    }

    private function createLanguageMockReturningTopicAsIs(): ilLanguage&\PHPUnit\Framework\MockObject\MockObject
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);

        return $lng;
    }

    /** @return ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject */
    private function createGuiWithMockedCheckPermission(): ilObjLanguageFolderGUI
    {
        /** @var ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkPermission'])
            ->getMock();

        $this->setProperty($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->setProperty($gui, 'tpl', $this->createMock(ilGlobalTemplateInterface::class));
        $this->setProperty($gui, 'ctrl', $this->createMock(ilCtrl::class));
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        // a readonly property of the leaf class is not found on a mock via a plain ReflectionProperty
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'current_user_id', 6);
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    private function setReadonlyPropertyDeclaredOnGuiClass(
        ilObjLanguageFolderGUI $gui,
        string $property_name,
        mixed $value
    ): void {
        (new ReflectionClass(ilObjLanguageFolderGUI::class))
            ->getProperty($property_name)
            ->setValue($gui, $value);
    }

    /** checkPermission('write') is the first statement; the Activity enforces the same permission, both must hold. */
    public function testUninstallObjectChecksWritePermissionBeforeActing(): void
    {
        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultOk($this->uninstallPerformResult([], [], [], []))
        );
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'uninstall_language', $uninstall_language);

        $gui->uninstallObject([]);
    }

    /** installObject() checks 'write' before any work, like its siblings. */
    public function testInstallObjectMustCheckWritePermissionBeforeActing(): void
    {
        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $install_language = $this->createMock(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk($this->emptyPerformResult()));
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'install_language', $install_language);

        // empty $ids avoid the legacy `new ilObjLanguage(...)` coupling
        $gui->installObject([], InstallLanguage::MODE_INSTALL);
    }

    /** refreshSelectedObject() variant. */
    public function testRefreshSelectedObjectMustCheckWritePermissionBeforeActing(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $update_language = $this->createMock(UpdateLanguage::class);
        $update_language->method('maybePerformAs')->willReturn(new ResultOk($this->updatePerformResult([], [])));
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'update_language', $update_language);

        $gui->refreshSelectedObject([]);
    }

    /** uninstallChangesObject() variant; the Activity enforces the same permission, both must hold. */
    public function testUninstallChangesObjectMustCheckWritePermissionBeforeActing(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult([], [], []))
        );
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'remove_local_language_changes',
            $remove_local_language_changes
        );

        $gui->uninstallChangesObject([]);
    }

    /** An empty selection shows 'no_checkbox' and redirects without calling the Activity; the Result handling is covered by InstallLanguageTest. */
    public function testInstallObjectWithEmptySelectionShowsNoCheckboxMessageAndNeverCallsTheActivity(): void
    {
        $install_language = $this->createMock(InstallLanguage::class);
        $install_language->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'no_checkbox', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->installObject([], InstallLanguage::MODE_INSTALL);
    }

    /**
     * @return array{installed_language_keys: list<string>, installed_with_local_language_keys: list<string>, already_installed_language_keys: list<string>, not_installed_language_keys: list<string>, invalid_local_language_files: list<string>}
     */
    private function emptyPerformResult(): array
    {
        return [
            'installed_language_keys' => [],
            'installed_with_local_language_keys' => [],
            'already_installed_language_keys' => [],
            'not_installed_language_keys' => [],
            'invalid_local_language_files' => [],
        ];
    }

    private function createGuiWithUpdateLanguageCollaborators(
        UpdateLanguage $update_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $this->setProperty($gui, 'update_language', $update_language);
        $this->setProperty($gui, 'current_user_id', 6);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /** refreshSelectedObject() always calls ilObjLanguage::refreshPlugins(): no plugins keep this a unit test. */
    private function stubComponentRepositoryWithNoPlugins(): void
    {
        $repository = $this->createMock(ilComponentRepository::class);
        $repository->method('getPlugins')->willReturn(new ArrayIterator([]));

        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['component.repository'] = $repository;
    }

    /** @return array{updated_language_keys: list<string>, not_installed_language_keys: list<string>} */
    private function updatePerformResult(array $updated, array $not_installed): array
    {
        return [
            'updated_language_keys' => $updated,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    /** An empty selection shows 'no_checkbox' and redirects without calling the Activity; the Result handling is covered by UpdateLanguageTest. */
    public function testRefreshSelectedObjectWithEmptySelectionShowsNoCheckboxMessageAndNeverCallsTheActivity(): void
    {
        $update_language = $this->createMock(UpdateLanguage::class);
        $update_language->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'no_checkbox', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithUpdateLanguageCollaborators(
            $update_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->refreshSelectedObject([]);
    }

    private function createGuiWithUninstallLanguageCollaborators(
        UninstallLanguage $uninstall_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $this->setProperty($gui, 'uninstall_language', $uninstall_language);
        $this->setProperty($gui, 'current_user_id', 6);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /**
     * @return array{uninstalled_language_keys: list<string>, system_language_keys: list<string>, user_language_keys: list<string>, not_installed_language_keys: list<string>}
     */
    private function uninstallPerformResult(
        array $uninstalled,
        array $system,
        array $user,
        array $not_installed
    ): array {
        return [
            'uninstalled_language_keys' => $uninstalled,
            'system_language_keys' => $system,
            'user_language_keys' => $user,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    public function testUninstallObjectEmbedsTheThrowableMessageFromAnErrorResultIntoTheFailureMessage(): void
    {
        $exception_message = 'boom';
        $this->stubLoggerLangError($exception_message);

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        // uninstallObject() wraps the message as "{error}<br/>action_aborted".
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
        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \DomainException('internal wording', 403))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', $this->stringContains($error_string), true);

        $ctrl = $this->createMock(ilCtrl::class);

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

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        // a dropped `return` after the redirect would reach `$result->value()`, which throws on an Error
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

    /** $ids needs a real database to resolve, so only empty $ids is used; the mock returns the error the real Activity would. */
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
        $uninstall_language = $this->createMock(UninstallLanguage::class);
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

    /** setOnScreenMessage() keeps one message per type: the three "nothing changed" buckets must be combined into one 'info' call. */
    public function testUninstallObjectCombinesSystemUserAndNotInstalledIntoOneInfoMessage(): void
    {
        $uninstall_language = $this->createMock(UninstallLanguage::class);
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

    /** A mix of uninstalled and "nothing changed" languages sets the success and the combined info message, neither clobbering the other. */
    public function testUninstallObjectSetsBothSuccessAndInfoMessagesWhenBothCategoriesAreNonEmpty(): void
    {
        $uninstall_language = $this->createMock(UninstallLanguage::class);
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

    /** With every bucket empty no message appears, not even an empty one. */
    public function testUninstallObjectSetsNoMessageWhenAllBucketsAreEmpty(): void
    {
        $uninstall_language = $this->createMock(UninstallLanguage::class);
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

    private function createGuiWithRemoveLocalLanguageChangesCollaborators(
        RemoveLocalLanguageChanges $remove_local_language_changes,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $this->setProperty($gui, 'remove_local_language_changes', $remove_local_language_changes);
        $this->setProperty($gui, 'current_user_id', 6);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /**
     * @return array{removed_local_changes_language_keys: list<string>, invalid_language_file_keys: list<string>, not_installed_language_keys: list<string>}
     */
    private function removeLocalChangesPerformResult(
        array $removed_local_changes,
        array $invalid_language_file,
        array $not_installed
    ): array {
        return [
            'removed_local_changes_language_keys' => $removed_local_changes,
            'invalid_language_file_keys' => $invalid_language_file,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    public function testUninstallChangesObjectEmbedsTheThrowableMessageFromAnErrorResultIntoTheFailureMessage(): void
    {
        // the error path returns before ilObjLanguage::refreshPlugins(); only the "lang" logger stub is needed
        $exception_message = 'boom';
        $this->stubLoggerLangError($exception_message);

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        // wrapped as "{error}<br/>action_aborted", like uninstallObject()
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
        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \DomainException('internal wording', 403))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', $this->stringContains($error_string), true);

        $ctrl = $this->createMock(ilCtrl::class);

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
        // the error path returns before ilObjLanguage::refreshPlugins(); only the "lang" logger stub is needed
        $this->stubLoggerLangError('boom');

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        // a dropped `return` after the redirect would reach `$result->value()` and the unstubbed refreshPlugins()
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

    /** $ids needs a real database to resolve, so only empty $ids is used; the mock returns the error the real Activity would. */
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

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
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

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
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

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
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

    /** The three buckets are different message types, so all three setOnScreenMessage() calls must happen. */
    public function testUninstallChangesObjectSetsAllThreeMessagesWhenAllBucketsAreNonEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
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

    /** With every bucket empty no message appears; unlike the legacy code, which always showed "selected_languages_updated". */
    public function testUninstallChangesObjectSetsNoMessageWhenAllBucketsAreEmpty(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
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

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        // invoke() bypasses visibility, setAccessible() is deprecated since PHP 8.1
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    private function createGuiWithSetLanguageDetectionEnabledCollaborators(
        SetLanguageDetectionEnabled $set_language_detection_enabled,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $this->setProperty($gui, 'set_language_detection_enabled', $set_language_detection_enabled);
        $this->setProperty($gui, 'current_user_id', 6);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    public function testEnableLanguageDetectionObjectCallsMaybePerformAsWithEnabledTrue(): void
    {
        // the Error's \Throwable goes through activityErrorMessage(), which logs via the "lang" logger: needs the stub
        $this->stubLoggerLangError('boom');

        $set_language_detection_enabled = $this->createMock(SetLanguageDetectionEnabled::class);
        $set_language_detection_enabled->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['enabled' => true])
            ->willReturn(new ResultError(new \RuntimeException('boom')));

        $gui = $this->createGuiWithSetLanguageDetectionEnabledCollaborators(
            $set_language_detection_enabled,
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilCtrl::class),
            $this->createLanguageMockReturningTopicAsIs()
        );

        $this->invokeProtectedMethod($gui, 'enableLanguageDetectionObject');
    }

    public function testDisableLanguageDetectionObjectCallsMaybePerformAsWithEnabledFalse(): void
    {
        // logger stub needed for the same reason as above
        $this->stubLoggerLangError('boom');

        $set_language_detection_enabled = $this->createMock(SetLanguageDetectionEnabled::class);
        $set_language_detection_enabled->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['enabled' => false])
            ->willReturn(new ResultError(new \RuntimeException('boom')));

        $gui = $this->createGuiWithSetLanguageDetectionEnabledCollaborators(
            $set_language_detection_enabled,
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilCtrl::class),
            $this->createLanguageMockReturningTopicAsIs()
        );

        $this->invokeProtectedMethod($gui, 'disableLanguageDetectionObject');
    }

    public function testSetLanguageDetectionEnabledObjectEmbedsTheThrowableMessageAndRedirectsOnError(): void
    {
        $exception_message = 'no write permission';
        $this->stubLoggerLangError($exception_message);

        $set_language_detection_enabled = $this->createMock(SetLanguageDetectionEnabled::class);
        $set_language_detection_enabled->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        // wrapped as "{error}<br/>action_aborted", like uninstallObject()
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithSetLanguageDetectionEnabledCollaborators(
            $set_language_detection_enabled,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        // a dropped `return` after the redirect would run the success path into un-stubbed legacy collaborators
        $this->invokeProtectedMethod($gui, 'enableLanguageDetectionObject');
    }

    /** Unlike the four write commands, enable/disableLanguageDetectionObject() do not call checkPermission(); only the Activity enforces it. */
    public function testSetLanguageDetectionEnabledObjectNeverCallsCheckPermission(): void
    {
        /** @var ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkPermission'])
            ->getMock();
        $gui->expects($this->never())->method('checkPermission');

        $this->setProperty($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->setProperty($gui, 'tpl', $this->createMock(ilGlobalTemplateInterface::class));
        $this->setProperty($gui, 'ctrl', $this->createMock(ilCtrl::class));
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'current_user_id', 6);
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        $set_language_detection_enabled = $this->createMock(SetLanguageDetectionEnabled::class);
        // denial is simulated at Activity level so the success path (viewObject()) is not taken
        $set_language_detection_enabled->method('maybePerformAs')->willReturn(
            new ResultError(new \DomainException('no write permission', 403))
        );
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'set_language_detection_enabled',
            $set_language_detection_enabled
        );

        $this->invokeProtectedMethod($gui, 'enableLanguageDetectionObject');
        $this->invokeProtectedMethod($gui, 'disableLanguageDetectionObject');
    }

    /**
     * Stubs `global $DIC["ilObjDataCache"]` for ilObject::_lookupType()/_lookupTitle(). Reuses an existing
     * $GLOBALS['DIC'] so it can coexist with stubComponentRepositoryWithNoPlugins().
     *
     * @param array<int, string> $types_by_id obj_id => type; a missing id resolves to "" like the real ilObjectDataCache
     * @param array<int, string> $titles_by_id obj_id => title; lookupTitle() must never be called when $expect_no_title_lookup is true
     */
    private function stubObjDataCache(
        array $types_by_id,
        array $titles_by_id = [],
        bool $expect_no_title_lookup = false
    ): void {
        $cache = $this->createMock(\ilObjectDataCache::class);
        $cache->method('lookupType')->willReturnCallback(
            static fn(int $id): string => $types_by_id[$id] ?? ''
        );
        if ($expect_no_title_lookup) {
            $cache->expects($this->never())->method('lookupTitle');
        } else {
            $cache->method('lookupTitle')->willReturnCallback(
                static fn(int $id): string => $titles_by_id[$id] ?? ''
            );
        }

        if (!isset($GLOBALS['DIC'])) {
            $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        }
        $GLOBALS['DIC']['ilObjDataCache'] = $cache;
    }

    /** A single non-"lng" id aborts the whole request ('obj_not_found'+'action_aborted', redirect) without calling the Activity. */
    public function testInstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsInstallLanguage(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        $install_language = $this->createMock(InstallLanguage::class);
        $install_language->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);
    }

    /** For all-"lng" ids the Activity is still called, with 'language_keys' from ilObject::_lookupTitle(). */
    public function testInstallObjectWithAllLanguageObjectIdsStillCallsInstallLanguageWithResolvedLanguageKeys(): void
    {
        $this->stubObjDataCache([5 => 'lng'], [5 => 'de']);

        $install_language = $this->createMock(InstallLanguage::class);
        $install_language->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_keys' => ['de'], 'mode' => InstallLanguage::MODE_INSTALL])
            ->willReturn(new ResultOk($this->emptyPerformResult()));

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $this->createMock(ilGlobalTemplateInterface::class),
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);
    }

    /** uninstallObject() variant. */
    public function testUninstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsUninstallLanguage(): void
    {
        $this->stubObjDataCache([5 => 'usr'], [], true);

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

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

        $gui->uninstallObject(['5']);
    }

    /** uninstallObject() with all-"lng" ids is unchanged. */
    public function testUninstallObjectWithAllLanguageObjectIdsStillCallsUninstallLanguageWithResolvedLanguageKeys(): void
    {
        $this->stubObjDataCache([5 => 'lng'], [5 => 'de']);

        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_keys' => ['de']])
            ->willReturn(new ResultOk($this->uninstallPerformResult([], [], [], [])));

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect');

        $gui = $this->createGuiWithUninstallLanguageCollaborators(
            $uninstall_language,
            $this->createMock(ilGlobalTemplateInterface::class),
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject(['5']);
    }

    /** uninstallChangesObject() variant. */
    public function testUninstallChangesObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsRemoveLocalLanguageChanges(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

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

        $gui->uninstallChangesObject(['5']);
    }

    /** refreshSelectedObject() variant. */
    public function testRefreshSelectedObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsUpdateLanguage(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        $update_language = $this->createMock(UpdateLanguage::class);
        $update_language->expects($this->never())->method('maybePerformAs');

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithUpdateLanguageCollaborators(
            $update_language,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->refreshSelectedObject(['5']);
    }

    /**
     * Stub for buildConfirmModal()'s abort branch; returns the Interruptive that chain produces, so a test can
     * assert the abort branch's `return` was taken.
     *
     * @return array{0: \ILIAS\UI\Factory&\PHPUnit\Framework\MockObject\MockObject, 1: \ILIAS\UI\Implementation\Component\Modal\Interruptive}
     */
    private function createStubModalUiFactory(): array
    {
        // buildConfirmModal() returns the concrete Interruptive class, so the interface alone fails the return type
        $interruptive = $this->createMock(\ILIAS\UI\Implementation\Component\Modal\Interruptive::class);
        $interruptive->method('withActionButtonLabel')->willReturnSelf();

        $modal_factory = $this->createMock(\ILIAS\UI\Component\Modal\Factory::class);
        $modal_factory->method('interruptive')->willReturn($interruptive);

        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('modal')->willReturn($modal_factory);

        return [$ui_factory, $interruptive];
    }

    /** buildConfirmModal() variant: the abort branch returns its own Interruptive, so the per-id loop was never reached. */
    public function testBuildConfirmModalAbortsWhenAnyIdIsNotALanguageObjectAndNeverLeaksData(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        [$ui_factory, $expected_interruptive] = $this->createStubModalUiFactory();

        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->setProperty($gui, 'ui_factory', $ui_factory);

        $result = $this->invokeProtectedMethod($gui, 'buildConfirmModal', [
            ['5'],
            'refresh_languages',
            'confirmRefresh',
            'lang_refresh_confirm_selected',
            'lang_refresh_confirm_info',
        ]);

        $this->assertSame($expected_interruptive, $result);
    }

    public function testOnlyTheStateChangingCommandsAreUnsafeGetCommandsSoTheCsrfTokenIsRequired(): void
    {
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();
        $this->assertInstanceOf(ilCtrlSecurityInterface::class, $gui);

        $this->assertEqualsCanonicalizing(
            ['executeTableAction', 'enableLanguageDetection', 'disableLanguageDetection'],
            $gui->getUnsafeGetCommands()
        );
        $this->assertSame([], $gui->getSafePostCommands());
    }

    /**
     * @param list<non-empty-string> $methods
     * @return ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createDispatchGui(string $cmd, ?string $table_action, array $methods): ilObjLanguageFolderGUI
    {
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array_merge(['checkPermission', 'prepareOutput'], $methods))
            ->getMock();

        $request_wrapper = $this->createStub(\ILIAS\HTTP\Wrapper\RequestWrapper::class);
        $request_wrapper->method('has')->willReturn($table_action !== null);
        $request_wrapper->method('retrieve')->willReturn($table_action);
        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('getNextClass')->willReturn('');
        $ctrl->method('getCmd')->willReturn($cmd);
        $lng = $this->createStub(ilLanguage::class);

        foreach ([
            'action_token' => new \ILIAS\UI\URLBuilderToken(['language_folder'], 'action'),
            'request_wrapper' => $request_wrapper,
            'refinery' => $this->createStub(\ILIAS\Refinery\Factory::class),
            'ctrl' => $ctrl,
            'lng' => $lng,
            'ui_renderer' => $this->createStub(\ILIAS\UI\Renderer::class),
            'tabs_gui' => $this->createStub(ilTabsGUI::class),
            'type' => 'lngf',
            'ref_id' => 1,
        ] as $name => $value) {
            (new ReflectionProperty(ilObjLanguageFolderGUI::class, $name))->setValue($gui, $value);
        }

        return $gui;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('commandsThatMustFallBackToView')]
    public function testExecuteCommandOnlyShowsTheViewForCommandsOutsideTheExactAllowlist(string $cmd): void
    {
        $gui = $this->createDispatchGui(
            $cmd,
            'checkLanguage',
            ['viewObject', 'executeTableActionObject', 'enableLanguageDetectionObject', 'saveObject', 'checkLanguageObject']
        );
        $gui->expects($this->once())->method('viewObject');
        $gui->expects($this->never())->method('executeTableActionObject');
        $gui->expects($this->never())->method('enableLanguageDetectionObject');
        $gui->expects($this->never())->method('saveObject');
        $gui->expects($this->never())->method('checkLanguageObject');

        $gui->executeCommand();
    }

    /** @return array<string, array{string}> */
    public static function commandsThatMustFallBackToView(): array
    {
        return [
            'upper case first letter' => ['ExecuteTableAction'],
            'lower case' => ['executetableaction'],
            'detection with upper case' => ['EnableLanguageDetection'],
            'inherited save' => ['save'],
            'empty' => [''],
        ];
    }

    public function testExecuteCommandDispatchesTheExactTableActionCommandToTheTableAction(): void
    {
        $gui = $this->createDispatchGui('executeTableAction', null, ['viewObject', 'executeTableActionObject']);
        $gui->expects($this->once())->method('executeTableActionObject');
        $gui->expects($this->never())->method('viewObject');

        $gui->executeCommand();
    }

    public function testTableActionCheckLanguageIsDispatchedToCheckLanguageObject(): void
    {
        $gui = $this->createDispatchGui('executeTableAction', 'checkLanguage', ['viewObject', 'checkLanguageObject']);
        $gui->expects($this->once())->method('checkLanguageObject');

        $this->invokeTableAction($gui);
    }

    public function testUnknownTableActionOnlyShowsTheView(): void
    {
        $gui = $this->createDispatchGui('executeTableAction', 'doesNotExist', ['viewObject', 'checkLanguageObject']);
        $gui->expects($this->once())->method('viewObject');
        $gui->expects($this->never())->method('checkLanguageObject');

        $this->invokeTableAction($gui);
    }

    private function invokeTableAction(ilObjLanguageFolderGUI $gui): void
    {
        (new ReflectionMethod(ilObjLanguageFolderGUI::class, 'executeTableActionObject'))->invoke($gui);
    }
}
