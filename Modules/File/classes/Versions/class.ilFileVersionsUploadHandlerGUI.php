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

use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareIRSSUploadHandler;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * Class ilFileVersionsUploadHandlerGUI
 *
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFileVersionsUploadHandlerGUI : ilFileVersionsGUI
 */
class ilFileVersionsUploadHandlerGUI extends AbstractCtrlAwareIRSSUploadHandler
{
    public const MODE_APPEND = 'append';
    public const MODE_REPLACE = 'replace';
    public const P_UPLOAD_MODE = 'upload_mode';
    protected bool $append;
    protected string $upload_mode = self::MODE_APPEND;

    public function __construct(protected ilObjFile $file, string $upload_mode = self::MODE_APPEND)
    {
        global $DIC;
        parent::__construct();
        $this->upload_mode = $this->http->wrapper()->query()->has(self::P_UPLOAD_MODE)
            ? $this->http->wrapper()->query()->retrieve(self::P_UPLOAD_MODE, $DIC->refinery()->kindlyTo()->string())
            : $upload_mode;

        $this->ctrl->setParameter($this, self::P_UPLOAD_MODE, $this->upload_mode);
    }

    protected function getStakeholder(): ResourceStakeholder
    {
        global $DIC;
        return new ilObjFileStakeholder($DIC->user()->getId());
    }

    protected function getClassPath(): array
    {
        return [self::class];
    }

    public function supportsChunkedUploads(): bool
    {
        return true;
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->register(new ilCountPDFPagesPreProcessors());
        $this->upload->process();
        $upload_mode =

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult && $result->isOK()) {
            if ($this->is_chunked) {
                return $this->processChunckedUpload($result);
            }

            if ($this->upload_mode === self::MODE_REPLACE) {
                $identifier = (string) $this->file->replaceWithUpload($result, $result->getName());
            } else {
                $identifier = (string) $this->file->appendUpload($result, $result->getName());
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
            if ($this->upload_mode === self::MODE_REPLACE) {
                $revision_number = $this->file->replaceWithStream($whole_file, $result->getName());
            } else {
                $revision_number = $this->file->appendStream($whole_file, $result->getName());
            }

            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                (string) $revision_number,
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

}
