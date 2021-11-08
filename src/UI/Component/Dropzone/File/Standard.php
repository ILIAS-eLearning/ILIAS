<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Button\Button;

/**
 * Interface Standard
 *
 * A standard file dropzone offers the possibility to upload dropped files to the server.
 * The dropzone also displays a button to select the files manually from the hard disk.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone\File
 */
interface Standard extends File
{
    /**
     * Get a dropzone like this, displaying the given message in it.
     */
    public function withMessage(string $message) : Standard;

    /**
     * Get the message of this dropzone.
     */
    public function getMessage() : string;

    /**
     * Get a dropzone like this, using the given button to upload the files to the server.
     */
    public function withUploadButton(Button $button) : Standard;

    /**
     * Get the button to upload the files to the server.
     */
    public function getUploadButton() : ?Button;
}
