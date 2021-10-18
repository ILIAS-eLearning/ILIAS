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
    private ?string $message = null;

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

    /**
     * @inheritDoc
     */
    public function getForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        return $this->factory->container()->form()->standard(
            $this->getPostURL(),
            [
                // @TODO: implement this
            ]
        );
    }
}
