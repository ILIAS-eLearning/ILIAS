<?php declare(strict_types=1);

namespace ILIAS\FileUpload\Handler;

use ILIAS\UI\Component\Input\Field\UploadHandler;

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
    public function executeCommand() : void;
}

