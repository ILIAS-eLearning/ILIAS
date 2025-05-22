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
 * Class BlacklistExtensionPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class BlacklistExtensionPreProcessorTest extends TestCase
{
    #[Test]

    public function testProcessWhichShouldSucceed(): void
    {
        $extensions = ['jpg', 'svg'];
        $filename = 'hello.ogg';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'audio/ogg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Extension is not blacklisted.', $result->getMessage());
    }

    #[Test]

    public function testProcessWithBlacklistedEmptyExtensionWhichShouldGetRejected(): void
    {
        $extensions = ['jpg', ''];
        $filename = 'hello';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'text/plain'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Extension is blacklisted. (hello)', $result->getMessage());
    }

    #[Test]

    public function testProcessWithBlacklistedExtensionWhichShouldGetRejected(): void
    {
        $extensions = ['jpg', 'exe'];
        $filename = 'hello.jpg';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Extension is blacklisted. (hello.jpg)', $result->getMessage());
    }
}
