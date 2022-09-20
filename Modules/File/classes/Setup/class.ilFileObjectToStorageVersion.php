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

/**
 * Class ilFileObjectToStorageVersion
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStorageVersion
{
    protected int $version;
    protected string $path;
    protected string  $file_name;
    protected string  $title;
    protected string $action;
    protected int $owner = 6;
    protected int $creation_date_timestamp = 0;

    /**
     * ilFileObjectToStorageVersion constructor.
     * @param int    $version
     * @param string $path
     * @param string $filename
     * @param string $title
     * @param string $action
     * @param int    $owner
     * @param int    $creation_date_timestamp
     */
    public function __construct(
        int $version,
        string $path,
        string $filename,
        string $title,
        string $action,
        int $creation_date_timestamp,
        int $owner = 6
    ) {
        $this->version = $version;
        $this->path = $path;
        $this->file_name = $filename;
        $this->title = $title;
        $this->action = $action;
        $this->owner = $owner;
        $this->creation_date_timestamp = $creation_date_timestamp;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getOwner(): int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getCreationDateTimestamp(): int
    {
        return $this->creation_date_timestamp;
    }
}
