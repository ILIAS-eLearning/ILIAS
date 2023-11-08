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

use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\UI\Component\Input\Field\UploadHandler as UploadHandlerInterface;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\FileUpload\UploadHandler;

require_once __DIR__ . '/../ContainerMock.php';

class UploadHandlerTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UploadHandler::class, new UploadHandler($this->fail(...)));
    }

    public function testGetFileIdentifierParameterName(): void
    {
        $this->assertSame(UploadHandlerInterface::DEFAULT_FILE_ID_PARAMETER, (new UploadHandler($this->fail(...)))->getFileIdentifierParameterName());
    }

    public function testLinks(): void
    {
        $instance = new UploadHandler(static fn(string $to): string => 'Will link to: ' . $to);
        $this->assertGetter($instance, [
            'getUploadURL' => 'Will link to: upload',
            'getFileRemovalURL' => 'Will link to: rm',
            'getExistingFileInfoURL' => 'Will link to: info',
            'supportsChunkedUploads' => false,
        ]);
        $this->assertSame([], $instance->getInfoForExistingFiles([]));
        $this->assertSame([], $instance->getInfoForExistingFiles(['foo']));
        $this->assertSame(null, $instance->getInfoResult('foo'));
    }
}
