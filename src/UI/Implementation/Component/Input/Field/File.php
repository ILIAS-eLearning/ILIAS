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
     * @var array
     */
    private $accepted_mime_types = [];
    /**
     * @var int
     */
    private $max_file_size = -1;
    /**
     * @var C\Input\Field\UploadHandler
     */
    private $upload_handler;

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

    protected function getConstraintForRequirement()
    {
        return $this->refinery->string();
    }

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

    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) {
            return '';
        };
    }

    public function withMaxFileSize(int $size_in_bytes) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    public function getMaxFileFize() : int
    {
        return $this->max_file_size;
    }

    public function withInput(InputData $input)
    {
        $value = $input->getOr($this->getName(), null);
        if ($value === null) {
            $this->value = null;
        }

        return parent::withInput($input);
    }

    public function getUploadHandler() : C\Input\Field\UploadHandler
    {
        return $this->upload_handler;
    }

    public function withAcceptedMimeTypes(array $mime_types) : \ILIAS\UI\Component\Input\Field\File
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    public function getAcceptedMimeTypes() : array
    {
        return $this->accepted_mime_types;
    }
}
