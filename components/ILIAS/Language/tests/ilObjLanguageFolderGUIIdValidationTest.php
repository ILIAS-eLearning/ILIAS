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
use PHPUnit\Framework\TestCase;

class ilObjLanguageFolderGUIIdValidationTest extends TestCase
{
    use ilObjLanguageFolderGUITestFixture;

    /** One non-"lng" id aborts the whole request: failure message, redirect to "view", InstallLanguage never called. */
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

    /** With only "lng" ids the Activity is still called with the language keys resolved from the titles. */
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
            $this->createStub(ilGlobalTemplateInterface::class),
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->installObject(['5'], InstallLanguage::MODE_INSTALL);
    }

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
            $this->createStub(ilGlobalTemplateInterface::class),
            $ctrl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        $gui->uninstallObject(['5']);
    }

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
     * ui_factory stub for buildConfirmModal()'s abort branch; returns the Interruptive that chain produces, so a test
     * can assert it was returned unchanged.
     *
     * @return array{0: \ILIAS\UI\Factory&\PHPUnit\Framework\MockObject\Stub, 1: \ILIAS\UI\Implementation\Component\Modal\Interruptive&\PHPUnit\Framework\MockObject\Stub}
     */
    private function createStubModalUiFactory(): array
    {
        // buildConfirmModal() returns the concrete Interruptive class, so a mock of the interface would fail its return type
        $interruptive = $this->createStub(\ILIAS\UI\Implementation\Component\Modal\Interruptive::class);
        $interruptive->method('withActionButtonLabel')->willReturnSelf();

        $modal_factory = $this->createStub(\ILIAS\UI\Component\Modal\Factory::class);
        $modal_factory->method('interruptive')->willReturn($interruptive);

        $ui_factory = $this->createStub(\ILIAS\UI\Factory::class);
        $ui_factory->method('modal')->willReturn($modal_factory);

        return [$ui_factory, $interruptive];
    }

    public function testBuildConfirmModalAbortsWhenAnyIdIsNotALanguageObjectAndNeverLeaksData(): void
    {
        $this->stubObjDataCache([5 => 'cat'], [], true);

        /** @var ilObjLanguageFolderGUI $gui */
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageFolderGUI::class);

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

        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'ui_factory', $ui_factory);

        $result = (new ReflectionMethod($gui, 'buildConfirmModal'))->invoke(
            $gui,
            ['5'],
            'refresh_languages',
            'confirmRefresh',
            'lang_refresh_confirm_selected',
            'lang_refresh_confirm_info'
        );

        $this->assertSame($expected_interruptive, $result);
    }
}
