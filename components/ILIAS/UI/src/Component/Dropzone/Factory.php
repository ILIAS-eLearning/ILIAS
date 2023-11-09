<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Dropzone;

/**
 * Interface Factory
 *
 * Describes a factory implementation for ILIAS UI File Dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Dropzone
 */
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
     * @return \ILIAS\UI\Component\Dropzone\File\Factory
     **/
    public function file(): File\Factory;
}
