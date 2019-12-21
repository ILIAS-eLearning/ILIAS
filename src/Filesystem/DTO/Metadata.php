<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\DTO;

use ILIAS\Filesystem\MetadataType;

/**
 * Class Metadata
 *
 * This class holds all default metadata send by the filesystem adapters.
 * Metadata instances are immutable.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class Metadata
{

    /**
     * @var string $path
     */
    private $path;
    /**
     * @var string $type
     */
    private $type;


    /**
     * Metadata constructor.
     *
     * Creates a new instance of the Metadata.
     *
     * @internal
     *
     * @param string $path     The path to the parent of the file or directory.
     * @param string $type     The file type which can be -> file or directory.
     *                         Please note that only constants defined in the MetadataType interface are considered as valid.
     *
     * @throws \InvalidArgumentException Thrown if the type of the given arguments are not correct.
     *
     * @see MetadataType
     */
    public function __construct(string $path, string $type)
    {
        if ($type !== MetadataType::FILE && $type !== MetadataType::DIRECTORY) {
            throw new \InvalidArgumentException("The metadata type must be FILE or DIRECTORY but \"$type\" was given.");
        }

        $this->path = $path;
        $this->type = $type;
    }

    /**
     * The path to the file or directory.
     *
     * @return string
     * @since 5.3
     */
    public function getPath() : string
    {
        return $this->path;
    }


    /**
     * The type of the subject which can be FILE or DIRECTORY.
     *
     * Use isDir or isFile in consumer-code and do not compare yourself against
     * MetadataType::DIRECTORY or MetadataType::FILE
     *
     * @return string
     * @since 5.3
     * @internal
     *
     * @see MetadataType
     */
    public function getType() : string
    {
        return $this->type;
    }


    /**
     * The path is a directory
     *
     * @return bool
     * @since 5.3
     */
    public function isDir() : bool
    {
        return (strcmp($this->getType(), MetadataType::DIRECTORY) === 0);
    }


    /**
     * The path is a file
     *
     * @return bool
     * @since 5.3
     */
    public function isFile() : bool
    {
        return (strcmp($this->getType(), MetadataType::FILE) === 0);
    }
}
