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
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\LegalDocuments\Value\DocumentContent;

require_once __DIR__ . '/../ContainerMock.php';

class UploadHandlerTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UploadHandler::class, new UploadHandler($this->fail(...), $this->fail(...), $this->fail(...)));
    }

    public function testGetFileIdentifierParameterName(): void
    {
        $instance = new UploadHandler($this->fail(...), $this->fail(...), $this->fail(...));
        $this->assertSame(UploadHandlerInterface::DEFAULT_FILE_ID_PARAMETER, $instance->getFileIdentifierParameterName());
    }

    public function testLinks(): void
    {
        $instance = new UploadHandler(
            static fn(string $to): string => 'Will link to: ' . $to,
            $this->fail(...),
            $this->fail(...)
        );
        $this->assertGetter($instance, [
            'getUploadURL' => 'Will link to: upload',
            'getFileRemovalURL' => 'Will link to: rm',
            'getExistingFileInfoURL' => 'Will link to: info',
            'supportsChunkedUploads' => false,
        ]);
        $this->assertSame([], $instance->getInfoForExistingFiles([]));
        $this->assertSame([], $instance->getInfoForExistingFiles(['foo']));
    }

    public function testInfoResultWithResult(): void
    {
        $value = 'Lorem ipsum';
        $document_content = $this->mockTree(DocumentContent::class, [
            'value' => $value,
            'type' => 'html',
        ]);
        $content = fn() => new Ok($document_content);
        $instance = new UploadHandler($this->fail(...), $content, fn(string $s) => 'Translated ' . $s);
        $info = $instance->getInfoResult('foo');
        $this->assertSame(strlen($value), $info->getSize());
        $this->assertSame('html', $info->getMimeType());
        $this->assertSame('Translated updated_document', $info->getName());
        $this->assertSame('foo', $info->getFileIdentifier());
    }

    public function testInfoResultWithoutResult(): void
    {
        $content = fn() => new Error('Nothing uploaded yet.');

        $instance = new UploadHandler($this->fail(...), $content, $this->fail(...));
        $this->assertSame(null, $instance->getInfoResult('foo'));
    }
}
