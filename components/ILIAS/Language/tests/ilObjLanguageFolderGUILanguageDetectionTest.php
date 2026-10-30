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
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ilObjLanguageFolderGUILanguageDetectionTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageFolderGUITestFixture;

    /**
     * GUI as executeCommand() needs it: only checkPermission() and prepareOutput() are replaced.
     *
     * @return ilObjLanguageFolderGUI&MockObject
     */
    private function createGuiForCommand(
        string $command,
        SetLanguageDetectionEnabled $set_language_detection_enabled,
        ilGlobalTemplateInterface $tpl,
        ilCtrl&Stub $ctrl
    ): ilObjLanguageFolderGUI {
        $ctrl->method('getNextClass')->willReturn('');
        $ctrl->method('getCmd')->willReturn($command);

        $request_wrapper = $this->createStub(\ILIAS\HTTP\Wrapper\RequestWrapper::class);
        $request_wrapper->method('has')->willReturn(false);
        $action_token = $this->createStub(\ILIAS\UI\URLBuilderToken::class);
        $action_token->method('getName')->willReturn('table_action');

        /** @var ilObjLanguageFolderGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageFolderGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkPermission', 'prepareOutput'])
            ->getMock();

        $this->inject($gui, 'set_language_detection_enabled', $set_language_detection_enabled);
        $this->inject($gui, 'current_user_id', 6);
        $this->inject($gui, 'ref_id', 17);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $this->createLanguageMockReturningTopicAsIs());
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->inject($gui, 'tabs_gui', $this->createStub(ilTabsGUI::class));
        $this->inject($gui, 'request_wrapper', $request_wrapper);
        $this->inject($gui, 'action_token', $action_token);

        return $gui;
    }

    /** @return array<string, array{string}> */
    public static function commandNameProvider(): array
    {
        return ['enable' => ['enableLanguageDetection'], 'disable' => ['disableLanguageDetection']];
    }

    /** @return array<string, array{string, bool}> command => expected "enabled" flag */
    public static function commandProvider(): array
    {
        return [
            'enable' => ['enableLanguageDetection', true],
            'disable' => ['disableLanguageDetection', false],
        ];
    }

    #[DataProvider('commandProvider')]
    public function testTheCommandPassesTheMatchingEnabledFlagAndTheCurrentUserToTheActivity(string $command, bool $enabled): void
    {
        $this->stubLoggerLangError('boom');
        $activity = $this->createMock(SetLanguageDetectionEnabled::class);
        $activity->expects($this->once())
            ->method('maybePerformAs')
            ->with($this->anything(), 6, ['enabled' => $enabled])
            ->willReturn(new ResultError(new \RuntimeException('boom')));

        $gui = $this->createGuiForCommand(
            $command,
            $activity,
            $this->createStub(ilGlobalTemplateInterface::class),
            $this->createStub(ilCtrl::class)
        );

        $gui->executeCommand();
    }

    public function testAnActivityErrorShowsTheGenericFailureAndRedirectsToTheViewWithoutTheSuccessPath(): void
    {
        $exception_message = 'no write permission';
        $this->stubLoggerLangError($exception_message);
        $activity = $this->createStub(SetLanguageDetectionEnabled::class);
        $activity->method('maybePerformAs')->willReturn(new ResultError(new \RuntimeException($exception_message)));

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setOnScreenMessage')->with('failure', 'action_aborted', true);
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageFolderGUI::class), 'view');

        // without the `return` after the redirect viewObject() would fatal here
        $this->createGuiForCommand('enableLanguageDetection', $activity, $tpl, $ctrl)->executeCommand();
    }

    /** Only the generic "read" gate runs; the write permission is enforced solely by the Activity's isAllowedToPerform(). */
    #[DataProvider('commandNameProvider')]
    public function testTheCommandChecksOnlyReadPermissionInTheGui(string $command): void
    {
        $activity = $this->createStub(SetLanguageDetectionEnabled::class);
        $activity->method('maybePerformAs')->willReturn(new ResultError(new \DomainException('no write permission', 403)));

        $gui = $this->createGuiForCommand(
            $command,
            $activity,
            $this->createStub(ilGlobalTemplateInterface::class),
            $this->createStub(ilCtrl::class)
        );
        $gui->expects($this->once())->method('checkPermission')->with('read');

        $gui->executeCommand();
    }
}
