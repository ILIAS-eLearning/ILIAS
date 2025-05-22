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

use ILIAS\Data\DataSize;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjFileInfo
{
    use ilObjFileSecureString;

    public function __construct(protected string $title, protected ?ResourceIdentification $rid, protected string $file_name, protected string $suffix, protected bool $deliver_inline, protected bool $download_directly, protected int $version, protected DateTimeImmutable $creation_date, protected bool $is_zip, protected string $mime_type, protected DataSize $file_size, protected ?int $page_count)
    {
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
        return $this->stripSuffix($this->title, $this->getSuffix());
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function getRID(): ?ResourceIdentification
    {
        return $this->rid;
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
