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

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;
use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class UploadInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class UploadInfoResolver extends AbstractInfoResolver implements InfoResolver
{
    protected string $path;
    protected string $file_name;
    protected string $suffix;
    protected string $mime_type;
    protected \DateTimeImmutable $creation_date;
    protected UploadResult $upload;

    public function __construct(
        UploadResult $upload,
        int $next_version_number,
        int $revision_owner_id,
        string $revision_title
    ) {
        $this->upload = $upload;
        parent::__construct($next_version_number, $revision_owner_id, $revision_title);
        $this->file_name = $upload->getName();
        $this->suffix = pathinfo($this->file_name, PATHINFO_EXTENSION);
        $this->mime_type = $upload->getMimeType();
        $this->creation_date = new \DateTimeImmutable();
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return $this->creation_date;
    }

    public function getSize(): int
    {
        return $this->upload->getSize();
    }
}
