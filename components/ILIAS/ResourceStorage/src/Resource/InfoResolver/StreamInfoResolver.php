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

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Stream\ZIPStream;

/**
 * Class StreamInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class StreamInfoResolver extends AbstractInfoResolver implements InfoResolver
{
    protected string $path;
    protected ?string $file_name = null;
    protected string $suffix;
    protected string $mime_type;
    protected ?\DateTimeImmutable $creation_date = null;
    protected int $size = 0;
    protected FileStream $file_stream;

    public function __construct(
        FileStream $file_stream,
        int $next_version_number,
        int $revision_owner_id,
        string $revision_title,
        ?string $file_name = null
    ) {
        $metadata = $file_stream->getMetadata();
        $uri = $metadata['uri'];

        if ($file_stream instanceof ZIPStream) {
            // ZIPStreams are not seekable and rewindable, we need to wrap them in another ZIPStream to
            // be able to read(255) and get the mime-type without loosing the 255 bytes
            $this->file_stream = new ZIPStream(fopen($uri, 'rb'));
        } else {
            $this->file_stream = $file_stream;
        }

        parent::__construct($next_version_number, $revision_owner_id, $revision_title);
        $this->path = $uri;
        $this->initFileName($file_name);
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
        /** @noRector */
        if (class_exists('finfo')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            //We only need the first few bytes to determine the mime-type this helps to reduce RAM-Usage
            $this->mime_type = finfo_buffer($finfo, $this->file_stream->read(255));
            if ($this->file_stream->isSeekable()) {
                $this->file_stream->rewind();
            }
            //All MS-Types are 'application/zip' we need to look at the extension to determine the type.
            if ($this->mime_type === 'application/zip' && $this->suffix !== 'zip') {
                $this->mime_type = $this->getFileTypeFromSuffix();
            }
            if ($this->mime_type === 'application/x-empty') {
                $this->mime_type = $this->getFileTypeFromSuffix();
            }
        }
    }

    protected function initSize(): void
    {
        $this->size = 0;
        try {
            $this->size = $this->file_stream->getSize();
        } catch (\Throwable $exception) {
            $mb_strlen_exists = function_exists('mb_strlen');
            //We only read one MB at a time as this radically reduces RAM-Usage
            while ($content = $this->file_stream->read(1_048_576)) {
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
        $this->creation_date = $filectime ? (new \DateTimeImmutable())->setTimestamp(
            $filectime
        ) : new \DateTimeImmutable();
    }

    protected function initFileName(?string $file_name = null): void
    {
        if ($file_name !== null) {
            $this->file_name = $file_name;
            return;
        }
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

    protected function getFileTypeFromSuffix(): string
    {
        $mime_types_array = MimeType::getExt2MimeMap();
        $suffix_with_dot = '.' . $this->getSuffix();
        if (array_key_exists($suffix_with_dot, $mime_types_array)) {
            return $mime_types_array[$suffix_with_dot];
        }
        return 'application/octet-stream';
    }
}
