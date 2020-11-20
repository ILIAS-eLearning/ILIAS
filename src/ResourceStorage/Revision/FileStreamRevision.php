<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * Class UploadedFileRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileStreamRevision extends FileRevision implements Revision
{

    /**
     * @var FileStream
     */
    private $stream;
    /**
     * @var bool
     */
    protected $keep_original = true;

    /**
     * @inheritDoc
     */
    public function __construct(ResourceIdentification $identification, FileStream $stream, bool $keep_original = false)
    {
        $this->stream = $stream;
        $this->keep_original = $keep_original;
        parent::__construct($identification);
        $information = new FileInformation();
        $this->setInformation($information);
    }

    /**
     * @return FileStream
     */
    public function getStream() : FileStream
    {
        return $this->stream;
    }

    /**
     * @return bool
     */
    public function keepOriginal() : bool
    {
        return $this->keep_original;
    }

}
