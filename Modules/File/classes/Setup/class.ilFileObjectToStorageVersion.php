<?php

/**
 * Class ilFileObjectToStorageVersion
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStorageVersion
{
    /**
     * @var int
     */
    protected $version;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $file_name;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var int
     */
    protected $owner = 6;
    /**
     * @var int
     */
    protected $creation_date_timestamp = 0;

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
    public function getVersion() : int
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFileName() : string
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getOwner() : int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getCreationDateTimestamp() : int
    {
        return $this->creation_date_timestamp;
    }

}
