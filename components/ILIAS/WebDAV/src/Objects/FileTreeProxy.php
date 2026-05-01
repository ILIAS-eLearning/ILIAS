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

namespace ILIAS\WebDAV\Objects;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FileTreeProxy extends TreeProxy implements FileProxy
{
    public function __construct(
        int $ref_id,
        int $obj_id,
        string $title,
        int $last_update,
        protected ?string $content_type = '',
        protected ?int $size = 0,
        protected ?StreamHandler $stream_resolver = null,
    ) {
        parent::__construct(
            $ref_id,
            $obj_id,
            $title,
            $last_update,
            Type::FILE
        );
    }

    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    public function getStreamHandler(): ?StreamHandler
    {
        return $this->stream_resolver;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

}
