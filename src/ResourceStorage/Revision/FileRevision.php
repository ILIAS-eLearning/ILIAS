<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class FileRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class FileRevision implements Revision
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
     * Revision constructor.
     *
     * @param ResourceIdentification $identification
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
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
}
