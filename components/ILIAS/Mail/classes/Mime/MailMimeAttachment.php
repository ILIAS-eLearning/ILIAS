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

namespace ILIAS\Mail\Mime;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

final class MailMimeAttachment
{
    private function __construct(
        private readonly ?string $path,
        private readonly ?ResourceIdentification $resource_identification,
        private readonly string $mime_type,
        private readonly string $disposition,
        private readonly string $display_name,
    ) {
    }

    public static function fromPath(
        string $path,
        string $mime_type = '',
        string $disposition = 'inline',
        ?string $display_name = null
    ): self {
        return new self(
            $path,
            null,
            $mime_type !== '' ? $mime_type : 'application/octet-stream',
            $disposition,
            $display_name ?? ''
        );
    }

    public static function fromResource(
        ResourceIdentification $resource_identification,
        string $display_name,
        string $mime_type = '',
        string $disposition = 'inline'
    ): self {
        return new self(
            null,
            $resource_identification,
            $mime_type !== '' ? $mime_type : 'application/octet-stream',
            $disposition,
            $display_name
        );
    }

    public function isResource(): bool
    {
        return $this->resource_identification !== null;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getResourceIdentification(): ?ResourceIdentification
    {
        return $this->resource_identification;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getDisposition(): string
    {
        return $this->disposition;
    }

    public function getDisplayName(): string
    {
        return $this->display_name;
    }
}
