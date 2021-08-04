<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class File
 * @package ILIAS\UI\Implementation\Component\Input\Field
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class File extends Input implements C\Input\Field\File
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var C\Input\Field\UploadHandler
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
     * @var Input[]
     */
    private $inputs = [];

    /**
     * File constructor
     *
     * @param DataFactory                 $data_factory
     * @param Factory                     $refinery
     * @param C\Input\Field\UploadHandler $handler
     * @param                             $label
     * @param                             $byline
     */
    public function __construct(
        DataFactory $data_factory,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        $label,
        $byline
    ) {
        $this->upload_handler = $handler;
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function getUploadHandler() : C\Input\Field\UploadHandler
    {
        return $this->upload_handler;
    }

    /**
     * @inheritDoc
     */
    public function withAcceptedMimeTypes(array $mime_types) : C\Input\Field\File
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
    public function withMaxFileSize(int $size_in_bytes) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFileSize() : int
    {
        return $this->max_file_size;
    }

    /**
     * @inheritDoc
     */
    public function withMaxFiles(int $amount) : C\Input\Field\File
    {
        $this->checkIntArg('amount', $amount);

        $clone = clone $this;
        $clone->max_files = $amount;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFiles() : int
    {
        if (null !== $this->max_files && 1 < $this->max_files) {
            return $this->max_files;
        }

        return 1;
    }

    /**
     * @inheritDoc
     */
    public function withMetadataInputs(array $inputs) : C\Input\Field\File
    {
        $this->checkArgListElements('inputs', $inputs, Input::class);

        $clone = clone $this;
        $clone->inputs = $inputs;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetadataInputs() : array
    {
        return $this->inputs;
    }

    /**
     * @inheritDoc
     */
    public function withInput(InputData $input)
    {
        $value = $input->getOr($this->getName(), null);
        if ($value === null) {
            $this->value = null;
        }

        return parent::withInput($input);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return static function ($id) {
            return '';
        };
    }

    /**
     * @inheritDoc
     */
    protected function getConstraintForRequirement()
    {
        return $this->refinery->string();
    }

    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (null === $value) {
            return true;
        }
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $string) {
            if (!is_string($string) && null !== $string) {
                return false;
            }
        }

        return true;
    }
}
