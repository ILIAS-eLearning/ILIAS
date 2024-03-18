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

namespace ILIAS\components\ResourceStorage\Container\Wrapper;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
final class File extends Item
{
    public function __construct(
        private string $path_inside_zip,
        private string $title,
        private string $mime_type,
        private int $size,
        private \DateTimeImmutable $modification_date,
    ) {
        parent::__construct($path_inside_zip, $title);
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getModificationDate(): \DateTimeImmutable
    {
        return $this->modification_date;
    }

}
