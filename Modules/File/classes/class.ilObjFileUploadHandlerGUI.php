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

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\ResourceStorage\Services;

/**
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilObjFileUploadHandlerGUI: ilObjFileGUI, ilRepositoryGUI, ilDashboardGUI
 */
class ilObjFileUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    private Services $storage;
    private ilObjFileStakeholder $stakeholder;
    private array $class_path = [ilRepositoryGUI::class, self::class];

    public function __construct()
    {
        global $DIC;

        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = new ilObjFileStakeholder($DIC->user()->getId());

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass($this->class_path, self::CMD_UPLOAD, null, true);
    }

    /**
     * @inheritDoc
     */
    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass($this->class_path, self::CMD_INFO, null, true);
    }

    /**
     * overrides parent method in order to provide an async URL.
     * @return string
     */
    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass($this->class_path, self::CMD_REMOVE, null, true);
    }

    /**
     * @return HandlerResult
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    protected function getUploadResult(): HandlerResult
    {
        $this->upload->register(new ilCountPDFPagesPreProcessors());
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = $this->storage->manage()->upload($result, $this->stakeholder)->serialize();
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    /**
     * @param string $identifier
     * @return HandlerResult
     */
    protected function getRemoveResult(string $identifier): HandlerResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $this->storage->manage()->remove($id, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $message = "file removal OK";
        } else {
            $status = HandlerResult::STATUS_OK;
            $message = "file with identifier '$identifier' doesn't exist, nothing to do.";
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    /**
     * @param string $identifier
     * @return FileInfoResult
     */
    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $revision = $this->storage->manage()->getCurrentRevision($id)->getInformation();
            $title = $revision->getTitle();
            $size = $revision->getSize();
            $mime = $revision->getMimeType();
        } else {
            $title = $mime = 'unknown';
            $size = 0;
        }

        return new BasicFileInfoResult($this->getFileIdentifierParameterName(), $identifier, $title, $size, $mime);
    }

    /**
     * @param array $file_ids
     * @return FileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_ids): array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }
}
