<?php

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;
use ILIAS\UI\Component\Input\Field\File;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form;

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

    /**
     * Get a dropzone like this with data from the current request attached.
     *
     * @param ServerRequestInterface $request
     * @return FileDropzone
     */
    public function withRequest(ServerRequestInterface $request) : FileDropzone;

    /**
     * Get the data of this dropzone after it has been submitted.
     *
     * @return    array|null
     */
    public function getData();
}
