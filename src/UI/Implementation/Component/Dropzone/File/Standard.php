<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Standard
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Standard extends File implements \ILIAS\UI\Component\Dropzone\File\Standard
{

    /**
     * @var string
     */
    protected $message = "";
    /**
     * @var Button
     */
    protected $upload_button;


    /**
     * @inheritdoc
     */
    public function withMessage($message)
    {
        $this->checkStringArg("message", $message);
        $clone = clone $this;
        $clone->message = $message;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @inheritdoc
     */
    public function withUploadButton(Button $button)
    {
        $clone = clone $this;
        $clone->upload_button = $button;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getUploadButton()
    {
        return $this->upload_button;
    }
}
