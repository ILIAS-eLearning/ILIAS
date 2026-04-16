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

use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\Filesystem\Security\Sanitizing\DefaultFilenameSanitizer;
use ILIAS\Filesystem\Configuration\FilesystemConfig;

#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
final class ilServicesFileServicesTest extends TestCase
{
    private ?Container $dic_backup;
    /**
     * @var ilDBInterface|ilDBInterface&MockObject|MockObject
     */
    private ?ilDBInterface $db_mock = null;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : null;

        $DIC = new Container();
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testSanitizing(): void
    {
        $settings = $this->createMock(FilesystemConfig::class);
        $settings->expects($this->once())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'jpg']);

        $sanitizer = new DefaultFilenameSanitizer($settings);
        $this->assertTrue($sanitizer->isClean('/lib/test.pdf'));
        $this->assertFalse($sanitizer->isClean('/lib/test.xml'));
        $this->assertSame('/lib/testxml.sec', $sanitizer->sanitize('/lib/test.xml'));
    }

    public function testBlacklistedUpload(): void
    {
        $settings = $this->createMock(FilesystemConfig::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);

        $settings->expects($this->once())
                 ->method('isByPassAllowedForCurrentUser')
                 ->willReturn(false);

        $stream = $this->createStub(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');

        $processor = new ilFileServicesPreProcessor(
            $settings,
            'the reason'
        );
        // is ok since user has permission
        $status = $processor->process($stream, $meta);
        $this->assertSame(ProcessingStatus::REJECTED, $status->getCode());
    }

    public function testBlacklistedUploadWithPermission(): void
    {
        $settings = $this->createMock(FilesystemConfig::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);

        $settings->expects($this->once())
                 ->method('isByPassAllowedForCurrentUser')
                 ->willReturn(true);

        $stream = $this->createStub(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');

        $processor = new ilFileServicesPreProcessor(
            $settings,
            'the reason'
        );
        // is ok since user has permission
        $status = $processor->process($stream, $meta);
        $this->assertSame(ProcessingStatus::OK, $status->getCode());
    }

    public function testRenamingNonWhitelistedFile(): void
    {
        $settings = $this->createMock(FilesystemConfig::class);
        $settings->expects($this->once())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'png', 'jpg']);

        $sanitizer = new DefaultFilenameSanitizer($settings);

        $sane_filename = 'bellerophon.pdf';
        $this->assertSame($sane_filename, $sanitizer->sanitize($sane_filename));

        $insane_filename = 'bellerophon.docx';
        $this->assertNotSame($insane_filename, $sanitizer->sanitize($insane_filename));
        $this->assertSame('bellerophondocx.sec', $sanitizer->sanitize($insane_filename));
    }

    public function testActualWhitelist(): void
    {
        $this->markTestSkipped('Due to the component revision we must rewrite this test completely'); // TODO implement again
        $settings_mock = $this->createMock(ilSetting::class);
        $ini_mock = $this->createStub(ilIniFile::class);

        $ref = new stdClass();
        $ref->ref_id = 32;
        $this->db_mock->expects($this->once())
                ->method('fetchObject')
                ->willReturn($ref);

        $this->db_mock->expects($this->once())
                ->method('fetchAssoc')
                ->willReturn([]);

        $default_whitelist = include __DIR__ . "/../defaults/default_whitelist.php";

        // Blacklist
        $consecutive = [
            ['suffix_custom_expl_black', 'bl001,bl002'], // blacklisted
            ['suffix_repl_additional', 'docx,doc'], // remove from whitelist
            ['suffix_custom_white_list', 'wl001,wl002'] // add whitelist
        ];
        $settings_mock
            ->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(
                function (string $k) use (&$consecutive) {
                    [$expected, $return] = array_shift($consecutive);
                    $this->assertEquals($expected, $k);
                    return $return;
                }
            );

        $settings = new ilFileServicesSettings($settings_mock, $ini_mock, $this->db_mock);
        $this->assertSame(['bl001', 'bl002'], $settings->getBlackListedSuffixes());
        $this->assertSame(['bl001', 'bl002'], $settings->getProhibited());
        $this->assertEquals($default_whitelist, $settings->getDefaultWhitelist());
        $this->assertSame(['docx', 'doc'], $settings->getWhiteListNegative());
        $this->assertSame(['wl001', 'wl002'], $settings->getWhiteListPositive());

        $whitelist = array_merge(
            array_diff($default_whitelist, ['docx', 'doc']),
            ['wl001', 'wl002', '']
        );
        $diff = array_diff($whitelist, $settings->getWhiteListedSuffixes());

        $this->assertSame([], $diff);
        $this->assertCount(0, $diff);
    }



    public function testFileNamePolicyOnDownloading(): void
    {
        $settings = $this->createMock(FilesystemConfig::class);

        $settings->expects($this->atLeastOnce())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['mp3']);

        $settings->expects($this->atLeastOnce())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'png', 'mp3']);

        $settings->expects($this->atLeastOnce())
                 ->method('isASCIIConvertionEnabled')
                 ->willReturn(true);

        $policy = new ilFileServicesPolicy($settings);
        $this->assertSame('testmp3.sec', $policy->prepareFileNameForConsumer('test.mp3'));
        $this->assertSame('test.png', $policy->prepareFileNameForConsumer('test.png'));
        $this->assertSame('test.pdf', $policy->prepareFileNameForConsumer('test.pdf'));
        $this->assertSame('aeaeaeaeaeaeaeaeae.pdf', $policy->prepareFileNameForConsumer('äääääääää.pdf'));
        $this->assertSame('oeoeoeoeoeoeoeoeoe.pdf', $policy->prepareFileNameForConsumer('ööööööööö.pdf'));
        $this->assertSame('ueueueueueueueueue.pdf', $policy->prepareFileNameForConsumer('üüüüüüüüü.pdf'));
    }

    /**
     * Control characters must not survive, they are invisible in the title but break
     * paths and HTTP headers built from it, see https://mantis.ilias.de/view.php?id=30709
     */
    public function testFileNamePolicyRemovesControlCharacters(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);

        $settings->expects($this->atLeastOnce())
                 ->method('getBlackListedSuffixes')
                 ->willReturn([]);

        $settings->expects($this->atLeastOnce())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf']);

        $policy = new ilFileServicesPolicy($settings);

        $this->assertSame(
            'KW 49 _ SW 5 - Halbschnitt __Vollschnitt',
            $policy->ascii('KW 49 / SW 5 - Halbschnitt /' . chr(0x0b) . 'Vollschnitt')
        );
        $this->assertSame('2._Shipflow Einstieg', $policy->ascii("2.\tShipflow Einstieg"));
        $this->assertSame('report__.pdf', $policy->ascii("report\r\n.pdf"));
    }
}
