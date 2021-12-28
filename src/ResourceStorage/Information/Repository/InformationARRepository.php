<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Information\Repository;

use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * Interface InformationRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 * @deprecated
 */
class InformationARRepository implements InformationRepository
{

    /**
     * @inheritDoc
     */
    public function blank()
    {
        return new FileInformation();
    }

    /**
     * @inheritDoc
     */
    public function store(Information $information, Revision $revision) : void
    {
        $internal = $this->getInternalID($revision);
        $r = ARInformation::find($internal);
        if (!$r instanceof ARInformation) {
            $r = new ARInformation();
            $r->setInternal($internal);
            $r->setIdentification($revision->getIdentification()->serialize());
            $r->create();
        }
        $r->setTitle($information->getTitle());
        $r->setMimeType($information->getMimeType());
        $r->setSize($information->getSize());
        $r->setSuffix($information->getSuffix());
        $r->setCreationDate($information->getCreationDate()->getTimestamp());
        $r->update();
    }

    /**
     * @inheritDoc
     */
    public function get(Revision $revision) : Information
    {
        $internal = $this->getInternalID($revision);

        $r = ARInformation::find($internal);
        $i = new FileInformation();
        if ($r instanceof ARInformation) {
            $i->setTitle($r->getTitle());
            $i->setSize($r->getSize());
            $i->setMimeType($r->getMimeType());
            $i->setSuffix($r->getSuffix());
            $i->setCreationDate((new \DateTimeImmutable())->setTimestamp($r->getCreationDate() ?? 0));
        }

        return $i;
    }

    public function delete(Information $information, Revision $revision) : void
    {
        $internal = $this->getInternalID($revision);
        $r = ARInformation::find($internal);
        if ($r instanceof ARInformation) {
            $r->delete();
        }
    }

    /**
     * @param Revision $revision
     * @return string
     */
    protected function getInternalID(Revision $revision) : string
    {
        return $revision->getIdentification()->serialize() . '_' . $revision->getVersionNumber();
    }

    public function getNamesForLocking() : array
    {
        return [
            (new ARInformation())->getConnectorContainerName()
        ];
    }

    public function preload(array $identification_strings) : void
    {
        // noting to to
    }

    public function populateFromArray(array $data) : void
    {
        // noting to to
    }

}
