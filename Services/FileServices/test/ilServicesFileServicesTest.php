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

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Manager\Manager;

/**
 * @runTestsInSeparateProcesses // this is necessary to avoid side effects with the DIC
 * @preserveGlobalState disabled
 */
class ilServicesFileServicesTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    /**
     * @var ilDBInterface|(ilDBInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private ?ilDBInterface $db_mock = null;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : null;

        $DIC = new \ILIAS\DI\Container();
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testSanitizing(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'jpg']);

        $sanitizer = new ilFileServicesFilenameSanitizer($settings);
        $this->assertTrue($sanitizer->isClean('/lib/test.pdf'));
        $this->assertFalse($sanitizer->isClean('/lib/test.xml'));
        $this->assertEquals('/lib/testxml.sec', $sanitizer->sanitize('/lib/test.xml'));
    }

    public function testBlacklistedUpload(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);

        $settings->expects($this->once())
                 ->method('isByPassAllowedForCurrentUser')
                 ->willReturn(false);

        $stream = $this->createMock(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');

        $processor = new ilFileServicesPreProcessor(
            $settings,
            'the reason'
        );
        // is ok since user has permission
        $status = $processor->process($stream, $meta);
        $this->assertEquals(ProcessingStatus::REJECTED, $status->getCode());
    }

    public function testBlacklistedUploadWithPermission(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);

        $settings->expects($this->once())
                 ->method('isByPassAllowedForCurrentUser')
                 ->willReturn(true);

        $stream = $this->createMock(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');

        $processor = new ilFileServicesPreProcessor(
            $settings,
            'the reason'
        );
        // is ok since user has permission
        $status = $processor->process($stream, $meta);
        $this->assertEquals(ProcessingStatus::OK, $status->getCode());
    }

    public function testRenamingNonWhitelistedFile(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'png', 'jpg']);

        $sanitizer = new ilFileServicesFilenameSanitizer($settings);

        $sane_filename = 'bellerophon.pdf';
        $this->assertEquals($sane_filename, $sanitizer->sanitize($sane_filename));

        $insane_filename = 'bellerophon.docx';
        $this->assertNotEquals($insane_filename, $sanitizer->sanitize($insane_filename));
        $this->assertEquals('bellerophondocx.sec', $sanitizer->sanitize($insane_filename));
    }

    public function testActualWhitelist(): void
    {
        $settings_mock = $this->createMock(ilSetting::class);
        $ini_mock = $this->createMock(ilIniFile::class);

        $ref = new stdClass();
        $ref->ref_id = 32;
        $this->db_mock->expects($this->once())
                ->method('fetchObject')
                ->willReturn($ref);

        $this->db_mock->expects($this->once())
                ->method('fetchAssoc')
                ->willReturn([]);

        $default_whitelist = include __DIR__ . "/../../../Services/FileServices/defaults/default_whitelist.php";

        // Blacklist
        $settings_mock->expects($this->exactly(3))
                      ->method('get')
                      ->withConsecutive(
                          ['suffix_custom_expl_black'],
                          ['suffix_repl_additional'],
                          ['suffix_custom_white_list']
                      )
                      ->willReturnOnConsecutiveCalls(
                          'bl001,bl002', // blacklisted
                          'docx,doc', // remove from whitelist
                          'wl001,wl002' // add whitelist
                      );

        $settings = new ilFileServicesSettings($settings_mock, $ini_mock, $this->db_mock);
        $this->assertEquals(['bl001', 'bl002'], $settings->getBlackListedSuffixes());
        $this->assertEquals(['bl001', 'bl002'], $settings->getProhibited());
        $this->assertEquals($default_whitelist, $settings->getDefaultWhitelist());
        $this->assertEquals(['docx', 'doc'], $settings->getWhiteListNegative());
        $this->assertEquals(['wl001', 'wl002'], $settings->getWhiteListPositive());

        $whitelist = array_merge(
            array_diff($default_whitelist, ['docx', 'doc']),
            ['wl001', 'wl002', '']
        );
        $diff = array_diff($whitelist, $settings->getWhiteListedSuffixes());

        $this->assertEquals([], $diff);
        $this->assertEquals(0, count($diff));
    }



    public function testFileNamePolicyOnDownloading(): void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);

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
        $this->assertEquals('testmp3.sec', $policy->prepareFileNameForConsumer('test.mp3'));
        $this->assertEquals('test.png', $policy->prepareFileNameForConsumer('test.png'));
        $this->assertEquals('test.pdf', $policy->prepareFileNameForConsumer('test.pdf'));
        $this->assertEquals('aeaeaeaeaeaeaeaeae.pdf', $policy->prepareFileNameForConsumer('äääääääää.pdf'));
        $this->assertEquals('oeoeoeoeoeoeoeoeoe.pdf', $policy->prepareFileNameForConsumer('ööööööööö.pdf'));
        $this->assertEquals('ueueueueueueueueue.pdf', $policy->prepareFileNameForConsumer('üüüüüüüüü.pdf'));
    }
}
