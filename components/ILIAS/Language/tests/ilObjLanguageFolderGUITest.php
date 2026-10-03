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
 * ilObjLanguageFolderGUI is bound tightly to the legacy $DIC/ilObjectGUI
 * bootstrap (its constructor alone touches ilCtrl, ilTemplate, ilObjUser,
 * ilRbac*, request wrappers, ...), so building a real instance is not
 * practical for a unit test. installObject() itself is reachable in
 * isolation, though: constructed via
 * ReflectionClass::newInstanceWithoutConstructor() plus reflection-injected
 * collaborators (a legitimate legacy-code testing technique - see
 * docs/development or "Characterization Tests" practice), and called with
 * an empty $ids array so the method's only other piece of legacy coupling
 * (`new ilObjLanguage((int) $obj_id)` inside the per-id loop) is never
 * reached.
 *
 * This covers the regression fix in installObject()'s error path: the
 * previously swallowed $result->error() message must now appear in the
 * failure message shown to the user.
 */
class ilObjLanguageFolderGUITest extends TestCase
{
    /**
     * Holds whatever stubLoggerLangError() built for the currently running
     * test, so every createGuiWith*Collaborators()/createGuiWith*
     * ()-style factory below can attach it to the GUI instance's
     * `activity_error_logger` property (see RendersActivityErrors) instead
     * of a plain permissive stub. Reset in tearDown() so it never leaks
     * between tests - PHP runs every test in this class within the same
     * process, and $GLOBALS['DIC'] (written by
     * stubComponentRepositoryWithNoPlugins()) is genuinely global state too,
     * for the same reason.
     */
    private ?\ilLogger $stubbed_activity_error_logger = null;

    protected function tearDown(): void
    {
        unset($GLOBALS['DIC']);
        $this->stubbed_activity_error_logger = null;
    }

    /**
     * activityErrorMessage() (see class docblock of the production class)
     * logs a \Throwable's raw message via `$this->activity_error_logger`
     * (a constructor-injected "lang" component logger - see
     * RendersActivityErrors) before returning the generic, localized
     * replacement text. This builds a mocked \ilLogger expecting exactly
     * that call and stashes it so the GUI-building helpers below can wire
     * it onto the instance under test via `activity_error_logger`.
     *
     * activityErrorMessage() logs `get_class($error) . ': ' .
     * $error->getMessage() . "\n" . $error->getTraceAsString()` (see
     * RendersActivityErrors::activityErrorMessage()) - the trace itself is
     * environment-dependent (absolute file paths, line numbers), so only
     * the "<class>: <message>\n" prefix is asserted here, not the full
     * logged string.
     *
     * @param non-empty-string $expected_logged_message The raw \Throwable
     *        message activityErrorMessage() must log - never the generic
     *        replacement text shown to the user.
     * @param class-string<\Throwable> $expected_exception_class The class
     *        of the \Throwable the test's Result\Error is constructed
     *        with.
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

    /**
     * The logger to wire onto a GUI instance's `activity_error_logger`
     * property while building it for a test: whatever stubLoggerLangError()
     * set up for this test, or - for tests that never expect a Throwable
     * to be logged - a permissive stub that tolerates being left untouched
     * or called incidentally.
     */
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

    /**
     * maybePerformAs() now takes the calling GUI's `ILIAS\UI\Factory::input()`
     * as its (unused by these Activities, see LanguageActivity) first
     * argument, so every reflection-constructed GUI instance that reaches a
     * maybePerformAs() call needs a `ui_factory` (a typed, non-nullable
     * property on the ilObjectGUI ancestor) - otherwise PHP fatals with
     * "must not be accessed before initialization" before the mocked
     * Activity is ever reached. What input() actually returns is irrelevant
     * to any of these tests (the Activities never use it), so a bare mock
     * is enough.
     */
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

    // -----------------------------------------------------------------
    // Regression coverage for the explicit ACL check in installObject(),
    // refreshSelectedObject(), uninstallObject() and
    // uninstallChangesObject().
    //
    // All four write-command methods on this class -  installObject(),
    // uninstallObject(), uninstallChangesObject() and
    // refreshSelectedObject() - call $this->checkPermission('write') as
    // their very first statement (verified against the class body; see
    // also commit 4c98c957d36, "re-introduce explicit permission checks").
    // This is a defense-in-depth check: all four additionally delegate to
    // their respective Activity's isAllowedToPerform() (via
    // maybePerformAs()) - InstallLanguage, UninstallLanguage, UpdateLanguage
    // and RemoveLocalLanguageChanges respectively - so the permission is
    // enforced twice: once explicitly here, once inside the Activity (plus
    // the `table_action` dispatch in executeCommand(), which calls
    // checkPermission('write') itself before invoking any of them when
    // reached that way - but any other caller of one of these *public*
    // methods gets no ACL protection beyond the method's own explicit
    // check and its Activity's isAllowedToPerform()).
    //
    // checkPermissionBool() (which checkPermission() delegates to) simply
    // returns false whenever $this->object is not an object - which it
    // never is on a reflection-constructed instance - so checkPermission()
    // takes its early, silent `return;` branch and never actually reaches
    // $this->access, $this->tpl or $this->ctrl. That makes it impossible
    // to fake a "permission denied" *outcome* through $access in this kind
    // of lightweight unit test: the method no-ops the same way regardless
    // of whether real RBAC would grant or deny 'write', and - even if it
    // didn't - checkPermission() itself never throws or exits, it only
    // records a flash message and calls $ctrl->redirectToURL(), which is a
    // real redirect+exit only at the HTTP layer; against a mocked $ctrl it
    // is just another recorded call, so it would not actually stop
    // execution here either. A real integration test (with a genuine
    // ilAccessHandler backed by RBAC/DB and a request that never reaches
    // executeCommand()'s own gate) would be needed to observe an *outcome*
    // difference (data changed vs. not changed) for a denied user.
    //
    // What a unit test *can* pin down directly is the contract itself:
    // that the method calls $this->checkPermission('write') exactly once,
    // as its first statement, before doing any work. This is a legitimate
    // case for interaction verification (rule 16): the presence of that
    // call *is* the contract under test, not an implementation detail. A
    // partial mock of the GUI itself (stubbing only checkPermission(), a
    // protected method not otherwise observable, while every other method
    // keeps its real implementation) makes that call directly assertable.
    // -----------------------------------------------------------------

    /**
     * @return ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject
     */
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
        // 'current_user_id' is a private readonly property declared directly
        // on ilObjLanguageFolderGUI (not an ancestor). On a PHPUnit mock
        // subclass, plain `new ReflectionProperty($gui, name)` cannot locate
        // a *readonly* property declared on the leaf class this way -
        // unlike the plain (non-readonly) $tpl/$ctrl/$lng above, which are
        // declared on the ilObjectGUI ancestor and are found fine either
        // way - so it must be looked up via the exact declaring class
        // instead, and set on the mock instance from there.
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

    /**
     * uninstallObject() must call $this->checkPermission('write') exactly
     * once, as its first statement, before doing any uninstall work -
     * exactly like installObject()/refreshSelectedObject()/
     * uninstallChangesObject() do. In addition, UninstallLanguage enforces
     * the same 'write' permission itself via isAllowedToPerform()/
     * maybePerformAs() (see
     * UninstallLanguageTest::testPermissionDeniedBeforePerformNeverCallsObjectFactoryOrUninstall()
     * for that side of the contract) - this is a deliberate
     * defense-in-depth: the GUI-level check here does not replace the
     * Activity-level check, both must hold.
     */
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

    /**
     * installObject() must check 'write' permission before doing any
     * installation work - exactly like its siblings
     * uninstallObject()/refreshSelectedObject()/uninstallChangesObject()
     * do (see the class body: all four call $this->checkPermission('write')
     * as their first statement).
     */
    public function testInstallObjectMustCheckWritePermissionBeforeActing(): void
    {
        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $install_language = $this->createMock(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk($this->emptyPerformResult()));
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'install_language', $install_language);

        // Empty $ids, same reasoning as the other installObject() tests
        // above: avoids the unrelated `new ilObjLanguage(...)` legacy
        // coupling, which is irrelevant to whether the permission check
        // happens at all.
        $gui->installObject([], InstallLanguage::MODE_INSTALL);
    }

    /**
     * refreshSelectedObject() variant: same reasoning as
     * testInstallObjectMustCheckWritePermissionBeforeActing() above.
     */
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

    /**
     * uninstallChangesObject() variant: same reasoning as
     * testInstallObjectMustCheckWritePermissionBeforeActing() above. Also
     * mirrors the defense-in-depth reasoning of
     * testUninstallObjectChecksWritePermissionBeforeActing(): in addition
     * to this explicit GUI-level check, RemoveLocalLanguageChanges enforces
     * the same 'write' permission itself via isAllowedToPerform()/
     * maybePerformAs() (see
     * RemoveLocalLanguageChangesTest::testPermissionDeniedBeforePerformNeverCallsObjectFactoryOrRemoveLocalChanges()
     * for that side of the contract) - the GUI-level check here does not
     * replace the Activity-level check, both must hold.
     */
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

    /**
     * Regression test for installObject()'s explicit "empty selection"
     * guard (checked BEFORE the Activity is ever called): an empty $ids
     * must show the established 'no_checkbox' failure message and redirect
     * to "view" WITHOUT ever calling InstallLanguage::maybePerformAs() -
     * before this guard existed, an empty selection reached the Activity
     * itself, which rejected it with a technical, untranslated message
     * (e.g. "language_keys: not_min_length").
     *
     * Note: because of this guard, $ids=[] can no longer be used (as it
     * was before this production change) to exercise installObject()'s
     * Result-handling logic (error/success/info message assembly, $mode
     * pass-through) while sidestepping the method's other legacy coupling
     * (`new ilObjLanguage((int) $obj_id)` for a non-empty $ids, which
     * cannot be safely reached in this kind of lightweight unit test - see
     * class docblock). That logic is therefore no longer directly
     * unit-testable via this public method; the underlying business logic
     * it merely assembles into a message remains covered by
     * InstallLanguageTest (perform()/maybePerformAs()).
     */
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

    // -----------------------------------------------------------------
    // refreshSelectedObject()
    //
    // Same reflection-based construction technique as installObject()
    // above, with $ids always [] for the same reason: the only other
    // piece of legacy coupling in the method (`new ilObjLanguage((int)
    // $obj_id)` inside the per-id loop that builds $language_keys) must
    // never be reached in a unit test.
    // -----------------------------------------------------------------

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

    /**
     * Unlike installObject(), refreshSelectedObject() calls the legacy
     * static ilObjLanguage::refreshPlugins() unconditionally on every
     * success path (even when nothing was updated) - and that static
     * method reaches into `global $DIC["component.repository"]`. An
     * empty plugin iterator here means the foreach in refreshPlugins()
     * never enters its body, which is what keeps this a unit test:
     * anything beyond "is there a plugin" (ilPluginLanguage's actual
     * update logic) reads real files and queries the database - legacy
     * internals that are unrelated to and unchanged by this refactoring,
     * so this test suite deliberately does not attempt to verify which
     * exact keys refreshPlugins() receives from
     * $value['updated_language_keys']. That one-line pass-through was
     * verified by inspection instead.
     */
    private function stubComponentRepositoryWithNoPlugins(): void
    {
        $repository = $this->createMock(ilComponentRepository::class);
        $repository->method('getPlugins')->willReturn(new ArrayIterator([]));

        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['component.repository'] = $repository;
    }

    /**
     * @return array{updated_language_keys: list<string>, not_installed_language_keys: list<string>}
     */
    private function updatePerformResult(array $updated, array $not_installed): array
    {
        return [
            'updated_language_keys' => $updated,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    /**
     * Regression test for refreshSelectedObject()'s explicit "empty
     * selection" guard - same reasoning as
     * testInstallObjectWithEmptySelectionShowsNoCheckboxMessageAndNeverCallsTheActivity()
     * above: an empty $ids must show 'no_checkbox' and redirect WITHOUT
     * ever calling UpdateLanguage::maybePerformAs().
     *
     * Note: as with installObject(), this guard means $ids=[] can no
     * longer be used to exercise refreshSelectedObject()'s own
     * Result-handling logic (error/success/info message assembly) while
     * sidestepping its other legacy coupling (`new ilObjLanguage((int)
     * $obj_id)` for a non-empty $ids) - see class docblock. That logic is
     * no longer directly unit-testable via this public method; the
     * underlying business logic it assembles into a message remains
     * covered by UpdateLanguageTest (perform()/maybePerformAs()).
     */
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

    // -----------------------------------------------------------------
    // uninstallObject()
    //
    // Same reflection-based construction technique as installObject()/
    // refreshSelectedObject() above, with $ids always [] for the same
    // reason: the only other piece of legacy coupling in the method
    // (`ilObject::_lookupTitle((int) $obj_id)` inside the per-id loop
    // that builds $language_keys) must never be reached in a unit test.
    // -----------------------------------------------------------------

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

    public function testUninstallObjectEmbedsAPlainStringErrorFromAnErrorResultIntoTheFailureMessage(): void
    {
        // ILIAS\Data\Result\Error also accepts a plain string (not just a
        // Throwable) - the "$error instanceof \Throwable ? ... : $error"
        // branch for that case must be covered too.
        $error_string = 'permission denied';
        $uninstall_language = $this->createMock(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(new ResultError($error_string));

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

        // If the `return` after the redirect were ever dropped, execution
        // would fall through to `$result->value()` - which throws on an
        // Error result (see ILIAS\Data\Result\Error::value()) - so this
        // would surface as a test error rather than silently passing.
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

    /**
     * $ids is resolved to language keys via
     * ilObject::_lookupTitle((int) $obj_id) - untestable in isolation
     * without a real database, so - exactly like the existing
     * install/update tests - this is only exercised with an empty $ids
     * array, which is enough to pin that maybePerformAs() receives an
     * (empty) 'language_keys' array and no other key.
     *
     * An empty $ids array means UninstallLanguage::toLanguageKeyList([])
     * would, in reality, throw \InvalidArgumentException('At least one
     * language key is required.') - the real Activity is never reached
     * here (it is mocked), but the mock is stubbed to return that same
     * realistic error instead of an artificial Ok result, so this test does
     * not silently pretend an empty request succeeds (m6). The 'with()'
     * expectation below - the actual point of this test - is unaffected
     * either way.
     */
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

    /**
     * system_language_keys, user_language_keys and not_installed_language_keys
     * are all "nothing changed for this language, here is why" outcomes.
     * setOnScreenMessage() only keeps one message per type, so - exactly
     * like installObject()'s combined info message - all three buckets
     * must be combined into a single 'info' call instead of three
     * separate calls that would silently overwrite each other.
     */
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

    /**
     * A single request can mix a genuinely uninstalled language with all
     * three "nothing changed" buckets at once - the success message and
     * the combined info message must both be set, as two separate calls
     * (different message types), neither one clobbering the other.
     */
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

    /**
     * Boundary: when every bucket is empty (e.g. an empty $ids request),
     * no message at all must appear - not even an empty one.
     */
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

    // -----------------------------------------------------------------
    // uninstallChangesObject()
    //
    // Same reflection-based construction technique as uninstallObject()
    // above, with $ids always [] for the same reason: the only other piece
    // of legacy coupling in the method (`ilObject::_lookupTitle((int)
    // $obj_id)` inside the per-id loop that builds $language_keys) must
    // never be reached in a unit test.
    //
    // Unlike uninstallObject(), uninstallChangesObject() calls the legacy
    // static ilObjLanguage::refreshPlugins() unconditionally on every
    // success path (even when nothing was changed) - exactly like
    // refreshSelectedObject() does (see stubComponentRepositoryWithNoPlugins()'s
    // docblock above for why an empty plugin iterator is what keeps this a
    // unit test), so every success-path test below needs that same stub.
    // -----------------------------------------------------------------

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
        // The error path is reached before ilObjLanguage::refreshPlugins()
        // would ever be called, so stubComponentRepositoryWithNoPlugins() is
        // not needed here - only the "lang" logger stub activityErrorMessage()
        // now requires for a \Throwable error.
        $exception_message = 'boom';
        $this->stubLoggerLangError($exception_message);

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException($exception_message))
        );

        // uninstallChangesObject() wraps the message as
        // "{error}<br/>action_aborted", same as uninstallObject().
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

    public function testUninstallChangesObjectEmbedsAPlainStringErrorFromAnErrorResultIntoTheFailureMessage(): void
    {
        // ILIAS\Data\Result\Error also accepts a plain string (not just a
        // Throwable) - the "$error instanceof \Throwable ? ... : $error"
        // branch for that case must be covered too.
        $error_string = 'permission denied';
        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(new ResultError($error_string));

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
        // The error path is reached (and returns) before
        // ilObjLanguage::refreshPlugins() would ever be called, so
        // stubComponentRepositoryWithNoPlugins() is not needed here - only
        // the "lang" logger stub activityErrorMessage() now requires for a
        // \Throwable error.
        $this->stubLoggerLangError('boom');

        $remove_local_language_changes = $this->createMock(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        // If the `return` after the redirect were ever dropped, execution
        // would fall through to `$result->value()` - which throws on an
        // Error result (see ILIAS\Data\Result\Error::value()) - and, even
        // past that, to the unconditional ilObjLanguage::refreshPlugins()
        // call, which reaches into `global $DIC["component.repository"]`
        // and is not stubbed here - so this would surface as a test error
        // rather than silently passing.
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

    /**
     * $ids is resolved to language keys via
     * ilObject::_lookupTitle((int) $obj_id) - untestable in isolation
     * without a real database, so - exactly like the existing
     * uninstallObject() test - this is only exercised with an empty $ids
     * array, which is enough to pin that maybePerformAs() receives an
     * (empty) 'language_keys' array and no other key.
     *
     * An empty $ids array means
     * RemoveLocalLanguageChanges::toLanguageKeyList([]) would, in reality,
     * throw \InvalidArgumentException('At least one language key is
     * required.') - the real Activity is never reached here (it is
     * mocked), but the mock is stubbed to return that same realistic error
     * instead of an artificial Ok result, so this test does not silently
     * pretend an empty request succeeds (m6). This also means the error
     * path is taken, so stubComponentRepositoryWithNoPlugins() is no longer
     * needed (refreshPlugins() is never reached on the error path) - only
     * the "lang" logger stub activityErrorMessage() now requires for a
     * \Throwable error is.
     */
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

    /**
     * Unlike uninstallObject()'s combined info message (three "nothing
     * happened" buckets sharing one message type, therefore explicitly
     * combined into one call to avoid a silent overwrite), all three
     * buckets here are different message types (success/failure/info) - so
     * all three setOnScreenMessage() calls must actually happen, as three
     * separate calls, none clobbering another.
     */
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

    /**
     * Boundary: when every bucket is empty (e.g. an empty $ids request),
     * no message at all must appear - not even an empty one. This is also
     * a deliberate behavior change against the pre-Activity legacy code,
     * which always showed a "selected_languages_updated" message, even
     * when nothing changed.
     */
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

    // -----------------------------------------------------------------
    // enableLanguageDetectionObject() / disableLanguageDetectionObject()
    // (both backed by setLanguageDetectionEnabledObject())
    //
    // Unlike installObject()/uninstallObject()/refreshSelectedObject()/
    // uninstallChangesObject(), neither of these two methods calls
    // $this->checkPermission('write') itself - this is unchanged legacy
    // behaviour: before the extraction, only the toggle
    // button's visibility in viewObject() was write-gated, the request
    // itself was not. The extraction deliberately does not retrofit a
    // GUI-level checkPermission() call here (that would be an unrequested,
    // additional behavioural change on top of the intended one) - the
    // *only* enforcement point is now SetLanguageDetectionEnabled's own
    // isAllowedToPerform(), reached through maybePerformAs(). This is
    // asserted directly below, in contrast to the defense-in-depth tests
    // above for the other four write methods.
    //
    // Both action methods are `protected` (dispatched only via ilCtrl),
    // hence invoked here through reflection.
    // -----------------------------------------------------------------

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        // ReflectionMethod::setAccessible() has had no effect (and has been
        // deprecated) since PHP 8.1 - invoke() already bypasses visibility
        // on its own since then.
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
        // The stubbed Error result's \Throwable is unconditionally passed
        // through activityErrorMessage(), which now logs it via the "lang"
        // component logger - this test is only about the maybePerformAs()
        // arguments, but still needs the logger stub so the (irrelevant to
        // this test) logging call does not fatally error on a null $DIC.
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
        // See testEnableLanguageDetectionObjectCallsMaybePerformAsWithEnabledTrue()
        // above for why this stub is needed even though this test itself is
        // only about the maybePerformAs() arguments.
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

        // setLanguageDetectionEnabledObject() wraps the message as
        // "{error}<br/>action_aborted", same as uninstallObject().
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

        // If the `return` after the redirect were ever dropped, execution
        // would fall through to the success path, which calls
        // $this->viewObject() - a method that reaches deep into
        // un-stubbed legacy collaborators ($this->languageFolderTable,
        // $this->ui_factory, $this->toolbar, ...) on this
        // reflection-constructed instance and would surface as a fatal
        // error/TypeError rather than silently passing.
        $this->invokeProtectedMethod($gui, 'enableLanguageDetectionObject');
    }

    /**
     * Regression test documenting the deliberate asymmetry described above:
     * unlike installObject()/uninstallObject()/refreshSelectedObject()/
     * uninstallChangesObject() (all four call $this->checkPermission('write')
     * as their first statement, see the tests earlier in this class),
     * enableLanguageDetectionObject()/disableLanguageDetectionObject() must
     * NOT call checkPermission() themselves - this was true before the
     * extraction and remains true after it. The only enforcement is now
     * SetLanguageDetectionEnabled::isAllowedToPerform() via maybePerformAs()
     * (see SetLanguageDetectionEnabledTest::
     * testMaybePerformAsWithDeniedPermissionReturnsErrorAndNeverWritesTheSetting()
     * for that side of the contract).
     */
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
        // Permission denial is simulated at the Activity level (an Error
        // result) so the success path (which would call $this->viewObject(),
        // unreachable here) is never taken - this test is only about
        // checkPermission() never being invoked, not about the outcome of
        // the permission check itself.
        $set_language_detection_enabled->method('maybePerformAs')->willReturn(
            new ResultError('no write permission')
        );
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'set_language_detection_enabled',
            $set_language_detection_enabled
        );

        $this->invokeProtectedMethod($gui, 'enableLanguageDetectionObject');
        $this->invokeProtectedMethod($gui, 'disableLanguageDetectionObject');
    }

    // -----------------------------------------------------------------
    // abortIfAnyIdIsNotALanguageObject() - the blocker fix: every request-
    // supplied id in $ids must be confirmed to be a "lng" object BEFORE its
    // title is ever trusted as a language key (see the method's own
    // docblock in the production class for the full rationale). Exercised
    // indirectly through every public/protected caller below, since the
    // method itself is private.
    //
    // ilObject::_lookupType()/_lookupTitle() are both backed by
    // `global $DIC["ilObjDataCache"]` (see class.ilObject.php) - stubbed via
    // stubObjDataCache() below, exactly like stubComponentRepositoryWithNoPlugins()
    // already stubs `global $DIC["component.repository"]` for
    // ilObjLanguage::refreshPlugins().
    // -----------------------------------------------------------------

    /**
     * Stubs `global $DIC["ilObjDataCache"]` (see ilObject::_lookupType()/
     * _lookupTitle()) so abortIfAnyIdIsNotALanguageObject() and the
     * downstream `ilObject::_lookupTitle((int) $obj_id)` calls in the
     * production class can be driven from a unit test without a real
     * database.
     *
     * Reuses (rather than overwrites) an already-present $GLOBALS['DIC']
     * container - e.g. one stubComponentRepositoryWithNoPlugins() built
     * first in the same test for ilObjLanguage::refreshPlugins() - so both
     * stubs can coexist when a single test needs both.
     *
     * @param array<int, string> $types_by_id obj_id => type (e.g. "lng",
     *        "cat"); an id missing from this array resolves to "" - a
     *        never-installed/non-existing object, exactly like the real
     *        ilObjectDataCache.
     * @param array<int, string> $titles_by_id obj_id => title. Ignored (and
     *        lookupTitle() must never even be called - enforced via
     *        expects($this->never())) when $expect_no_title_lookup is true,
     *        which every "the request must be aborted before any id's title
     *        is trusted" test below uses: that IS the property the blocker
     *        fix guarantees, not just an implementation detail.
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

    /**
     * Regression test for installObject(): a single non-"lng" id among
     * otherwise-plausible ids must abort the WHOLE request - the established
     * 'obj_not_found'+'action_aborted' failure message, a redirect to
     * "view", and InstallLanguage::maybePerformAs() must NEVER be called
     * (i.e. nothing about the request is honoured, see
     * abortIfAnyIdIsNotALanguageObject()'s own docblock).
     */
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

    /**
     * Regression test documenting that installObject()'s previous behaviour
     * (for ids that ARE all "lng" objects) is unchanged by the blocker fix:
     * InstallLanguage::maybePerformAs() must still be called, with
     * 'language_keys' resolved from ilObject::_lookupTitle() exactly as
     * before.
     */
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

    /**
     * uninstallObject() variant of
     * testInstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsInstallLanguage().
     */
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

    /**
     * Regression test documenting that uninstallObject()'s previous
     * behaviour (for ids that ARE all "lng" objects) is unchanged by the
     * blocker fix.
     */
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

    /**
     * uninstallChangesObject() variant of
     * testInstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsInstallLanguage().
     */
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

    /**
     * refreshSelectedObject() variant of
     * testInstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsInstallLanguage().
     */
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
     * Builds a `ui_factory` stub sufficient for buildConfirmModal()'s abort
     * branch: `$f->modal()->interruptive($title, '', '')->withActionButtonLabel(...)`.
     * Returns the exact Interruptive instance that chain produces, so a test
     * can assert buildConfirmModal() returned it unchanged (i.e. that the
     * abort branch's `return` was actually taken, rather than falling
     * through into the per-id loop).
     *
     * @return array{0: \ILIAS\UI\Factory&\PHPUnit\Framework\MockObject\MockObject, 1: \ILIAS\UI\Implementation\Component\Modal\Interruptive}
     */
    private function createStubModalUiFactory(): array
    {
        // buildConfirmModal() is declared to return the concrete
        // Implementation\Component\Modal\Interruptive class, not just the
        // M\Interruptive interface - a mock of the interface alone fails
        // that return type check, so the concrete class must be mocked
        // here instead (createMock() disables its constructor, so the real
        // SignalGeneratorInterface dependency is never actually needed).
        $interruptive = $this->createMock(\ILIAS\UI\Implementation\Component\Modal\Interruptive::class);
        $interruptive->method('withActionButtonLabel')->willReturnSelf();

        $modal_factory = $this->createMock(\ILIAS\UI\Component\Modal\Factory::class);
        $modal_factory->method('interruptive')->willReturn($interruptive);

        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('modal')->willReturn($modal_factory);

        return [$ui_factory, $interruptive];
    }

    /**
     * buildConfirmModal() variant of
     * testInstallObjectAbortsWhenAnyIdIsNotALanguageObjectAndNeverCallsInstallLanguage():
     * a non-"lng" id must abort exactly like the four write-command methods
     * above (buildConfirmModal() delegates to the very same
     * abortIfAnyIdIsNotALanguageObject()) - the method's return type still
     * requires a Modal value even on abort (see the inline comment on that
     * abort branch: never actually rendered in production, since the
     * redirect takes precedence), which this test also pins down: the
     * exact Interruptive instance the
     * abort branch itself builds must be returned, proving the per-id loop
     * (and therefore ilObject::_lookupTitle()/ilObjLanguage::_getLastLocalChange())
     * was never reached - no data belonging to the wrongly-typed object ever
     * leaks into the built UI.
     */
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

    /**
     * confirmRefreshSelectedObject() variant of the abort tests above: takes
     * $ids straight from its own $a_ids argument, so - unlike
     * confirmUninstallObject()/confirmUninstallChangesObject() below - no
     * request_wrapper/id_token stubbing is needed to reach the abort check.
     */
    public function testConfirmRefreshSelectedObjectAbortsWhenAnyIdIsNotALanguageObject(): void
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

        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());

        $gui->confirmRefreshSelectedObject(['5']);
    }

    /**
     * Builds a reflection-constructed GUI instance whose private
     * getIdsFromQueryToken() (used by confirmUninstallObject()/
     * confirmUninstallChangesObject()) resolves to exactly $ids - stubbing
     * `request_wrapper`/`id_token`/`refinery` (see the production method's
     * body) rather than mocking the private method itself, which PHPUnit
     * cannot intercept (private methods are not polymorphic in PHP).
     */
    private function createGuiWithIdsFromQueryToken(
        array $ids,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();

        $id_token = new \ILIAS\UI\URLBuilderToken(['language_folder'], 'obj_ids');

        $request_wrapper = $this->createMock(\ILIAS\HTTP\Wrapper\RequestWrapper::class);
        $request_wrapper->method('has')->with($id_token->getName())->willReturn(true);
        $request_wrapper->method('retrieve')->willReturn($ids);

        $custom_group = $this->createMock(\ILIAS\Refinery\Custom\Group::class);
        $custom_group->method('transformation')->willReturn(
            $this->createMock(\ILIAS\Refinery\Transformation::class)
        );
        $refinery = $this->createMock(\ILIAS\Refinery\Factory::class);
        $refinery->method('custom')->willReturn($custom_group);

        $this->setProperty($gui, 'id_token', $id_token);
        $this->setProperty($gui, 'request_wrapper', $request_wrapper);
        $this->setProperty($gui, 'refinery', $refinery);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'ctrl', $ctrl);

        return $gui;
    }

    /**
     * confirmUninstallObject() variant of the abort tests above.
     */
    public function testConfirmUninstallObjectAbortsWhenAnyIdIsNotALanguageObject(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithIdsFromQueryToken(
            ['5'],
            $this->createLanguageMockReturningTopicAsIs(),
            $tpl,
            $ctrl
        );

        $gui->confirmUninstallObject();
    }

    /**
     * confirmUninstallChangesObject() variant of the abort tests above.
     */
    public function testConfirmUninstallChangesObjectAbortsWhenAnyIdIsNotALanguageObject(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())
            ->method('setOnScreenMessage')
            ->with('failure', 'obj_not_found<br/>action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageFolderGUI::class),
            'view'
        );

        $gui = $this->createGuiWithIdsFromQueryToken(
            ['5'],
            $this->createLanguageMockReturningTopicAsIs(),
            $tpl,
            $ctrl
        );

        $gui->confirmUninstallChangesObject();
    }
}
