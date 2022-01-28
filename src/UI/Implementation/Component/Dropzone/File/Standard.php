<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Dropzone\File as F;

/**
 * Class Standard
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Standard extends File implements F\Standard
{
    protected string $message = "";
    protected ?Button $upload_button = null;


    /**
     * @inheritdoc
     */
    public function withMessage(string $message) : F\Standard
    {
        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function withUploadButton(Button $button) : F\Standard
    {
        $clone = clone $this;
        $clone->upload_button = $button;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUploadButton() : ?Button
    {
        return $this->upload_button;
    }
}
