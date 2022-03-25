<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * Class File
 * @package ILIAS\UI\Implementation\Component\Input\Field
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class File extends Input implements C\Input\Field\File
{
    private array $accepted_mime_types = [];
    private int $max_file_size;
    private C\Input\Field\UploadHandler $upload_handler;

    public function __construct(
        DataFactory $data_factory,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        string $label,
        ?string $byline
    ) {
        $this->upload_handler = $handler;
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->to()->string();
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

    public function getUpdateOnLoadCode() : Closure
    {
        return fn() => '';
    }

    public function withMaxFileSize(int $size_in_bytes) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    public function getMaxFileSize() : int
    {
        return $this->max_file_size;
    }

    public function withInput(InputData $input) : C\Input\Field\Input
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

    public function withAcceptedMimeTypes(array $mime_types) : C\Input\Field\File
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
