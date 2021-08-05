<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

/**
 * Class Standard
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Standard extends FileDropzone implements \ILIAS\UI\Component\Dropzone\File\Standard
{
    /**
     * @var string|null
     */
    private $message;

    /**
     * @inheritdoc
     */
    public function withMessage(string $message) : Standard
    {
        $this->checkStringArg("message", $message);

        $clone = clone $this;
        $clone->message = $message;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }
}
