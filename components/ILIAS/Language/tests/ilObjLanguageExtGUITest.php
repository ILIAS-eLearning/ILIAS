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
 * Built like ilObjLanguageFolderGUITest (newInstanceWithoutConstructor plus reflection-injected collaborators).
 * The ilPropertyFormGUI builders are stubbed via a partial mock: the form constructor needs `global $DIC`.
 */
class ilObjLanguageExtGUITest extends TestCase
{
    /** $GLOBALS['DIC'] is global state shared by all tests of the process. */
    protected function tearDown(): void
    {
        unset($GLOBALS['DIC']);
        $this->stubbed_activity_error_logger = null;
    }

    /** Set by stubLoggerLangError(), attached to the GUI's `activity_error_logger`; reset in tearDown(). */
    private ?\ilLogger $stubbed_activity_error_logger = null;

    /** The logger of stubLoggerLangError(), or a permissive stub for tests expecting no logging. */
    private function activityErrorLoggerForGui(): \ilLogger
    {
        return $this->stubbed_activity_error_logger ?? $this->createMock(\ilLogger::class);
    }

    private function setProperty(object $object, string $property_name, mixed $value): void
    {
        $property = new ReflectionProperty($object, $property_name);
        $property->setValue($object, $value);
    }

    /** Every GUI reaching maybePerformAs() needs a `ui_factory` (typed property of ilObjectGUI); a bare mock suffices. */
    private function stubUiFactoryForMaybePerformAs(): \ILIAS\UI\Factory&MockObject
    {
        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('input')->willReturn(
            $this->createMock(\ILIAS\UI\Component\Input\Factory::class)
        );

        return $ui_factory;
    }

    /** `private readonly` properties of the leaf class are not found on a PHPUnit mock subclass by a plain ReflectionProperty. */
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
     * Only the "<class>: <message>\n" prefix of the logged string is asserted, the trace is environment-dependent.
     * The mock is wired onto the GUI by the createGuiFor*() factories.
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

    /** A non-empty "translation" POST value means the checkbox was checked; parameters come from $this->object->key. */
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

    /** An unchecked HTML checkbox is not submitted: a missing "translation" key builds enabled=false. */
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

    /** Only shown if the value changed (see 'changed' in SetLanguageTranslationEnabled::getOutputDescription()). */
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

    /** saveSettingsObject() never redirects; a \Throwable still goes through activityErrorMessage(). */
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

        // no $ctrl injected: a redirect would fatally error instead of silently passing
        $gui->saveSettingsObject();
    }

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

    /** 'translations' is keyed by every installed language, values trimmed from the form's "trans_<lang_key>". */
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

    /** A \Throwable shows the generic logged message and redirects to "view" without reaching the success path. */
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

        // a dropped `return` after the redirect would run the success path too and fail the once() expectations
        $gui->saveNewEntryObject();
    }

    /** An input error (4xx other than 403) re-shows the form with the entered values instead of redirecting. */
    public function testSaveNewEntryObjectDoesNotRedirectAndReshowsTheFormWithPostedValuesOnAnInputError(): void
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

        // no redirect: the form is re-rendered in this request
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->never())->method('redirect');

        $form = $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']);
        $form->expects($this->once())->method('setValuesByPost');
        // addNewEntryObject($form) is the re-render path under test; its `global $DIC["tpl"]` access is stubbed
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
        // addNewEntryObject() also checks the "eid" query parameter: stubbed as absent
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
