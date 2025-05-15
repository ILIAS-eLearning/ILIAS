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
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;

/**
 * @ilCtrl_isCalledBy ilMDCopyrightImageUploadHandlerGUI: ilMDCopyrightSelectionGUI
 */
class ilMDCopyrightImageUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    protected ilMDCopyrightImageStakeholder $stakeholder;
    protected Services $storage;
    protected string $identification;

    public function __construct(string $identification)
    {
        global $DIC;

        $this->stakeholder = new ilMDCopyrightImageStakeholder();
        $this->storage = $DIC->resourceStorage();
        $this->identification = $identification;

        parent::__construct();
    }

    protected function getUploadResult(): HandlerResult
    {
        $status = HandlerResult::STATUS_FAILED;
        $new_identification = 'unknown';
        $message = 'file upload failed';

        $this->upload->process();
        $uploadResults = $this->upload->getResults();
        $result = end($uploadResults);

        if ($result instanceof UploadResult && $result->isOK() && $result->getSize()) {
            $resource_id = $this->storage->manage()->find($this->identification);
            if (is_null($resource_id)) {
                $new_identification = $this->storage->manage()->upload($result, $this->stakeholder)->serialize();
                $status = HandlerResult::STATUS_OK;
                $message = 'Upload ok';
            } elseif ($this->identification !== '') {
                $this->storage->manage()->replaceWithUpload($resource_id, $result, $this->stakeholder);
                $new_identification = $this->identification;
                $status = HandlerResult::STATUS_OK;
                $message = 'Upload ok';
            }
        }
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $new_identification,
            $message
        );
    }

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

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $title,
            $size,
            $mime
        );
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }
}
