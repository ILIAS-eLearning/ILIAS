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
     * @var \ilLanguage
     */
    private \ilLanguage $lang;

    /**
     * @var \ILIAS\UI\Implementation\Component\Input\Factory
     */
    private \ILIAS\UI\Implementation\Component\Input\Factory $input_factory;

    /**
     * Factory Constructor
     *
     * @param \ILIAS\UI\Implementation\Component\Input\Factory $input_factory
     * @param \ilLanguage                                      $lang
     */
    public function __construct(\ILIAS\UI\Implementation\Component\Input\Factory $input_factory, \ilLanguage $lang)
    {
        $this->input_factory = $input_factory;
        $this->lang          = $lang;
    }

    /**
     * @inheritdoc
     */
    public function standard(UploadHandler $upload_handler, string $post_url, bool $with_zip_options = false) : Standard
    {
        return new Standard($this->input_factory, $this->lang, $upload_handler, $post_url, $with_zip_options);
    }

    /**
     * @inheritdoc
     */
    public function wrapper(UploadHandler $upload_handler, string $post_url, array $components, bool $with_zip_options = false) : Wrapper
    {
        return new Wrapper($this->input_factory, $this->lang, $upload_handler, $post_url, $components, $with_zip_options);
    }
}
