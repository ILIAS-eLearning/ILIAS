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

namespace ILIAS\Exercise\IRSS;

class ResourceInformation
{
    protected string $rid;
    protected string $src;
    protected string $mime_type;
    protected int $creation_timestamp;
    protected int $size;
    protected string $title;

    public function __construct(
        string $rid,
        string $title,
        int $size,
        int $creation_timestamp,
        string $mime_type,
        string $src
    ) {
        $this->rid = $rid;
        $this->title = $title;
        $this->size = $size;
        $this->creation_timestamp = $creation_timestamp;
        $this->mime_type = $mime_type;
        $this->src = $src;
    }

    public function getRid(): string
    {
        return $this->rid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getCreationTimestamp(): int
    {
        return $this->creation_timestamp;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getSrc(): string
    {
        return $this->src;
    }
}
