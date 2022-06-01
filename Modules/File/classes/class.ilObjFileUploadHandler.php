<?php

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

use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;

/**
 * Class ilObjFileUploadHandler
 *
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjFileUploadHandler : ilObjFileGUI
 */
class ilObjFileUploadHandler extends ilCtrlAwareStorageUploadHandler
{
    
    /**
     * ilObjFileUploadHandler constructor
     */
    public function __construct()
    {
        global $DIC;
        $DIC->upload()->register(new ilCountPDFPagesPreProcessors());
        parent::__construct(new ilObjFileStakeholder($DIC->user()->getId()));
    }
}
