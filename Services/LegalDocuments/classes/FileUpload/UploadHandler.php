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

namespace ILIAS\LegalDocuments\FileUpload;

use ILIAS\UI\Component\Input\Field\UploadHandler as UploadHandlerInterface;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use Closure;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\Data\Result\Ok;

class UploadHandler implements UploadHandlerInterface
{
    /**
     * @param Closure(string): string $link
     * @param Closure(): Result<DocumentContent> $content
     * @param Closure(string): string $txt
     */
    public function __construct(
        private readonly Closure $link,
        private readonly Closure $content,
        private readonly Closure $txt
    ) {
    }

    public function getFileIdentifierParameterName(): string
    {
        return UploadHandlerInterface::DEFAULT_FILE_ID_PARAMETER;
    }

    public function getUploadURL(): string
    {
        return $this->to('upload');
    }

    public function getFileRemovalURL(): string
    {
        return $this->to('rm');
    }

    public function getExistingFileInfoURL(): string
    {
        return $this->to('info');
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        return [];
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        return ($this->content)()->map(fn(DocumentContent $c) => new BasicFileInfoResult(
            $identifier,
            $identifier,
            ($this->txt)('updated_document'),
            strlen($c->value()),
            $c->type()
        ))->except(fn() => new Ok(null))->value();
    }

    public function supportsChunkedUploads(): bool
    {
        return false;
    }

    private function to(string $cmd): string
    {
        return ($this->link)($cmd);
    }
}
