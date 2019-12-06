<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Information\Repository;

use ILIAS\MainMenu\Storage\Information\FileInformation;
use ILIAS\MainMenu\Storage\Information\Information;
use ILIAS\MainMenu\Storage\Revision\Revision;

/**
 * Interface InformationRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
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
     *
     * @return string
     */
    protected function getInternalID(Revision $revision) : string
    {
        return $revision->getIdentification()->serialize() . '_' . $revision->getVersionNumber();
    }
}
