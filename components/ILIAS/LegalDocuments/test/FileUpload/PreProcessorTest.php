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

namespace ILIAS\LegalDocuments\test\FileUpload;

use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\FileUpload\PreProcessor;

require_once __DIR__ . '/../ContainerMock.php';

class PreProcessorTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PreProcessor::class, new PreProcessor($this->fail(...)));
    }

    public function testProcess(): void
    {
        $expected_value = 'Dummty content';
        $value = null;
        $instance = new PreProcessor(static function (string $content) use (&$value): void {
            $value = $content;
        });

        $result = $instance->process(
            $this->mockMethod(FileStream::class, 'getContents', [], $expected_value),
            new Metadata('dummy file name', 1234, 'text/html') // Cannot be mocked because it is final.
        );

        $this->assertSame($expected_value, $value);
        $this->assertInstanceOf(ProcessingStatus::class, $result);
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('idontcare', $result->getMessage());
    }
}
