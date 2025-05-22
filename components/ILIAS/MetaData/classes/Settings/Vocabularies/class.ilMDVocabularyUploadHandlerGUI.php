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
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;

class ilMDVocabularyUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    protected Filesystem $temp;

    public function __construct()
    {
        global $DIC;

        $this->temp = $DIC->filesystem()->temp();
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
            $new_identification = uniqid('md_vocab_');
            $move_success = $this->upload->moveOneFileTo(
                $result,
                $new_identification,
                Location::TEMPORARY
            );
            if ($move_success) {
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

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $identifier,
            'asynchronous removal blocked'
        );
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        $title = $mime = 'unknown';
        $size = 0;

        $files = $this->temp->hasDir($identifier) ?
            $this->temp->listContents($identifier) :
            [];

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $title = $file->getFilename();
            $size = $file->getSize();
            $mime = $file->getMimeType();
            break;
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
        return [];
    }
}
