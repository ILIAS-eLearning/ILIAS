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
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUISaveSettingsTest extends TestCase
{
    use ilObjLanguageExtGUITestFixture;

    /** @return ilObjLanguageExtGUI&MockObject */
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

        $form = $this->createStub(ilPropertyFormGUI::class);
        $form->method('getHTML')->willReturn('');
        $gui->method('initNewSettingsForm')->willReturn($form);

        $this->inject($gui, 'http', $http);
        $this->inject($gui, 'user', $user);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'lng', $lng ?? $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'object', $this->createFakeLanguageObject('de'));
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->inject(
            $gui,
            'set_language_translation_enabled',
            $set_language_translation_enabled
        );
        $this->inject(
            $gui,
            'activity_error_logger',
            $this->activityErrorLoggerForGui()
        );

        return $gui;
    }

    /** A non-empty "translation" POST value means the checkbox was submitted; the parameters come from $this->object->key. */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectBuildsEnabledTrueFromANonEmptyTranslationPostValue(): void
    {
        $user = $this->createStub(ilObjUser::class);
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
            $this->createStub(ilGlobalTemplateInterface::class)
        );

        $gui->saveSettingsObject();
    }

    /** An unchecked checkbox is not submitted at all: a missing "translation" key builds enabled=false. */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectBuildsEnabledFalseWhenTranslationPostKeyIsMissing(): void
    {
        $user = $this->createStub(ilObjUser::class);
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
            $this->createStub(ilGlobalTemplateInterface::class)
        );

        $gui->saveSettingsObject();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectShowsSuccessMessageWhenResultReportsChangedTrue(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createStub(SetLanguageTranslationEnabled::class);
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

    /** Shown only if the value actually changed (see SetLanguageTranslationEnabled::getOutputDescription()). */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectShowsNoMessageWhenResultReportsChangedFalse(): void
    {
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createStub(SetLanguageTranslationEnabled::class);
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

    /** Unlike the folder GUI's write commands saveSettingsObject() never redirects; a \Throwable still goes through activityErrorMessage(). */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectShowsGenericFailureMessageAndNeverRedirectsOnThrowableError(): void
    {
        $this->stubLoggerLangError('boom');

        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $set_language_translation_enabled = $this->createStub(SetLanguageTranslationEnabled::class);
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

        // no $ctrl is injected: a redirect would fatally error instead of passing silently
        $gui->saveSettingsObject();
    }
}
