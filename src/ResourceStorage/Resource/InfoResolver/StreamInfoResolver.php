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

    public function __construct(
        FileStream $stream,
        int $next_version_number,
        int $revision_owner_id,
        string $revision_title
    ) {
        parent::__construct($next_version_number, $revision_owner_id, $revision_title);
        $this->file_stream = $stream;
        $this->path = $stream->getMetadata('uri');
        $this->file_name = basename($this->path);
        $this->suffix = pathinfo($this->file_name, PATHINFO_EXTENSION);
        $this->mime_type = function_exists('mime_content_type') ? mime_content_type($this->path) : 'unknown';
        $this->initCreationDate();
    }

    protected function initCreationDate() : void
    {
        $filectime = filectime($this->path);
        $this->creation_date = $filectime ? (new \DateTimeImmutable())->setTimestamp($filectime) : new \DateTimeImmutable();
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
        return $this->file_stream->getSize() ?? 0;
    }
}
