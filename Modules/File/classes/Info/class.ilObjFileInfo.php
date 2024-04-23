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

use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\Data\DataSize;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjFileInfo
{
    use ilObjFileSecureString;

    private string $title;
    protected string $file_name;
    protected string $suffix;
    protected bool $deliver_inline;
    protected bool $download_directly;
    protected int $version;
    protected DateTimeImmutable $creation_date;
    protected bool $is_zip;
    protected string $mime_type;
    protected DataSize $file_size;
    protected ?int $page_count;

    public function __construct(
        string $title,
        string $file_name,
        string $suffix,
        bool $deliver_inline,
        bool $download_directly,
        int $version,
        DateTimeImmutable $creation_date,
        bool $is_zip,
        string $mime_type,
        DataSize $file_size,
        ?int $page_count
    ) {
        $this->title = $title;
        $this->file_name = $file_name;
        $this->suffix = $suffix;
        $this->deliver_inline = $deliver_inline;
        $this->download_directly = $download_directly;
        $this->version = $version;
        $this->creation_date = $creation_date;
        $this->is_zip = $is_zip;
        $this->mime_type = $mime_type;
        $this->file_size = $file_size;
        $this->page_count = $page_count;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getListTitle(): string
    {
        return $this->stripSuffix($this->title, $this->getSuffix());
    }

    public function getHeaderTitle(): string
    {
        return $this->ensureSuffix($this->title, $this->getSuffix());
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function shouldDeliverInline(): bool
    {
        return $this->deliver_inline;
    }

    public function shouldDownloadDirectly(): bool
    {
        return $this->download_directly;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function isZip(): bool
    {
        return $this->is_zip;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getFileSize(): DataSize
    {
        return $this->file_size;
    }

    public function getPageCount(): ?int
    {
        return $this->page_count;
    }

}
