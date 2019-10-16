<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements the radio input.
 */
class File extends Input implements C\Input\Field\File
{

    use JavaScriptBindable;
    use Triggerer;
    /**
     * @var int
     */
    private $max_file_size;


    /**
     * @inheritDoc
     */
    protected function getConstraintForRequirement()
    {
        return null; // TODO
    }


    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return true; // TODO
    }


    /**
     * @inheritDoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) { // TODO
            return '';
        };
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
    public function getMaxFileFize() : int
    {
        return $this->max_file_size;
    }
}
