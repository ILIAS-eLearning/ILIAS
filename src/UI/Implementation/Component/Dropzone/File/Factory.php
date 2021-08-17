<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Field\UploadHandler;
/**
 * Class Factory
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Factory implements \ILIAS\UI\Component\Dropzone\File\Factory
{
    /**
     * @var \ILIAS\UI\Implementation\Component\Input\Factory
     */
    private $input_factory;

    public function __construct(\ILIAS\UI\Implementation\Component\Input\Factory $input_factory) {
        $this->input_factory = $input_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard(UploadHandler $upload_handler, string $post_url) : Standard
    {
        return new Standard($upload_handler, $post_url);
    }

    /**
     * @inheritdoc
     */
    public function wrapper(UploadHandler $upload_handler, string $post_url, array $components) : Wrapper
    {
        return new Wrapper($this->input_factory, $upload_handler, $post_url, $components);
    }
}
