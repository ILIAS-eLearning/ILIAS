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
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Data\Result\Ok as ResultOk;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../UI/tests/UITestHelper.php';

class ilObjLanguageFolderGUITest extends TestCase
{
    use RestoresProcessState;

    use UITestHelper;
    use ilObjLanguageFolderGUITestFixture {
        tearDown as private tearDownGuiTestSupport;
    }

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    /** The real, static ilSession writes into $_SESSION. */
    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->tearDownGuiTestSupport();
    }

    /** @return ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject */
    private function createGuiWithMockedCheckPermission(): ilObjLanguageFolderGUI
    {
        /** @var ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkPermission'])
            ->getMock();

        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'tpl', $this->createStub(ilGlobalTemplateInterface::class));
        $this->inject($gui, 'ctrl', $this->createStub(ilCtrl::class));
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        // a readonly property declared on the leaf class cannot be found on a PHPUnit mock via a plain ReflectionProperty,
        // so it is looked up on the declaring class
        $this->inject($gui, 'current_user_id', 6);
        $this->inject(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    /** checkPermission('write') once before any work; the Activity checks again (defense in depth). */
    public function testUninstallObjectChecksWritePermissionBeforeActing(): void
    {
        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $uninstall_language = $this->createStub(UninstallLanguage::class);
        $uninstall_language->method('maybePerformAs')->willReturn(
            new ResultOk($this->uninstallPerformResult([], [], [], []))
        );
        $this->inject($gui, 'uninstall_language', $uninstall_language);

        $gui->uninstallObject([]);
    }

    public function testInstallObjectMustCheckWritePermissionBeforeActing(): void
    {
        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $install_language = $this->createStub(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk($this->emptyPerformResult()));
        $this->inject($gui, 'install_language', $install_language);

        // empty $ids avoid the legacy `new ilObjLanguage(...)` coupling
        $gui->installObject([], InstallLanguage::MODE_INSTALL);
    }

    public function testRefreshSelectedObjectMustCheckWritePermissionBeforeActing(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $update_language = $this->createStub(UpdateLanguage::class);
        $update_language->method('maybePerformAs')->willReturn(new ResultOk($this->updatePerformResult([], [])));
        $this->inject($gui, 'update_language', $update_language);

        $gui->refreshSelectedObject([]);
    }

    public function testUninstallChangesObjectMustCheckWritePermissionBeforeActing(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();

        $gui = $this->createGuiWithMockedCheckPermission();
        $gui->expects($this->once())->method('checkPermission')->with('write');

        $remove_local_language_changes = $this->createStub(RemoveLocalLanguageChanges::class);
        $remove_local_language_changes->method('maybePerformAs')->willReturn(
            new ResultOk($this->removeLocalChangesPerformResult([], [], []))
        );
        $this->inject(
            $gui,
            'remove_local_language_changes',
            $remove_local_language_changes
        );

        $gui->uninstallChangesObject([]);
    }

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

    /** @return array{updated_language_keys: list<string>, not_installed_language_keys: list<string>} */
    private function updatePerformResult(array $updated, array $not_installed): array
    {
        return [
            'updated_language_keys' => $updated,
            'not_installed_language_keys' => $not_installed,
        ];
    }

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

    #[AllowMockObjectsWithoutExpectations]
    public function testInstallObjectShowsAFailureNamingTheLanguagesWhoseOverlayWasNotWritten(): void
    {
        $this->stubObjDataCache([5 => 'lng', 6 => 'lng'], [5 => 'de', 6 => 'fr']);
        $install_language = $this->createStub(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk(
            ['installed_language_keys' => ['de', 'fr'], 'overlay_write_failed_language_keys' => ['de', 'fr']] + $this->emptyPerformResult()
        ));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->installObject(['5', '6'], InstallLanguage::MODE_INSTALL);

        $this->assertSame(
            [
                ['success', 'meta_l_de, meta_l_fr installed.'],
                ['failure', 'lng_po_overlay_not_written_languages: meta_l_de, meta_l_fr'],
            ],
            $messages
        );
    }

    /** setOnScreenMessage() keeps one message per type: overlay failure and invalid local file go into ONE failure message. */
    #[AllowMockObjectsWithoutExpectations]
    public function testInstallObjectCombinesTheOverlayFailureWithTheInvalidLocalFileFailure(): void
    {
        $this->stubObjDataCache([5 => 'lng'], [5 => 'de']);
        $install_language = $this->createStub(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk([
            'installed_language_keys' => ['de'],
            'invalid_local_language_files' => ['ilias_de.lang.local'],
            'overlay_write_failed_language_keys' => ['de'],
        ] + $this->emptyPerformResult()));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);

        $failures = array_values(array_filter($messages, static fn(array $m): bool => $m[0] === 'failure'));
        $this->assertSame(
            [['failure', 'local_language_files: ilias_de.lang.local. file_not_valid<br />lng_po_overlay_not_written_languages: meta_l_de']],
            $failures
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInstallObjectNamesTheDuplicateCustomizingEntriesInTheFailureMessage(): void
    {
        $this->stubObjDataCache([5 => 'lng'], [5 => 'de']);
        $install_language = $this->createStub(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk([
            'installed_language_keys' => ['de'],
            'overlay_write_failed_language_keys' => ['de'],
            'duplicate_customizing_entries' => ['de: common#:#<dup> (mme: <dup>)'],
        ] + $this->emptyPerformResult()));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));
        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);

        $failures = array_values(array_filter($messages, static fn(array $m): bool => $m[0] === 'failure'));
        $this->assertSame(
            [['failure', 'lng_po_overlay_not_written_languages: meta_l_de<br />lng_identifier_not_unique: de: common#:#&lt;dup&gt; (mme: &lt;dup&gt;)']],
            $failures
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInstallObjectShowsNoFailureWhenEveryOverlayWasWritten(): void
    {
        $this->stubObjDataCache([5 => 'lng'], [5 => 'de']);
        $install_language = $this->createStub(InstallLanguage::class);
        $install_language->method('maybePerformAs')->willReturn(new ResultOk(
            ['installed_language_keys' => ['de'], 'overlay_write_failed_language_keys' => []] + $this->emptyPerformResult()
        ));
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'meta_l_de installed.', true);

        $gui = $this->createGuiWithCollaborators(
            $install_language,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRefreshSelectedObjectShowsAFailureNamingTheLanguagesWhoseOverlayWasNotWritten(): void
    {
        $this->stubComponentRepositoryWithNoPlugins();
        $this->stubObjDataCache([5 => 'lng', 6 => 'lng'], [5 => 'de', 6 => 'fr']);
        $update_language = $this->createStub(UpdateLanguage::class);
        $update_language->method('maybePerformAs')->willReturn(new ResultOk(
            ['overlay_write_failed_language_keys' => ['fr']] + $this->updatePerformResult(['de', 'fr'], [])
        ));
        $messages = [];
        $tpl = $this->messageCollectingTemplate($messages);

        $gui = $this->createGuiWithUpdateLanguageCollaborators(
            $update_language,
            $tpl,
            $this->createStub(ilCtrl::class),
            $this->createLanguageMockWithFormatStrings()
        );
        $gui->refreshSelectedObject(['5', '6']);

        $this->assertSame(
            [
                ['success', 'selected_languages_updated meta_l_de, meta_l_fr'],
                ['failure', 'lng_po_overlay_not_written_languages: meta_l_fr'],
            ],
            $messages
        );
    }

    public function testOnlyTheStateChangingCommandsAreUnsafeGetCommandsSoTheCsrfTokenIsRequired(): void
    {
        $gui = (new ReflectionClass(ilObjLanguageFolderGUI::class))->newInstanceWithoutConstructor();
        $this->assertInstanceOf(ilCtrlSecurityInterface::class, $gui);

        $this->assertEqualsCanonicalizing(
            ['executeTableAction', 'enableLanguageDetection', 'disableLanguageDetection', 'confirmLanguageDetection'],
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

    private function stubHttpWithMethod(string $method): \ILIAS\HTTP\Services&\PHPUnit\Framework\MockObject\Stub
    {
        $request = $this->createStub(\Psr\Http\Message\ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $http = $this->createStub(\ILIAS\HTTP\Services::class);
        $http->method('request')->willReturn($request);

        return $http;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('detectionSwitchCommandsAndMethods')]
    public function testLanguageDetectionIsOnlySwitchedByPostAndOtherwiseOnlyTheViewIsShown(
        string $cmd,
        string $method,
        bool $must_switch
    ): void {
        $switch_method = $cmd . 'Object';
        $gui = $this->createDispatchGui($cmd, null, ['viewObject', 'enableLanguageDetectionObject', 'disableLanguageDetectionObject']);
        (new ReflectionProperty(ilObjLanguageFolderGUI::class, 'http'))->setValue($gui, $this->stubHttpWithMethod($method));

        $other = $cmd === 'enableLanguageDetection' ? 'disableLanguageDetectionObject' : 'enableLanguageDetectionObject';
        $gui->expects($must_switch ? $this->once() : $this->never())->method($switch_method);
        $gui->expects($this->never())->method($other);
        $gui->expects($must_switch ? $this->never() : $this->once())->method('viewObject');

        $gui->executeCommand();
    }

    /** @return array<string, array{string, string, bool}> */
    public static function detectionSwitchCommandsAndMethods(): array
    {
        return [
            'enable by GET' => ['enableLanguageDetection', 'GET', false],
            'disable by GET' => ['disableLanguageDetection', 'GET', false],
            'enable by HEAD' => ['enableLanguageDetection', 'HEAD', false],
            'enable by POST' => ['enableLanguageDetection', 'POST', true],
            'disable by POST' => ['disableLanguageDetection', 'POST', true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmParameterValues')]
    public function testConfirmLanguageDetectionIsReadOnlyAndRedirectsToTheViewWithTheRequestedModalMarked(
        ?string $parameter,
        bool|string $expected_confirm_state
    ): void {
        $gui = $this->createDispatchGui('confirmLanguageDetection', null, ['viewObject', 'enableLanguageDetectionObject', 'disableLanguageDetectionObject']);
        (new ReflectionProperty(ilObjLanguageFolderGUI::class, 'http'))->setValue($gui, $this->stubHttpWithMethod('GET'));

        $request_wrapper = $this->createStub(\ILIAS\HTTP\Wrapper\RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(
            static fn(string $name): bool => $name === 'lang_detection_enable' && $parameter !== null
        );
        $request_wrapper->method('retrieve')->willReturnCallback(
            static fn(string $name, \ILIAS\Refinery\Transformation $trafo): mixed => $trafo->transform($parameter)
        );
        $refinery = new \ILIAS\Refinery\Factory(new \ILIAS\Data\Factory(), $this->createStub(ilLanguage::class));
        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'refinery', $refinery);

        $set_language_detection_enabled = $this->createMock(SetLanguageDetectionEnabled::class);
        $set_language_detection_enabled->expects($this->never())->method('maybePerformAs');
        $this->inject($gui, 'set_language_detection_enabled', $set_language_detection_enabled);
        $settings = $this->createMock(ilSetting::class);
        $settings->expects($this->never())->method('set');
        $this->inject($gui, 'settings', $settings);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getNextClass')->willReturn('');
        $ctrl->method('getCmd')->willReturn('confirmLanguageDetection');
        $ctrl->expects($this->once())->method('redirect')->with($gui, 'view');
        $this->inject($gui, 'ctrl', $ctrl);

        $gui->expects($this->never())->method('enableLanguageDetectionObject');
        $gui->expects($this->never())->method('disableLanguageDetectionObject');
        $gui->expects($this->never())->method('viewObject');

        $gui->executeCommand();

        $this->assertSame($expected_confirm_state, $_SESSION['lang_detection_confirm'] ?? 'not marked');
    }

    /** @return array<string, array{?string, bool|string}> */
    public static function confirmParameterValues(): array
    {
        return [
            'enable' => ['1', true],
            'disable' => ['0', false],
            'parameter missing opens no modal' => [null, 'not marked'],
            'invalid value opens no modal' => ['abc', 'not marked'],
        ];
    }

    /**
     * @return array{0: ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject, 1: object}
     *   the GUI and a recorder: ->toolbar (components), ->toggle (args), ->modals (title => action), ->modal_state (spl id => [title, on_load])
     */
    private function createViewGuiRecordingTheToolbar(?bool $confirm_detection, bool $detection_on): array
    {
        // another test may have left the translation runtime without a build: its problem is logged, keep that off the output
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $dic = new \ILIAS\DI\Container();
        $dic['ilLoggerFactory'] = static fn() => $logger_factory;

        $record = new stdClass();
        $record->toolbar = [];
        $record->toggle = null;
        $record->modals = [];
        $record->modal_state = [];

        $signal = $this->createStub(\ILIAS\UI\Component\Signal::class);
        $modal_factory = $this->createMock(\ILIAS\UI\Component\Modal\Factory::class);
        $modal_factory->method('interruptive')->willReturnCallback(
            function (string $title, string $message, string $action) use ($record, $signal): \ILIAS\UI\Component\Modal\Interruptive {
                $record->modals[$title] = $action;
                $build = function (bool $on_load) use ($title, $record, $signal, &$build): \ILIAS\UI\Component\Modal\Interruptive {
                    $modal = $this->createMock(\ILIAS\UI\Component\Modal\Interruptive::class);
                    $record->modal_state[spl_object_id($modal)] = ['title' => $title, 'on_load' => $on_load];
                    $modal->method('getShowSignal')->willReturn($signal);
                    $modal->method('withActionButtonLabel')->willReturnSelf();
                    $modal->method('withOnLoad')->willReturnCallback(
                        static fn() => $build(true)
                    );

                    return $modal;
                };

                return $build(false);
            }
        );

        $toggle = $this->createMock(\ILIAS\UI\Component\Button\Toggle::class);
        $toggle->method('withAriaLabel')->willReturnSelf();
        $standard = $this->createStub(\ILIAS\UI\Component\Button\Standard::class);
        $standard->method('withOnClick')->willReturnSelf();
        $button_factory = $this->createMock(\ILIAS\UI\Component\Button\Factory::class);
        $button_factory->method('standard')->willReturn($standard);
        $button_factory->method('toggle')->willReturnCallback(
            function (string $label, $on, $off, bool $is_on) use ($record, $toggle) {
                $record->toggle = ['on' => $on, 'off' => $off, 'is_on' => $is_on];

                return $toggle;
            }
        );
        $ui_factory = $this->createStub(\ILIAS\UI\Factory::class);
        $ui_factory->method('modal')->willReturn($modal_factory);
        $ui_factory->method('button')->willReturn($button_factory);

        $toolbar = $this->createMock(ilToolbarGUI::class);
        $toolbar->method('addComponent')->willReturnCallback(
            function ($component) use ($record): void {
                $record->toolbar[] = $component;
            }
        );

        // minimal stateful ilCtrl: the link carries the parameter that was set at the time of the call
        $parameters = [];
        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('setParameter')->willReturnCallback(
            function (object $gui, string $name, $value) use (&$parameters): void {
                $parameters[$name] = $value;
            }
        );
        $ctrl->method('clearParameterByClass')->willReturnCallback(
            function (string $class, string $name) use (&$parameters): void {
                unset($parameters[$name]);
            }
        );
        $ctrl->method('getLinkTarget')->willReturnCallback(
            function (object $gui, string $cmd) use (&$parameters): string {
                return 'link:' . $cmd . '?' . http_build_query($parameters);
            }
        );
        $ctrl->method('getFormActionByClass')->willReturnCallback(
            static fn(string $class, string $cmd): string => 'form:' . $cmd
        );

        $table = $this->createStub(\ILIAS\UI\Component\Table\Data::class);
        $table->method('withRequest')->willReturnSelf();
        $folder_table = $this->createStub(ilLanguageFolderTable::class);
        $folder_table->method('getTable')->willReturn($table);

        $settings = $this->createStub(ilSetting::class);
        $settings->method('get')->willReturnCallback(
            static fn(string $key): string => $key === 'lang_detection' && $detection_on ? '1' : ''
        );

        $ini = $this->createStub(ilIniFile::class);
        $ini->method('variableExists')->willReturn(false);
        $GLOBALS['DIC'] = $dic;
        $GLOBALS['DIC']['ilClientIniFile'] = $ini;

        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkPermissionBool', 'confirmRefreshObject', 'buildConfirmModal', 'getUrl'])
            ->getMock();
        $gui->method('checkPermissionBool')->willReturn(true);
        $gui->method('confirmRefreshObject')->willReturn([]);
        $gui->method('getUrl')->willReturn('url');
        $gui->method('buildConfirmModal')->willReturn($this->createStub(\ILIAS\UI\Component\Modal\Interruptive::class));

        $ui_renderer = $this->createStub(\ILIAS\UI\Renderer::class);
        $ui_renderer->method('render')->willReturn('');
        foreach ([
            'lng' => $this->createLanguageMockReturningTopicAsIs(),
            'ctrl' => $ctrl,
            'ui_factory' => $ui_factory,
            'ui_renderer' => $ui_renderer,
            'toolbar' => $toolbar,
            'tpl' => $this->createStub(ilGlobalTemplateInterface::class),
            'settings' => $settings,
            'request' => $this->createStub(\Psr\Http\Message\ServerRequestInterface::class),
            'languageFolderTable' => $folder_table,
        ] as $name => $value) {
            (new ReflectionProperty(ilObjLanguageFolderGUI::class, $name))->setValue($gui, $value);
        }
        $_SESSION = $confirm_detection === null ? [] : ['lang_detection_confirm' => $confirm_detection];

        return [$gui, $record];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('viewDetectionStates')]
    public function testViewObjectBuildsTheToggleFromConfirmUrlsAndOpensOnlyTheRequestedModal(
        ?bool $confirm_detection,
        bool $detection_on,
        ?string $modal_with_on_load
    ): void {
        [$gui, $record] = $this->createViewGuiRecordingTheToolbar($confirm_detection, $detection_on);

        $gui->viewObject();

        $this->assertNotNull($record->toggle, 'toggle must be built and added');
        $this->assertSame('link:confirmLanguageDetection?lang_detection_enable=1', $record->toggle['on']);
        $this->assertSame('link:confirmLanguageDetection?lang_detection_enable=0', $record->toggle['off']);
        $this->assertSame($detection_on, $record->toggle['is_on']);

        $this->assertSame('form:enableLanguageDetection', $record->modals['ON']);
        $this->assertSame('form:disableLanguageDetection', $record->modals['OFF']);

        // judged on the modals that actually reached the toolbar
        $on_load_by_title = [];
        foreach ($record->toolbar as $component) {
            $state = $record->modal_state[spl_object_id($component)] ?? null;
            if ($state !== null && isset($record->modals[$state['title']])) {
                $on_load_by_title[$state['title']] = $state['on_load'];
            }
        }
        $this->assertSame(
            ['ON' => $modal_with_on_load === 'ON', 'OFF' => $modal_with_on_load === 'OFF'],
            $on_load_by_title
        );
        $this->assertArrayNotHasKey('lang_detection_confirm', $_SESSION, 'a reload must not open the modal again');
    }

    /** @return array<string, array{?bool, bool, ?string}> */
    public static function viewDetectionStates(): array
    {
        return [
            'plain view, detection off' => [null, false, null],
            'plain view, detection on' => [null, true, null],
            'confirm enable' => [true, false, 'ON'],
            'confirm disable' => [false, true, 'OFF'],
        ];
    }

    /**
     * Real Refinery, the request wrapper applies the transformation like the real one does.
     *
     * @param list<non-empty-string> $methods
     * @return ilObjLanguageFolderGUI&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createTableActionGui(?string $action, string $http_method, mixed $ids, array $methods): ilObjLanguageFolderGUI
    {
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array_merge(['checkPermission'], $methods))
            ->getMock();

        $action_token = new \ILIAS\UI\URLBuilderToken(['language_folder'], 'action');
        $id_token = new \ILIAS\UI\URLBuilderToken(['language_folder'], 'id');
        $values = [];
        if ($action !== null) {
            $values[$action_token->getName()] = $action;
        }
        if ($ids !== 'absent') {
            $values[$id_token->getName()] = $ids;
        }
        $request_wrapper = $this->createStub(\ILIAS\HTTP\Wrapper\RequestWrapper::class);
        $request_wrapper->method('has')->willReturnCallback(static fn(string $key): bool => array_key_exists($key, $values));
        $request_wrapper->method('retrieve')->willReturnCallback(
            static fn(string $key, \ILIAS\Refinery\Transformation $trafo): mixed => $trafo->transform($values[$key])
        );
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $language = $this->createStub(\ILIAS\Language\Language::class);

        foreach ([
            'action_token' => $action_token,
            'id_token' => $id_token,
            'request_wrapper' => $request_wrapper,
            'refinery' => new \ILIAS\Refinery\Factory(new \ILIAS\Data\Factory(), $language),
            'http' => $this->stubHttpWithMethod($http_method),
            'ui_renderer' => $this->createStub(\ILIAS\UI\Renderer::class),
            'lng' => $lng,
        ] as $name => $value) {
            (new ReflectionProperty(ilObjLanguageFolderGUI::class, $name))->setValue($gui, $value);
        }

        return $gui;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('installActions')]
    public function testInstallTableActionsOnlyOpenTheConfirmationForTheConfirmedActionAndExecuteNothing(
        string $action,
        string $confirmed_action,
        string $text
    ): void {
        // GET is fine here: the modal is fetched asynchronously, it changes nothing
        $gui = $this->createTableActionGui($action, 'GET', ['5', '6'], ['buildConfirmModal', 'installObject', 'viewObject']);
        $modal = $this->createStub(\ILIAS\UI\Component\Modal\Interruptive::class);
        $gui->expects($this->once())->method('buildConfirmModal')
            ->with(['5', '6'], $action, $confirmed_action, $text)
            ->willReturn($modal);
        $gui->expects($this->never())->method('installObject');
        $gui->expects($this->never())->method('viewObject');

        // the real code echoes the modal and exit()s, the exception stands in for it
        $renderer = $this->createMock(\ILIAS\UI\Renderer::class);
        $renderer->expects($this->once())->method('renderAsync')->with([$modal])
            ->willThrowException(new \LogicException('rendered'));
        $this->inject($gui, 'ui_renderer', $renderer);

        $this->expectExceptionMessage('rendered');
        $this->invokeTableAction($gui);
    }

    /** @return array<string, array{string, string, string}> */
    public static function installActions(): array
    {
        return [
            'install' => ['install', 'installConfirmed', 'lang_install_confirm_selected'],
            'install_local' => ['install_local', 'installLocalConfirmed', 'lang_install_local_confirm_selected'],
        ];
    }

    /** @param list<mixed> $expected_args */
    #[\PHPUnit\Framework\Attributes\DataProvider('confirmedTableActions')]
    public function testConfirmedTableActionsRunOnlyByPostAndOtherwiseOnlyTheListIsShown(
        string $action,
        string $expected_method,
        array $expected_args,
        string $http_method,
        bool $must_run
    ): void {
        $all = ['confirmRefreshObject', 'refreshSelectedObject', 'uninstallObject', 'uninstallChangesObject', 'installObject', 'viewObject'];
        $gui = $this->createTableActionGui($action, $http_method, ['5'], $all);
        $gui->method('confirmRefreshObject')->willReturn(['5']);

        foreach ($all as $method) {
            if ($method === 'viewObject') {
                $gui->expects($this->once())->method('viewObject');
            } elseif ($must_run && $method === $expected_method) {
                $gui->expects($this->once())->method($method)->with(...$expected_args);
            } elseif ($method !== 'confirmRefreshObject') {
                $gui->expects($this->never())->method($method);
            }
        }

        $this->invokeTableAction($gui);
    }

    /** @return array<string, array{string, string, list<mixed>, string, bool}> */
    public static function confirmedTableActions(): array
    {
        $cases = [];
        foreach ([
            'confirmRefresh' => ['refreshSelectedObject', [['5']]],
            'refreshConfirmed' => ['refreshSelectedObject', [['5']]],
            'uninstallConfirmed' => ['uninstallObject', [['5']]],
            'uninstallChanges' => ['uninstallChangesObject', [['5']]],
            'installConfirmed' => ['installObject', [['5'], InstallLanguage::MODE_INSTALL]],
            'installLocalConfirmed' => ['installObject', [['5'], InstallLanguage::MODE_INSTALL_LOCAL]],
        ] as $action => [$method, $args]) {
            $cases[$action . ' by POST'] = [$action, $method, $args, 'POST', true];
            $cases[$action . ' by GET'] = [$action, $method, $args, 'GET', false];
            $cases[$action . ' by HEAD'] = [$action, $method, $args, 'HEAD', false];
        }

        return $cases;
    }

    /** @param list<string> $expected */
    #[\PHPUnit\Framework\Attributes\DataProvider('idsFromQueryToken')]
    public function testGetIdsFromQueryTokenNormalisesListScalarAndNonsenseToAListOfStrings(mixed $request_value, array $expected): void
    {
        $gui = $this->createTableActionGui(null, 'POST', $request_value, []);

        $this->assertSame($expected, $this->invokeProtectedMethod($gui, 'getIdsFromQueryToken'));
    }

    /** @return array<string, array{mixed, list<string>}> */
    public static function idsFromQueryToken(): array
    {
        return [
            'list' => [['5', '6'], ['5', '6']],
            'scalar becomes a one element list' => ['5', ['5']],
            'ALL_OBJECTS marker is kept' => [['ALL_OBJECTS', '7'], ['ALL_OBJECTS', '7']],
            'nested array is nonsense' => [[['x']], []],
            'null is nonsense' => [null, []],
            'parameter missing' => ['absent', []],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('detectionSwitchCommands')]
    public function testSetLanguageDetectionEnabledObjectKeepsTheSuccessMessageAcrossTheRedirectAndDoesNotRenderTheList(
        string $method,
        bool $enabled
    ): void {
        $activity = $this->createMock(SetLanguageDetectionEnabled::class);
        $activity->expects($this->once())->method('maybePerformAs')->with($this->anything(), 6, ['enabled' => $enabled])
            ->willReturn(new ResultOk(null));

        // persistent (true): the message has to survive the redirect of the POST-redirect-GET
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'saved_successfully', true);
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageFolderGUI::class), 'view');

        $gui = $this->createGuiWithSetLanguageDetectionEnabledCollaborators(
            $activity,
            $tpl,
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $this->invokeProtectedMethod($gui, $method);
    }

    /** @return array<string, array{string, bool}> */
    public static function detectionSwitchCommands(): array
    {
        return ['enable' => ['enableLanguageDetectionObject', true], 'disable' => ['disableLanguageDetectionObject', false]];
    }

    private function invokeTableAction(ilObjLanguageFolderGUI $gui): void
    {
        (new ReflectionMethod(ilObjLanguageFolderGUI::class, 'executeTableActionObject'))->invoke($gui);
    }

    /** The code scan of ilLangDeprecated runs over ILIAS_ABSOLUTE_PATH: an empty directory in an own process keeps the constant out of other tests. */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testListDeprecatedObjectShowsOneEscapedModuleAndIdentifierLinePerEntry(): void
    {
        $dir = sys_get_temp_dir() . '/lng_deprecated_' . bin2hex(random_bytes(6));
        mkdir($dir);
        define('ILIAS_ABSOLUTE_PATH', $dir);
        try {
            $rows = [
                ['module' => 'common', 'identifier' => 'plain_key'],
                ['module' => '<b>mod</b>', 'identifier' => '"><img src=x onerror=alert(1)>&'],
            ];
            $log = $this->createStub(ilDBStatement::class);
            $data = $this->createStub(ilDBStatement::class);
            $queues = [spl_object_id($log) => [], spl_object_id($data) => $rows];
            $db = $this->createStub(ilDBInterface::class);
            $db->method('query')->willReturnCallback(
                static fn(string $sql): ilDBStatement => str_contains($sql, 'lng_log') ? $log : $data
            );
            $db->method('quote')->willReturnArgument(0);
            $db->method('fetchAssoc')->willReturnCallback(
                static function (ilDBStatement $set) use (&$queues): ?array {
                    return array_shift($queues[spl_object_id($set)]);
                }
            );
            $dic = $this->init();
            $dic['ilDB'] = $db;
            $GLOBALS['DIC'] = $dic;

            $ctrl = $this->createStub(ilCtrl::class);
            $ctrl->method('getLinkTarget')->willReturn('link');
            $toolbar = $this->createMock(ilToolbarGUI::class);
            $toolbar->expects($this->once())->method('addComponent');
            $contents = [];
            $tpl = $this->createStub(ilGlobalTemplateInterface::class);
            $tpl->method('setContent')->willReturnCallback(static function (string $c) use (&$contents): void {
                $contents[] = $c;
            });

            $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
            foreach ([
                'lng' => $this->createLanguageMockReturningTopicAsIs(),
                'ctrl' => $ctrl,
                'toolbar' => $toolbar,
                'tpl' => $tpl,
                'ui_factory' => $this->factory(),
                'ui_renderer' => $this->renderer(),
            ] as $name => $value) {
                (new ReflectionProperty(ilObjLanguageFolderGUI::class, $name))->setValue($gui, $value);
            }

            $gui->listDeprecatedObject();

            $this->assertCount(1, $contents);
            $html = $contents[0];
            $this->assertStringContainsString('common,plain_key', $html);
            $this->assertStringContainsString('&lt;b&gt;mod&lt;/b&gt;,&quot;&gt;&lt;img src=x onerror=alert(1)&gt;&amp;', $html);
            $this->assertStringNotContainsString('<img', $html);
            $this->assertStringNotContainsString('<b>', $html);
            $this->assertStringNotContainsString('&amp;amp;', $html, 'escaped once only');
        } finally {
            rmdir($dir);
        }
    }
}
