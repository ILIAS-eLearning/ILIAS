<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Information;

use DateTimeImmutable;
use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;

/**
 * Class Information
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileInformation implements Information
{

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $suffix = '';
    /**
     * @var string
     */
    protected $mime_type = '';
    /**
     * @var int
     */
    protected $size = 0;
    /**
     * @var DateTimeImmutable
     */
    protected $creation_date;



    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @param string $title
     *
     * @return FileInformation
     */
    public function setTitle(string $title) : FileInformation
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @return string
     */
    public function getSuffix() : string
    {
        return $this->suffix;
    }


    /**
     * @param string $suffix
     *
     * @return FileInformation
     */
    public function setSuffix(string $suffix) : FileInformation
    {
        $this->suffix = $suffix;

        return $this;
    }


    /**
     * @return string
     */
    public function getMimeType() : string
    {
        return $this->mime_type;
    }


    /**
     * @param string $mime_type
     *
     * @return FileInformation
     */
    public function setMimeType(string $mime_type) : FileInformation
    {
        $this->mime_type = $mime_type;

        return $this;
    }


    /**
     * @return int
     */
    public function getSize() : int
    {
        return $this->size;
    }


    /**
     * @param int $size
     *
     * @return FileInformation
     */
    public function setSize(int $size) : FileInformation
    {
        $this->size = $size;

        return $this;
    }


    /**
     * @return DateTimeImmutable
     */
    public function getCreationDate() : DateTimeImmutable
    {
        return $this->creation_date;
    }


    /**
     * @param DateTimeImmutable $creation_date
     */
    public function setCreationDate(DateTimeImmutable $creation_date) : void
    {
        $this->creation_date = $creation_date;
    }
}
