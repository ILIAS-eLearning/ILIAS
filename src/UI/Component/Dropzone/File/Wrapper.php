<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Component;

/**
 * Interface Wrapper
 *
 * A wrapper file drozpone wraps around any other component from the UI framework, e.g. a calendar entry.
 * Any wrapper dropzone is highlighted as soon as some files are dragged over the browser window.
 * Dropping the files opens a modal where the user can start the upload process.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Wrapper extends FileDropzone
{
    /**
     * Returns the component(s) wrapped by this dropzone.
     *
     * @return Component[]
     */
    public function getComponents() : array;

    /**
     * Get a wrapper dropzone like this, but showing a custom title in the appearing modal.
     *
     * @param string $title
     * @return Wrapper
     */
    public function withTitle(string $title) : Wrapper;


    /**
     * Get the custom title if set.
     *
     * @return string
     */
    public function getTitle() : ?string;

    /**
     * Returns a file input like this, with additional (metadata) inputs.
     *
     * @param Input[] $inputs
     * @return Wrapper
     */
    public function withMetadataInputs(array $inputs) : Wrapper;

    /**
     * Returns additional (metadata) inputs of this input.
     *
     * @return Input[]|null
     */
    public function getMetadataInputs() : ?array;
}
