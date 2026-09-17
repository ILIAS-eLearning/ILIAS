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

use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ilObjLanguageExtGUI is bound tightly to the legacy $DIC/ilObjectGUI
 * bootstrap, exactly like ilObjLanguageFolderGUI (see
 * ilObjLanguageFolderGUITest's class docblock for the general technique this
 * file reuses: ReflectionClass::newInstanceWithoutConstructor() plus
 * reflection-injected collaborators).
 *
 * saveSettingsObject()/saveNewEntryObject() both unconditionally build a
 * legacy ilPropertyFormGUI at some point (initNewSettingsForm()/
 * initAddNewEntryForm()) - and ilPropertyFormGUI's own constructor reaches
 * into `global $DIC` for $DIC->language()/$DIC->ctrl() unconditionally. Since
 * building that form is not what these tests are about (the actual
 * behaviour under test is the Activity dispatch and the GUI's handling of
 * its Result), initNewSettingsForm()/initAddNewEntryForm() are stubbed out
 * via a partial mock of the GUI itself instead - the exact same technique
 * ilObjLanguageFolderGUITest uses for checkPermission().
 */
class ilObjLanguageExtGUITest extends TestCase
{
    /**
     * $GLOBALS['DIC'] is genuinely global state shared by every test in this
     * (single) PHP process - see ilObjLanguageFolderGUITest::tearDown() for
     * the same reasoning/pattern.
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['DIC']);
        $this->stubbed_activity_error_logger = null;
    }

    /**
     * Holds whatever stubLoggerLangError() built for the currently running
     * test, so the createGuiFor*() factories below can attach it to the
     * GUI instance's `activity_error_logger` property (see
     * RendersActivityErrors) instead of a plain permissive stub. Reset in
     * tearDown() so it never leaks between tests.
     */
    private ?\ilLogger $stubbed_activity_error_logger = null;

    /**
     * The logger to wire onto a GUI instance's `activity_error_logger`
     * property while building it for a test: whatever stubLoggerLangError()
     * set up for this test, or - for tests that never expect a Throwable
     * to be logged - a permissive stub.
     */
    private function activityErrorLoggerForGui(): \ilLogger
    {
        return $this->stubbed_activity_error_logger ?? $this->createMock(\ilLogger::class);
    }

    private function setProperty(object $object, string $property_name, mixed $value): void
    {
        $property = new ReflectionProperty($object, $property_name);
        $property->setValue($object, $value);
    }

    /**
     * maybePerformAs() now takes the calling GUI's `ILIAS\UI\Factory::input()`
     * as its (unused by these Activities, see LanguageActivity) first
     * argument, so every reflection-/mock-builder-constructed GUI instance
     * that reaches a maybePerformAs() call needs a `ui_factory` (a typed,
     * non-nullable property on the ilObjectGUI ancestor) - otherwise PHP
     * fatals with "must not be accessed before initialization" before the
     * mocked Activity is ever reached. What input() actually returns is
     * irrelevant to any of these tests (the Activities never use it), so a
     * bare mock is enough.
     */
    private function stubUiFactoryForMaybePerformAs(): \ILIAS\UI\Factory&MockObject
    {
        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('input')->willReturn(
            $this->createMock(\ILIAS\UI\Component\Input\Factory::class)
        );

        return $ui_factory;
    }

    /**
     * add_language_entry/set_language_translation_enabled are `private
     * readonly` properties declared directly on ilObjLanguageExtGUI (not an
     * ancestor) - on a PHPUnit mock subclass, plain `new
     * ReflectionProperty($gui, name)` cannot locate a *readonly* property
     * declared on the leaf class this way, so it must be looked up via the
     * exact declaring class instead (see ilObjLanguageFolderGUITest's
     * identical helper for 'current_user_id').
     */
    private function setReadonlyPropertyDeclaredOnGuiClass(
        ilObjLanguageExtGUI $gui,
        string $property_name,
        mixed $value
    ): void {
        (new ReflectionClass(ilObjLanguageExtGUI::class))
            ->getProperty($property_name)
            ->setValue($gui, $value);
    }

    private function createLanguageMockReturningTopicAsIs(array $installed_languages = []): ilLanguage&MockObject
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $lng->method('getInstalledLanguages')->willReturn($installed_languages);

        return $lng;
    }

    /**
     * activityErrorMessage() logs `get_class($error) . ': ' .
     * $error->getMessage() . "\n" . $error->getTraceAsString()` via
     * `$this->activity_error_logger` (see RendersActivityErrors) - the
     * trace itself is environment-dependent (absolute file paths, line
     * numbers), so only the "<class>: <message>\n" prefix is asserted
     * here, not the full logged string. The built mock is stashed so the
     * createGuiFor*() factories below can wire it onto the instance under
     * test (see ilObjLanguageFolderGUITest::stubLoggerLangError() for the
     * identical reasoning/pattern).
     *
     * @param non-empty-string $expected_logged_message
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

    private function mockHttpWithParsedBody(array $parsed_body): \ILIAS\HTTP\GlobalHttpState
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($parsed_body);

        $http = $this->createMock(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($request);

        return $http;
    }

    private function createFakeLanguageObject(string $key): ilObjLanguageExt&MockObject
    {
        /** @var ilObjLanguageExt&MockObject $object */
        $object = $this->createMock(ilObjLanguageExt::class);
        $object->key = $key;

        return $object;
    }

    // -----------------------------------------------------------------
    // saveSettingsObject()
    // -----------------------------------------------------------------

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForSaveSettings(
        SetLanguageTranslationEnabled $set_language_translation_enabled,
        \ILIAS\HTTP\GlobalHttpState $http,
        ilObjUser $user,
        ilGlobalTemplateInterface $tpl,
        ?ilLanguage $lng = null
    ): ilObjLanguageExtGUI {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initNewSettingsForm'])
            ->getMock();

        $form = $this->createMock(ilPropertyFormGUI::class);
        $form->method('getHTML')->willReturn('');
        $gui->method('initNewSettingsForm')->willReturn($form);

        $this->setProperty($gui, 'http', $http);
        $this->setProperty($gui, 'user', $user);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'lng', $lng ?? $this->createLanguageMockReturningTopicAsIs());
        $this->setProperty($gui, 'object', $this->createFakeLanguageObject('de'));
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'set_language_translation_enabled',
            $set_language_translation_enabled
        );
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    /**
     * A non-empty, non-'' "translation" POST value means the checkbox was
     * submitted (checked) - see saveSettingsObject()'s own docblock on the
     * production class. This also pins down that the parameters are built
     * from $this->object->key, not some other source.
     */
    public function testSaveSettingsObjectBuildsEnabledTrueFromANonEmptyTranslationPostValue(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createMock(SetLanguageTranslationEnabled::class);
        $set_language_translation_enabled->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_key' => 'de', 'enabled' => true])
            ->willReturn(new ResultOk(['changed' => true]));

        $gui = $this->createGuiForSaveSettings(
            $set_language_translation_enabled,
            $this->mockHttpWithParsedBody(['translation' => '1']),
            $user,
            $this->createMock(ilGlobalTemplateInterface::class)
        );

        $gui->saveSettingsObject();
    }

    /**
     * An unchecked HTML checkbox is not submitted at all - a missing
     * "translation" POST key must build enabled=false, exactly like an
     * explicit empty string does.
     */
    public function testSaveSettingsObjectBuildsEnabledFalseWhenTranslationPostKeyIsMissing(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createMock(SetLanguageTranslationEnabled::class);
        $set_language_translation_enabled->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['language_key' => 'de', 'enabled' => false])
            ->willReturn(new ResultOk(['changed' => false]));

        $gui = $this->createGuiForSaveSettings(
            $set_language_translation_enabled,
            $this->mockHttpWithParsedBody([]),
            $user,
            $this->createMock(ilGlobalTemplateInterface::class)
        );

        $gui->saveSettingsObject();
    }

    public function testSaveSettingsObjectShowsSuccessMessageWhenResultReportsChangedTrue(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createMock(SetLanguageTranslationEnabled::class);
        $set_language_translation_enabled->method('maybePerformAs')->willReturn(new ResultOk(['changed' => true]));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'settings_saved');

        $gui = $this->createGuiForSaveSettings(
            $set_language_translation_enabled,
            $this->mockHttpWithParsedBody(['translation' => '1']),
            $user,
            $tpl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->saveSettingsObject();
    }

    /**
     * Only shown if the value actually changed - exactly reproducing the
     * extracted code's original behaviour (see the 'changed' field's own
     * description in SetLanguageTranslationEnabled::getOutputDescription()).
     */
    public function testSaveSettingsObjectShowsNoMessageWhenResultReportsChangedFalse(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createMock(SetLanguageTranslationEnabled::class);
        $set_language_translation_enabled->method('maybePerformAs')->willReturn(new ResultOk(['changed' => false]));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->never())->method('setOnScreenMessage');

        $gui = $this->createGuiForSaveSettings(
            $set_language_translation_enabled,
            $this->mockHttpWithParsedBody(['translation' => '1']),
            $user,
            $tpl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->saveSettingsObject();
    }

    /**
     * Unlike ilObjLanguageFolderGUI's write-command methods, saveSettingsObject()
     * never redirects - it renders the settings form directly with a
     * non-persisted message. A \Throwable error must still go through
     * activityErrorMessage() (logged, generic message shown), never the raw
     * message.
     */
    public function testSaveSettingsObjectShowsGenericFailureMessageAndNeverRedirectsOnThrowableError(): void
    {
        $this->stubLoggerLangError('boom');

        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createMock(SetLanguageTranslationEnabled::class);
        $set_language_translation_enabled->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', 'action_aborted');

        $gui = $this->createGuiForSaveSettings(
            $set_language_translation_enabled,
            $this->mockHttpWithParsedBody(['translation' => '1']),
            $user,
            $tpl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        // saveSettingsObject() has no $ctrl collaborator injected at all
        // here - if it ever tried to redirect, this would fatally error
        // (Reflection-constructed instance, $ctrl never set), rather than
        // silently passing.
        $gui->saveSettingsObject();
    }

    // -----------------------------------------------------------------
    // saveNewEntryObject()
    // -----------------------------------------------------------------

    /**
     * @return ilObjLanguageExtGUI&MockObject
     */
    private function createGuiForSaveNewEntry(
        AddLanguageEntry $add_language_entry,
        ilCtrl $ctrl,
        ilObjUser $user,
        ilGlobalTemplateInterface $tpl,
        ilPropertyFormGUI&MockObject $form,
        ilLanguage $lng
    ): ilObjLanguageExtGUI {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initAddNewEntryForm'])
            ->getMock();

        $gui->method('initAddNewEntryForm')->willReturn($form);

        $this->setProperty($gui, 'ctrl', $ctrl);
        $this->setProperty($gui, 'user', $user);
        $this->setProperty($gui, 'tpl', $tpl);
        $this->setProperty($gui, 'lng', $lng);
        $this->setProperty($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->setReadonlyPropertyDeclaredOnGuiClass($gui, 'add_language_entry', $add_language_entry);
        $this->setReadonlyPropertyDeclaredOnGuiClass(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    private function mockCheckedFormWithInputs(array $inputs): ilPropertyFormGUI&MockObject
    {
        $form = $this->createMock(ilPropertyFormGUI::class);
        $form->method('checkInput')->willReturn(true);
        $form->method('getInput')->willReturnCallback(
            static fn(string $name): mixed => $inputs[$name] ?? ''
        );

        return $form;
    }

    /**
     * Exercises the full parameter build-up: 'module'/'identifier' straight
     * from the form, and 'translations' as an array keyed by every
     * installed language (via $this->lng->getInstalledLanguages()), each
     * value trimmed from the form's own "trans_<lang_key>" field.
     */
    public function testSaveNewEntryObjectBuildsModuleIdentifierAndTranslationsFromFormAcrossAllInstalledLanguages(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createMock(AddLanguageEntry::class);
        $add_language_entry->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, [
                'module' => 'common',
                'identifier' => 'sometopic',
                'translations' => ['de' => 'Hallo', 'en' => 'Hello'],
            ])
            ->willReturn(new ResultOk(['added_language_keys' => ['de', 'en']]));

        $form = $this->mockCheckedFormWithInputs([
            'mod' => 'common',
            'id' => 'sometopic',
            'trans_de' => '  Hallo  ',
            'trans_en' => '  Hello  ',
        ]);

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $this->createMock(ilCtrl::class),
            $user,
            $this->createMock(ilGlobalTemplateInterface::class),
            $form,
            $this->createLanguageMockReturningTopicAsIs(['de', 'en'])
        );

        $gui->saveNewEntryObject();
    }

    public function testSaveNewEntryObjectShowsSuccessMessageAndRedirectsToViewOnSuccess(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createMock(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(
            new ResultOk(['added_language_keys' => ['de']])
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'settings_saved', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $ctrl,
            $user,
            $tpl,
            $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']),
            $this->createLanguageMockReturningTopicAsIs(['de'])
        );

        $gui->saveNewEntryObject();
    }

    /**
     * A \Throwable error must show the generic, logged message (via
     * activityErrorMessage()) and redirect to "view", exactly like
     * ilObjLanguageFolderGUI's write commands - and must not crash/continue
     * to the success path.
     */
    public function testSaveNewEntryObjectShowsGenericFailureMessageAndRedirectsToViewOnThrowableError(): void
    {
        $this->stubLoggerLangError('boom');

        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createMock(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(
            new ResultError(new \RuntimeException('boom'))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', 'action_aborted', true);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with(
            $this->isInstanceOf(ilObjLanguageExtGUI::class),
            'view'
        );

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $ctrl,
            $user,
            $tpl,
            $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']),
            $this->createLanguageMockReturningTopicAsIs(['de'])
        );

        // If the `return` after the redirect were ever dropped, execution
        // would fall through past the error branch to the success path,
        // which would call setOnScreenMessage('success', ...) a second
        // time and $ctrl->redirect() a second time too - both `once()`
        // expectations above would then fail rather than silently passing.
        $gui->saveNewEntryObject();
    }

    /**
     * Regression test: a SafeToDisplayActivityError (e.g. AddLanguageEntry's
     * InvalidInputException for a missing/blank mandatory "de"/"en" value)
     * must NOT redirect - unlike a generic internal failure (see
     * testSaveNewEntryObjectShowsGenericFailureMessageAndRedirectsToViewOnThrowableError()
     * above), the form is re-shown with the already-entered values still
     * filled in ($form->setValuesByPost(), then addNewEntryObject($form)),
     * exactly like the existing $form->checkInput() === false branch does.
     */
    public function testSaveNewEntryObjectDoesNotRedirectAndReshowsTheFormWithPostedValuesOnASafeToDisplayError(): void
    {
        $user = $this->createMock(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createMock(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(
            new ResultError(new \ILIAS\Language\Activities\InvalidInputException('A value is required for: en.'))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'A value is required for: en.'
        );

        // No `true` (persisted) argument, and no redirect() at all - the
        // form is re-rendered directly in this same request.
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->never())->method('redirect');

        $form = $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']);
        $form->expects($this->once())->method('setValuesByPost');
        // addNewEntryObject($form) (unmocked - it is the actual re-render
        // path this test verifies is taken) calls `global $DIC["tpl"]` and
        // renders $form->getHTML() into it - unrelated to this test's own
        // concern (which is that saveNewEntryObject() takes this path and
        // never redirects), so both are stubbed to harmless no-ops.
        $form->method('getHTML')->willReturn('');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['tpl'] = $this->createMock(ilGlobalTemplateInterface::class);

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $ctrl,
            $user,
            $tpl,
            $form,
            $this->createLanguageMockReturningTopicAsIs(['de', 'en'])
        );
        // addNewEntryObject($form) also unconditionally checks
        // $this->http->wrapper()->query()->has("eid") before falling
        // through to the given $form - unrelated to this test's concern,
        // stubbed to "no such query parameter".
        $query_wrapper = $this->createMock(\ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper::class);
        $query_wrapper->method('has')->willReturn(false);
        $wrapper_factory = $this->createMock(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $wrapper_factory->method('query')->willReturn($query_wrapper);
        $http = $this->createMock(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('wrapper')->willReturn($wrapper_factory);
        $this->setProperty($gui, 'http', $http);

        $gui->saveNewEntryObject();
    }
}
