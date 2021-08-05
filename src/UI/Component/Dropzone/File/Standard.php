<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Dropzone\File;

/**
 * Interface Standard
 *
 * A standard file dropzone offers the possibility to upload dropped files to the server.
 * The dropzone also displays a button to select the files manually from the hard disk.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Standard extends FileDropzone
{
    /**
     * Get a dropzone like this, displaying the given message in it.
     *
     * @param string $message
     * @return Standard
     */
    public function withMessage(string $message) : Standard;


    /**
     * Get the message of of this dropzone.
     *
     * @return string
     */
    public function getMessage() : string;
}
