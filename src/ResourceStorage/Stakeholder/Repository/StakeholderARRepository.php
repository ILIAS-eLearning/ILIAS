<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Stakeholder\Repository;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface StakeholderARRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 * @deprecated
 */
class StakeholderARRepository implements StakeholderRepository
{

    public function register(ResourceIdentification $i, ResourceStakeholder $s) : bool
    {
        $internal = $this->getInternalID($i, $s);
        $r = ARStakeholder::find($internal);
        if (!$r instanceof ARStakeholder) {
            $r = new ARStakeholder();
            $r->setInternal($internal);
            $r->setStakeholderId($s->getId());
            $r->setStakeholderClass($s->getFullyQualifiedClassName());
            $r->setIdentification($i->serialize());
            $r->create();
        }
        return true;
    }

    public function deregister(ResourceIdentification $i, ResourceStakeholder $s) : bool
    {
        $internal = $this->getInternalID($i, $s);
        $r = ARStakeholder::find($internal);
        if ($r instanceof ARStakeholder) {
            $r->delete();
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getStakeholders(ResourceIdentification $i) : array
    {
        $stakeholders = [];
        /**
         * @var $item ARStakeholder
         */
        foreach (ARStakeholder::where(['identification' => $i->serialize()])->get() as $item) {
            $class_name = $item->getStakeholderClass();
            $stakeholders[] = new $class_name();
        }
        return $stakeholders;
    }

    /**
     * @param ResourceIdentification $r
     * @param ResourceStakeholder    $s
     * @return string
     */
    protected function getInternalID(ResourceIdentification $r, ResourceStakeholder $s) : string
    {
        return $r->serialize() . '_' . $s->getId();
    }

    public function getNamesForLocking() : array
    {
        return [
            (new ARStakeholder())->getConnectorContainerName()
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
