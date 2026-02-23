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

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult as HandlerResultInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Consumer\InlineConsumer;
use ILIAS\ResourceStorage\Consumer\FileStreamConsumer;

class ilMailFormUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    private readonly ILIAS\ResourceStorage\Services $storage;
    private readonly ilMailAttachmentStakeholder $stakeholder;
    private readonly ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = new ilMailAttachmentStakeholder();
        $this->lng = $DIC->language();
    }

    protected function getUploadResult(): HandlerResultInterface
    {
        $this->upload->process();
        /**
         * @var UploadResult $result
         */
        $array = $this->upload->getResults();
        $result = end($array);
        if ($result instanceof UploadResult && $result->isOK()) {
            $i = $this->storage->manage()->upload($result, $this->stakeholder);
            $status = HandlerResultInterface::STATUS_OK;
            $identifier = $i->serialize();
            $message = $this->lng->txt('upload_ok');
        } else {
            $status = HandlerResultInterface::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier): HandlerResultInterface
    {
        $id = $this->storage->manage()->find($identifier);
        if ($id !== null) {
            $this->storage->manage()->remove($id, $this->stakeholder);
            return new BasicHandlerResult($this->getFileIdentifierParameterName(), HandlerResultInterface::STATUS_OK, $identifier, 'file deleted');
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), HandlerResultInterface::STATUS_FAILED, $identifier, 'file not found');
    }

    public function getInfoResult(string $identifier): FileInfoResult
    {
        $id = $this->storage->manage()->find($identifier);
        if ($id === null) {
            return new BasicFileInfoResult($this->getFileIdentifierParameterName(), 'unknown', 'unknown', 0, 'unknown');
        }
        $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

        return new BasicFileInfoResult($this->getFileIdentifierParameterName(), $identifier, $r->getTitle(), $r->getSize(), $r->getMimeType());
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $infos = [];
        foreach ($file_ids as $file_id) {
            $id = $this->storage->manage()->find($file_id);
            if ($id === null) {
                continue;
            }
            $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

            $infos[] = new BasicFileInfoResult($this->getFileIdentifierParameterName(), $file_id, $r->getTitle(), $r->getSize(), $r->getMimeType());
        }

        return $infos;
    }

    public function getInlineConsumer(string $identifier): InlineConsumer
    {
        return $this->storage->consume()->inline(new ResourceIdentification($identifier));
    }

    public function removeFileForIdentifier(string $identifier): HandlerResultInterface
    {
        return $this->getRemoveResult($identifier);
    }

    public function getStreamConsumer(string $identifier): FileStreamConsumer
    {
        return $this->storage->consume()->stream(new ResourceIdentification($identifier));
    }
}
