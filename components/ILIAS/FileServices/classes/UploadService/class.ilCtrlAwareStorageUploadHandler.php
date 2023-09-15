<?php

declare(strict_types=1);


use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\ilCtrlAwareUploadHandler;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;

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
 * Class ilCtrlAwareStorageUploadHandler
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilCtrlAwareStorageUploadHandler extends AbstractCtrlAwareUploadHandler
{
    protected ResourceStakeholder $stakeholder;
    protected \ILIAS\ResourceStorage\Services $storage;

    public function __construct(ResourceStakeholder $stakeholder)
    {
        global $DIC;
        parent::__construct();
        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = $stakeholder;
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->register(new ilCountPDFPagesPreProcessors());
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult && $result->isOK()) {
            if (!$this->is_chunked) {
                $identifier = $this->storage->manage()->upload($result, $this->stakeholder)->serialize();
                $status = HandlerResult::STATUS_OK;
                $message = "file upload OK";
            } else {
                $identifier = '';
                $status = HandlerResult::STATUS_FAILED;
                $message = 'chunking not supported';
            }
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

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

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }
}
