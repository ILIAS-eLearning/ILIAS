<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File\Standard as StandardInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Button\Button;
use ilLanguage;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Standard extends File implements StandardInterface
{
    protected string $message = "";
    protected ?Button $upload_button = null;

    public function __construct(
        InputFactory $input_factory,
        ilLanguage $language,
        UploadHandler $upload_handler,
        string $post_url,
        ?Input $metadata_input = null
    ) {
        parent::__construct($input_factory, $language, $upload_handler, $post_url, $metadata_input);
    }

    public function withMessage(string $message) : self
    {
        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function withUploadButton(Button $button) : self
    {
        $clone = clone $this;
        $clone->upload_button = $button;
        return $clone;
    }

    public function getUploadButton() : ?Button
    {
        return $this->upload_button;
    }
}
