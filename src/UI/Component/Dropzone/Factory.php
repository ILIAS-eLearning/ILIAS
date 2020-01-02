<?php
/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI File Dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone
 */

namespace ILIAS\UI\Component\Dropzone;

interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      File dropzones are used to drop files from outside the browser window.
     *      The dropped files are presented to the user and can be uploaded to the server.
     *      File dropzones offer additional convenience beside manually selecting files
     *      over the file browser.
     *   composition: >
     *      File dropzones are areas to drop the files. They contain either a message
     *      (standard file dropzone) or other ILIAS UI components (wrapper file dropzone).
     *   effect: >
     *      A dropzone is highlighted when the user drags files over it.
     *
     * rules:
     *   accessibility:
     *     1: >
     *       There MUST be alternative ways in the system to upload the files due to
     *       the limited accessibility of file dropzones.
     * ---
     *
     * @return \ILIAS\UI\Component\Dropzone\File\Factory
     **/
    public function file();
}
