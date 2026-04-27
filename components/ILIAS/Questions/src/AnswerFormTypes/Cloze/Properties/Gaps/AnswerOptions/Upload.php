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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions;

use ILIAS\Questions\Presentation\Definitions\Actor;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\UI\Component\Input\Field\UploadHandler;

class Upload implements UploadHandler, Actor
{
    private const string SUB_ACTION_UPLOAD = 'uhu';
    private const string SUB_ACTION_REMOVE = 'uhr';
    private const string SUB_ACTION_INFO = 'uhi';

    public function __construct(
        private readonly FileUpload $upload,
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
        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            'unknown',
            0,
            'unknown'
        );
    }

    #[\Override]
    public function supportsChunkedUploads(): bool
    {
        return false;
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
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if (!($result instanceof UploadResult) || !$result->isOK()) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $result->getStatus()->getMessage()
            );
        }

        $content = base64_encode(file_get_contents($result->getPath()));
        unlink($result->getPath());

        return json_encode(
            new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                $content,
                'file upload OK'
            )
        );
    }

    private function remove(): string
    {
        return json_encode(
            new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                $this->retrieveFileIdentifier(),
                'We just don\'t do anything here.'
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
