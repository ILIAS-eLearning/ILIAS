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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistFileHeaderPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class WhitelistFileHeaderPreProcessorTest extends TestCase
{
    #[Test]

    public function testProcessWhichShouldSucceed(): void
    {
        $fileHeaderStart = hex2bin('FFD8FF'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new WhitelistFileHeaderPreProcessor($fileHeaderStart);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('File header complies with whitelist.', $result->getMessage());
    }

    #[Test]

    public function testProcessWithHeaderMismatchWhichShouldGetRejected(): void
    {
        $fileHeaderWhitelist = hex2bin('FFD8FF'); //jpg header start
        $fileHeaderStart = hex2bin('FFD8FB'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new WhitelistFileHeaderPreProcessor($fileHeaderWhitelist);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('File header don\'t complies with whitelist.', $result->getMessage());
    }
}
