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
 * @ilCtrl_isCalledBy ilObjFileUploadHandlerGUI: ilObjFileGUI, ilRepositoryGUI, ilDashboardGUI, ilAdministrationGUI
 */
class ilObjFileUploadHandlerGUI extends AbstractCtrlAwareIRSSUploadHandler
{
    private const PARAM_REF_ID = 'ref_id';

    private ilAccessHandler $access;
    private \ILIAS\Refinery\Factory $refinery;
    private ilObjUser $user;

    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->refinery = $DIC->refinery();
        $this->user = $DIC->user();

        parent::__construct();
    }

    protected function getStakeholder(): ResourceStakeholder
    {
        global $DIC;
        return new ilObjFileStakeholder($DIC->user()->getId());
    }

    protected function getUploadResult(): HandlerResult
    {
        if (!$this->mayStageUploadForRepository()) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $this->language->txt('permission_denied')
            );
        }

        return parent::getUploadResult();
    }

    private function mayStageUploadForRepository(): bool
    {
        $query = $this->http->wrapper()->query();

        if ($query->has(self::PARAM_REF_ID)) {
            $ref_id = $query->retrieve(self::PARAM_REF_ID, $this->refinery->kindlyTo()->int());
            return $ref_id > 0
                && $this->access->checkAccess('create_' . ilObjFile::OBJECT_TYPE, '', $ref_id);
        }

        // No repository target (e.g. personal workspace via wsp_id); the create permission
        // is enforced in ilObjFileGUI::uploadFiles() for that case. An anonymous user has
        // no such target and is refused here as well.
        return !($this->user->isAnonymous() || $this->user->getId() < 1);
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
