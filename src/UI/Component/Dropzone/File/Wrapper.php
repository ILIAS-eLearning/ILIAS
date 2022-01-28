<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * Interface Wrapper
 *
 * A wrapper file dropzone wraps around any other component from the UI framework, e.g. a calendar entry.
 * Any wrapper dropzone is highlighted as soon as some files are dragged over the browser window.
 * Dropping the files opens a modal where the user can start the upload process.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Wrapper extends File
{
    /**
     * Get a wrapper dropzone like this, but showing a custom title in the appearing modal.
     */
    public function withTitle(string $title) : Wrapper;

    /**
     * Get the custom title if set.
     */
    public function getTitle() : string;

    /**
     * Get a wrapper dropzone like this, wrapping around the given component(s).
     *
     * @param Component[]|Component $content
     */
    public function withContent($content) : Wrapper;

    /**
     * Get the components being wrapped by this dropzone.
     *
     * @return Component[]
     */
    public function getContent() : array;
}
