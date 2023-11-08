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

declare(strict_types=1);

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\ResourceStorage\Services;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareIRSSUploadHandler;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilObjFileUploadHandlerGUI: ilObjFileGUI, ilRepositoryGUI, ilDashboardGUI
 */
class ilObjFileUploadHandlerGUI extends AbstractCtrlAwareIRSSUploadHandler
{
    protected function getStakeholder(): ResourceStakeholder
    {
        global $DIC;
        return new ilObjFileStakeholder($DIC->user()->getId());
    }

    protected function getClassPath(): array
    {
        return [self::class];
    }

    public function supportsChunkedUploads(): bool
    {
        return true;
    }
}
