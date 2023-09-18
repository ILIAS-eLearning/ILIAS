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

namespace ILIAS\Filesystem\Stream;

/**
 * The streaming options are used by the stream implementation.
 * This class only hold configuration options which can be used by the Stream class.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class StreamOptions
{
    public const UNKNOWN_STREAM_SIZE = -1;

    /**
     * StreamOptions constructor.
     *
     * @param \string[] $metadata Additional metadata for the stream.
     * @param int       $size     The known stream size in byte. -1 indicates an unknown size.
     */
    public function __construct(private array $metadata = [], private int $size = self::UNKNOWN_STREAM_SIZE)
    {
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return \string[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
