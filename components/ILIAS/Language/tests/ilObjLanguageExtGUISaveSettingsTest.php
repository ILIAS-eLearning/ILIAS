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
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUISaveSettingsTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageExtGUITestFixture;

    /** @return ilObjLanguageExtGUI&MockObject */
    private function createGuiForSaveSettings(
        SetLanguageTranslationEnabled $set_language_translation_enabled,
        \ILIAS\HTTP\GlobalHttpState $http,
        ilObjUser $user,
        ilGlobalTemplateInterface $tpl,
        ?ilLanguage $lng = null,
        ?\ILIAS\UI\Component\Input\Container\Form\Standard $form = null
    ): ilObjLanguageExtGUI {
        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildSettingsForm', 'settingsObject'])
            ->getMock();

        if ($form === null) {
            $post = $http->request()->getParsedBody();
            $form = $this->createStub(\ILIAS\UI\Component\Input\Container\Form\Standard::class);
            $form->method('withRequest')->willReturnSelf();
            $form->method('getData')->willReturn(['translation' => ($post['translation'] ?? '') === 'checked']);
        }
        $gui->method('buildSettingsForm')->willReturn($form);
        $renderer = $this->createStub(\ILIAS\UI\Renderer::class);
        $renderer->method('render')->willReturn('');
        $this->inject($gui, 'ui_renderer', $renderer);

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

    /** A checked "translation" checkbox enables the translation; the parameters come from $this->object->key. */
    #[AllowMockObjectsWithoutExpectations]
    public function testSaveSettingsObjectBuildsEnabledTrueFromACheckedTranslationPostValue(): void
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
            $this->mockHttpWithParsedBody(['translation' => 'checked']),
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
            $this->mockHttpWithParsedBody(['translation' => 'checked']),
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
            $this->mockHttpWithParsedBody(['translation' => 'checked']),
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
            $this->mockHttpWithParsedBody(['translation' => 'checked']),
            $user,
            $tpl,
            $this->createLanguageMockReturningTopicAsIs()
        );

        // no $ctrl is injected: a redirect would fatally error instead of passing silently
        $gui->saveSettingsObject();
    }

    /** A rejected request (e.g. a TypeError of a KS input) is logged and never switches the setting. */
    public function testSaveSettingsObjectNeverCallsTheActivityForARejectedRequest(): void
    {
        $activity = $this->createMock(SetLanguageTranslationEnabled::class);
        $activity->expects($this->never())->method('maybePerformAs');
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', 'form_input_not_valid');
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('warning');
        $this->stubbed_activity_error_logger = $logger;

        $form = $this->createStub(\ILIAS\UI\Component\Input\Container\Form\Standard::class);
        $form->method('withRequest')->willThrowException(new TypeError('array given'));
        $gui = $this->createGuiForSaveSettings(
            $activity,
            $this->mockHttpWithParsedBody(['translation' => ['checked']]),
            $this->createStub(ilObjUser::class),
            $tpl,
            null,
            $form
        );
        $gui->expects($this->once())->method('settingsObject');

        $gui->saveSettingsObject();
    }
}
