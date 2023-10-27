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
use Closure;

class UploadHandler implements UploadHandlerInterface
{
    /**
     * @param Closure(string): string $link
     */
    public function __construct(private readonly Closure $link)
    {
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
        return null;
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
