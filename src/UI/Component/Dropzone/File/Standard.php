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

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Button\Button;

/**
 * A standard file dropzone offers the possibility to upload dropped files to the server.
 * The dropzone also displays a button to select the files manually from the hard disk.
 * @author  nmaerchy <nm@studer-raimann.ch>
 */
interface Standard extends File
{
    /**
     * Get a dropzone like this, displaying the given message in it.
     */
    public function withMessage(string $message): Standard;

    /**
     * Get the message of this dropzone.
     */
    public function getMessage(): string;

    /**
     * Get a dropzone like this, using the given button to upload the files to the server.
     */
    public function withUploadButton(Button $button): Standard;

    /**
     * Get the button to upload the files to the server.
     */
    public function getUploadButton(): ?Button;
}
