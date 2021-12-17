<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * A wrapper file dropzone wraps around any other component from the UI framework, e.g. a calendar entry.
 * Any wrapper dropzone is highlighted as soon as some files are dragged over the browser window.
 * Dropping the files opens a modal where the user can start the upload process.
 * @author  nmaerchy <nm@studer-raimann.ch>
 */
interface Wrapper extends File
{
    /**
     * Get the components being wrapped by this dropzone.
     *
     * @return Component[]
     */
    public function getContent() : array;
}
