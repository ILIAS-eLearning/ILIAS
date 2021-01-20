<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class CloneRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CloneRevision implements Revision
{

    /**
     * @var bool
     */
    protected $available = true;
    /**
     * @var ResourceIdentification
     */
    protected $identification;
    /**
     * @var int
     */
    protected $version_number = 0;
    /**
     * @var FileInformation
     */
    protected $information;
    /**
     * @var int
     */
    protected $owner_id = 0;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var int
     */
    protected $revision_number_to_clone;

    /**
     * Revision constructor.
     * @param ResourceIdentification $identification
     */
    public function __construct(ResourceIdentification $identification, int $revision_number_to_clone)
    {
        $this->identification = $identification;
        $this->revision_number_to_clone = $revision_number_to_clone;
    }

    /**
     * @inheritDoc
     */
    public function getIdentification() : ResourceIdentification
    {
        return $this->identification;
    }

    /**
     * @param int $version_number
     */
    public function setVersionNumber(int $version_number) : void
    {
        $this->version_number = $version_number;
    }

    public function getVersionNumber() : int
    {
        return $this->version_number;
    }

    /**
     * @inheritDoc
     */
    public function getInformation() : Information
    {
        return $this->information ?? new FileInformation();
    }

    /**
     * @param Information $information
     */
    public function setInformation(Information $information)
    {
        $this->information = $information;
    }

    /**
     * @inheritDoc
     */
    public function setUnavailable() : void
    {
        $this->available = false;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable() : bool
    {
        return $this->available;
    }

    /**
     * @return int
     */
    public function getOwnerId() : int
    {
        return $this->owner_id;
    }

    /**
     * @param int $owner_id
     * @return FileRevision
     */
    public function setOwnerId(int $owner_id) : FileRevision
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    /**
     * @param string $title
     * @return $this|Revision
     */
    public function setTitle(string $title) : Revision
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getRevisionNumberToClone() : int
    {
        return $this->revision_number_to_clone;
    }

    /**
     * @param int $revision_number_to_clone
     * @return CloneRevision
     */
    public function setRevisionNumberToClone(int $revision_number_to_clone) : CloneRevision
    {
        $this->revision_number_to_clone = $revision_number_to_clone;
        return $this;
    }

}
