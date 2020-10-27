<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision;

use DateTimeImmutable;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class NullRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullRevision implements Revision
{

    /**
     * @var ResourceIdentification
     */
    private $identification;


    /**
     * NullRevision constructor.
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
     * @inheritDoc
     */
    public function getVersionNumber() : int
    {
        return 0;
    }


    /**
     * @inheritDoc
     */
    public function getCreationDate() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }


    /**
     * @inheritDoc
     */
    public function getInformation() : Information
    {
        return new FileInformation();
    }


    public function setInformation(Information $information)
    {
    }


    public function setUnavailable() : void
    {
        // do nothing
    }


    /**
     * @inheritDoc
     */
    public function isAvailable() : bool
    {
        return false;
    }
}
