<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use ILIAS\Filesystem\Stream\FileStream;
use DateTimeImmutable;

/**
 * Class StreamInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class StreamInfoResolver extends AbstractInfoResolver implements InfoResolver
{
    /**
     * @var FileStream
     */
    protected $file_stream;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $file_name;
    /**
     * @var string
     */
    protected $suffix;
    /**
     * @var string
     */
    protected $mime_type;
    /**
     * @var DateTimeImmutable
     */
    protected $creation_date;
    protected $size = 0;

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

    protected function initMimeType() : void
    {
        $this->mime_type = 'unknown';
        if (function_exists('mime_content_type')) {
            if (file_exists($this->path)) {
                $this->mime_type = mime_content_type($this->path);
                return;
            }
        }
        if (class_exists('finfo')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mime_type = finfo_buffer($finfo, $this->file_stream->getContents());
            return;
        }

    }

    protected function initSize() : void
    {
        $this->size = 0;
        try {
            $this->size = $this->file_stream->getSize();
        } catch (\Throwable $t) {
            if (function_exists('mb_strlen')) {
                $this->size = mb_strlen($this->file_stream->getContents(), '8bit');
            } else {
                $this->size = strlen($this->file_stream->getContents());
            }
        }
    }

    protected function initCreationDate() : void
    {
        $filectime = file_exists($this->path) ? filectime($this->path) : false;
        $this->creation_date = $filectime ? (new \DateTimeImmutable())->setTimestamp($filectime) : new \DateTimeImmutable();
    }

    protected function initFileName() : void
    {
        $this->file_name = basename($this->path);
        if ($this->file_name === 'memory') { // in case the stream is ofString
            $this->file_name = $this->getRevisionTitle();
        }
    }

    public function getFileName() : string
    {
        return $this->file_name;
    }

    public function getMimeType() : string
    {
        return $this->mime_type;
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function getCreationDate() : DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function getSize() : int
    {
        return $this->size;
    }
}
