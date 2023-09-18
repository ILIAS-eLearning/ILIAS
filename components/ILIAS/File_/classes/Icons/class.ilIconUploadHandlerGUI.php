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

namespace ILIAS\File\Icon;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\ResourceStorage\Services;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;

/**
 * @author            Lukas Zehnder <lukas@sr.solutions>
 *
 * @ilCtrl_isCalledBy ILIAS\File\Icon\ilIconUploadHandlerGUI: ILIAS\File\Icon\ilObjFileIconsOverviewGUI
 */
class ilIconUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    private Services $storage;
    private ilObjFileIconStakeholder $stakeholder;
    private WrapperFactory $wrapper;
    private Factory $refinery;

    /**
     * ilUIDemoFileUploadHandlerGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->storage = $DIC->resourceStorage();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->stakeholder = new ilObjFileIconStakeholder();
    }

    /**
     * @inheritDoc
     */
    protected function getUploadResult(): HandlerResult
    {
        $status = null;
        $rid = null;
        $message = null;
        $this->upload->process();
        /**
         * @var $result UploadResult
         */
        $array = $this->upload->getResults();
        $result = end($array);
        $failed = false;
        if ($result instanceof UploadResult && $result->isOK()) {
            if ($this->wrapper->query()->has('rid')) { // entered when creating a new icon
                $to_str = $this->refinery->to()->string();
                $rid = $this->wrapper->query()->retrieve('rid', $to_str);
                $id = $this->storage->manage()->find($rid);
                if ($id !== null) {
                    $this->storage->manage()->appendNewRevision($id, $result, $this->stakeholder);
                } else {
                    $failed = true;
                }
            } else { // entered when updating an existing icon
                $i = $this->storage->manage()->upload($result, $this->stakeholder);
                $rid = $i->serialize();
            }
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
        } else {
            $failed = true;
        }

        if ($failed) {
            $status = HandlerResult::STATUS_FAILED;
            $rid = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $rid, $message);
    }

    protected function getRemoveResult(string $rid): HandlerResult
    {
        $id = $this->storage->manage()->find($rid);
        if ($id !== null) {
            $rev = $this->storage->manage()->getCurrentRevision($id);
            $rev_num = $rev->getVersionNumber();
            $this->storage->manage()->removeRevision($id, $rev_num);
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                $rid,
                'file deleted'
            );
        }
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_FAILED,
            $rid,
            'file not found'
        );
    }

    public function getInfoResult(string $rid): ?FileInfoResult
    {
        $id = $this->storage->manage()->find($rid);
        if (!$id instanceof \ILIAS\ResourceStorage\Identification\ResourceIdentification) {
            return new BasicFileInfoResult($this->getFileIdentifierParameterName(), 'unknown', 'unknown', 0, 'unknown');
        }
        $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $rid,
            $r->getTitle(),
            $r->getSize(),
            $r->getMimeType()
        );
    }

    /**
     * @return \ILIAS\FileUpload\Handler\BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $resource_identifiers): array
    {
        $infos = [];
        foreach ($resource_identifiers as $rid) {
            $id = $this->storage->manage()->find($rid);
            if (!$id instanceof \ILIAS\ResourceStorage\Identification\ResourceIdentification) {
                continue;
            }
            $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

            $infos[] = new BasicFileInfoResult(
                $this->getFileIdentifierParameterName(),
                $rid,
                $r->getTitle(),
                $r->getSize(),
                $r->getMimeType()
            );
        }

        return $infos;
    }
}
