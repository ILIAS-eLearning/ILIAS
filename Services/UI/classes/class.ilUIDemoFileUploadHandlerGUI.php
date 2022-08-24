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
 ********************************************************************
 */

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;

/**
 * Class ilUIDemoFileUploadHandlerGUI
 *
 * @ilCtrl_isCalledBy ilUIDemoFileUploadHandlerGUI: ilUIPluginRouterGUI
 */
class ilUIDemoFileUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    /**
     * @inheritDoc
     */
    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_UPLOAD);
    }


    /**
     * @inheritDoc
     */
    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_INFO);
    }


    /**
     * @inheritDoc
     */
    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_REMOVE,
            null,
            false
        );
    }


    /**
     * @inheritDoc
     */
    public function getFileIdentifierParameterName(): string
    {
        return 'my_file_id';
    }


    /**
     * @inheritDoc
     */
    protected function getUploadResult(): HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $identifier = md5(random_bytes(65));
        $message = 'Everything ok';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }


    protected function getRemoveResult(string $identifier): HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $message = 'File Deleted';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }


    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            "My funny Testfile $identifier.txt",
            64,
            ""
        );
    }


    public function getInfoForExistingFiles(array $file_ids): array
    {
        $infos = [];
        foreach ($file_ids as $file_id) {
            $infos[] = new BasicFileInfoResult(
                $this->getFileIdentifierParameterName(),
                $file_id,
                "Name $file_id.txt",
                rand(1000, 2000),
                "text/plain"
            );
        }

        return $infos;
    }
}
