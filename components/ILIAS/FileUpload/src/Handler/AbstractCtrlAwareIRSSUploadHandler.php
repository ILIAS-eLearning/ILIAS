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

namespace ILIAS\FileUpload\Handler;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Services as HttpServices;
use ilCtrl;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class AbstractCtrlAwareIRSSUploadHandler
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractCtrlAwareIRSSUploadHandler extends AbstractCtrlAwareUploadHandler
{
    abstract protected function getStakeholder(): ResourceStakeholder;

    protected \ILIAS\ResourceStorage\Services $irss;
    protected ResourceStakeholder $stakeholder;

    public function __construct()
    {
        global $DIC;

        $this->irss = $DIC->resourceStorage();
        $this->stakeholder = $this->getStakeholder();

        parent::__construct();
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process(); // Process the uploads to rund things like PreProcessors

        $result_array = $this->upload->getResults();
        $result = end($result_array); // get the last result aka the Upload of the user

        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = $this->irss->manage()->upload($result, $this->stakeholder)->serialize();
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        if (null !== ($id = $this->irss->manage()->find($identifier))) {
            $this->irss->manage()->remove($id, $this->stakeholder);
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
        return array_map(function ($file_id): FileInfoResult {
            return $this->getInfoResult($file_id);
        }, $file_ids);
    }
}
