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

namespace ILIAS\components\ResourceStorage;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;

/**
 * Reassembles an upload the dropzone sent in chunks.
 *
 * A chunk cannot be stored on its own: every chunk of a file carries the same
 * name, so whatever stored it would overwrite what the chunk before it
 * produced. They are appended to a single file in the temp filesystem, and only
 * once the last chunk has arrived is that file handed to the using handler,
 * under the name it was uploaded with.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ChunkedUploadBuffer
{
    /**
     * Chunks of an unfinished upload are buffered below this directory in the
     * temp filesystem, one sub-directory per upload.
     */
    private const CHUNK_DIRECTORY = 'irss_chunked_uploads';

    private Filesystem $chunk_filesystem;
    private \ilLogger $chunk_logger;
    private \ilLanguage $chunk_language;
    private int $chunk_user_id;

    private bool $is_chunked = false;
    private string $uuid = '';
    private int $chunk_index = 0;
    private int $amount_of_chunks = 0;
    private int $chunk_byte_offset = 0;
    private int $chunk_total_size = 0;

    /**
     * Provided by the UploadHandler both users of this trait implement. It names
     * the field an identifier is reported back in.
     */
    abstract public function getFileIdentifierParameterName(): string;

    protected function initChunkedUploadBuffer(): void
    {
        global $DIC;
        $this->chunk_filesystem = $DIC->filesystem()->temp();
        $this->chunk_logger = \ilLoggerFactory::getLogger('irss');
        $this->chunk_language = $DIC->language();
        $this->chunk_user_id = $DIC->user()->getId();
    }

    /**
     * The dropzone announces a chunked upload with these fields in the body of
     * every single chunk request.
     */
    protected function readChunkInformation(): void
    {
        global $DIC;
        $body = $DIC->http()->request()->getParsedBody();

        $uuid = (string) ($body['dzuuid'] ?? '');
        $amount_of_chunks = (int) ($body['dztotalchunkcount'] ?? 0);

        // the uuid is used as a directory name, therefore nothing but the format
        // the dropzone generates is accepted
        if ($amount_of_chunks < 1 || preg_match('/^[0-9a-f-]{36}$/i', $uuid) !== 1) {
            return;
        }

        $this->is_chunked = true;
        $this->uuid = $uuid;
        $this->amount_of_chunks = $amount_of_chunks;
        $this->chunk_index = (int) ($body['dzchunkindex'] ?? 0);
        $this->chunk_byte_offset = (int) ($body['dzchunkbyteoffset'] ?? 0);
        $this->chunk_total_size = (int) ($body['dztotalfilesize'] ?? 0);
    }

    protected function isChunkedUpload(): bool
    {
        return $this->is_chunked;
    }

    /**
     * Buffers the chunk of the current request and, once it was the last one,
     * hands the reassembled file to $store.
     *
     * @param UploadResult[] $upload_results the results of the current request
     * @param callable(string, string): ?string $store receives the path of the
     *        reassembled file and the name it was uploaded under, and returns
     *        the identifier to report back, or null if it did not store it. The
     *        file is removed as soon as $store returns.
     */
    protected function assembleChunk(array $upload_results, callable $store): BasicHandlerResult
    {
        $result = end($upload_results);
        if (!$result instanceof UploadResult) {
            return $this->chunkFailed('the request carried no upload result');
        }
        if (!$result->isOK()) {
            $this->logChunkFailure('rejected while processing: ' . $result->getStatus()->getMessage());
            return $this->discardChunks($this->failedResult($result->getStatus()->getMessage()));
        }

        // A fixed name keeps the uploaded file name out of the path the chunks are
        // written to. It has to end in the clean suffix: the temp filesystem is
        // wrapped in a FilesystemWhitelistDecorator, which rewrites the suffix of
        // anything written to it but leaves the path of a read untouched - a
        // buffer under any other suffix could be written but never read back.
        $part_file = $this->getChunkDirectory() . '/upload.' . FilenameSanitizer::CLEAN_FILE_SUFFIX;

        try {
            if (!$this->chunk_filesystem->has($part_file)) {
                $this->chunk_filesystem->write($part_file, '');
            }
            $part_path = $this->getLocalPath($part_file);

            // the dropzone sends the chunks of a file strictly in order, an
            // offset that does not match what has been written so far means the
            // assembled file would be garbage
            clearstatcache(true, $part_path);
            $buffered = filesize($part_path);
            if ($buffered !== $this->chunk_byte_offset) {
                return $this->chunkFailed(
                    "chunk starts at byte $this->chunk_byte_offset but $buffered bytes are buffered"
                );
            }

            $this->appendToFile($result->getPath(), $part_path);

            if (($this->chunk_index + 1) < $this->amount_of_chunks) {
                return new BasicHandlerResult(
                    $this->getFileIdentifierParameterName(),
                    BasicHandlerResult::STATUS_PARTIAL,
                    '',
                    'chunk upload OK'
                );
            }

            clearstatcache(true, $part_path);
            $assembled_size = filesize($part_path);
            if ($assembled_size !== $this->chunk_total_size) {
                return $this->chunkFailed(
                    "assembled $assembled_size bytes, the upload announced $this->chunk_total_size"
                );
            }

            // The name decides what the file ends up being called, so the
            // assembled file has to carry the uploaded one and be alone in its
            // directory. Moving it is done on the local path: going through the
            // temp filesystem would hand the name to the whitelist decorator,
            // which would rewrite the suffix the user uploaded.
            $file_name = basename($result->getName());
            $assembly_directory = dirname($part_path) . '/file';
            $assembled_path = $assembly_directory . '/' . $file_name;

            if (!is_dir($assembly_directory) && !mkdir($assembly_directory, 0700, true) && !is_dir($assembly_directory)) {
                return $this->chunkFailed("could not create the directory '$assembly_directory'");
            }
            if (!rename($part_path, $assembled_path)) {
                return $this->chunkFailed("could not move the assembled file to '$assembled_path'");
            }

            $identifier = $store($assembled_path, $file_name);
            if ($identifier === null) {
                return $this->chunkFailed('the assembled file was not stored');
            }

            return $this->discardChunks(
                new BasicHandlerResult(
                    $this->getFileIdentifierParameterName(),
                    BasicHandlerResult::STATUS_OK,
                    $identifier,
                    'file upload OK'
                )
            );
        } catch (\Throwable $exception) {
            return $this->chunkFailed($exception::class . ': ' . $exception->getMessage());
        }
    }

    protected function failedResult(string $message = ''): BasicHandlerResult
    {
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            BasicHandlerResult::STATUS_FAILED,
            '',
            $message !== '' ? $message : $this->chunk_language->txt('rids_appended_failed')
        );
    }

    /**
     * Chunks are kept apart per user: the uuid identifying an upload is chosen by
     * the client and must not allow one user to write into the upload of another.
     */
    private function getChunkDirectory(): string
    {
        return self::CHUNK_DIRECTORY . '/' . $this->chunk_user_id . '/' . $this->uuid;
    }

    private function getLocalPath(string $path_in_temp_filesystem): string
    {
        $stream = $this->chunk_filesystem->readStream($path_in_temp_filesystem);
        $uri = $stream->getMetadata()['uri'];
        $stream->close();

        return (string) $uri;
    }

    private function appendToFile(string $source_path, string $target_path): void
    {
        $source = fopen($source_path, 'rb');
        $target = fopen($target_path, 'ab');

        try {
            if ($source === false || $target === false) {
                throw new \RuntimeException("Could not append '$source_path' to '$target_path'.");
            }
            stream_copy_to_stream($source, $target);
        } finally {
            if ($source !== false) {
                fclose($source);
            }
            if ($target !== false) {
                fclose($target);
            }
        }
    }

    /**
     * Removes everything buffered for the current upload, whether it completed
     * or failed. Chunks of an upload the user abandoned are never reported here
     * and stay behind until the temp directory is cleaned up.
     */
    private function discardChunks(BasicHandlerResult $result): BasicHandlerResult
    {
        try {
            if ($this->chunk_filesystem->hasDir($this->getChunkDirectory())) {
                $this->chunk_filesystem->deleteDir($this->getChunkDirectory());
            }
        } catch (\Throwable) {
            // a leftover directory must not turn a successful upload into a failure
        }

        return $result;
    }

    /**
     * Every way a chunked upload can fail ends up here. Without it the user is
     * left with nothing but a generic message and no trace of what went wrong.
     */
    private function chunkFailed(string $reason): BasicHandlerResult
    {
        $this->logChunkFailure($reason);

        return $this->discardChunks($this->failedResult());
    }

    private function logChunkFailure(string $reason): void
    {
        $this->chunk_logger->warning(sprintf(
            'chunked upload %s of user %d failed on chunk %d of %d: %s',
            $this->uuid,
            $this->chunk_user_id,
            $this->chunk_index + 1,
            $this->amount_of_chunks,
            $reason
        ));
    }
}
