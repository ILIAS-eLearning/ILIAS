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

namespace ILIAS\VirusScanner\tests;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;
use ilVirusScannerPreProcessor;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class VirusScannerPreProcessorTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testVirusDetected(): void
    {
        $stream = Streams::ofString('Awesome stuff');
        $mock = $this->getMockBuilder(\ilVirusScanner::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $mock->expects($this->once())->method('scanFile')->with($stream->getMetadata('uri'))->willReturn(
            'Virus found!!!'
        );

        $subject = new ilVirusScannerPreProcessor($mock);
        $result = $subject->process(
            $stream,
            new Metadata('MyVirus.exe', $stream->getSize(), 'application/vnd.microsoft.portable-executable')
        );
        $this->assertSame(ProcessingStatus::DENIED, $result->getCode());
        $this->assertSame('Virus detected.', $result->getMessage());
    }

    public function testNoVirusDetected(): void
    {
        $stream = Streams::ofString('Awesome stuff');

        $mock = $this->getMockBuilder(\ilVirusScanner::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $mock->expects($this->once())->method('scanFile')->with($stream->getMetadata('uri'))->willReturn('');

        $subject = new ilVirusScannerPreProcessor($mock);
        $result = $subject->process(
            $stream,
            new Metadata('MyVirus.exe', $stream->getSize(), 'application/vnd.microsoft.portable-executable')
        );
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
    }
}
