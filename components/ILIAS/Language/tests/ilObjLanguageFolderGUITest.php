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

use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Data\Result\Ok as ResultOk;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ilObjLanguageFolderGUITest extends TestCase
{
    use ilObjLanguageFolderGUITestFixture;

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
