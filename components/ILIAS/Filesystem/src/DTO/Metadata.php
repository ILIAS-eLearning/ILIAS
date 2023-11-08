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

namespace ILIAS\Filesystem\DTO;

use ILIAS\Filesystem\MetadataType;

/**
 * This class holds all default metadata send by the filesystem adapters.
 * Metadata instances are immutable.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class Metadata
{
    private string $type;

    /**
     * Metadata constructor.
     *
     * Creates a new instance of the Metadata.
     *
     * @param string $path     The path to the parent of the file or directory.
     * @param string $type     The file type which can be -> file or directory.
     *                         Please note that only constants defined in the MetadataType interface are considered as valid.
     *
     * @throws \InvalidArgumentException Thrown if the type of the given arguments are not correct.
     *
     * @internal
     *
     * @see MetadataType
     */
    public function __construct(private string $path, string $type)
    {
        if ($type !== MetadataType::FILE && $type !== MetadataType::DIRECTORY) {
            throw new \InvalidArgumentException("The metadata type must be FILE or DIRECTORY but \"$type\" was given.");
        }
        $this->type = $type;
    }

    /**
     * The path to the file or directory.
     *
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * The type of the subject which can be FILE or DIRECTORY.
     *
     * Use isDir or isFile in consumer-code and do not compare yourself against
     * MetadataType::DIRECTORY or MetadataType::FILE
     *
     * @internal
     * @see MetadataType
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * The path is a directory
     *
     */
    public function isDir(): bool
    {
        return (strcmp($this->getType(), MetadataType::DIRECTORY) === 0);
    }

    /**
     * The path is a file
     *
     */
    public function isFile(): bool
    {
        return (strcmp($this->getType(), MetadataType::FILE) === 0);
    }
}
