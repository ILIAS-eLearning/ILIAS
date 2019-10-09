<?php

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
     *
     * @param string $message
     *
     * @return $this
     */
    public function withMessage($message);


    /**
     * Get the message of of this dropzone.
     *
     * @return string
     */
    public function getMessage();


    /**
     * Get a dropzone like this, using the given button to upload the files to the server.
     *
     * @param Button $button
     * @return $this
     */
    public function withUploadButton(Button $button);


    /**
     * Get the button to upload the files to the server.
     *
     * @return Button
     */
    public function getUploadButton();
}
