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

namespace ILIAS\FileUpload\Processor;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;

require_once('./vendor/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class BlacklistMimeTypePreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class BlacklistMimeTypePreProcessorTest extends TestCase
{
    #[Test]

    public function testProcessWithBlacklistedMimeTypeWhichShouldGetRejected(): void
    {
        $blacklist = ['text/html', 'audio/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.html', 4000, $blacklist[0]);

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    #[Test]

    public function testProcessWithBlacklistedAnyKindOfTextMimeTypeWhichGetRejected(): void
    {
        $blacklist = ['text/*', '*/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.html', 4000, 'text/html');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    #[Test]

    public function testProcessWithBlacklistedAnyKindOfOggMimeTypeWhichGetRejected(): void
    {
        $blacklist = ['text/html', '*/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.ogg', 4000, 'audio/ogg');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    #[Test]

    public function testCreateSubjectWithAnyKindOfMimeTypeWhichShouldFail(): void
    {
        $blacklist = ['audio/ogg', '*/*'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mime type */* matches all mime types which would black all files.');

        $subject = new BlacklistMimeTypePreProcessor($blacklist);
    }
}
