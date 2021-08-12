<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;
use ILIAS\UI\Component\Input\Field\File;

/**
 * Interface FileDropzone
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\FileDropzone
 */
interface FileDropzone extends Droppable, Component, File
{
    /**
     * Returns the post-url of this dropzone.
     *
     * @return string
     */
    public function getPostURL() : string;
}
