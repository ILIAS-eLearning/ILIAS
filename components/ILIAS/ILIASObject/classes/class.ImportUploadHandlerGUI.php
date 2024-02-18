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
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Data\DataSize;

/**
 *
 * @author Stephan Kergomard <webmaster@kergomard.ch>
 */
class ImportUploadHandlerGUI extends AbstractCtrlAwareUploadHandler implements \ilCtrlBaseClassInterface
{
    protected Filesystem $temp_system;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->temp_system = $DIC->filesystem()->temp();
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        $tempname = '';
        if ($result instanceof UploadResult
            && in_array($result->getMimeType(), ilObjectGUI::SUPPORTED_IMPORT_MIME_TYPES)
            && $result->isOK()) {
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
            $tempname = $this->moveUploadedFileToTemp($result);
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $tempname,
            $message
        );
    }

    protected function getRemoveResult(string $filename): HandlerResult
    {
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $filename,
            "We just don't do anything here."
        );
    }

    public function getInfoResult(string $file_name): ?FileInfoResult
    {
        if ($this->temp_system->hasDir($file_name)
            && ($files = $this->temp_system->listContents($file_name))) {
            $path = $files[0]->getPath();
            $filename = basename($path);
            $title = $filename;
            $mime = $this->temp_system->getMimeType($path);
            $size = intval($this->temp_system->getSize($path, DataSize::Byte)->inBytes());

        } else {
            $title = $mime = 'unknown';
            $size = 0;
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $file_name,
            $title,
            $size,
            $mime
        );
    }

    /**
     * @return \ILIAS\FileUpload\Handler\BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_names): array
    {
        return [$this->getInfoResult($file_names[0])];
    }

    protected function moveUploadedFileToTemp(UploadResult $result): string
    {
        $tempfile_path = uniqid('tmp');
        $this->temp_system->createDir($tempfile_path);
        $this->temp_system->put(
            $tempfile_path . DIRECTORY_SEPARATOR . ilFileUtils::getValidFilename($result->getName()),
            file_get_contents($result->getPath())
        );
        return $tempfile_path;
    }
}
