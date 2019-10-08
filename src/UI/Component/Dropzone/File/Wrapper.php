<?php
namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;

/**
 * Interface Wrapper
 *
 * A wrapper file drozpone wraps around any other component from the UI framework, e.g. a calendar entry.
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
     *
     * @param Component[]|Component $content
     *
     * @return $this
     */
    public function withTitle($title);


    /**
     * Get the custom title if set.
     *
     * @return Component[]
     */
    public function getTitle();

    /**
     * Get a wrapper dropzone like this, wrapping around the given component(s).
     *
     * @param Component[]|Component $content
     *
     * @return $this
     */
    public function withContent($content);


    /**
     * Get the components being wrapped by this dropzone.
     *
     * @return Component[]
     */
    public function getContent();
}
