<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * Class FileDropzone
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
abstract class FileDropzone implements \ILIAS\UI\Component\Dropzone\File\FileDropzone
{
    use Triggerer;
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string name of the event in javascript, e.g.
     *             used with jQuery .on('drop', ...).
     */
    private const EVENT = 'drop';

    /**
     * @var UploadHandler
     */
    private $upload_handler;

    /**
     * @var string[]
     */
    private $accepted_mime_types = [];

    /**
     * @var int|null
     */
    private $max_file_size;

    /**
     * @var int|null
     */
    private $max_files;

    /**
     * FileDropzone constructor.
     *
     * @param UploadHandler $upload_handler
     */
    public function __construct(UploadHandler $upload_handler)
    {
        $this->upload_handler = $upload_handler;
    }

    /**
     * @inheritDoc
     */
    public function getUploadHandler() : UploadHandler
    {
        return $this->upload_handler;
    }

    /**
     * @inheritDoc
     */
    public function withAcceptedMimeTypes(array $mime_types) : File
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getAcceptedMimeTypes() : array
    {
        return $this->accepted_mime_types;
    }

    /**
     * @inheritDoc
     */
    public function withMaxFileSize(int $size_in_bytes) : File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFileSize() : ?int
    {
        return $this->max_file_size;
    }

    /**
     * @inheritDoc
     */
    public function withMaxFiles(int $amount) : File
    {
        $clone = clone $this;
        $clone->max_files = $amount;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFiles() : int
    {
        return $this->max_files;
    }

    /**
     * @inheritDoc
     */
    public function withOnDrop(\ILIAS\UI\Component\Signal $signal)
    {
        return $this->withTriggeredSignal($signal, self::EVENT);
    }

    /**
     * @inheritDoc
     */
    public function withAdditionalDrop(\ILIAS\UI\Component\Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, self::EVENT);
    }
}
