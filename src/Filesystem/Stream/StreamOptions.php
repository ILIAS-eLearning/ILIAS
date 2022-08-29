<?php

declare(strict_types=1);

namespace ILIAS\Filesystem\Stream;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class StreamOptions
 *
 * The streaming options are used by the stream implementation.
 * This class only hold configuration options which can be used by the Stream class.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 * @see Stream
 * @internal
 */
final class StreamOptions
{
    public const UNKNOWN_STREAM_SIZE = -1;

    private int $size;
    /**
     * @var string[] $metadata
     */
    private array $metadata;


    /**
     * StreamOptions constructor.
     *
     * @param \string[] $metadata   Additional metadata for the stream.
     * @param int       $size       The known stream size in byte. -1 indicates an unknown size.
     */
    public function __construct(array $metadata = [], int $size = self::UNKNOWN_STREAM_SIZE)
    {
        $this->size = $size;
        $this->metadata = $metadata;
    }


    /**
     * @return int
     */
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
