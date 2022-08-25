<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Stream\FileStream;
use DateTimeImmutable;

/**
 * Class StreamInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class StreamInfoResolver extends AbstractInfoResolver implements InfoResolver
{
    protected \ILIAS\Filesystem\Stream\FileStream $file_stream;
    protected string $path;
    protected ?string $file_name = null;
    protected string $suffix;
    protected string $mime_type;
    protected ?\DateTimeImmutable $creation_date = null;
    protected int $size = 0;

    public function __construct(
        FileStream $stream,
        int $next_version_number,
        int $revision_owner_id,
        string $revision_title
    ) {
        parent::__construct($next_version_number, $revision_owner_id, $revision_title);
        $this->file_stream = $stream;
        $this->path = $stream->getMetadata('uri');
        $this->initFileName();
        $this->suffix = pathinfo($this->file_name, PATHINFO_EXTENSION);
        $this->initSize();
        $this->initMimeType();
        $this->initCreationDate();
    }

    protected function initMimeType(): void
    {
        $this->mime_type = 'unknown';
        if (function_exists('mime_content_type') && file_exists($this->path)) {
            $this->mime_type = mime_content_type($this->path);
            return;
        }
        /** @noRector  */
        if (class_exists('finfo')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            //We only need the first few bytes to determine the mime-type this helps to reduce RAM-Usage
            $this->mime_type = finfo_buffer($finfo, $this->file_stream->read(255));
            if ($this->file_stream->isSeekable()) {
                $this->file_stream->rewind();
            }
            //All MS-Types are 'application/zip' we need to look at the extension to determine the type.
            if ($this->mime_type === 'application/zip' && $this->suffix !== 'zip') {
                $this->mime_type = $this->getMSFileTypeFromSuffix();
            }
        }
    }

    protected function initSize(): void
    {
        $this->size = 0;
        try {
            $this->size = $this->file_stream->getSize();
        } catch (\Throwable $t) {
            $mb_strlen_exists = function_exists('mb_strlen');
            //We only read one MB at a time as this radically reduces RAM-Usage
            while ($content = $this->file_stream->read(1048576)) {
                if ($mb_strlen_exists) {
                    $this->size += mb_strlen($content, '8bit');
                } else {
                    $this->size += strlen($content);
                }
            }

            if ($this->file_stream->isSeekable()) {
                $this->file_stream->rewind();
            }
        }
    }

    protected function initCreationDate(): void
    {
        $filectime = file_exists($this->path) ? filectime($this->path) : false;
        $this->creation_date = $filectime ? (new \DateTimeImmutable())->setTimestamp($filectime) : new \DateTimeImmutable();
    }

    protected function initFileName(): void
    {
        $this->file_name = basename($this->path);
        if ($this->file_name === 'memory' || $this->file_name === 'input') { // in case the stream is ofString or of php://input
            $this->file_name = $this->getRevisionTitle();
        }
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    protected function getMSFileTypeFromSuffix(): string
    {
        $mime_types_array = MimeType::getExt2MimeMap();
        $suffix_with_dot = '.' . $this->getSuffix();
        if (array_key_exists($suffix_with_dot, $mime_types_array)) {
            return $mime_types_array[$suffix_with_dot];
        }
        return 'application/zip';
    }
}
