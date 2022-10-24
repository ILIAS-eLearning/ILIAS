<?php

declare(strict_types=1);

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilCtrlAwareUploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilCtrlAwareUploadHandler extends UploadHandler
{
    /**
     * Since this is a ilCtrl aware UploadHandler executeCommand MUST be
     * implemented. The Implementation MUST make sure, the Upload and the Removal
     * Command are handled correctly
     */
    public function executeCommand(): void;
}
