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

namespace ILIAS\Questions\Presentation\Layout\Tools;

use ILIAS\Questions\Presentation\Definitions\Actor;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\UI\Component\Input\Field\UploadHandler as UploadHandlerInterface;

class UploadHandler implements UploadHandlerInterface, Actor
{
    private const string SUB_ACTION_UPLOAD = 'uhu';
    private const string SUB_ACTION_REMOVE = 'uhr';
    private const string SUB_ACTION_INFO = 'uhi';

    private bool $is_chunked = false;
    private int $chunk_index = 0;
    private int $amount_of_chunks = 0;
    private ?string $chunk_id = null;
    private int $chunk_total_size = 0;

    public function __construct(
        private readonly IRSS $irss,
        private readonly Filesystem $temp_filesystem,
        private readonly FileUpload $upload,
        private readonly \ilFileServicesFilenameSanitizer $sanitizer,
        private readonly ResourceStakeholder $stakeholder,
        private readonly Environment $environment
    ) {
    }

    #[\Override]
    public function getFileIdentifierParameterName(): string
    {
        return self::DEFAULT_FILE_ID_PARAMETER;
    }

    #[\Override]
    public function getUploadURL(): string
    {
        return $this->environment
            ->withSubActionParameter(self::SUB_ACTION_UPLOAD)
            ->getUrlBuilder()
            ->buildURI()
            ->__toString();
    }

    #[\Override]
    public function getFileRemovalURL(): string
    {
        return $this->environment
            ->withSubActionParameter(self::SUB_ACTION_REMOVE)
            ->getUrlBuilder()
            ->buildURI()
            ->__toString();
    }

    #[\Override]
    public function getExistingFileInfoURL(): string
    {
        return $this->environment
            ->withSubActionParameter(self::SUB_ACTION_INFO)
            ->getUrlBuilder()
            ->buildURI()
            ->__toString();
    }

    #[\Override]
    public function getInfoForExistingFiles(
        array $file_ids
    ): array {
        return array_map(
            fn($file_id): FileInfoResult => $this->getInfoResult($file_id),
            $file_ids
        );
    }

    #[\Override]
    public function getInfoResult(
        string $identifier
    ): ?FileInfoResult {
        $title = $mime = 'unknown';
        $size = 0;
        $id = $this->irss->manage()->find($identifier);
        if ($id !== null) {
            $revision = $this->irss->manage()->getCurrentRevision($id)->getInformation();
            $title = $revision->getTitle();
            $size = $revision->getSize();
            $mime = $revision->getMimeType();
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $title,
            $size,
            $mime
        );
    }

    #[\Override]
    public function supportsChunkedUploads(): bool
    {
        return true;
    }

    #[\Override]
    public function can(
        string $sub_action
    ): bool {
        $has_file_identifier = $this->hasFileIdentifier();

        return $sub_action === self::SUB_ACTION_UPLOAD
            || $sub_action === self::SUB_ACTION_REMOVE && $has_file_identifier
            || $sub_action === self::SUB_ACTION_INFO && $has_file_identifier;
    }

    #[\Override]
    public function do(
        string $action
    ): Async {
        $response = match($action) {
            self::SUB_ACTION_UPLOAD => $this->upload(),
            self::SUB_ACTION_REMOVE => $this->remove(),
            self::SUB_ACTION_INFO => $this->info(),
            default => ''
        };

        return $this->environment->getPresentationFactory()->getAsync($response);
    }

    private function upload(): string
    {
        try {
            $this->readChunkedInformation();
            return json_encode($this->getUploadResult());
        } catch (\Throwable $t) {
            return json_encode(
                new BasicHandlerResult(
                    $this->getFileIdentifierParameterName(),
                    BasicHandlerResult::STATUS_FAILED,
                    '',
                    $t->getMessage()
                )
            );
        }
    }

    private function remove(): string
    {
        return json_encode(
            $this->getRemoveResult(
                $this->retrieveFileIdentifier()
            )
        );
    }

    private function info(): string
    {
        return json_encode(
            $this->getInfoResult(
                $this->retrieveFileIdentifier()
            )
        );
    }

    private function readChunkedInformation(): void
    {
        $body = $this->environment->getHttpServices()->request()->getParsedBody();
        $this->chunk_id = $body['dzuuid'] ?? null;
        $this->amount_of_chunks = (int) ($body['dztotalchunkcount'] ?? 0);
        $this->chunk_index = (int) ($body['dzchunkindex'] ?? 0);
        $this->chunk_total_size = (int) ($body['dztotalfilesize'] ?? 0);
        $this->is_chunked = ($this->chunk_id !== null && $this->amount_of_chunks > 0);
    }

    private function getUploadResult(): HandlerResult
    {
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        $identifier = '';
        $status = HandlerResult::STATUS_FAILED;
        $message = $this->environment->getLanguage()->txt(
            'msg_info_blacklisted'
        );
        if ($result instanceof UploadResult && $result->isOK()) {
            if ($this->is_chunked) {
                return $this->processChunckedUpload($result);
            }

            $identifier = $this->irss->manage()->upload(
                $result,
                $this->stakeholder
            )->serialize();
            $status = HandlerResult::STATUS_OK;
            $message = "file upload OK";
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }

    private function getRemoveResult(
        string $identifier
    ): HandlerResult {
        $status = HandlerResult::STATUS_OK;
        $message = "file with identifier '{$identifier}' doesn't exist, nothing to do.";

        $id = $this->irss->manage()->find($identifier);
        if ($id !== null) {
            $this->irss->manage()->remove(
                $id,
                $this->stakeholder
            );
            $status = HandlerResult::STATUS_OK;
            $message = "file removal OK";
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $identifier,
            $message
        );
    }

    private function processChunckedUpload(
        UploadResult $result
    ): HandlerResult {
        $temp_path = $this->sanitizer->sanitize(
            "{$this->chunk_id}/{$result->getName()}"
        );

        try {
            $this->writeChunkedTempFile(
                $result,
                $temp_path
            );
        } catch (\Throwable $t) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $t->getMessage()
            );
        }

        if (($this->chunk_index + 1) === $this->amount_of_chunks) {
            return $this->storeChunkedUpload(
                $result,
                $temp_path
            );
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_PARTIAL,
            '',
            'chunk upload OK'
        );
    }

    private function writeChunkedTempFile(
        UploadResult $result,
        string $temp_path
    ): void {
        if ($this->temp_filesystem->has($temp_path)) {
            fwrite(
                fopen(
                    $this->temp_filesystem->readStream($temp_path)
                        ->getMetadata()['uri'],
                    'ab'
                ),
                file_get_contents(
                    $result->getPath()
                )
            );
            return;
        }

        $this->temp_filesystem->write(
            $temp_path,
            file_get_contents(
                $result->getPath()
            )
        );
    }

    private function storeChunkedUpload(
        UploadResult $result,
        string $temp_path
    ): HandlerResult {
        $id = $this->irss->manage()->stream(
            $this->temp_filesystem->readStream($temp_path),
            $this->stakeholder,
            $result->getName()
        );

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $id->serialize(),
            'file upload OK'
        );
    }

    private function hasFileIdentifier(): bool
    {
        return $this->environment
            ->getHttpServices()
            ->wrapper()
            ->query()
            ->has($this->getFileIdentifierParameterName());
    }

    private function retrieveFileIdentifier(): string
    {
        if (!$this->hasFileIdentifier()) {
            return '';
        }

        return $this->environment
            ->getHttpServices()
            ->wrapper()
            ->query()
            ->retrieve(
                $this->getFileIdentifierParameterName(),
                $this->environment->getRefinery()->kindlyTo()->string()
            );
    }
}
