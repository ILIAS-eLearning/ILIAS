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

use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;

/**
 * Class ilFileVersionsUploadHandlerGUI
 *
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFileVersionsUploadHandlerGUI : ilFileVersionsGUI
 */
class ilFileVersionsUploadHandlerGUI extends ilCtrlAwareStorageUploadHandler
{
    const MODE_APPEND = 'append';
    const MODE_REPLACE = 'replace';
    const P_UPLOAD_MODE = 'upload_mode';
    protected ilObjFile $file;
    protected bool $append;
    protected string $upload_mode = self::MODE_APPEND;
    
    public function __construct(ilObjFile $existing_file, string $upload_mode = self::MODE_APPEND)
    {
        global $DIC;
        parent::__construct(new ilObjFileStakeholder($DIC->user()->getId()));
        $this->file = $existing_file;
        $this->upload_mode = $this->http->wrapper()->query()->has(self::P_UPLOAD_MODE)
            ? $this->http->wrapper()->query()->retrieve(self::P_UPLOAD_MODE, $DIC->refinery()->kindlyTo()->string())
            : $upload_mode;
        
        $this->ctrl->setParameter($this, self::P_UPLOAD_MODE, $this->upload_mode);
    }
    
    protected function getUploadResult() : HandlerResult
    {
        $this->upload->register(new ilCountPDFPagesPreProcessors());
        $this->upload->process();
        $upload_mode =
        
        $result_array = $this->upload->getResults();
        $result = end($result_array);
        
        if ($result instanceof UploadResult && $result->isOK()) {
            if ($this->upload_mode === self::MODE_REPLACE) {
                $identifier = (string) $this->file->replaceWithUpload($result, $this->file->getTitle());
            } else {
                $identifier = (string) $this->file->appendUpload($result, $this->file->getTitle());
            }
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }
        
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }
}
