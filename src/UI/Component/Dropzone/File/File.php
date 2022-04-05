<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Field\FileUpload;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Droppable;
use ILIAS\Refinery\Transformation;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface File extends FileUpload, Component, Droppable
{
    /**
     * Get a dropzone like this, but showing a custom title in the appearing modal.
     */
    public function withTitle(string $title) : File;

    /**
     * Get the custom title if set.
     */
    public function getTitle() : string;

    /**
     * Get a dropzone like this with a server request.
     */
    public function withRequest(ServerRequestInterface $request) : File;

    /**
     * Apply a transformation to the data of the dropzone's form.
     */
    public function withAdditionalTransformation(Transformation $transformation) : File;

    /**
     * Get the data from the dropzone's form if all inputs were OK, otherwise
     * null will be returned.
     * @return mixed|null
     */
    public function getData();
}
