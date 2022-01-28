<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

/**
 * Class Dropzone
 *
 * Basic implementation for dropzones. Provides functionality which are needed
 * for all dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
use ILIAS\Data\DataSize;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Droppable;
use ILIAS\UI\Component\Dropzone\File as F;

abstract class File implements F\File
{
    use Triggerer;
    use ComponentHelper;
    use JavaScriptBindable;

    public const DROP_EVENT = "drop"; // Name of the drop-event in JS, e.g. used with jQuery .on('drop', ...)

    protected string $url;
    protected array $allowed_file_types = [];
    protected ?DataSize $file_size_limit = null;
    protected int $max_files = 0;
    protected bool $custom_file_names = false;
    protected bool $file_descriptions = false;
    protected string $parameter_name = 'files';

    public function __construct(string $url)
    {
        $this->checkStringArg('url', $url);
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function withUploadUrl(string $url) : F\File
    {
        $clone = clone $this;
        $clone->url = $url;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUploadUrl() : string
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function withAllowedFileTypes(array $types) : F\File
    {
        $clone = clone $this;
        $clone->allowed_file_types = $types;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedFileTypes() : array
    {
        return $this->allowed_file_types;
    }

    /**
     * @inheritdoc
     */
    public function withMaxFiles(int $max) : F\File
    {
        $clone = clone $this;
        $clone->max_files = $max;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMaxFiles() : int
    {
        return $this->max_files;
    }

    /**
     * @inheritdoc
     */
    public function withFileSizeLimit(DataSize $limit) : F\File
    {
        $clone = clone $this;
        $clone->file_size_limit = $limit;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFileSizeLimit() : ?DataSize
    {
        return $this->file_size_limit;
    }

    /**
     * @inheritdoc
     */
    public function withUserDefinedFileNamesEnabled(bool $state) : F\File
    {
        $clone = clone $this;
        $clone->custom_file_names = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function allowsUserDefinedFileNames() : bool
    {
        return $this->custom_file_names;
    }

    /**
     * @inheritdoc
     */
    public function withUserDefinedDescriptionEnabled(bool $state) : F\File
    {
        $clone = clone $this;
        $clone->file_descriptions = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function allowsUserDefinedFileDescriptions() : bool
    {
        return $this->file_descriptions;
    }

    /**
     * @inheritdoc
     */
    public function withParameterName(string $parameter_name) : F\File
    {
        $clone = clone $this;
        $clone->parameter_name = $parameter_name;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getParameterName() : string
    {
        return $this->parameter_name;
    }

    /**
     * @inheritDoc
     */
    public function withOnDrop(Signal $signal) : Droppable
    {
        return $this->withTriggeredSignal($signal, self::DROP_EVENT);
    }


    /**
     * @inheritDoc
     */
    public function withAdditionalDrop(Signal $signal) : Droppable
    {
        return $this->appendTriggeredSignal($signal, self::DROP_EVENT);
    }
}
