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
    protected \ilLanguage $language;
    protected \ILIAS\ResourceStorage\Services $irss;
    protected ResourceStakeholder $stakeholder;
    protected \ILIAS\Filesystem\Filesystem $temp_filesystem;
    protected array $class_path;

    public function __construct()
    {
        global $DIC;

        $this->irss = $DIC->resourceStorage();
        $this->stakeholder = $this->getStakeholder();
        $this->temp_filesystem = $DIC->filesystem()->temp();
        $this->class_path = $this->getClassPath();
        $this->language = $DIC->language();

        parent::__construct();
    }

    abstract protected function getStakeholder(): ResourceStakeholder;

    abstract protected function getClassPath(): array;

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process(); // Process the uploads to rund things like PreProcessors

        $result_array = $this->upload->getResults();
        $result = end($result_array); // get the last result aka the Upload of the user

        if ($result instanceof UploadResult && $result->isOK()) {
            if ($this->is_chunked) {
                return $this->processChunckedUpload($result);
            }

            $identifier = $this->irss->manage()->upload($result, $this->stakeholder)->serialize();
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        } else {
            $identifier = '';
            $status = HandlerResult::STATUS_FAILED;
            $message = $this->language->txt('msg_info_blacklisted'); // this is the most common reason for a failed upload
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function processChunckedUpload(UploadResult $result): HandlerResult
    {
        $temp_path = "$this->chunk_id/{$result->getName()}";

        try {
            if ($this->temp_filesystem->has($temp_path)) {
                $stream = fopen($this->temp_filesystem->readStream($temp_path)->getMetadata()['uri'], 'ab');
                fwrite($stream, file_get_contents($result->getPath()));
            } else {
                $this->temp_filesystem->write($temp_path, file_get_contents($result->getPath()));
            }
        } catch (Throwable $t) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $t->getMessage()
            );
        }

        if (($this->chunk_index + 1) === $this->amount_of_chunks) {
            $whole_file = $this->temp_filesystem->readStream($temp_path);
            $id = $this->irss->manage()->stream($whole_file, $this->stakeholder, $result->getName());

            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                $id->serialize(),
                "file upload OK"
            );
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_PARTIAL,
            '',
            "chunk upload OK"
        );
    }

    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass($this->class_path, self::CMD_UPLOAD, null, true);
    }

    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass($this->class_path, self::CMD_INFO, null, true);
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        if (null !== ($id = $this->irss->manage()->find($identifier))) {
            $revision = $this->irss->manage()->getCurrentRevision($id)->getInformation();
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
