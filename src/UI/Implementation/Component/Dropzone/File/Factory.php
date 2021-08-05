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
     * @inheritdoc
     */
    public function standard(UploadHandler $upload_handler) : Standard
    {
        return new Standard($upload_handler);
    }

    /**
     * @inheritdoc
     */
    public function wrapper(UploadHandler $upload_handler, array $components) : Wrapper
    {
        return new Wrapper($upload_handler, $components);
    }
}
