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

use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use ILIAS\Language\Activities\AddLanguageEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUISaveNewEntryTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageExtGUITestFixture;

    /** @return ilObjLanguageExtGUI&MockObject */
    private function createGuiForSaveNewEntry(
        AddLanguageEntry $add_language_entry,
        ilCtrl $ctrl,
        ilObjUser $user,
        ilGlobalTemplateInterface $tpl,
        \ILIAS\UI\Component\Input\Container\Form\Standard $form,
        ilLanguage $lng
    ): ilObjLanguageExtGUI {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildAddNewEntryForm', 'getNewEntryIdentifier', 'addNewEntryObject'])
            ->getMock();

        $gui->method('getNewEntryIdentifier')->willReturn('sometopic');
        $gui->method('buildAddNewEntryForm')->willReturn($form);
        $http = $this->createStub(\ILIAS\HTTP\GlobalHttpState::class);
        $http->method('request')->willReturn($this->createStub(\Psr\Http\Message\ServerRequestInterface::class));
        $this->inject($gui, 'http', $http);

        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'user', $user);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->inject($gui, 'add_language_entry', $add_language_entry);
        $this->inject(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    /** A KS form whose request data are $inputs; the identifier comes from "eid", not from the form. */
    private function mockCheckedFormWithInputs(array $inputs): \ILIAS\UI\Component\Input\Container\Form\Standard&MockObject
    {
        $form = $this->createMock(\ILIAS\UI\Component\Input\Container\Form\Standard::class);
        $form->method('withRequest')->willReturnSelf();
        $form->method('getData')->willReturn($inputs);

        return $form;
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectBuildsModuleIdentifierAndTranslationsFromFormAcrossAllInstalledLanguages(): void
    {
        $user = $this->createStub(ilObjUser::class);
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
            $this->createStub(ilCtrl::class),
            $user,
            $this->createStub(ilGlobalTemplateInterface::class),
            $form,
            $this->createLanguageMockReturningTopicAsIs(['de', 'en'])
        );

        $gui->saveNewEntryObject();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectShowsSuccessMessageAndRedirectsToViewOnSuccess(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createStub(AddLanguageEntry::class);
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

    /** A \Throwable shows the generic logged message (activityErrorMessage()) and redirects to "view". */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectShowsGenericFailureMessageAndRedirectsToViewOnThrowableError(): void
    {
        $this->stubLoggerLangError('boom');

        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createStub(AddLanguageEntry::class);
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

        // a dropped `return` after the redirect would fall through to the success path and trip the once() expectations
        $gui->saveNewEntryObject();
    }

    /** An input error (4xx) must not redirect: the form is re-shown with the entered values. */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectDoesNotRedirectAndReshowsTheFormWithPostedValuesOnAnInputError(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $add_language_entry = $this->createStub(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(
            new ResultError(new \ILIAS\Language\Activities\InvalidInputException('A value is required for: en.'))
        );

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'A value is required for: en.'
        );

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->never())->method('redirect');

        $form = $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']);

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $ctrl,
            $user,
            $tpl,
            $form,
            $this->createLanguageMockReturningTopicAsIs(['de', 'en'])
        );
        // the re-render gets the form that carries the entered values
        $gui->expects($this->once())->method('addNewEntryObject')->with($form);

        $gui->saveNewEntryObject();
    }

    /** If the PO/MO overlay of a PO module cannot be written for some languages, a failure naming them replaces "settings_saved". */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectShowsAFailureInsteadOfSuccessWhenAnOverlayWasNotWritten(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $add_language_entry = $this->createStub(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(new ResultOk([
            'added_language_keys' => ['de', 'en'],
            'overlay_write_failed_language_keys' => ['de', 'en'],
        ]));
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with(
            'failure',
            'lng_po_overlay_not_written_languages: de, en',
            true
        );
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageExtGUI::class), 'view');

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $ctrl,
            $user,
            $tpl,
            $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']),
            $this->createLanguageMockWithFormatStrings(['de', 'en'])
        );

        $gui->saveNewEntryObject();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveNewEntryObjectShowsSuccessWhenTheOverlayListIsEmpty(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);
        $add_language_entry = $this->createStub(AddLanguageEntry::class);
        $add_language_entry->method('maybePerformAs')->willReturn(new ResultOk([
            'added_language_keys' => ['de'],
            'overlay_write_failed_language_keys' => [],
        ]));
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('success', 'settings_saved', true);

        $gui = $this->createGuiForSaveNewEntry(
            $add_language_entry,
            $this->createStub(ilCtrl::class),
            $user,
            $tpl,
            $this->mockCheckedFormWithInputs(['mod' => 'common', 'id' => 'sometopic']),
            $this->createLanguageMockWithFormatStrings(['de'])
        );

        $gui->saveNewEntryObject();
    }
}
