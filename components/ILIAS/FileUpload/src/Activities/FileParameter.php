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

namespace ILIAS\FileUpload\Activities;

use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessorManager;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;

/**
 * A file as a parameter of an activity: how it is described, and how it is read.
 *
 * Every activity that takes a file uses this, so all of them look the same from
 * outside and none of them handles bytes on its own. It describes two fields -
 * the name of the file and its content, base64 encoded - and turns them back
 * into a FileContent the activity can work with.
 *
 * Base64 is the wire format because it is the one every transport can carry:
 * JSON knows no bytes, and SOAP encodes them the same way. The REST service
 * accepts a multipart upload as well and encodes it before the activity sees
 * it, which costs the activity nothing.
 *
 * The limits belong here too. A file field of the UI framework carries a
 * maximum size and a list of accepted mime types, and an activity taking a file
 * should be able to say the same - hence withMaxFileSize() and
 * withAcceptedMimeTypes(), named after their counterparts in the UI. The upload
 * limit of the installation applies on top of them.
 *
 * A file that arrives this way is a file upload like any other, so it passes
 * the same preprocessors an upload through the FileUpload service passes - the
 * virus scanner and the checks for insecure files among them.
 */
class FileParameter
{
    public const string DEFAULT_CONTENT_NAME = 'content';
    public const string DEFAULT_FILENAME_NAME = 'filename';

    /**
     * @param string[] $accepted_mime_types
     */
    public function __construct(
        private readonly TempFileStore $files,
        private readonly PreProcessorManager $processors,
        private readonly Parameter $parameter,
        private readonly ?GlobalUploadLimit $upload_limit = null,
        private readonly string $content_name = self::DEFAULT_CONTENT_NAME,
        private readonly string $filename_name = self::DEFAULT_FILENAME_NAME,
        private readonly int $max_file_size = 0,
        private readonly array $accepted_mime_types = [],
    ) {
    }

    /**
     * Use different parameter names, e.g. for an activity taking two files.
     */
    public function withNames(string $content_name, string $filename_name): self
    {
        return new self(
            $this->files,
            $this->processors,
            $this->parameter,
            $this->upload_limit,
            $content_name,
            $filename_name,
            $this->max_file_size,
            $this->accepted_mime_types
        );
    }

    /**
     * @param int $size_in_bytes 0 means no limit of our own, the limits of the
     *                           installation still apply
     */
    public function withMaxFileSize(int $size_in_bytes): self
    {
        return new self(
            $this->files,
            $this->processors,
            $this->parameter,
            $this->upload_limit,
            $this->content_name,
            $this->filename_name,
            $size_in_bytes,
            $this->accepted_mime_types
        );
    }

    public function getMaxFileSize(): int
    {
        return $this->max_file_size;
    }

    /**
     * @param string[] $mime_types an empty list accepts anything
     */
    public function withAcceptedMimeTypes(array $mime_types): self
    {
        return new self(
            $this->files,
            $this->processors,
            $this->parameter,
            $this->upload_limit,
            $this->content_name,
            $this->filename_name,
            $this->max_file_size,
            array_values(array_map(strtolower(...), $mime_types))
        );
    }

    /**
     * @return string[]
     */
    public function getAcceptedMimeTypes(): array
    {
        return $this->accepted_mime_types;
    }

    /**
     * The fields describing this file, to be merged into the input description
     * of the activity.
     *
     * @return array<string, FormInput>
     */
    public function describe(FieldFactory $f, string $label = 'File', ?string $purpose = null): array
    {
        return [
            $this->filename_name => $f->text(
                $label . ' name',
                'Name of the file, including its suffix.'
            )->withRequired(true)->withDedicatedName($this->filename_name),

            $this->content_name => $f->textarea(
                $label,
                $this->byline($purpose)
            )->withRequired(true)->withDedicatedName($this->content_name),
        ];
    }

    /**
     * Reads the file out of the raw parameters of a request.
     *
     * The content is put into the temp storage right here, because that is
     * where it has to be for anything to store it. Whoever calls this owns the
     * result and has to release() it.
     *
     * @param array<string, mixed> $raw_parameters
     */
    public function read(array $raw_parameters): FileContent
    {
        $filename = (string) $this->parameter->string($raw_parameters, $this->filename_name);

        if ($filename === '') {
            throw new \InvalidArgumentException('A file name is required.', 400);
        }

        $encoded = $raw_parameters[$this->content_name] ?? null;

        if (!is_string($encoded) || $encoded === '') {
            throw new \InvalidArgumentException('The content of the file is required.', 400);
        }

        $limit = $this->getEffectiveLimit();

        // refuse before decoding: base64 is a third larger than what it carries
        if ($limit > 0 && (strlen($encoded) / 4) * 3 > $limit + 3) {
            throw $this->tooLarge($limit);
        }

        $content = base64_decode($encoded, true);

        if ($content === false) {
            throw new \InvalidArgumentException('The content is not valid base64.', 400);
        }

        if ($limit > 0 && strlen($content) > $limit) {
            throw $this->tooLarge($limit);
        }

        return $this->accept($this->files->store($filename, $content));
    }

    private function accept(string $handle): FileContent
    {
        $file = new FileContent(
            $this->files,
            $handle,
            $this->files->getFilename($handle),
            $this->files->getMimeType($handle),
            $this->files->getSize($handle),
        );

        try {
            $this->process($file);
        } catch (\Throwable $e) {
            $file->release();

            throw $e;
        }

        return $file;
    }

    /**
     * The same checks an upload through the FileUpload service gets: the
     * preprocessors other components contribute, then our own mime types.
     */
    private function process(FileContent $file): void
    {
        $stream = $file->stream();

        try {
            $status = $this->processors->process(
                $stream,
                new Metadata($file->filename, $file->size, $file->mime_type)
            );
        } finally {
            $stream->close();
        }

        // the reason stays inside, as in the GUI: a processor that failed hands
        // out the message of its exception, which may name paths of the server
        if (in_array($status->getCode(), [ProcessingStatus::REJECTED, ProcessingStatus::DENIED], true)) {
            throw new \InvalidArgumentException('The file has been rejected.', 400);
        }

        if ($this->accepted_mime_types !== [] && !in_array($file->mime_type, $this->accepted_mime_types, true)) {
            throw new \InvalidArgumentException(
                "Files of the type '{$file->mime_type}' are not accepted here.",
                400
            );
        }
    }

    /**
     * The smaller one of our own limit and the one of the installation, which
     * may depend on the user and is therefore asked at the time of the request.
     * 0 means no limit besides what PHP accepts as a request.
     */
    private function getEffectiveLimit(): int
    {
        $limits = array_filter(
            [$this->max_file_size, (int) $this->upload_limit?->getGlobalUploadLimitInBytes()],
            static fn(int $limit): bool => $limit > 0
        );

        return $limits === [] ? 0 : min($limits);
    }

    private function tooLarge(int $limit): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            "The file is larger than the {$limit} bytes accepted here.",
            400
        );
    }

    private function byline(?string $purpose): string
    {
        $byline = $purpose ?? 'The content of the file';
        $byline = rtrim($byline, '.') . ', base64 encoded.';

        if ($this->max_file_size > 0) {
            $byline .= " At most {$this->max_file_size} bytes.";
        }

        if ($this->accepted_mime_types !== []) {
            $byline .= ' Accepted types: ' . implode(', ', $this->accepted_mime_types) . '.';
        }

        return $byline;
    }
}
