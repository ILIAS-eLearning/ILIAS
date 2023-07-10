<?php

declare(strict_types=1);

namespace ILIAS\FileUpload\Handler;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Services as HttpServices;
use ilCtrl;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilCtrlAwareUploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractCtrlAwareUploadHandler implements ilCtrlAwareUploadHandler
{
    protected const CMD_UPLOAD = 'upload';
    protected const CMD_REMOVE = 'remove';
    protected const CMD_INFO = 'info';

    protected HttpServices $http;
    protected ilCtrl $ctrl;
    protected FileUpload $upload;

    protected bool $is_chunked = false;
    protected int $chunk_index = 0;
    protected int $amount_of_chunks = 0;
    protected ?string $chunk_id = null;
    protected int $chunk_total_size = 0;


    /**
     * ilUIDemoFileUploadHandlerGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->upload = $DIC->upload();
        $this->http = $DIC->http();
    }


    /**
     * @inheritDoc
     */
    public function getFileIdentifierParameterName(): string
    {
        return self::DEFAULT_FILE_ID_PARAMETER;
    }


    /**
     * @inheritDoc
     */
    public function getUploadURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([static::class], self::CMD_UPLOAD);
    }


    /**
     * @inheritDoc
     */
    public function getExistingFileInfoURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([static::class], self::CMD_INFO);
    }


    /**
     * @inheritDoc
     */
    public function getFileRemovalURL(): string
    {
        return $this->ctrl->getLinkTargetByClass([static::class], self::CMD_REMOVE);
    }

    protected function readChunkedInformation(): void
    {
        $body = $this->http->request()->getParsedBody();
        $this->chunk_id = $body['dzuuid'] ?? null;
        $this->amount_of_chunks = (int) ($body['dztotalchunkcount'] ?? 0);
        $this->chunk_index = (int) ($body['dzchunkindex'] ?? 0);
        $this->chunk_total_size = (int) ($body['dztotalfilesize'] ?? 0);
        $this->is_chunked = ($this->chunk_id !== null && $this->amount_of_chunks > 0);
    }


    public function executeCommand(): void
    {
        switch ($this->ctrl->getCmd()) {
            case self::CMD_UPLOAD:
                // Here you must save the file and tell the input item the
                // file-id which will be a FileStorage-ID in a later version
                // of ILIAS and for now you must implement an own ID which allows
                // identifying the file after the request
                try {
                    $this->readChunkedInformation();
                    $content = json_encode($this->getUploadResult());
                } catch (\Throwable $t) {
                    $content = json_encode(
                        new BasicHandlerResult(
                            $this->getFileIdentifierParameterName(),
                            BasicHandlerResult::STATUS_FAILED,
                            '',
                            $t->getMessage()
                        )
                    );
                }
                break;
            case self::CMD_REMOVE:
                // here you delete the previously uploaded file again, you know
                // which file to delete since you defined what 'my_file_id' is.
                $file_identifier = $this->http->request()->getQueryParams()[$this->getFileIdentifierParameterName()];
                $content = json_encode($this->getRemoveResult($file_identifier));
                break;
            case self::CMD_INFO:
                // here you give info about an already existing file
                // return a JsonEncoded \ILIAS\FileUpload\Handler\FileInfoResult
                $file_identifier = $this->http->request()->getQueryParams()[$this->getFileIdentifierParameterName()];
                $content = json_encode($this->getInfoResult($file_identifier));
                break;
            default:
                $content = '';
                break;
        }
        $response = $this->http->response()->withBody(Streams::ofString($content));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
        $this->http->close();
    }


    abstract protected function getUploadResult(): HandlerResult;


    abstract protected function getRemoveResult(string $identifier): HandlerResult;


    abstract public function getInfoResult(string $identifier): ?FileInfoResult;


    abstract public function getInfoForExistingFiles(array $file_ids): array;

    public function supportsChunkedUploads(): bool
    {
        return false;
    }

}
