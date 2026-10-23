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

use ILIAS\FileUpload\FileUpload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * uploadObject(): an error of the upload itself is shown (escaped, code 400), anything else only as the generic
 * "action_aborted" - it may carry SQL - and goes to the log.
 */
class ilObjLanguageExtGUIUploadTest extends TestCase
{
    use ilObjLanguageExtGUITestFixture;

    /**
     * @param list<array{0: string, 1: string}> $messages
     */
    private function uploadWith(FileUpload $upload, array &$messages, string $not_found_text = 'not found'): void
    {
        $form = $this->createStub(ilPropertyFormGUI::class);
        $form->method('checkInput')->willReturn(true);
        $tpl = $this->createStub(ilGlobalTemplateInterface::class);
        $tpl->method('setOnScreenMessage')->willReturnCallback(
            static function (string $type, string $message) use (&$messages): void {
                $messages[] = [$type, $message];
            }
        );
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageExtGUI::class), 'import')
            // the real redirect() ends the request
            ->willThrowException(new LogicException('redirected'));
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(static fn(string $topic): string => $topic === 'upload_error_file_not_found' ? $not_found_text : $topic);

        $dic = $this->createStub(\ILIAS\DI\Container::class);
        $dic->method('upload')->willReturn($upload);
        $dic->method('language')->willReturn($lng);
        $GLOBALS['DIC'] = $dic;

        /** @var ilObjLanguageExtGUI&MockObject $gui */
        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['initNewImportForm'])
            ->getMock();
        $gui->expects($this->once())->method('initNewImportForm')->willReturn($form);
        $this->inject($gui, 'http', $this->mockHttpWithParsedBody(['mode_existing' => 'replace']));
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());

        $session_before = $_SESSION ?? null;
        try {
            $gui->uploadObject();
        } catch (LogicException $e) {
            $this->assertSame('redirected', $e->getMessage());
        } finally {
            if ($session_before === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $session_before;
            }
        }
    }

    public function testAnErrorThatMayCarrySqlIsLoggedAndOnlyTheGenericMessageIsShown(): void
    {
        $this->stubLoggerLangError('SELECT secret FROM lng_data failed', \RuntimeException::class);
        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(false);
        $upload->method('process')->willThrowException(new \RuntimeException('SELECT secret FROM lng_data failed'));
        $messages = [];

        $this->uploadWith($upload, $messages);

        $this->assertSame([['failure', 'action_aborted']], $messages);
    }

    public function testAnUploadErrorWithCode400IsShownEscaped(): void
    {
        $upload = $this->createStub(FileUpload::class);
        $upload->method('hasBeenProcessed')->willReturn(true);
        $upload->method('hasUploads')->willReturn(false);
        $messages = [];

        $this->uploadWith($upload, $messages, '<b>not found</b>');

        $this->assertSame([['failure', '&lt;b&gt;not found&lt;/b&gt;']], $messages);
    }
}
